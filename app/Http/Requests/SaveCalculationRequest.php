<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveCalculationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->hasVerifiedEmail();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'currency' => ['nullable', 'string', 'size:3'],
            'split_mode' => ['required', 'string', 'in:equal,itemized'],
            'status' => ['nullable', 'string', 'in:draft,completed'],
            'notes' => ['nullable', 'string', 'max:2000'],

            'participants' => ['required', 'array', 'min:1'],
            'participants.*.id' => ['required', 'string'],
            'participants.*.name' => ['required', 'string', 'max:100'],
            'participants.*.position' => ['nullable', 'integer'],

            'items' => ['required_if:split_mode,itemized', 'array'],
            'items.*.id' => ['nullable', 'string'],
            'items.*.description' => ['required_with:items', 'string', 'max:180'],
            'items.*.quantity' => ['required_with:items', 'numeric', 'gt:0', 'max:999999'],
            'items.*.unit_price_minor' => ['required_with:items', 'integer', 'min:0'],
            'items.*.assigned_participant_ids' => ['required_with:items', 'array', 'min:1'],
            'items.*.position' => ['nullable', 'integer'],

            'adjustments' => ['nullable', 'array'],
            'adjustments.*.id' => ['nullable', 'string'],
            'adjustments.*.type' => ['required_with:adjustments', 'string', 'in:discount,tax,service_charge,tip'],
            'adjustments.*.label' => ['required_with:adjustments', 'string', 'max:100'],
            'adjustments.*.value_type' => ['required_with:adjustments', 'string', 'in:fixed,percentage'],
            'adjustments.*.value_minor' => ['nullable', 'integer', 'min:0'],
            'adjustments.*.percentage_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'adjustments.*.base_type' => ['nullable', 'string', 'in:subtotal,after_discount'],
            'adjustments.*.is_enabled' => ['nullable', 'boolean'],
            'adjustments.*.position' => ['nullable', 'integer'],

            'equal_subtotal_minor' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Get custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul perhitungan wajib diisi.',
            'participants.required' => 'Minimal harus ada 1 orang partisipan.',
            'participants.min' => 'Minimal harus ada 1 orang partisipan.',
            'items.*.description.required_with' => 'Nama item wajib diisi.',
            'items.*.quantity.gt' => 'Jumlah item harus lebih dari 0.',
            'items.*.assigned_participant_ids.min' => 'Setiap item harus memiliki minimal 1 partisipan yang ditugaskan.',
            'adjustments.*.percentage_rate.max' => 'Persentase tidak boleh lebih dari 100%.',
        ];
    }
}
