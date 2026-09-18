<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super_admin', 'description' => 'Akses semua fitur'],
            ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Operasional perumahan'],
            ['name' => 'Finance', 'slug' => 'finance', 'description' => 'Keuangan & IPL'],
            ['name' => 'RT', 'slug' => 'rt', 'description' => 'Data warga & administrasi'],
            ['name' => 'RW', 'slug' => 'rw', 'description' => 'Monitoring wilayah'],
            ['name' => 'Security', 'slug' => 'security', 'description' => 'Tamu, kendaraan, paket'],
            ['name' => 'Maintenance', 'slug' => 'maintenance', 'description' => 'Pengaduan & maintenance'],
            ['name' => 'Resident', 'slug' => 'resident', 'description' => 'Warga'],
        ];

        foreach ($roles as $data) {
            Role::firstOrCreate(['slug' => $data['slug']], $data + ['status' => 'active']);
        }

        $permissions = [
            'general' => ['view-dashboard', 'access-admin'],
            'master' => ['manage-houses', 'manage-residents'],
            'billing' => ['manage-billing', 'manage-payment', 'verify-payment'],
            'communication' => ['manage-announcement', 'manage-event', 'manage-forum'],
            'service' => ['manage-complaint', 'manage-administration', 'manage-facility', 'manage-booking'],
            'marketplace' => ['manage-marketplace'],
            'security' => ['manage-visitor', 'manage-vehicle', 'manage-package', 'manage-emergency'],
            'finance' => ['manage-finance'],
            'system' => ['manage-user', 'manage-role', 'view-activity-log'],
        ];

        $labels = [
            'view-dashboard' => 'Lihat Dashboard',
            'access-admin' => 'Akses Area Admin',
            'manage-houses' => 'Kelola Rumah', 'manage-residents' => 'Kelola Warga',
            'manage-billing' => 'Kelola Tagihan', 'manage-payment' => 'Kelola Pembayaran', 'verify-payment' => 'Verifikasi Pembayaran',
            'manage-announcement' => 'Kelola Pengumuman',
            'manage-event' => 'Kelola Event', 'manage-forum' => 'Kelola Forum',
            'manage-complaint' => 'Kelola Pengaduan', 'manage-administration' => 'Kelola Administrasi',
            'manage-facility' => 'Kelola Fasilitas', 'manage-booking' => 'Kelola Booking',
            'manage-marketplace' => 'Kelola Marketplace',
            'manage-visitor' => 'Kelola Tamu', 'manage-vehicle' => 'Kelola Kendaraan',
            'manage-package' => 'Kelola Paket', 'manage-emergency' => 'Kelola Emergency',
            'manage-finance' => 'Kelola Keuangan',
            'manage-user' => 'Kelola User', 'manage-role' => 'Kelola Role', 'view-activity-log' => 'Lihat Activity Log',
        ];

        $permIds = [];
        foreach ($permissions as $group => $slugs) {
            foreach ($slugs as $slug) {
                $perm = Permission::firstOrCreate(['slug' => $slug], [
                    'name' => $labels[$slug] ?? $slug,
                    'group' => $group,
                ]);
                $permIds[$slug] = $perm->id;
            }
        }

        $map = [
            'super_admin' => array_keys($permIds),
            'admin' => array_keys($permIds),
            'finance' => ['view-dashboard', 'access-admin', 'manage-billing', 'manage-payment', 'verify-payment', 'manage-finance'],
            'rt' => ['view-dashboard', 'access-admin', 'manage-residents', 'manage-houses', 'manage-announcement', 'manage-event', 'manage-administration'],
            'rw' => ['view-dashboard', 'access-admin', 'manage-residents', 'manage-announcement', 'manage-event'],
            'security' => ['view-dashboard', 'access-admin', 'manage-visitor', 'manage-vehicle', 'manage-package', 'manage-emergency'],
            'maintenance' => ['view-dashboard', 'access-admin', 'manage-complaint', 'manage-facility', 'manage-booking'],
            'resident' => ['view-dashboard'],
        ];

        foreach ($map as $slug => $slugs) {
            $role = Role::where('slug', $slug)->first();
            if ($role) {
                $role->permissions()->sync(collect($slugs)->map(fn ($s) => $permIds[$s])->all());
            }
        }

        // Permission sisa dari fitur yang sudah dihapus (mis. modul Air/Token) dibuang
        // agar tidak lagi muncul di halaman Role & Akses.
        Permission::whereNotIn('slug', array_keys($permIds))
            ->get()
            ->each(function (Permission $permission) {
                $permission->roles()->detach();
                $permission->delete();
            });

        $superAdmin = Role::where('slug', 'super_admin')->first();
        User::firstOrCreate(['email' => 'admin@housinghub.id'], [
            'name' => 'Super Admin',
            'password' => Hash::make('password123'),
            'role_id' => $superAdmin?->id,
            'status' => 'active',
        ]);
    }
}
