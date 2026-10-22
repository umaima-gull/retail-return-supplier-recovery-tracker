@extends('layouts.app')

@php
$isEdit = $record->exists;
@endphp

@section('title', $isEdit ? __('damaged.edit') : __('damaged.add'))

@section('content')
@include('partials.topbar', ['title' => $isEdit ? __('damaged.edit') : __('damaged.add'), 'subtitle' => __('damaged.form_sub')])

@if ($errors->any())
<div class="flash flash-error">
<ul>
@foreach ($errors->all() as $error)
<li>{{ $error }}</li>
@endforeach
</ul>
</div>
@endif

<form method="POST" class="panel" enctype="multipart/form-data"
      action="{{ $isEdit ? route('damaged-products.update', $record) : route('damaged-products.store') }}">
@csrf
@if ($isEdit) @method('PUT') @endif

<div class="form-grid">

<div class="field full">
<label for="product_id">{{ __('damaged.f_product_id') }} <i>*</i></label>
<select id="product_id" name="product_id" class="input">
<option value="">{{ __('damaged.select') }}</option>
@foreach ($products as $p)
<option value="{{ $p->id }}" @selected(old('product_id', $record->product_id) == $p->id)>{{ $p->display_name }} — {{ $p->sku }}</option>
@endforeach
</select>
</div>

<div class="field">
<label for="qty">{{ __('damaged.f_qty') }} <i>*</i></label>
<input id="qty" name="qty" type="number" min="1" step="1" class="input" value="{{ old('qty', $record->qty) }}">
</div>

<div class="field">
<label for="reported_at">{{ __('damaged.f_reported_at') }} <i>*</i></label>
<input id="reported_at" name="reported_at" type="date" max="{{ now()->format('Y-m-d') }}" class="input"
       value="{{ old('reported_at', optional($record->reported_at)->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
</div>

<div class="field">
<label for="damage_stage">{{ __('damaged.f_damage_stage') }} <i>*</i></label>
<select id="damage_stage" name="damage_stage" class="input">
@foreach (\App\Models\DamagedProduct::STAGES as $st)
<option value="{{ $st }}" @selected(old('damage_stage', $record->damage_stage) === $st)>{{ __('damaged.stage.' . $st) }}</option>
@endforeach
</select>
</div>

<div class="field">
<label for="status">{{ __('damaged.f_status') }} <i>*</i></label>
<select id="status" name="status" class="input">
@foreach (\App\Models\DamagedProduct::STATUSES as $st)
<option value="{{ $st }}" @selected(old('status', $record->status) === $st)>{{ __('damaged.status.' . $st) }}</option>
@endforeach
</select>
</div>

<div class="field">
<label for="loss_value">{{ __('damaged.f_loss_value') }}</label>
<input id="loss_value" name="loss_value" type="number" min="0" step="0.01" class="input" value="{{ old('loss_value', $record->loss_value) }}">
<span class="hint">{{ __('damaged.hint_loss') }}</span>
</div>

<div class="field">
<label for="reason">{{ __('damaged.f_reason') }}</label>
<input id="reason" name="reason" type="text" class="input" value="{{ old('reason', $record->reason) }}">
</div>

<div class="field full">
<label for="notes">{{ __('damaged.f_notes') }}</label>
<textarea id="notes" name="notes" class="input">{{ old('notes', $record->notes) }}</textarea>
</div>

<div class="field full">
<label for="photo">{{ __('damaged.f_photo') }}</label>
@if ($record->photo_url)
<div style="margin-bottom:8px"><a href="{{ $record->photo_url }}" target="_blank"><img class="thumb-lg" src="{{ $record->photo_url }}" alt=""></a></div>
@endif
<input id="photo" name="photo" type="file" accept="image/png,image/jpeg,image/webp" class="input">
<span class="hint">{{ __('damaged.hint_photo') }}</span>
</div>

@unless ($isEdit)
<div class="field full">
<label class="check"><input type="checkbox" name="deduct_stock" value="1" @checked(old('deduct_stock', true))> {{ __('damaged.f_deduct_stock') }}</label>
<span class="hint">{{ __('damaged.hint_deduct') }}</span>
</div>
@endunless

</div>

<div class="form-actions">
<button class="btn btn-primary" type="submit">{{ __('common.save') }}</button>
<a class="btn btn-ghost" href="{{ route('damaged-products.index') }}">{{ __('common.cancel') }}</a>
</div>
</form>
@endsection