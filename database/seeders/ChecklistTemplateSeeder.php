<?php

namespace Database\Seeders;

use App\Models\ChecklistTemplate;
use Illuminate\Database\Seeder;

class ChecklistTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Desktop PC / Laptop',
                'asset_type' => null, // universal default
                'items' => [
                    ['key' => 'storage_check',       'label' => 'Cek Storage / Disk Space'],
                    ['key' => 'ram_check',            'label' => 'Cek RAM Usage'],
                    ['key' => 'temp_files_cleanup',   'label' => 'Bersihkan File Temp'],
                    ['key' => 'ssd_health_check',     'label' => 'Cek Kesehatan SSD/HDD'],
                    ['key' => 'windows_update_check', 'label' => 'Update Windows'],
                    ['key' => 'driver_check',         'label' => 'Cek & Update Driver'],
                    ['key' => 'virus_scan',           'label' => 'Scan Virus/Malware'],
                    ['key' => 'hardware_cleaning',    'label' => 'Bersihkan Hardware'],
                ],
            ],
            [
                'name' => 'Desktop PC',
                'asset_type' => 'desktop_pc',
                'items' => [
                    ['key' => 'storage_check',       'label' => 'Cek Storage / Disk Space'],
                    ['key' => 'ram_check',            'label' => 'Cek RAM Usage'],
                    ['key' => 'temp_files_cleanup',   'label' => 'Bersihkan File Temp'],
                    ['key' => 'ssd_health_check',     'label' => 'Cek Kesehatan SSD/HDD'],
                    ['key' => 'windows_update_check', 'label' => 'Update Windows'],
                    ['key' => 'driver_check',         'label' => 'Cek & Update Driver'],
                    ['key' => 'virus_scan',           'label' => 'Scan Virus/Malware'],
                    ['key' => 'hardware_cleaning',    'label' => 'Bersihkan Hardware (debu)'],
                    ['key' => 'fan_check',            'label' => 'Cek Fan & Pendingin'],
                ],
            ],
            [
                'name' => 'Laptop',
                'asset_type' => 'laptop',
                'items' => [
                    ['key' => 'storage_check',       'label' => 'Cek Storage / Disk Space'],
                    ['key' => 'ram_check',            'label' => 'Cek RAM Usage'],
                    ['key' => 'temp_files_cleanup',   'label' => 'Bersihkan File Temp'],
                    ['key' => 'ssd_health_check',     'label' => 'Cek Kesehatan SSD'],
                    ['key' => 'windows_update_check', 'label' => 'Update Windows'],
                    ['key' => 'driver_check',         'label' => 'Cek & Update Driver'],
                    ['key' => 'virus_scan',           'label' => 'Scan Virus/Malware'],
                    ['key' => 'battery_check',        'label' => 'Cek Kondisi Baterai'],
                    ['key' => 'hardware_cleaning',    'label' => 'Bersihkan Keyboard & Ventilasi'],
                ],
            ],
            [
                'name' => 'Printer',
                'asset_type' => 'printer',
                'items' => [
                    ['key' => 'ink_toner_check',      'label' => 'Cek Level Tinta/Toner'],
                    ['key' => 'print_quality_check',  'label' => 'Uji Kualitas Cetak'],
                    ['key' => 'paper_jam_check',      'label' => 'Cek & Bersihkan Paper Jam'],
                    ['key' => 'roller_cleaning',      'label' => 'Bersihkan Roller'],
                    ['key' => 'head_cleaning',        'label' => 'Head Cleaning'],
                    ['key' => 'driver_check',         'label' => 'Cek Driver Printer'],
                    ['key' => 'connectivity_check',   'label' => 'Konektivitas Jaringan/USB'],
                    ['key' => 'hardware_cleaning',    'label' => 'Bersihkan Bodi Printer'],
                ],
            ],
            [
                'name' => 'Server',
                'asset_type' => 'server',
                'items' => [
                    ['key' => 'disk_space_check',     'label' => 'Cek Disk Space'],
                    ['key' => 'cpu_usage_check',      'label' => 'Cek CPU Usage'],
                    ['key' => 'ram_usage_check',      'label' => 'Cek RAM Usage'],
                    ['key' => 'backup_check',         'label' => 'Verifikasi Backup'],
                    ['key' => 'service_status_check', 'label' => 'Status Service/Process'],
                    ['key' => 'security_update_check','label' => 'Update Keamanan/Patch'],
                    ['key' => 'log_review',           'label' => 'Review Log Error'],
                    ['key' => 'temp_check',           'label' => 'Cek Suhu Server'],
                    ['key' => 'ups_check',            'label' => 'Cek UPS/Power Supply'],
                ],
            ],
            [
                'name' => 'Router',
                'asset_type' => 'router',
                'items' => [
                    ['key' => 'connectivity_check',   'label' => 'Cek Konektivitas Internet'],
                    ['key' => 'firmware_update',      'label' => 'Cek Update Firmware'],
                    ['key' => 'config_backup',        'label' => 'Backup Konfigurasi'],
                    ['key' => 'bandwidth_check',      'label' => 'Cek Bandwidth'],
                    ['key' => 'security_check',       'label' => 'Cek Keamanan (password, firewall)'],
                    ['key' => 'dhcp_check',           'label' => 'Cek DHCP Lease'],
                    ['key' => 'hardware_cleaning',    'label' => 'Bersihkan & Cek Kabel'],
                ],
            ],
            [
                'name' => 'Switch',
                'asset_type' => 'switch',
                'items' => [
                    ['key' => 'port_status_check',    'label' => 'Cek Status Port'],
                    ['key' => 'connectivity_check',   'label' => 'Cek Konektivitas Antar Perangkat'],
                    ['key' => 'firmware_update',      'label' => 'Cek Update Firmware'],
                    ['key' => 'config_backup',        'label' => 'Backup Konfigurasi'],
                    ['key' => 'vlan_check',           'label' => 'Verifikasi VLAN'],
                    ['key' => 'hardware_cleaning',    'label' => 'Bersihkan & Cek Kabel'],
                ],
            ],
            [
                'name' => 'Access Point',
                'asset_type' => 'access_point',
                'items' => [
                    ['key' => 'signal_check',         'label' => 'Cek Kekuatan Sinyal'],
                    ['key' => 'connectivity_check',   'label' => 'Cek Konektivitas WiFi'],
                    ['key' => 'firmware_update',      'label' => 'Cek Update Firmware'],
                    ['key' => 'ssid_check',           'label' => 'Verifikasi SSID & Password'],
                    ['key' => 'security_check',       'label' => 'Cek Enkripsi (WPA2/WPA3)'],
                    ['key' => 'hardware_cleaning',    'label' => 'Bersihkan Bodi & Posisi AP'],
                ],
            ],
            [
                'name' => 'NAS',
                'asset_type' => 'nas',
                'items' => [
                    ['key' => 'disk_space_check',     'label' => 'Cek Disk Space & Kesehatan Disk'],
                    ['key' => 'raid_status_check',    'label' => 'Cek Status RAID'],
                    ['key' => 'backup_check',         'label' => 'Verifikasi Backup'],
                    ['key' => 'firmware_update',      'label' => 'Cek Update Firmware'],
                    ['key' => 'access_check',         'label' => 'Cek Hak Akses User'],
                    ['key' => 'hardware_cleaning',    'label' => 'Bersihkan Fan & Ventilasi'],
                ],
            ],
            [
                'name' => 'CCTV',
                'asset_type' => 'cctv',
                'items' => [
                    ['key' => 'image_quality_check',  'label' => 'Cek Kualitas Gambar'],
                    ['key' => 'recording_check',      'label' => 'Verifikasi Rekaman Berjalan'],
                    ['key' => 'storage_check',        'label' => 'Cek Storage DVR/NVR'],
                    ['key' => 'camera_position_check','label' => 'Cek Posisi & Arah Kamera'],
                    ['key' => 'night_vision_check',   'label' => 'Uji Night Vision'],
                    ['key' => 'hardware_cleaning',    'label' => 'Bersihkan Lensa & Bodi Kamera'],
                    ['key' => 'connectivity_check',   'label' => 'Cek Konektivitas Jaringan'],
                ],
            ],
        ];

        foreach ($templates as $data) {
            ChecklistTemplate::updateOrCreate(
                ['asset_type' => $data['asset_type']],
                $data
            );
        }
    }
}
