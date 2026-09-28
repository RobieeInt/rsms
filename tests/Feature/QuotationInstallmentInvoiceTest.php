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

    public function test_cannot_delete_non_draft_invoice(): void
    {
        $quotation = $this->makeApprovedQuotation();

        $create = $this->actingAs($this->admin)
            ->postJson("/api/quotations/{$quotation->id}/convert-to-invoice", ['amount' => 3_000_000, 'send_immediately' => true]);

        $invoiceId = $create->json('data.id');

        $response = $this->actingAs($this->admin)->deleteJson("/api/invoices/{$invoiceId}");

        $response->assertStatus(422);
        $this->assertNotNull(Invoice::find($invoiceId));
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
}
