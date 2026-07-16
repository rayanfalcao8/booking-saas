<?php

namespace Tests\Feature\Filament;

use App\Core\Tenancy\TenantManager;
use App\Filament\App\Pages\SendFeedback;
use App\Models\Business;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FeedbackTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        TenantManager::forget();

        parent::tearDown();
    }

    public function test_an_owner_can_send_feedback_to_the_platform(): void
    {
        $business = Business::query()->create([
            'name' => 'Studio Pilote',
            'slug' => 'studio-pilote',
            'timezone' => 'America/Montreal',
        ]);
        $user = User::factory()->create([
            'business_id' => $business->id,
            'email' => 'owner@studio-pilote.test',
        ]);

        TenantManager::set($business);
        Filament::setCurrentPanel(Filament::getPanel('app'));
        $this->actingAs($user);

        Livewire::test(SendFeedback::class)
            ->fillForm([
                'category' => 'improvement',
                'rating' => 4,
                'message' => 'Je voudrais pouvoir exporter les rendez-vous de la semaine.',
                'contact_email' => 'owner@studio-pilote.test',
            ])
            ->call('send')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('feedback', [
            'business_id' => $business->id,
            'user_id' => $user->id,
            'category' => 'improvement',
            'rating' => 4,
            'status' => 'new',
        ]);
    }
}
