<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanySettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_name' => $this->company_name,
            'logo' => $this->logo,
            'logo_url' => $this->logo_url,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'bank_name' => $this->bank_name,
            'bank_account_number' => $this->bank_account_number,
            'bank_account_holder' => $this->bank_account_holder,
            'website' => $this->website,
            'tax_number' => $this->tax_number,
        ];
    }
}
