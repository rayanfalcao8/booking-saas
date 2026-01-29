<?php

namespace App\Core\Tenancy;

use App\Models\Business;

class TenantManager
{
    private static ?Business $tenant = null;

    public static function set(Business $business): void
    {
        self::$tenant = $business;
    }

    public static function get(): ?Business
    {
        return self::$tenant;
    }

    public static function id(): ?int
    {
        return self::$tenant?->id;
    }

    public static function forget(): void
    {
        self::$tenant = null;
    }
}
