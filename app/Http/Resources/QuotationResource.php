<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'client' => new ClientResource($this->whenLoaded('client')),
            'created_by' => $this->created_by,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'quotation_number' => $this->quotation_number,
            'date' => $this->date?->format('Y-m-d'),
            'expiry_date' => $this->expiry_date?->format('Y-m-d'),
            'subtotal' => (float) $this->subtotal,
            'tax_percent' => (float) $this->tax_percent,
            'tax_amount' => (float) $this->tax_amount,
            'discount_amount' => (float) $this->discount_amount,
            'total_amount' => (float) $this->total_amount,
            'notes' => $this->notes,
            'status' => $this->status,
            'approval_token' => $this->approval_token,
            'approval_url' => $this->getApprovalUrl(),
            'pdf_url' => $this->getPublicPdfUrl(),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'approved_by_name' => $this->approved_by_name,
            'approval_notes' => $this->approval_notes,
            'items' => QuotationItemResource::collection($this->whenLoaded('items')),
            'invoices' => InvoiceResource::collection($this->whenLoaded('invoices')),
            'total_invoiced' => (float) $this->totalInvoiced(),
            'remaining_balance' => (float) $this->remainingBalance(),
            'payment_terms' => $this->scheduledTerms(),
            'next_term' => $this->nextTerm(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
