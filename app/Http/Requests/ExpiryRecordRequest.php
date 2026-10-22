<?php

namespace App\Http\Requests;

use App\Models\ExpiryRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpiryRecordRequest extends FormRequest
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
            'product_id'    => ['required', 'integer', 'exists:products,id'],
            'qty'           => ['required', 'integer', 'min:1', 'max:99999'],
            'expiry_date'   => ['required', 'date'],
            'action_status' => ['required', Rule::in(ExpiryRecord::ACTIONS)],
            'loss_value'    => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'notes'         => ['nullable', 'string', 'max:2000'],
            'deduct_stock'  => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => __('common.v_required'),
            'max'      => __('common.v_max'),
            'min'      => __('common.v_min'),
            'integer'  => __('common.v_integer'),
            'numeric'  => __('common.v_numeric'),
            'exists'   => __('common.v_exists'),
            'in'       => __('common.v_exists'),
            'date'     => __('common.v_date'),
        ];
    }

    public function attributes(): array
    {
        return collect(array_keys($this->rules()))
            ->mapWithKeys(fn ($f) => [$f => __('expiry.f_' . $f)])
            ->all();
    }
}