<?php

namespace App\Http\Requests;

use App\Models\DamagedProduct;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DamagedProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['deduct_stock' => $this->boolean('deduct_stock')]);
    }

    public function rules(): array
    {
        return [
            'product_id'   => ['required', 'integer', 'exists:products,id'],
            'qty'          => ['required', 'integer', 'min:1', 'max:99999'],
            'damage_stage' => ['required', Rule::in(DamagedProduct::STAGES)],
            'reason'       => ['nullable', 'string', 'max:255'],
            'loss_value'   => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'photo'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status'       => ['required', Rule::in(DamagedProduct::STATUSES)],
            'reported_at'  => ['required', 'date', 'before_or_equal:today'],
            'notes'        => ['nullable', 'string', 'max:2000'],
            'deduct_stock' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'required'        => __('common.v_required'),
            'max'             => __('common.v_max'),
            'min'             => __('common.v_min'),
            'integer'         => __('common.v_integer'),
            'numeric'         => __('common.v_numeric'),
            'exists'          => __('common.v_exists'),
            'in'              => __('common.v_exists'),
            'date'            => __('common.v_date'),
            'before_or_equal' => __('common.v_future'),
            'image'           => __('common.v_image'),
            'mimes'           => __('common.v_image'),
        ];
    }

    public function attributes(): array
    {
        return collect(array_keys($this->rules()))
            ->mapWithKeys(fn ($f) => [$f => __('damaged.f_' . $f)])
            ->all();
    }
}