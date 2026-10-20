<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'has_expiry'    => $this->boolean('has_expiry'),
            'is_returnable' => $this->boolean('is_returnable'),
            'is_active'     => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        return [
            'category_id'   => ['nullable', 'integer', 'exists:categories,id'],
            'supplier_id'   => ['nullable', 'integer', 'exists:suppliers,id'],
            'sku'           => ['required', 'string', 'max:60', Rule::unique('products', 'sku')->ignore($this->route('product'))],
            'barcode'       => ['nullable', 'string', 'max:60'],
            'name'          => ['required', 'string', 'max:200'],
            'name_ur'       => ['nullable', 'string', 'max:200'],
            'unit'          => ['required', 'string', 'max:20'],
            'cost_price'    => ['required', 'numeric', 'min:0', 'max:99999999'],
            'sale_price'    => ['required', 'numeric', 'min:0', 'max:99999999'],
            'stock_qty'     => ['required', 'integer', 'min:0', 'max:99999999'],
            'reorder_level' => ['required', 'integer', 'min:0', 'max:99999999'],
            'has_expiry'    => ['boolean'],
            'is_returnable' => ['boolean'],
            'is_active'     => ['boolean'],
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
            'unique'   => __('common.v_unique'),
            'exists'   => __('common.v_exists'),
        ];
    }

    public function attributes(): array
    {
        return collect(array_keys($this->rules()))
            ->mapWithKeys(fn ($f) => [$f => __('products.f_' . $f)])
            ->all();
    }
}