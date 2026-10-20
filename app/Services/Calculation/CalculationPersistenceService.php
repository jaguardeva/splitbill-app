<?php

namespace App\Services\Calculation;

use App\Models\BillItem;
use App\Models\Calculation;
use App\Models\CalculationAdjustment;
use App\Models\CalculationParticipant;
use App\Models\ItemAssignment;
use App\Models\ParticipantAdjustmentShare;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CalculationPersistenceService
{
    public function __construct(
        protected SplitBillCalculatorService $calculator
    ) {}

    /**
     * Persist calculation and its children inside a database transaction.
     *
     * @param  array<string, mixed>  $data
     */
    public function save(User $user, array $data, ?Calculation $existing = null): Calculation
    {
        // 1. Authoritative calculation
        $result = $this->calculator->calculate($data);

        return DB::transaction(function () use ($user, $data, $result, $existing): Calculation {
            $calculation = $existing ?? new Calculation;
            $calculation->user_id = $user->id;
            $calculation->title = trim($data['title']);
            $calculation->currency = $data['currency'] ?? 'IDR';
            $calculation->split_mode = $data['split_mode'];
            $calculation->status = $data['status'] ?? 'completed';
            $calculation->notes = $data['notes'] ?? null;
            $calculation->subtotal_minor = $result['subtotal_minor'];
            $calculation->discount_total_minor = $result['discount_total_minor'];
            $calculation->tax_total_minor = $result['tax_total_minor'];
            $calculation->service_charge_minor = $result['service_charge_minor'];
            $calculation->tip_total_minor = $result['tip_total_minor'];
            $calculation->grand_total_minor = $result['grand_total_minor'];
            $calculation->calculation_version = 1;
            $calculation->save();

            // If updating, remove old children (foreign keys will cascade)
            if ($existing) {
                // Delete existing child records cleanly
                $calculation->participants()->delete();
                $calculation->items()->delete();
                $calculation->adjustments()->delete();
            }

            // 2. Persist participants and maintain ID mapping (client ID -> DB UUID)
            $participantIdMap = [];
            foreach ($data['participants'] as $idx => $p) {
                $clientKey = (string) $p['id'];
                $part = new CalculationParticipant;
                $part->id = (string) Str::uuid();
                $part->calculation_id = $calculation->id;
                $part->name = trim((string) $p['name']);
                $part->position = $p['position'] ?? $idx;
                $part->save();

                $participantIdMap[$clientKey] = $part->id;
            }

            // 3. Persist items & assignments
            if ($calculation->split_mode === 'itemized' && ! empty($result['items'])) {
                foreach ($result['items'] as $itemData) {
                    $item = new BillItem;
                    $item->id = (string) Str::uuid();
                    $item->calculation_id = $calculation->id;
                    $item->description = $itemData['description'];
                    $item->quantity = (string) $itemData['quantity'];
                    $item->unit_price_minor = $itemData['unit_price_minor'];
                    $item->line_subtotal_minor = $itemData['line_subtotal_minor'];
                    $item->position = $itemData['position'];
                    $item->save();

                    foreach ($itemData['assignments'] as $clientPId => $allocatedMinor) {
                        if (! isset($participantIdMap[$clientPId])) {
                            continue;
                        }

                        $assignment = new ItemAssignment;
                        $assignment->id = (string) Str::uuid();
                        $assignment->calculation_id = $calculation->id;
                        $assignment->bill_item_id = $item->id;
                        $assignment->participant_id = $participantIdMap[$clientPId];
                        $assignment->share_weight = '1.0000';
                        $assignment->allocated_subtotal_minor = $allocatedMinor;
                        $assignment->save();
                    }
                }
            }

            // 4. Persist adjustments & adjustment shares
            if (! empty($result['adjustments'])) {
                foreach ($result['adjustments'] as $adjData) {
                    $adj = new CalculationAdjustment;
                    $adj->id = (string) Str::uuid();
                    $adj->calculation_id = $calculation->id;
                    $adj->type = $adjData['type'];
                    $adj->label = $adjData['label'];
                    $adj->value_type = $adjData['value_type'];
                    $adj->value_minor = $adjData['value_minor'];
                    $adj->percentage_rate = $adjData['percentage_rate'] !== null ? (string) $adjData['percentage_rate'] : null;
                    $adj->base_type = $adjData['base_type'];
                    $adj->is_enabled = $adjData['is_enabled'];
                    $adj->position = $adjData['position'];
                    $adj->amount_minor = $adjData['amount_minor'];
                    $adj->save();

                    foreach ($adjData['shares'] as $clientPId => $shareMinor) {
                        if (! isset($participantIdMap[$clientPId])) {
                            continue;
                        }

                        $adjShare = new ParticipantAdjustmentShare;
                        $adjShare->id = (string) Str::uuid();
                        $adjShare->calculation_id = $calculation->id;
                        $adjShare->adjustment_id = $adj->id;
                        $adjShare->participant_id = $participantIdMap[$clientPId];
                        $adjShare->amount_minor = $shareMinor;
                        $adjShare->save();
                    }
                }
            }

            return $calculation->load([
                'participants',
                'items.assignments',
                'adjustments.shares',
            ]);
        });
    }

    /**
     * Duplicate a saved calculation into a new draft calculation for the user.
     */
    public function duplicate(Calculation $source, User $user): Calculation
    {
        return DB::transaction(function () use ($source, $user): Calculation {
            $clone = new Calculation;
            $clone->user_id = $user->id;
            $clone->title = "{$source->title} (Salinan)";
            $clone->currency = $source->currency;
            $clone->split_mode = $source->split_mode;
            $clone->status = 'draft';
            $clone->notes = $source->notes;
            $clone->subtotal_minor = $source->subtotal_minor;
            $clone->discount_total_minor = $source->discount_total_minor;
            $clone->tax_total_minor = $source->tax_total_minor;
            $clone->service_charge_minor = $source->service_charge_minor;
            $clone->tip_total_minor = $source->tip_total_minor;
            $clone->grand_total_minor = $source->grand_total_minor;
            $clone->calculation_version = $source->calculation_version;
            $clone->save();

            $participantMap = [];
            foreach ($source->participants as $sourcePart) {
                $newPart = new CalculationParticipant;
                $newPart->id = (string) Str::uuid();
                $newPart->calculation_id = $clone->id;
                $newPart->name = $sourcePart->name;
                $newPart->position = $sourcePart->position;
                $newPart->save();

                $participantMap[$sourcePart->id] = $newPart->id;
            }

            foreach ($source->items as $sourceItem) {
                $newItem = new BillItem;
                $newItem->id = (string) Str::uuid();
                $newItem->calculation_id = $clone->id;
                $newItem->description = $sourceItem->description;
                $newItem->quantity = $sourceItem->quantity;
                $newItem->unit_price_minor = $sourceItem->unit_price_minor;
                $newItem->line_subtotal_minor = $sourceItem->line_subtotal_minor;
                $newItem->position = $sourceItem->position;
                $newItem->save();

                foreach ($sourceItem->assignments as $sourceAssoc) {
                    if (! isset($participantMap[$sourceAssoc->participant_id])) {
                        continue;
                    }

                    $newAssoc = new ItemAssignment;
                    $newAssoc->id = (string) Str::uuid();
                    $newAssoc->calculation_id = $clone->id;
                    $newAssoc->bill_item_id = $newItem->id;
                    $newAssoc->participant_id = $participantMap[$sourceAssoc->participant_id];
                    $newAssoc->share_weight = $sourceAssoc->share_weight;
                    $newAssoc->allocated_subtotal_minor = $sourceAssoc->allocated_subtotal_minor;
                    $newAssoc->save();
                }
            }

            foreach ($source->adjustments as $sourceAdj) {
                $newAdj = new CalculationAdjustment;
                $newAdj->id = (string) Str::uuid();
                $newAdj->calculation_id = $clone->id;
                $newAdj->type = $sourceAdj->type;
                $newAdj->label = $sourceAdj->label;
                $newAdj->value_type = $sourceAdj->value_type;
                $newAdj->value_minor = $sourceAdj->value_minor;
                $newAdj->percentage_rate = $sourceAdj->percentage_rate;
                $newAdj->base_type = $sourceAdj->base_type;
                $newAdj->is_enabled = $sourceAdj->is_enabled;
                $newAdj->position = $sourceAdj->position;
                $newAdj->amount_minor = $sourceAdj->amount_minor;
                $newAdj->save();

                foreach ($sourceAdj->shares as $sourceShare) {
                    if (! isset($participantMap[$sourceShare->participant_id])) {
                        continue;
                    }

                    $newShare = new ParticipantAdjustmentShare;
                    $newShare->id = (string) Str::uuid();
                    $newShare->calculation_id = $clone->id;
                    $newShare->adjustment_id = $newAdj->id;
                    $newShare->participant_id = $participantMap[$sourceShare->participant_id];
                    $newShare->amount_minor = $sourceShare->amount_minor;
                    $newShare->save();
                }
            }

            return $clone->load([
                'participants',
                'items.assignments',
                'adjustments.shares',
            ]);
        });
    }
}
