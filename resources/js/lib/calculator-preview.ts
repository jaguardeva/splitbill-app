export interface ParticipantInput {
    id: string;
    name: string;
    position?: number;
    [key: string]: any;
}

export interface BillItemInput {
    id?: string;
    description: string;
    quantity: number;
    unit_price_minor: number;
    assigned_participant_ids: string[];
    position?: number;
    [key: string]: any;
}

export interface AdjustmentInput {
    id?: string;
    type: 'discount' | 'tax' | 'service_charge' | 'tip';
    label: string;
    value_type: 'fixed' | 'percentage';
    value_minor?: number | null;
    percentage_rate?: number | null;
    base_type?: 'subtotal' | 'after_discount';
    is_enabled?: boolean;
    position?: number;
    [key: string]: any;
}

export interface CalculationInput {
    split_mode: 'equal' | 'itemized';
    participants: ParticipantInput[];
    items?: BillItemInput[];
    adjustments?: AdjustmentInput[];
    equal_subtotal_minor?: number;
}

export interface ParticipantShareResult {
    id: string;
    name: string;
    position: number;
    subtotal_minor: number;
    discount_minor: number;
    tax_minor: number;
    service_charge_minor: number;
    tip_minor: number;
    final_total_minor: number;
}

export interface CalculationPreviewResult {
    subtotal_minor: number;
    discount_total_minor: number;
    tax_total_minor: number;
    service_charge_minor: number;
    tip_total_minor: number;
    grand_total_minor: number;
    participants: Record<string, ParticipantShareResult>;
    items: Array<{
        id?: string;
        description: string;
        quantity: number;
        unit_price_minor: number;
        line_subtotal_minor: number;
        position: number;
        assignments: Record<string, number>;
    }>;
    adjustments: Array<{
        id?: string;
        type: string;
        label: string;
        value_type: string;
        value_minor: number | null;
        percentage_rate: number | null;
        base_type: string;
        is_enabled: boolean;
        position: number;
        amount_minor: number;
        shares: Record<string, number>;
    }>;
}

/**
 * Distribute an integer remainder equally among participant IDs.
 */
function distributeRemainderEqually(amount: number, participantIds: string[]): Record<string, number> {
    const count = participantIds.length;
    if (count === 0) return {};

    const baseShare = Math.floor(amount / count);
    const remainder = amount % count;

    const result: Record<string, number> = {};
    participantIds.forEach((pId, idx) => {
        result[pId] = baseShare + (idx < remainder ? 1 : 0);
    });

    return result;
}

/**
 * Distribute an adjustment amount proportionally according to subtotal shares.
 */
function distributeAdjustmentProportionally(
    totalAmount: number,
    participants: Record<string, ParticipantShareResult>,
    baseKey: keyof ParticipantShareResult = 'subtotal_minor'
): Record<string, number> {
    const participantKeys = Object.keys(participants);
    if (totalAmount === 0 || participantKeys.length === 0) {
        const zeros: Record<string, number> = {};
        for (const id of participantKeys) zeros[id] = 0;
        return zeros;
    }

    let totalBase = 0;
    for (const id of participantKeys) {
        totalBase += (participants[id][baseKey] as number) || 0;
    }

    if (totalBase <= 0) {
        return distributeRemainderEqually(totalAmount, participantKeys);
    }

    const provisional: Record<string, number> = {};
    const fractions: Array<{ id: string; fraction: number; position: number }> = [];
    let allocatedSum = 0;

    for (const id of participantKeys) {
        const exact = (totalAmount * (participants[id][baseKey] as number)) / totalBase;
        const floor = Math.floor(exact);
        provisional[id] = floor;
        allocatedSum += floor;
        fractions.push({
            id,
            fraction: exact - floor,
            position: participants[id].position || 0,
        });
    }

    let remainder = totalAmount - allocatedSum;
    if (remainder > 0) {
        fractions.sort((a, b) => {
            if (b.fraction === a.fraction) return a.position - b.position;
            return b.fraction - a.fraction;
        });

        for (let i = 0; i < remainder && i < fractions.length; i++) {
            provisional[fractions[i].id] += 1;
        }
    }

    return provisional;
}

/**
 * Run client-side preview calculation matching the exact server-side algorithm.
 */
export function calculatePreview(input: CalculationInput): CalculationPreviewResult {
    const participants: Record<string, ParticipantShareResult> = {};
    const rawParticipants = input.participants || [];

    rawParticipants.forEach((p, idx) => {
        const id = String(p.id);
        participants[id] = {
            id,
            name: (p.name || '').trim() || `Partisipan ${idx + 1}`,
            position: p.position ?? idx,
            subtotal_minor: 0,
            discount_minor: 0,
            tax_minor: 0,
            service_charge_minor: 0,
            tip_minor: 0,
            final_total_minor: 0,
        };
    });

    const participantIds = Object.keys(participants);
    let subtotalMinor = 0;
    const processedItems: CalculationPreviewResult['items'] = [];

    if (input.split_mode === 'equal') {
        if (input.equal_subtotal_minor !== undefined && input.equal_subtotal_minor !== null) {
            subtotalMinor = Math.max(0, Math.round(Number(input.equal_subtotal_minor) || 0));
        } else if (input.items && input.items.length > 0) {
            input.items.forEach((item, idx) => {
                const qty = Number(item.quantity) || 0;
                const unitPrice = Number(item.unit_price_minor) || 0;
                const lineSubtotal = Math.round(qty * unitPrice);
                subtotalMinor += lineSubtotal;

                processedItems.push({
                    id: item.id,
                    description: item.description,
                    quantity: qty,
                    unit_price_minor: unitPrice,
                    line_subtotal_minor: lineSubtotal,
                    position: item.position ?? idx,
                    assignments: {},
                });
            });
        } else {
            subtotalMinor = 0;
        }

        const shares = distributeRemainderEqually(subtotalMinor, participantIds);
        for (const id of participantIds) {
            participants[id].subtotal_minor = shares[id] || 0;
        }
    } else {
        // Itemized mode
        const items = input.items || [];
        items.forEach((item, idx) => {
            const qty = Number(item.quantity) || 0;
            const unitPrice = Number(item.unit_price_minor) || 0;
            const lineSubtotal = Math.round(qty * unitPrice);
            subtotalMinor += lineSubtotal;

            const assignedIds = (item.assigned_participant_ids || []).filter((id) =>
                participantIds.includes(String(id))
            );

            const effectiveAssigned = assignedIds.length > 0 ? assignedIds : participantIds;
            const itemShares = distributeRemainderEqually(lineSubtotal, effectiveAssigned);

            for (const pId of effectiveAssigned) {
                if (participants[pId]) {
                    participants[pId].subtotal_minor += itemShares[pId] || 0;
                }
            }

            processedItems.push({
                id: item.id,
                description: item.description,
                quantity: qty,
                unit_price_minor: unitPrice,
                line_subtotal_minor: lineSubtotal,
                position: item.position ?? idx,
                assignments: itemShares,
            });
        });
    }

    // Process adjustments
    const adjustmentsInput = [...(input.adjustments || [])].sort(
        (a, b) => (a.position ?? 0) - (b.position ?? 0)
    );

    let discountTotalMinor = 0;
    let taxTotalMinor = 0;
    let serviceChargeMinor = 0;
    let tipTotalMinor = 0;
    const processedAdjustments: CalculationPreviewResult['adjustments'] = [];

    // 1. Discounts
    adjustmentsInput.forEach((adj, idx) => {
        if (adj.type !== 'discount' || adj.is_enabled === false) return;

        let amount = 0;
        if (adj.value_type === 'percentage') {
            amount = Math.round(subtotalMinor * ((adj.percentage_rate || 0) / 100));
        } else {
            amount = Math.round(adj.value_minor || 0);
        }

        amount = Math.min(amount, Math.max(0, subtotalMinor - discountTotalMinor));
        discountTotalMinor += amount;

        const shares = distributeAdjustmentProportionally(amount, participants, 'subtotal_minor');
        for (const pId of participantIds) {
            participants[pId].discount_minor += shares[pId] || 0;
        }

        processedAdjustments.push({
            id: adj.id,
            type: adj.type,
            label: adj.label,
            value_type: adj.value_type,
            value_minor: adj.value_minor ?? null,
            percentage_rate: adj.percentage_rate ?? null,
            base_type: adj.base_type || 'subtotal',
            is_enabled: Boolean(adj.is_enabled ?? true),
            position: adj.position ?? idx,
            amount_minor: amount,
            shares,
        });
    });

    const taxableBase = Math.max(0, subtotalMinor - discountTotalMinor);

    // 2. Tax, Service, Tip
    adjustmentsInput.forEach((adj, idx) => {
        if (adj.type === 'discount' || adj.is_enabled === false) return;

        const base = adj.base_type === 'subtotal' ? subtotalMinor : taxableBase;
        let amount = 0;

        if (adj.value_type === 'percentage') {
            amount = Math.round(base * ((adj.percentage_rate || 0) / 100));
        } else {
            amount = Math.round(adj.value_minor || 0);
        }

        const shares = distributeAdjustmentProportionally(amount, participants, 'subtotal_minor');

        if (adj.type === 'tax') {
            taxTotalMinor += amount;
            for (const pId of participantIds) {
                participants[pId].tax_minor += shares[pId] || 0;
            }
        } else if (adj.type === 'service_charge') {
            serviceChargeMinor += amount;
            for (const pId of participantIds) {
                participants[pId].service_charge_minor += shares[pId] || 0;
            }
        } else if (adj.type === 'tip') {
            tipTotalMinor += amount;
            for (const pId of participantIds) {
                participants[pId].tip_minor += shares[pId] || 0;
            }
        }

        processedAdjustments.push({
            id: adj.id,
            type: adj.type,
            label: adj.label,
            value_type: adj.value_type,
            value_minor: adj.value_minor ?? null,
            percentage_rate: adj.percentage_rate ?? null,
            base_type: adj.base_type || 'after_discount',
            is_enabled: Boolean(adj.is_enabled ?? true),
            position: adj.position ?? idx,
            amount_minor: amount,
            shares,
        });
    });

    const grandTotalMinor =
        subtotalMinor - discountTotalMinor + taxTotalMinor + serviceChargeMinor + tipTotalMinor;

    for (const pId of participantIds) {
        participants[pId].final_total_minor =
            participants[pId].subtotal_minor -
            participants[pId].discount_minor +
            participants[pId].tax_minor +
            participants[pId].service_charge_minor +
            participants[pId].tip_minor;
    }

    return {
        subtotal_minor: subtotalMinor,
        discount_total_minor: discountTotalMinor,
        tax_total_minor: taxTotalMinor,
        service_charge_minor: serviceChargeMinor,
        tip_total_minor: tipTotalMinor,
        grand_total_minor: grandTotalMinor,
        participants,
        items: processedItems,
        adjustments: processedAdjustments,
    };
}
