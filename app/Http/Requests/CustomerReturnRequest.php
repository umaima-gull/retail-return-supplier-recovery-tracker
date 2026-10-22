<?php

namespace App\Http\Requests;

use App\Models\CustomerReturn;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['restock' => $this->boolean('restock')]);
    }

    public function rules(): array
    {
        return [
            'product_id'     => ['required', 'integer', 'exists:products,id'],
            'qty'            => ['required', 'integer', 'min:1', 'max:99999'],
            'returned_at'    => ['required', 'date', 'before_or_equal:today'],
            'reason'         => ['required', Rule::in(CustomerReturn::REASONS)],
            'item_condition' => ['required', Rule::in(CustomerReturn::CONDITIONS)],
            'refund_amount'  => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'refund_method'  => ['required', Rule::in(CustomerReturn::METHODS)],
            'customer_name'  => ['nullable', 'string', 'max:150'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'invoice_no'     => ['nullable', 'string', 'max:60'],
            'status'         => ['required', Rule::in(CustomerReturn::STATUSES)],
            'notes'          => ['nullable', 'string', 'max:2000'],
            'restock'        => ['boolean'],
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
        ];
    }

    public function attributes(): array
    {
        return collect(array_keys($this->rules()))
            ->mapWithKeys(fn ($f) => [$f => __('customer_returns.f_' . $f)])
            ->all();
    }
}