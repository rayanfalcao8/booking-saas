<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_landing_page_presents_reservix(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('Reservix')
            ->assertSee("Vos rendez-vous n'ont plus besoin de vos messages.", escape: false)
            ->assertSee('Le service')
            ->assertSee('Les avantages')
            ->assertSee('Sécurité')
            ->assertSee('FAQ')
            ->assertSee('/app/login', escape: false)
            ->assertDontSee('Laravel has an incredibly rich ecosystem');
    }
}
