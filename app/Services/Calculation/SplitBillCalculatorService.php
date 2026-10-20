<?php

namespace App\Services\Calculation;

use InvalidArgumentException;

class SplitBillCalculatorService
{
    /**
     * Calculate split bill breakdown deterministically.
     *
     * @param  array<string, mixed>  $input
     * @return array{
     *     subtotal_minor: int,
     *     discount_total_minor: int,
     *     tax_total_minor: int,
     *     service_charge_minor: int,
     *     tip_total_minor: int,
     *     grand_total_minor: int,
     *     items: list<array{id: string|null, description: string, quantity: float, unit_price_minor: int, line_subtotal_minor: int, position: int, assignments: array<string, int>}>,
     *     adjustments: list<array{id: string|null, type: string, label: string, value_type: string, value_minor: int|null, percentage_rate: float|null, base_type: string, is_enabled: bool, position: int, amount_minor: int, shares: array<string, int>}>,
     *     participants: array<string, array{id: string, name: string, position: int, subtotal_minor: int, discount_minor: int, tax_minor: int, service_charge_minor: int, tip_minor: int, final_total_minor: int}>
     * }
     */
    public function calculate(array $input): array
    {
        $participantsInput = $input['participants'] ?? [];
        if (empty($participantsInput) || ! is_array($participantsInput)) {
            throw new InvalidArgumentException('Minimal harus ada 1 partisipan.');
        }

        /** @var array<string, CalculationParticipantDto> $participants */
        $participants = [];
        foreach ($participantsInput as $idx => $p) {
            $pId = (string) $p['id'];
            $participants[$pId] = new CalculationParticipantDto(
                id: $pId,
                name: trim((string) ($p['name'] ?? '')),
                position: isset($p['position']) ? (int) $p['position'] : $idx,
            );
        }

        $splitMode = (string) ($input['split_mode'] ?? 'equal');
        $processedItems = [];
        $subtotalMinor = 0;

        if ($splitMode === 'equal') {
            $items = $input['items'] ?? [];
            if (! empty($items) && is_array($items)) {
                foreach ($items as $idx => $item) {
                    $qty = (float) ($item['quantity'] ?? 1);
                    $unitPrice = (int) ($item['unit_price_minor'] ?? 0);
                    $lineSubtotal = (int) round($qty * $unitPrice);
                    $subtotalMinor += $lineSubtotal;

                    $processedItems[] = [
                        'id' => isset($item['id']) ? (string) $item['id'] : null,
                        'description' => (string) ($item['description'] ?? ''),
                        'quantity' => $qty,
                        'unit_price_minor' => $unitPrice,
                        'line_subtotal_minor' => $lineSubtotal,
                        'position' => isset($item['position']) ? (int) $item['position'] : $idx,
                        'assignments' => [],
                    ];
                }
            } else {
                $subtotalMinor = max(0, (int) ($input['equal_subtotal_minor'] ?? 0));
            }

            $participantIds = array_keys($participants);
            $subtotalShares = $this->distributeRemainderEqually($subtotalMinor, $participantIds);
            foreach ($subtotalShares as $pId => $share) {
                $participants[$pId]->subtotal_minor = $share;
            }
        } else {
            $itemsInput = $input['items'] ?? [];
            if (! is_array($itemsInput)) {
                $itemsInput = [];
            }

            foreach ($itemsInput as $idx => $item) {
                $desc = (string) ($item['description'] ?? "Item #{$idx}");
                $qty = (float) ($item['quantity'] ?? 0);
                if ($qty <= 0) {
                    throw new InvalidArgumentException("Kuantitas untuk item '{$desc}' harus lebih dari 0.");
                }

                $unitPrice = (int) ($item['unit_price_minor'] ?? 0);
                if ($unitPrice < 0) {
                    throw new InvalidArgumentException("Harga unit untuk item '{$desc}' tidak boleh negatif.");
                }

                $lineSubtotal = (int) round($qty * $unitPrice);
                $subtotalMinor += $lineSubtotal;

                $assignedList = is_array($item['assigned_participant_ids'] ?? null)
                    ? $item['assigned_participant_ids']
                    : [];

                $assignedIds = array_values(array_intersect(
                    array_map('strval', $assignedList),
                    array_keys($participants)
                ));

                if (empty($assignedIds)) {
                    throw new InvalidArgumentException("Item '{$desc}' harus memiliki minimal 1 partisipan yang ditugaskan.");
                }

                $itemShares = $this->distributeRemainderEqually($lineSubtotal, $assignedIds);

                foreach ($itemShares as $pId => $share) {
                    $participants[$pId]->subtotal_minor += $share;
                }

                $processedItems[] = [
                    'id' => isset($item['id']) ? (string) $item['id'] : null,
                    'description' => $desc,
                    'quantity' => $qty,
                    'unit_price_minor' => $unitPrice,
                    'line_subtotal_minor' => $lineSubtotal,
                    'position' => isset($item['position']) ? (int) $item['position'] : $idx,
                    'assignments' => $itemShares,
                ];
            }
        }

        // Adjustments processing
        $adjustmentsInput = is_array($input['adjustments'] ?? null) ? $input['adjustments'] : [];
        $processedAdjustments = [];

        $discountTotalMinor = 0;
        $taxTotalMinor = 0;
        $serviceChargeMinor = 0;
        $tipTotalMinor = 0;

        usort($adjustmentsInput, fn ($a, $b) => (int) ($a['position'] ?? 0) <=> (int) ($b['position'] ?? 0));

        // 1. Discount
        foreach ($adjustmentsInput as $idx => $adj) {
            $type = (string) ($adj['type'] ?? '');
            $isEnabled = (bool) ($adj['is_enabled'] ?? true);
            if ($type !== 'discount' || ! $isEnabled) {
                continue;
            }

            $amount = $this->calculateAdjustmentAmount($adj, $subtotalMinor);
            $amount = min($amount, $subtotalMinor - $discountTotalMinor);
            $discountTotalMinor += $amount;

            $shares = $this->distributeAdjustmentProportionally($amount, $participants, 'subtotal_minor');
            foreach ($shares as $pId => $share) {
                $participants[$pId]->discount_minor += $share;
            }

            $processedAdjustments[] = $this->formatProcessedAdjustment($adj, $idx, $amount, $shares);
        }

        $taxableBase = max(0, $subtotalMinor - $discountTotalMinor);

        // 2. Tax, Service Charge, Tip
        foreach ($adjustmentsInput as $idx => $adj) {
            $type = (string) ($adj['type'] ?? '');
            $isEnabled = (bool) ($adj['is_enabled'] ?? true);
            if ($type === 'discount' || ! $isEnabled) {
                continue;
            }

            $base = match ((string) ($adj['base_type'] ?? 'after_discount')) {
                'subtotal' => $subtotalMinor,
                default => $taxableBase,
            };

            $amount = $this->calculateAdjustmentAmount($adj, $base);
            $shares = $this->distributeAdjustmentProportionally($amount, $participants, 'subtotal_minor');

            switch ($type) {
                case 'tax':
                    $taxTotalMinor += $amount;
                    foreach ($shares as $pId => $share) {
                        $participants[$pId]->tax_minor += $share;
                    }
                    break;
                case 'service_charge':
                    $serviceChargeMinor += $amount;
                    foreach ($shares as $pId => $share) {
                        $participants[$pId]->service_charge_minor += $share;
                    }
                    break;
                case 'tip':
                    $tipTotalMinor += $amount;
                    foreach ($shares as $pId => $share) {
                        $participants[$pId]->tip_minor += $share;
                    }
                    break;
            }

            $processedAdjustments[] = $this->formatProcessedAdjustment($adj, $idx, $amount, $shares);
        }

        $grandTotalMinor = $subtotalMinor - $discountTotalMinor + $taxTotalMinor + $serviceChargeMinor + $tipTotalMinor;

        $finalParticipants = [];
        foreach ($participants as $pId => $pDto) {
            $pDto->final_total_minor = $pDto->subtotal_minor
                - $pDto->discount_minor
                + $pDto->tax_minor
                + $pDto->service_charge_minor
                + $pDto->tip_minor;

            $finalParticipants[$pId] = $pDto->toArray();
        }

        return [
            'subtotal_minor' => $subtotalMinor,
            'discount_total_minor' => $discountTotalMinor,
            'tax_total_minor' => $taxTotalMinor,
            'service_charge_minor' => $serviceChargeMinor,
            'tip_total_minor' => $tipTotalMinor,
            'grand_total_minor' => $grandTotalMinor,
            'items' => $processedItems,
            'adjustments' => $processedAdjustments,
            'participants' => $finalParticipants,
        ];
    }

    /**
     * Distribute an amount equally among a list of participant IDs using largest remainder.
     *
     * @param  array<int, string>  $participantIds
     * @return array<string, int>
     */
    public function distributeRemainderEqually(int $amount, array $participantIds): array
    {
        $count = count($participantIds);
        if ($count === 0) {
            return [];
        }

        $baseShare = intdiv($amount, $count);
        $remainder = $amount % $count;

        $result = [];
        foreach ($participantIds as $index => $pId) {
            $extra = ($index < $remainder) ? 1 : 0;
            $result[$pId] = $baseShare + $extra;
        }

        return $result;
    }

    /**
     * Distribute an adjustment amount proportionally according to each participant's base amount.
     * Uses Largest Remainder Method (Hamilton-Hare) to guarantee the sum equals the exact amount.
     *
     * @param  array<string, CalculationParticipantDto>  $participants
     * @return array<string, int>
     */
    public function distributeAdjustmentProportionally(int $totalAmount, array $participants, string $baseKey = 'subtotal_minor'): array
    {
        if ($totalAmount === 0 || empty($participants)) {
            $zeroShares = [];
            foreach (array_keys($participants) as $pId) {
                $zeroShares[$pId] = 0;
            }

            return $zeroShares;
        }

        $totalBase = 0;
        foreach ($participants as $p) {
            $totalBase += $p->subtotal_minor;
        }

        if ($totalBase <= 0) {
            return $this->distributeRemainderEqually($totalAmount, array_keys($participants));
        }

        $provisionalShares = [];
        $fractions = [];
        $allocatedSum = 0;

        foreach ($participants as $pId => $p) {
            $exact = ($totalAmount * $p->subtotal_minor) / $totalBase;
            $floor = (int) floor($exact);
            $provisionalShares[$pId] = $floor;
            $allocatedSum += $floor;
            $fractions[$pId] = [
                'fraction' => $exact - $floor,
                'position' => $p->position,
            ];
        }

        $remainder = $totalAmount - $allocatedSum;

        if ($remainder > 0) {
            uasort($fractions, function ($a, $b) {
                if ($b['fraction'] === $a['fraction']) {
                    return $a['position'] <=> $b['position'];
                }

                return ($b['fraction'] > $a['fraction']) ? 1 : -1;
            });

            $distributed = 0;
            foreach (array_keys($fractions) as $pId) {
                if ($distributed < $remainder) {
                    $provisionalShares[$pId] += 1;
                    $distributed++;
                } else {
                    break;
                }
            }
        }

        return $provisionalShares;
    }

    /**
     * Calculate individual adjustment amount based on type (percentage or fixed).
     *
     * @param  array<string, mixed>  $adj
     */
    protected function calculateAdjustmentAmount(array $adj, int $baseMinor): int
    {
        $valueType = (string) ($adj['value_type'] ?? 'fixed');

        if ($valueType === 'percentage') {
            $rate = (float) ($adj['percentage_rate'] ?? 0);
            if ($rate < 0) {
                throw new InvalidArgumentException('Persentase penyesuaian tidak boleh negatif.');
            }

            return (int) round($baseMinor * ($rate / 100.0));
        }

        $fixedMinor = (int) ($adj['value_minor'] ?? 0);
        if ($fixedMinor < 0) {
            throw new InvalidArgumentException('Nominal penyesuaian tidak boleh negatif.');
        }

        return $fixedMinor;
    }

    /**
     * Format processed adjustment record.
     *
     * @param  array<string, mixed>  $adj
     * @param  array<string, int>  $shares
     * @return array{id: string|null, type: string, label: string, value_type: string, value_minor: int|null, percentage_rate: float|null, base_type: string, is_enabled: bool, position: int, amount_minor: int, shares: array<string, int>}
     */
    protected function formatProcessedAdjustment(array $adj, int $position, int $amount, array $shares): array
    {
        return [
            'id' => isset($adj['id']) ? (string) $adj['id'] : null,
            'type' => (string) ($adj['type'] ?? 'discount'),
            'label' => (string) ($adj['label'] ?? ''),
            'value_type' => (string) ($adj['value_type'] ?? 'fixed'),
            'value_minor' => isset($adj['value_minor']) ? (int) $adj['value_minor'] : null,
            'percentage_rate' => isset($adj['percentage_rate']) ? (float) $adj['percentage_rate'] : null,
            'base_type' => (string) ($adj['base_type'] ?? 'subtotal'),
            'is_enabled' => (bool) ($adj['is_enabled'] ?? true),
            'position' => isset($adj['position']) ? (int) $adj['position'] : $position,
            'amount_minor' => $amount,
            'shares' => $shares,
        ];
    }
}
