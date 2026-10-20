<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    public function rules(): array
    {
        return [
            'name'               => ['required', 'string', 'max:150'],
            'name_ur'            => ['nullable', 'string', 'max:150'],
            'contact_person'     => ['nullable', 'string', 'max:120'],
            'phone'              => ['nullable', 'string', 'max:30'],
            'email'              => ['nullable', 'email', 'max:150'],
            'city'               => ['nullable', 'string', 'max:80'],
            'address'            => ['nullable', 'string', 'max:255'],
            'return_policy_days' => ['required', 'integer', 'min:0', 'max:365'],
            'response_time_days' => ['required', 'integer', 'min:0', 'max:365'],
            'payment_terms'      => ['nullable', 'string', 'max:100'],
            'notes'              => ['nullable', 'string', 'max:2000'],
            'is_active'          => ['boolean'],
        ];
    }

    
    public function messages(): array
    {
        return [
            'required' => __('common.v_required'),
            'max'      => __('common.v_max'),
            'min'      => __('common.v_min'),
            'email'    => __('common.v_email'),
            'integer'  => __('common.v_integer'),
        ];
    }

    public function attributes(): array
    {
        return collect(array_keys($this->rules()))
            ->mapWithKeys(fn ($f) => [$f => __('suppliers.f_' . $f)])
            ->all();
    }
}