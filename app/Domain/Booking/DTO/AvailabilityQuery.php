<?php

namespace App\Domain\Booking\DTO;

class AvailabilityQuery
{
    public function __construct(
        public int $serviceId,
        public int $staffId,
        public string $date,
        public int $stepMin = 15,
    ) {}
}
