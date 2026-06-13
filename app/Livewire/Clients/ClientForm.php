<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Livewire\Component;
use Livewire\Attributes\Validate;

class ClientForm extends Component
{
    public ?Client $client = null;

    #[Validate('required|string|max:255')]
    public string $company_name = '';

    #[Validate('required|string|max:255')]
    public string $pic_name = '';

    #[Validate('required|email|max:255')]
    public string $pic_email = '';

    #[Validate('nullable|string|max:50')]
    public string $pic_phone = '';

    #[Validate('nullable|string')]
    public string $address = '';

    #[Validate('required|numeric|min:0')]
    public string $monthly_retainer_fee = '0';

    #[Validate('nullable|integer|min:1|max:28')]
    public ?int $invoice_due_date = null;

    #[Validate('boolean')]
    public bool $is_active = true;

    #[Validate('nullable|string')]
    public string $notes = '';

    public function mount(?Client $client = null): void
    {
        $client = $client ?? (request()->route('client') instanceof Client ? request()->route('client') : null);

        if ($client && $client->exists) {
            $this->client = $client;
            $this->company_name = $client->company_name;
            $this->pic_name = $client->pic_name;
            $this->pic_email = $client->pic_email;
            $this->pic_phone = $client->pic_phone ?? '';
            $this->address = $client->address ?? '';
            $this->monthly_retainer_fee = (string) $client->monthly_retainer_fee;
            $this->invoice_due_date = $client->invoice_due_date;
            $this->is_active = $client->is_active;
            $this->notes = $client->notes ?? '';
        }
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'company_name' => $this->company_name,
            'pic_name' => $this->pic_name,
            'pic_email' => $this->pic_email,
            'pic_phone' => $this->pic_phone ?: null,
            'address' => $this->address ?: null,
            'monthly_retainer_fee' => (float) $this->monthly_retainer_fee,
            'invoice_due_date' => $this->invoice_due_date ?? 30,
            'is_active' => $this->is_active,
            'notes' => $this->notes ?: null,
        ];

        if ($this->client && $this->client->exists) {
            $this->client->update($data);
            session()->flash('success', 'Client updated successfully.');
        } else {
            $data['health_score'] = 100;
            $data['health_status'] = 'healthy';
            Client::create($data);
            session()->flash('success', 'Client created successfully.');
        }

        $this->redirect(route('clients.index'), navigate: true);
    }

    public function render()
    {
        $isEdit = $this->client && $this->client->exists;
        return view('livewire.clients.client-form', compact('isEdit'))
            ->layout('layouts.app', ['title' => $isEdit ? 'Edit Client' : 'Add Client']);
    }
}
