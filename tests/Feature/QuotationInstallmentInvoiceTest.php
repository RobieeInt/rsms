<?php

namespace Tests\Feature;

use App\Livewire\Invoices\InvoiceForm;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceSendLog;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QuotationInstallmentInvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    protected function makeApprovedQuotation(float $total = 10_000_000): Quotation
    {
        $client = Client::create([
            'company_name' => 'PT Contoh HRIS',
            'pic_name' => 'Budi',
            'pic_email' => 'budi@example.com',
            'invoice_due_date' => 14,
        ]);

        $quotation = Quotation::create([
            'client_id' => $client->id,
            'created_by' => $this->admin->id,
            'quotation_number' => Quotation::generateNumber(),
            'date' => now()->toDateString(),
            'expiry_date' => now()->addDays(30)->toDateString(),
            'subtotal' => $total,
            'tax_percent' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => $total,
            'status' => 'approved',
            'approval_token' => Quotation::generateToken(),
            'approved_at' => now(),
            'approved_by_name' => 'Budi',
        ]);

        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'description' => 'Pengembangan Aplikasi HRIS',
            'quantity' => 1,
            'unit' => 'paket',
            'unit_price' => $total,
            'total_price' => $total,
            'sort_order' => 0,
        ]);

        return $quotation;
    }

    public function test_can_create_partial_installment_invoice_leaving_remaining_balance(): void
    {
        $quotation = $this->makeApprovedQuotation();

        $response = $this->actingAs($this->admin)
            ->postJson("/api/quotations/{$quotation->id}/convert-to-invoice", ['amount' => 3_000_000]);

        $response->assertCreated();
        $response->assertJsonPath('data.installment_number', 1);
        $this->assertEquals(3000000, $response->json('data.total_amount'));

        $this->assertEquals(7_000_000, $quotation->fresh()->remainingBalance());
    }

    public function test_can_create_second_installment_invoice(): void
    {
        $quotation = $this->makeApprovedQuotation();

        $this->actingAs($this->admin)
            ->postJson("/api/quotations/{$quotation->id}/convert-to-invoice", ['amount' => 3_000_000])
            ->assertCreated();

        $response = $this->actingAs($this->admin)
            ->postJson("/api/quotations/{$quotation->id}/convert-to-invoice", ['amount' => 4_000_000]);

        $response->assertCreated();
        $response->assertJsonPath('data.installment_number', 2);
        $this->assertEquals(3_000_000, $quotation->fresh()->remainingBalance());
    }

    public function test_rejects_installment_amount_exceeding_remaining_balance(): void
    {
        $quotation = $this->makeApprovedQuotation();

        $this->actingAs($this->admin)
            ->postJson("/api/quotations/{$quotation->id}/convert-to-invoice", ['amount' => 3_000_000])
            ->assertCreated();
        $this->actingAs($this->admin)
            ->postJson("/api/quotations/{$quotation->id}/convert-to-invoice", ['amount' => 4_000_000])
            ->assertCreated();

        $response = $this->actingAs($this->admin)
            ->postJson("/api/quotations/{$quotation->id}/convert-to-invoice", ['amount' => 3_000_001]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'Jumlah invoice melebihi sisa saldo penawaran (Rp 3.000.000).']);
    }

    public function test_rejects_conversion_when_quotation_not_approved(): void
    {
        $quotation = $this->makeApprovedQuotation();
        $quotation->update(['status' => 'draft']);

        $response = $this->actingAs($this->admin)
            ->postJson("/api/quotations/{$quotation->id}/convert-to-invoice", []);

        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'Hanya penawaran yang disetujui yang bisa diubah ke invoice.']);
    }

    public function test_rejects_conversion_when_fully_invoiced(): void
    {
        $quotation = $this->makeApprovedQuotation(3_000_000);

        $this->actingAs($this->admin)
            ->postJson("/api/quotations/{$quotation->id}/convert-to-invoice", [])
            ->assertCreated();

        $response = $this->actingAs($this->admin)
            ->postJson("/api/quotations/{$quotation->id}/convert-to-invoice", []);

        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'Penawaran ini sudah ditagih penuh.']);
    }

    public function test_send_immediately_flag_marks_invoice_sent_and_creates_send_log(): void
    {
        Notification::fake();

        $quotation = $this->makeApprovedQuotation();

        $response = $this->actingAs($this->admin)->postJson(
            "/api/quotations/{$quotation->id}/convert-to-invoice",
            ['amount' => 3_000_000, 'send_immediately' => true]
        );

        $response->assertCreated();
        $response->assertJsonPath('data.status', 'sent');

        $invoice = Invoice::findOrFail($response->json('data.id'));
        $this->assertEquals(1, InvoiceSendLog::where('invoice_id', $invoice->id)->count());
    }

    public function test_can_delete_sent_invoice_but_not_paid_invoice(): void
    {
        $quotation = $this->makeApprovedQuotation();

        $sent = $this->actingAs($this->admin)
            ->postJson("/api/quotations/{$quotation->id}/convert-to-invoice", ['amount' => 3_000_000, 'send_immediately' => true])
            ->json('data.id');

        $this->actingAs($this->admin)->deleteJson("/api/invoices/{$sent}")->assertOk();
        $this->assertNull(Invoice::find($sent));
        $this->assertEquals(10_000_000, $quotation->fresh()->remainingBalance());

        $paid = $this->actingAs($this->admin)
            ->postJson("/api/quotations/{$quotation->id}/convert-to-invoice", ['amount' => 3_000_000])
            ->json('data.id');
        Invoice::whereKey($paid)->update(['status' => 'paid']);

        $this->actingAs($this->admin)->deleteJson("/api/invoices/{$paid}")->assertStatus(422);
        $this->actingAs($this->admin)->putJson("/api/invoices/{$paid}", ['notes' => 'x'])->assertStatus(422);
        $this->assertNotNull(Invoice::find($paid));
    }

    public function test_approval_with_payment_terms_bills_only_first_term(): void
    {
        Notification::fake();

        $quotation = $this->makeApprovedQuotation();
        $quotation->update([
            'status' => 'sent',
            'payment_terms' => [
                ['label' => 'DP', 'percent' => 30],
                ['label' => 'Progress', 'percent' => 40],
                ['label' => 'Pelunasan', 'percent' => 30],
            ],
        ]);

        $this->postJson("/api/quotation/approve/{$quotation->approval_token}", [
            'action' => 'approved',
            'approved_by_name' => 'Budi',
        ])->assertOk();

        $quotation->refresh();
        $this->assertEquals(1, $quotation->invoices()->count());
        $invoice = $quotation->invoices()->first();
        $this->assertEquals(1, $invoice->installment_number);
        $this->assertEquals(3_000_000, (float) $invoice->total_amount);
        $this->assertEquals('sent', $invoice->status);
        $this->assertEquals(7_000_000, $quotation->remainingBalance());

        // Termin berikutnya default ke skema (Progress 40%)...
        $next = $quotation->nextTerm();
        $this->assertSame('Progress', $next['label']);
        $this->assertEquals(4_000_000, $next['amount']);

        // ...tapi bisa diturunin sesuai kemampuan klien.
        $this->actingAs($this->admin)
            ->postJson("/api/quotations/{$quotation->id}/convert-to-invoice", ['amount' => 2_000_000])
            ->assertCreated()
            ->assertJsonPath('data.installment_number', 2);

        // Termin terakhir di skema selalu nagih sisa saldo penuh.
        $next = $quotation->fresh()->nextTerm();
        $this->assertSame('Pelunasan', $next['label']);
        $this->assertEquals(5_000_000, $next['amount']);

        $this->actingAs($this->admin)
            ->postJson("/api/quotations/{$quotation->id}/convert-to-invoice", [])
            ->assertCreated();
        $this->assertEquals(0, $quotation->fresh()->remainingBalance());
    }

    public function test_quotation_form_rejects_terms_not_summing_to_100(): void
    {
        $quotation = $this->makeApprovedQuotation();
        $quotation->update(['status' => 'draft']);

        Livewire::actingAs($this->admin)->test(\App\Livewire\Quotations\QuotationForm::class, ['quotation' => $quotation])
            ->set('payment_terms', [['label' => 'DP', 'percent' => 30], ['label' => 'Pelunasan', 'percent' => 60]])
            ->call('save')
            ->assertHasErrors('payment_terms');

        Livewire::actingAs($this->admin)->test(\App\Livewire\Quotations\QuotationForm::class, ['quotation' => $quotation])
            ->set('payment_terms', [['label' => 'DP', 'percent' => 30], ['label' => 'Pelunasan', 'percent' => 70]])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertCount(2, $quotation->fresh()->scheduledTerms());
    }

    public function test_quotation_show_modal_prefills_next_term_and_allows_payoff(): void
    {
        $quotation = $this->makeApprovedQuotation();
        $quotation->update(['payment_terms' => [['label' => 'DP', 'percent' => 50], ['label' => 'Pelunasan', 'percent' => 50]]]);

        Livewire::actingAs($this->admin)->test(\App\Livewire\Quotations\QuotationShow::class, ['quotation' => $quotation])
            ->call('openInvoiceModal')
            ->assertSet('newInvoiceAmount', '5000000.00')
            ->assertSet('newInvoicePercent', '50')
            ->set('newInvoicePercent', '20')
            ->assertSet('newInvoiceAmount', '2000000.00')
            ->call('payOffRemaining')
            ->assertSet('newInvoiceAmount', '10000000.00')
            ->call('createInvoice')
            ->assertHasNoErrors();

        $this->assertEquals(0, $quotation->fresh()->remainingBalance());
    }

    public function test_public_approval_still_creates_single_full_invoice_unchanged(): void
    {
        $client = Client::create([
            'company_name' => 'PT Full Payment',
            'pic_name' => 'Sari',
            'pic_email' => 'sari@example.com',
            'invoice_due_date' => 14,
        ]);

        $quotation = Quotation::create([
            'client_id' => $client->id,
            'created_by' => $this->admin->id,
            'quotation_number' => Quotation::generateNumber(),
            'date' => now()->toDateString(),
            'expiry_date' => now()->addDays(30)->toDateString(),
            'subtotal' => 5_000_000,
            'tax_percent' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 5_000_000,
            'status' => 'sent',
            'approval_token' => Quotation::generateToken(),
        ]);

        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'description' => 'Jasa Konsultasi',
            'quantity' => 1,
            'unit' => 'paket',
            'unit_price' => 5_000_000,
            'total_price' => 5_000_000,
            'sort_order' => 0,
        ]);

        $response = $this->postJson("/api/quotation/approve/{$quotation->approval_token}", [
            'action' => 'approved',
            'approved_by_name' => 'Sari',
        ]);

        $response->assertOk();

        $quotation->refresh();
        $this->assertEquals(1, $quotation->invoices()->count());
        $invoice = $quotation->invoices()->first();
        $this->assertEquals('quotation', $invoice->type);
        $this->assertEquals(5_000_000, (float) $invoice->total_amount);
        $this->assertNull($invoice->installment_number);
    }

    public function test_invoice_form_from_quotation_creates_installment(): void
    {
        $quotation = $this->makeApprovedQuotation();

        Livewire::actingAs($this->admin)->test(InvoiceForm::class)
            ->set('type', 'quotation')
            ->set('quotation_id', $quotation->id)
            ->assertSet('termin_amount', '10000000.00')
            ->set('termin_amount', '4000000')
            ->set('termin_description', 'Termin 1 - DP 40%')
            ->call('save')
            ->assertHasNoErrors();

        $invoice = $quotation->invoices()->firstOrFail();
        $this->assertEquals(1, $invoice->installment_number);
        $this->assertEquals($quotation->client_id, $invoice->client_id);
        $this->assertEquals(4_000_000, (float) $invoice->total_amount);
        $this->assertEquals(6_000_000, $quotation->fresh()->remainingBalance());
    }

    public function test_invoice_form_from_quotation_rejects_amount_over_remaining(): void
    {
        $quotation = $this->makeApprovedQuotation();

        Livewire::actingAs($this->admin)->test(InvoiceForm::class)
            ->set('type', 'quotation')
            ->set('quotation_id', $quotation->id)
            ->set('termin_amount', '10000001')
            ->call('save')
            ->assertHasErrors('termin_amount');

        Livewire::actingAs($this->admin)->test(InvoiceForm::class)
            ->set('type', 'quotation')
            ->call('save')
            ->assertHasErrors('quotation_id');

        $this->assertEquals(0, $quotation->invoices()->count());
    }

    public function test_invoice_shows_remaining_balance_after_each_termin(): void
    {
        $quotation = $this->makeApprovedQuotation();

        $first = $this->actingAs($this->admin)
            ->postJson("/api/quotations/{$quotation->id}/convert-to-invoice", ['amount' => 3_000_000])
            ->json('data.id');
        $second = $this->actingAs($this->admin)
            ->postJson("/api/quotations/{$quotation->id}/convert-to-invoice", ['amount' => 2_000_000])
            ->json('data.id');
        Invoice::whereKey($first)->update(['status' => 'paid']);

        $this->actingAs($this->admin)->getJson("/api/invoices/{$first}")
            ->assertJsonPath('data.quotation_summary.billed_before', 0)
            ->assertJsonPath('data.quotation_summary.remaining_after', 7000000);

        $this->actingAs($this->admin)->getJson("/api/invoices/{$second}")
            ->assertJsonPath('data.quotation_summary.quotation_total', 10000000)
            ->assertJsonPath('data.quotation_summary.billed_before', 3000000)
            ->assertJsonPath('data.quotation_summary.this_invoice', 2000000)
            ->assertJsonPath('data.quotation_summary.remaining_after', 5000000)
            ->assertJsonPath('data.quotation_summary.this_percent', 20)
            ->assertJsonPath('data.quotation_summary.billed_before_percent', 30)
            ->assertJsonPath('data.quotation_summary.remaining_after_percent', 50)
            ->assertJsonPath('data.quotation_summary.paid_total', 3000000)
            ->assertJsonPath('data.quotation_summary.outstanding', 7000000);

        // Web detail & PDF render the block.
        $this->actingAs($this->admin)->get(route('invoices.show', $second))
            ->assertOk()->assertSee('Sisa Setelah Invoice Ini')->assertSee('Termin ke-2 · 20%');
        $this->actingAs($this->admin)->get(route('pdf.invoice', $second))->assertOk();
        $company = \App\Models\CompanySetting::getSettings();
        $firstPdf = view('pdfs.invoice', ['invoice' => Invoice::with(['client', 'items', 'quotation'])->find($first), 'company' => $company])->render();
        $this->assertStringNotContainsString('Sudah Ditagih Sebelumnya', $firstPdf);
        $this->assertStringContainsString('Sisa Pembayaran', $firstPdf);
        $this->assertStringContainsString('Termin ke-2 (20% dari total proyek)', view('pdfs.invoice', ['invoice' => Invoice::with(['client', 'items', 'quotation'])->find($second), 'company' => \App\Models\CompanySetting::getSettings()])->render());
    }
}
