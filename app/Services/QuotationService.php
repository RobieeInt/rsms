<?php

namespace App\Services;

use App\Models\Finding;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Notifications\QuotationSentNotification;
use Illuminate\Support\Facades\DB;

class QuotationService
{
    public function create(array $data, int $createdBy): Quotation
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $items = $data['items'] ?? [];
            unset($data['items']);

            $subtotal = collect($items)->sum('total_price');
            $taxAmount = $subtotal * (($data['tax_percent'] ?? 0) / 100);
            $total = $subtotal + $taxAmount - ($data['discount_amount'] ?? 0);

            $quotation = Quotation::create([
                ...$data,
                'created_by' => $createdBy,
                'quotation_number' => Quotation::generateNumber(),
                'approval_token' => Quotation::generateToken(),
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $total,
            ]);

            foreach ($items as $index => $item) {
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'] ?? 'unit',
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                    'sort_order' => $index,
                ]);
            }

            return $quotation;
        });
    }

    public function createFromFindings(array $findingIds, array $data, int $createdBy): Quotation
    {
        $findings = Finding::whereIn('id', $findingIds)->with('recommendations')->get();

        $items = $findings->map(function ($finding) {
            return [
                'finding_id' => $finding->id,
                'description' => $finding->title . ($finding->recommendations->first()
                    ? ': ' . $finding->recommendations->first()->recommendation
                    : ''),
                'quantity' => 1,
                'unit' => 'unit',
                'unit_price' => 0,
                'total_price' => 0,
            ];
        })->toArray();

        return $this->create(array_merge($data, ['items' => $items]), $createdBy);
    }

    public function send(Quotation $quotation): void
    {
        $quotation->update(['status' => 'sent']);
        $quotation->client->notifyNow(new QuotationSentNotification($quotation));
    }
}
