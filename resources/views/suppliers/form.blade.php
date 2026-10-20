@extends('layouts.app')

@php
$isEdit = $supplier->exists;
@endphp

@section('title', $isEdit ? __('suppliers.edit') : __('suppliers.add'))

@section('content')
@include('partials.topbar', ['title' => $isEdit ? __('suppliers.edit') : __('suppliers.add'), 'subtitle' => __('suppliers.form_sub')])

@if ($errors->any())
<div class="flash flash-error">
<ul>
@foreach ($errors->all() as $error)
<li>{{ $error }}</li>
@endforeach
</ul>
</div>
@endif

@php
$fields = [
    ['name' => 'name',               'type' => 'text',   'required' => true],
    ['name' => 'name_ur',            'type' => 'text',   'ur' => true],
    ['name' => 'contact_person',     'type' => 'text'],
    ['name' => 'phone',              'type' => 'text',   'ltr' => true],
    ['name' => 'email',              'type' => 'email',  'ltr' => true],
    ['name' => 'city',               'type' => 'text'],
    ['name' => 'return_policy_days', 'type' => 'number', 'required' => true, 'hint' => 'hint_return'],
    ['name' => 'response_time_days', 'type' => 'number', 'required' => true, 'hint' => 'hint_response'],
    ['name' => 'payment_terms',      'type' => 'text'],
];
@endphp

<form method="POST" class="panel" action="{{ $isEdit ? route('suppliers.update', $supplier) : route('suppliers.store') }}">
@csrf
@if ($isEdit) @method('PUT') @endif

<div class="form-grid">
@foreach ($fields as $f)
<div class="field">
<label for="{{ $f['name'] }}">{{ __('suppliers.f_' . $f['name']) }} @if (! empty($f['required']))<i>*</i>@endif</label>
<input id="{{ $f['name'] }}" name="{{ $f['name'] }}" type="{{ $f['type'] }}"
       value="{{ old($f['name'], $supplier->{$f['name']}) }}"
       class="input {{ ! empty($f['ur']) ? 'ur-input' : '' }}"
       @if (! empty($f['ur'])) dir="rtl" @elseif (! empty($f['ltr'])) dir="ltr" @endif
       @if ($f['type'] === 'number') min="0" max="365" @endif>
@if (! empty($f['hint']))<span class="hint">{{ __('suppliers.' . $f['hint']) }}</span>@endif
</div>
@endforeach

<div class="field full">
<label for="address">{{ __('suppliers.f_address') }}</label>
<input id="address" name="address" type="text" class="input" value="{{ old('address', $supplier->address) }}">
</div>

<div class="field full">
<label for="notes">{{ __('suppliers.f_notes') }}</label>
<textarea id="notes" name="notes" class="input">{{ old('notes', $supplier->notes) }}</textarea>
</div>

<div class="field full">
<input type="hidden" name="is_active" value="0">
<label class="check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $supplier->is_active))> {{ __('suppliers.f_is_active') }}</label>
</div>
</div>

<div class="form-actions">
<button class="btn btn-primary" type="submit">{{ __('common.save') }}</button>
<a class="btn btn-ghost" href="{{ route('suppliers.index') }}">{{ __('common.cancel') }}</a>
</div>
</form>
@endsection