<?php

namespace Tests\Feature;

use App\Livewire\Admin\Estates\Index;
use App\Models\House;
use App\Models\HouseResident;
use App\Models\HousingBlock;
use App\Models\HousingEstate;
use App\Models\User;
use Database\Seeders\HousingSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HousingEstateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(HousingSeeder::class);
    }

    protected function admin(): User
    {
        return User::where('email', 'admin@housinghub.id')->firstOrFail();
    }

    public function test_admin_can_create_housing_estate_when_none_exists(): void
    {
        // Aturan bisnis: hanya boleh ada 1 perumahan, jadi uji saat belum ada data.
        // Hapus berurutan sesuai ketergantungan FK (anak -> induk).
        HouseResident::query()->delete();
        House::query()->delete();
        HousingBlock::query()->delete();
        HousingEstate::query()->delete();

        Livewire::actingAs($this->admin())
            ->test(Index::class)
            ->set('code', 'HH-TEST-01')
            ->set('name', 'Perumahan Uji Baru')
            ->call('save');

        $this->assertDatabaseHas('housing_estates', [
            'code' => 'HH-TEST-01',
            'name' => 'Perumahan Uji Baru',
            'status' => 'active',
        ]);
    }

    public function test_admin_cannot_create_second_housing_estate(): void
    {
        // HousingSeeder sudah membuat 1 perumahan; pembuatan perumahan kedua diblokir.
        Livewire::actingAs($this->admin())
            ->test(Index::class)
            ->set('code', 'HH-TEST-01')
            ->set('name', 'Perumahan Uji Baru')
            ->call('save');

        $this->assertDatabaseMissing('housing_estates', ['code' => 'HH-TEST-01']);
        $this->assertSame(1, HousingEstate::count());
    }
}
