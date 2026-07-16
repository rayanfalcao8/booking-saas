<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthCheckCommandTest extends TestCase
{
    public function test_up_route_reports_a_healthy_boot_response(): void
    {
        $response = $this->get('/up');

        $response->assertOk();
    }
}
