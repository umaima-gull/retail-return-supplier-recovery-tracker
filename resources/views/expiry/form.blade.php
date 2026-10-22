@extends('layouts.app')

@php
$isEdit = $record->exists;
@endphp

@section('title', $isEdit ? __('expiry.edit') : __('expiry.add'))

@section('content')
@include('partials.topbar', ['title' => $isEdit ? __('expiry.edit') : __('expiry.add'), 'subtitle' => __('expiry.form_sub')])

@if ($errors->any())
<div class="flash flash-error">
<ul>
@foreach ($errors->all() as $error)
<li>{{ $error }}</li>
@endforeach
</ul>
</div>
@endif

<form method="POST" class="panel" action="{{ $isEdit ? route('expiry.update', $record) : route('expiry.store') }}">
@csrf
@if ($isEdit) @method('PUT') @endif

<div class="form-grid">

<div class="field full">
<label for="product_id">{{ __('expiry.f_product_id') }} <i>*</i></label>
<select id="product_id" name="product_id" class="input">
<option value="">{{ __('expiry.select') }}</option>
@foreach ($products as $p)
<option value="{{ $p->id }}" @selected(old('product_id', $record->product_id) == $p->id)>{{ $p->display_name }} — {{ $p->sku }}</option>
@endforeach
</select>
</div>

<div class="field">
<label for="qty">{{ __('expiry.f_qty') }} <i>*</i></label>
<input id="qty" name="qty" type="number" min="1" step="1" class="input" value="{{ old('qty', $record->qty) }}">
</div>

<div class="field">
<label for="expiry_date">{{ __('expiry.f_expiry_date') }} <i>*</i></label>
<input id="expiry_date" name="expiry_date" type="date" class="input"
       value="{{ old('expiry_date', optional($record->expiry_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
</div>

<div class="field">
<label for="action_status">{{ __('expiry.f_action_status') }} <i>*</i></label>
<select id="action_status" name="action_status" class="input">
@foreach (\App\Models\ExpiryRecord::ACTIONS as $st)
<option value="{{ $st }}" @selected(old('action_status', $record->action_status) === $st)>{{ __('expiry.action.' . $st) }}</option>
@endforeach
</select>
</div>

<div class="field">
<label for="loss_value">{{ __('expiry.f_loss_value') }}</label>
<input id="loss_value" name="loss_value" type="number" min="0" step="0.01" class="input" value="{{ old('loss_value', $record->loss_value) }}">
<span class="hint">{{ __('expiry.hint_loss') }}</span>
</div>

<div class="field full">
<label for="notes">{{ __('expiry.f_notes') }}</label>
<textarea id="notes" name="notes" class="input">{{ old('notes', $record->notes) }}</textarea>
</div>

@unless ($isEdit)
<div class="field full">
<label class="check"><input type="checkbox" name="deduct_stock" value="1" @checked(old('deduct_stock', true))> {{ __('expiry.f_deduct_stock') }}</label>
<span class="hint">{{ __('expiry.hint_deduct') }}</span>
</div>
@endunless

</div>

<div class="form-actions">
<button class="btn btn-primary" type="submit">{{ __('common.save') }}</button>
<a class="btn btn-ghost" href="{{ route('expiry.index') }}">{{ __('common.cancel') }}</a>
</div>
</form>
@endsection