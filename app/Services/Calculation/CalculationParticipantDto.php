<?php

namespace App\Services\Calculation;

class CalculationParticipantDto
{
    public function __construct(
        public string $id,
        public string $name,
        public int $position = 0,
        public int $subtotal_minor = 0,
        public int $discount_minor = 0,
        public int $tax_minor = 0,
        public int $service_charge_minor = 0,
        public int $tip_minor = 0,
        public int $final_total_minor = 0,
    ) {}

    /**
     * Convert DTO to array.
     *
     * @return array{
     *     id: string,
     *     name: string,
     *     position: int,
     *     subtotal_minor: int,
     *     discount_minor: int,
     *     tax_minor: int,
     *     service_charge_minor: int,
     *     tip_minor: int,
     *     final_total_minor: int
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'position' => $this->position,
            'subtotal_minor' => $this->subtotal_minor,
            'discount_minor' => $this->discount_minor,
            'tax_minor' => $this->tax_minor,
            'service_charge_minor' => $this->service_charge_minor,
            'tip_minor' => $this->tip_minor,
            'final_total_minor' => $this->final_total_minor,
        ];
    }
}
