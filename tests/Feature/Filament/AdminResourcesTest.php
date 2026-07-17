<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\BusinessResource\Pages\CreateBusiness;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Models\Business;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminResourcesTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_superadmin_can_create_a_business_and_its_user(): void
    {
        $superadmin = User::factory()->create([
            'is_super_admin' => true,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($superadmin);

        Livewire::test(CreateBusiness::class)
            ->fillForm([
                'name' => 'Studio Pilote',
                'slug' => 'studio-pilote',
                'timezone' => 'America/Montreal',
                'email' => 'contact@studio-pilote.test',
                'phone' => '514-555-0100',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $business = Business::query()->where('slug', 'studio-pilote')->firstOrFail();

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Gestionnaire Pilote',
                'email' => 'gestionnaire@studio-pilote.test',
                'password' => 'mot-de-passe-solide',
                'is_super_admin' => false,
                'business_id' => $business->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::query()->where('email', 'gestionnaire@studio-pilote.test')->firstOrFail();

        $this->assertSame($business->id, $user->business_id);
        $this->assertFalse($user->is_super_admin);
        $this->assertTrue(Hash::check('mot-de-passe-solide', $user->password));
    }

    public function test_a_superadmin_is_never_attached_to_a_business(): void
    {
        $business = Business::query()->create([
            'name' => 'Studio Pilote',
            'slug' => 'studio-pilote',
            'timezone' => 'America/Montreal',
        ]);

        $user = User::factory()->create([
            'business_id' => $business->id,
            'is_super_admin' => true,
        ]);

        $this->assertNull($user->business_id);
    }
}
