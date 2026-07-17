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
            ->assertSee('Vos clients réservent.')
            ->assertSee('Vous gardez le rythme.')
            ->assertSee('Un parcours qui va droit au but')
            ->assertSee('Ce qui change au quotidien')
            ->assertSee('Sécurité intégrée')
            ->assertSee('Questions fréquentes')
            ->assertSee('/app/login', escape: false)
            ->assertDontSee('Laravel has an incredibly rich ecosystem');
    }
}
