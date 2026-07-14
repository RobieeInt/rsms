<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceSendLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_id' => $this->invoice_id,
            'type' => $this->type,
            'type_label' => $this->typeLabel(),
            'sent_to' => $this->sent_to,
            'channel' => $this->channel,
            'sent_at' => $this->sent_at?->toIso8601String(),
        ];
    }
}
