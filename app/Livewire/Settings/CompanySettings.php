<?php

namespace App\Livewire\Settings;

use App\Models\CompanySetting;
use Livewire\Component;
use Livewire\WithFileUploads;

class CompanySettings extends Component
{
    use WithFileUploads;

    public string $company_name = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';
    public string $bank_name = '';
    public string $bank_account_number = '';
    public string $bank_account_holder = '';
    public string $website = '';
    public string $tax_number = '';
    public $logo = null;

    public function mount(): void
    {
        $settings = CompanySetting::getSettings();
        $this->company_name = $settings->company_name ?? '';
        $this->email = $settings->email ?? '';
        $this->phone = $settings->phone ?? '';
        $this->address = $settings->address ?? '';
        $this->bank_name = $settings->bank_name ?? '';
        $this->bank_account_number = $settings->bank_account_number ?? '';
        $this->bank_account_holder = $settings->bank_account_holder ?? '';
        $this->website = $settings->website ?? '';
        $this->tax_number = $settings->tax_number ?? '';
    }

    public function save(): void
    {
        $this->validate([
            'company_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:30',
            'logo' => 'nullable|image|max:2048',
        ]);

        $settings = CompanySetting::getSettings();

        $data = [
            'company_name' => $this->company_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'bank_name' => $this->bank_name,
            'bank_account_number' => $this->bank_account_number,
            'bank_account_holder' => $this->bank_account_holder,
            'website' => $this->website,
            'tax_number' => $this->tax_number,
        ];

        if ($this->logo) {
            $data['logo'] = $this->logo->store('logos', 'public');
            $this->logo = null;
        }

        $settings->update($data);
        $this->dispatch('notify', message: 'Company settings saved.', type: 'success');
    }

    public function render()
    {
        $settings = CompanySetting::getSettings();
        return view('livewire.settings.company-settings', compact('settings'))
            ->layout('layouts.app', ['title' => 'Company Settings']);
    }
}
