@extends('layouts.app')

@php
$isEdit = $product->exists;
@endphp

@section('title', $isEdit ? __('products.edit') : __('products.add'))

@section('content')
@include('partials.topbar', ['title' => $isEdit ? __('products.edit') : __('products.add'), 'subtitle' => __('products.form_sub')])

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
    ['name' => 'sku',           'type' => 'text',   'required' => true, 'ltr' => true],
    ['name' => 'barcode',       'type' => 'text',   'ltr' => true],
    ['name' => 'name',          'type' => 'text',   'required' => true],
    ['name' => 'name_ur',       'type' => 'text',   'ur' => true],
    ['name' => 'unit',          'type' => 'text',   'required' => true, 'hint' => 'hint_unit'],
    ['name' => 'stock_qty',     'type' => 'number', 'required' => true, 'step' => '1'],
    ['name' => 'cost_price',    'type' => 'number', 'required' => true, 'step' => '0.01'],
    ['name' => 'sale_price',    'type' => 'number', 'required' => true, 'step' => '0.01'],
    ['name' => 'reorder_level', 'type' => 'number', 'required' => true, 'step' => '1', 'hint' => 'hint_reorder'],
];
@endphp

<form method="POST" class="panel" action="{{ $isEdit ? route('products.update', $product) : route('products.store') }}">
@csrf
@if ($isEdit) @method('PUT') @endif

<div class="form-grid">

<div class="field">
<label for="category_id">{{ __('products.f_category_id') }}</label>
<select id="category_id" name="category_id" class="input">
<option value="">{{ __('products.select') }}</option>
@foreach ($categories as $c)
<option value="{{ $c->id }}" @selected(old('category_id', $product->category_id) == $c->id)>{{ $c->display_name }}</option>
@endforeach
</select>
</div>

<div class="field">
<label for="supplier_id">{{ __('products.f_supplier_id') }}</label>
<select id="supplier_id" name="supplier_id" class="input">
<option value="">{{ __('products.select') }}</option>
@foreach ($suppliers as $s)
<option value="{{ $s->id }}" @selected(old('supplier_id', $product->supplier_id) == $s->id)>{{ $s->display_name }}</option>
@endforeach
</select>
</div>

@foreach ($fields as $f)
<div class="field">
<label for="{{ $f['name'] }}">{{ __('products.f_' . $f['name']) }} @if (! empty($f['required']))<i>*</i>@endif</label>
<input id="{{ $f['name'] }}" name="{{ $f['name'] }}" type="{{ $f['type'] }}"
       value="{{ old($f['name'], $product->{$f['name']}) }}"
       class="input {{ ! empty($f['ur']) ? 'ur-input' : '' }}"
       @if (! empty($f['ur'])) dir="rtl" @elseif (! empty($f['ltr'])) dir="ltr" @endif
       @if ($f['type'] === 'number') min="0" step="{{ $f['step'] ?? '1' }}" @endif>
@if (! empty($f['hint']))<span class="hint">{{ __('products.' . $f['hint']) }}</span>@endif
</div>
@endforeach

<div class="field full">
<div class="checks">
<input type="hidden" name="has_expiry" value="0">
<label class="check"><input type="checkbox" name="has_expiry" value="1" @checked(old('has_expiry', $product->has_expiry))> {{ __('products.f_has_expiry') }}</label>

<input type="hidden" name="is_returnable" value="0">
<label class="check"><input type="checkbox" name="is_returnable" value="1" @checked(old('is_returnable', $product->is_returnable))> {{ __('products.f_is_returnable') }}</label>

<input type="hidden" name="is_active" value="0">
<label class="check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active))> {{ __('products.f_is_active') }}</label>
</div>
</div>

</div>

<div class="form-actions">
<button class="btn btn-primary" type="submit">{{ __('common.save') }}</button>
<a class="btn btn-ghost" href="{{ route('products.index') }}">{{ __('common.cancel') }}</a>
</div>
</form>
@endsection