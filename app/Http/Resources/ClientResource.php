<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_name' => $this->company_name,
            'pic_name' => $this->pic_name,
            'pic_email' => $this->pic_email,
            'pic_phone' => $this->pic_phone,
            'address' => $this->address,
            'monthly_retainer_fee' => (float) $this->monthly_retainer_fee,
            'invoice_due_date' => $this->invoice_due_date,
            'is_active' => $this->is_active,
            'notes' => $this->notes,
            'health_score' => (float) $this->health_score,
            'health_status' => $this->health_status,
            'assets_count' => $this->whenCounted('assets'),
            'schedules_count' => $this->whenCounted('schedules'),
            'findings_count' => $this->whenCounted('findings'),
            'invoices_count' => $this->whenCounted('invoices'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
