<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'client' => new ClientResource($this->whenLoaded('client')),
            'quotation_id' => $this->quotation_id,
            'installment_number' => $this->installment_number,
            'created_by' => $this->created_by,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'invoice_number' => $this->invoice_number,
            'type' => $this->type,
            'invoice_date' => $this->invoice_date?->format('Y-m-d'),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'subtotal' => (float) $this->subtotal,
            'tax_percent' => (float) $this->tax_percent,
            'tax_amount' => (float) $this->tax_amount,
            'discount_amount' => (float) $this->discount_amount,
            'total_amount' => (float) $this->total_amount,
            'notes' => $this->notes,
            'status' => $this->status,
            'payment_date' => $this->payment_date?->format('Y-m-d'),
            'payment_method' => $this->payment_method,
            'payment_proof' => $this->payment_proof,
            'payment_notes' => $this->payment_notes,
            'is_overdue' => $this->isOverdue(),
            'pdf_url' => $this->getPublicPdfUrl(),
            'items' => InvoiceItemResource::collection($this->whenLoaded('items')),
            'send_logs' => InvoiceSendLogResource::collection($this->whenLoaded('sendLogs')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
