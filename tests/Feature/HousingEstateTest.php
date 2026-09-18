<?php

namespace Tests\Feature;

use App\Livewire\Admin\Estates\Index;
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

    public function test_admin_can_create_housing_estate_from_livewire_component(): void
    {
        Livewire::actingAs($this->admin())
            ->test(Index::class)
            ->call('create')
            ->set('code', 'HH-TEST-01')
            ->set('name', 'Perumahan Uji Baru')
            ->call('save');

        $this->assertDatabaseHas('housing_estates', [
            'code' => 'HH-TEST-01',
            'name' => 'Perumahan Uji Baru',
            'status' => 'active',
        ]);
    }
}
