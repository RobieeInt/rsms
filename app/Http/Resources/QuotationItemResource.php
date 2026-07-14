<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quotation_id' => $this->quotation_id,
            'finding_id' => $this->finding_id,
            'description' => $this->description,
            'detail' => $this->detail,
            'quantity' => (float) $this->quantity,
            'unit' => $this->unit,
            'unit_price' => (float) $this->unit_price,
            'discount_amount' => (float) $this->discount_amount,
            'total_price' => (float) $this->total_price,
            'sort_order' => $this->sort_order,
        ];
    }
}
