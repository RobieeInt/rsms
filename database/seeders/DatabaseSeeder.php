<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\CompanySetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::create(['name' => 'admin']);
        $techRole = Role::create(['name' => 'technician']);

        $permissions = [
            'manage-clients', 'manage-technicians', 'manage-schedules',
            'manage-assets', 'manage-quotations', 'manage-invoices',
            'manage-reports', 'view-dashboard', 'configure-settings',
            'checkin-visit', 'create-reports', 'create-findings',
            'upload-photos', 'create-recommendations',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        $adminRole->givePermissionTo($permissions);
        $techRole->givePermissionTo([
            'view-dashboard', 'manage-schedules', 'manage-assets',
            'checkin-visit', 'create-reports', 'create-findings',
            'upload-photos', 'create-recommendations',
        ]);

        $admin = User::create([
            'name' => 'Admin Reconext',
            'email' => 'admin@reconext.com',
            'password' => bcrypt('password'),
            'phone' => '+62 812 3456 7890',
            'position' => 'System Administrator',
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        $tech = User::create([
            'name' => 'Budi Santoso',
            'email' => 'technician@reconext.com',
            'password' => bcrypt('password'),
            'phone' => '+62 813 9876 5432',
            'position' => 'IT Technician',
            'is_active' => true,
        ]);
        $tech->assignRole('technician');

        CompanySetting::create([
            'company_name' => 'Reconext Digital Kreasi',
            'email' => 'info@reconext.com',
            'phone' => '+62 21 5555 1234',
            'address' => 'Jl. Sudirman No. 123, Jakarta Pusat, DKI Jakarta 10220',
            'bank_name' => 'Bank BCA',
            'bank_account_number' => '1234567890',
            'bank_account_holder' => 'Reconext Digital Kreasi',
            'website' => 'https://reconext.com',
            'tax_number' => '01.234.567.8-901.000',
        ]);

        Client::create([
            'company_name' => 'PT Maju Bersama',
            'pic_name' => 'Siti Rahayu',
            'pic_email' => 'siti@majubersama.co.id',
            'pic_phone' => '+62 812 1111 2222',
            'address' => 'Jl. Kebon Jeruk No. 45, Jakarta Barat',
            'monthly_retainer_fee' => 5000000,
            'invoice_due_date' => 30,
            'is_active' => true,
        ]);

        Client::create([
            'company_name' => 'CV Teknologi Nusantara',
            'pic_name' => 'Ahmad Fauzi',
            'pic_email' => 'ahmad@tekno-nusantara.com',
            'pic_phone' => '+62 811 3333 4444',
            'address' => 'Jl. Kemang Raya No. 88, Jakarta Selatan',
            'monthly_retainer_fee' => 3500000,
            'invoice_due_date' => 14,
            'is_active' => true,
        ]);
    }
}
