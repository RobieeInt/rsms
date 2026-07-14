<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompanySettingResource;
use App\Models\CompanySetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function show(): CompanySettingResource
    {
        $settings = CompanySetting::getSettings();

        return new CompanySettingResource($settings);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'address' => ['sometimes', 'required', 'string'],
            'bank_name' => ['sometimes', 'required', 'string'],
            'bank_account_number' => ['sometimes', 'required', 'string'],
            'bank_account_holder' => ['sometimes', 'required', 'string'],
            'website' => ['sometimes', 'required', 'string'],
            'tax_number' => ['sometimes', 'required', 'string'],
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $settings = CompanySetting::getSettings();
        $settings->update($validated);

        return response()->json([
            'message' => 'Pengaturan berhasil diperbarui.',
            'data' => new CompanySettingResource($settings->fresh()),
        ]);
    }
}
