@extends('layouts.app')

@php
$isEdit = $record->exists;
@endphp

@section('title', $isEdit ? __('customer_returns.edit') : __('customer_returns.add'))

@section('content')
@include('partials.topbar', ['title' => $isEdit ? __('customer_returns.edit') : __('customer_returns.add'), 'subtitle' => __('customer_returns.form_sub')])

@if ($errors->any())
<div class="flash flash-error">
<ul>
@foreach ($errors->all() as $error)
<li>{{ $error }}</li>
@endforeach
</ul>
</div>
@endif

<form method="POST" class="panel" action="{{ $isEdit ? route('customer-returns.update', $record) : route('customer-returns.store') }}">
@csrf
@if ($isEdit) @method('PUT') @endif

<div class="form-grid">

@if ($isEdit)
<div class="field full">
<label>{{ __('customer_returns.f_return_no') }}</label>
<input type="text" class="input" value="{{ $record->return_no }}" dir="ltr" disabled>
</div>
@endif

<div class="field full">
<label for="product_id">{{ __('customer_returns.f_product_id') }} <i>*</i></label>
<select id="product_id" name="product_id" class="input">
<option value="">{{ __('customer_returns.select') }}</option>
@foreach ($products as $p)
<option value="{{ $p->id }}" @selected(old('product_id', $record->product_id) == $p->id)>{{ $p->display_name }} — {{ $p->sku }}</option>
@endforeach
</select>
</div>

<div class="field">
<label for="qty">{{ __('customer_returns.f_qty') }} <i>*</i></label>
<input id="qty" name="qty" type="number" min="1" step="1" class="input" value="{{ old('qty', $record->qty) }}">
</div>

<div class="field">
<label for="returned_at">{{ __('customer_returns.f_returned_at') }} <i>*</i></label>
<input id="returned_at" name="returned_at" type="date" max="{{ now()->format('Y-m-d') }}" class="input"
       value="{{ old('returned_at', optional($record->returned_at)->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
</div>

<div class="field">
<label for="reason">{{ __('customer_returns.f_reason') }} <i>*</i></label>
<select id="reason" name="reason" class="input">
@foreach (\App\Models\CustomerReturn::REASONS as $st)
<option value="{{ $st }}" @selected(old('reason', $record->reason) === $st)>{{ __('customer_returns.reason.' . $st) }}</option>
@endforeach
</select>
</div>

<div class="field">
<label for="item_condition">{{ __('customer_returns.f_item_condition') }} <i>*</i></label>
<select id="item_condition" name="item_condition" class="input">
@foreach (\App\Models\CustomerReturn::CONDITIONS as $st)
<option value="{{ $st }}" @selected(old('item_condition', $record->item_condition) === $st)>{{ __('customer_returns.condition.' . $st) }}</option>
@endforeach
</select>
</div>

<div class="field">
<label for="refund_amount">{{ __('customer_returns.f_refund_amount') }}</label>
<input id="refund_amount" name="refund_amount" type="number" min="0" step="0.01" class="input" value="{{ old('refund_amount', $record->refund_amount) }}">
<span class="hint">{{ __('customer_returns.hint_refund') }}</span>
</div>

<div class="field">
<label for="refund_method">{{ __('customer_returns.f_refund_method') }} <i>*</i></label>
<select id="refund_method" name="refund_method" class="input">
@foreach (\App\Models\CustomerReturn::METHODS as $st)
<option value="{{ $st }}" @selected(old('refund_method', $record->refund_method) === $st)>{{ __('customer_returns.method.' . $st) }}</option>
@endforeach
</select>
</div>

<div class="field">
<label for="customer_name">{{ __('customer_returns.f_customer_name') }}</label>
<input id="customer_name" name="customer_name" type="text" class="input" value="{{ old('customer_name', $record->customer_name) }}">
</div>

<div class="field">
<label for="customer_phone">{{ __('customer_returns.f_customer_phone') }}</label>
<input id="customer_phone" name="customer_phone" type="text" dir="ltr" class="input" value="{{ old('customer_phone', $record->customer_phone) }}">
</div>

<div class="field">
<label for="invoice_no">{{ __('customer_returns.f_invoice_no') }}</label>
<input id="invoice_no" name="invoice_no" type="text" dir="ltr" class="input" value="{{ old('invoice_no', $record->invoice_no) }}">
</div>

<div class="field">
<label for="status">{{ __('customer_returns.f_status') }} <i>*</i></label>
<select id="status" name="status" class="input">
@foreach (\App\Models\CustomerReturn::STATUSES as $st)
<option value="{{ $st }}" @selected(old('status', $record->status) === $st)>{{ __('customer_returns.status.' . $st) }}</option>
@endforeach
</select>
</div>

<div class="field full">
<label for="notes">{{ __('customer_returns.f_notes') }}</label>
<textarea id="notes" name="notes" class="input">{{ old('notes', $record->notes) }}</textarea>
</div>

@unless ($isEdit)
<div class="field full">
<label class="check"><input type="checkbox" name="restock" value="1" @checked(old('restock', true))> {{ __('customer_returns.f_restock') }}</label>
<span class="hint">{{ __('customer_returns.hint_restock') }}</span>
</div>
@endunless

</div>

<div class="form-actions">
<button class="btn btn-primary" type="submit">{{ __('common.save') }}</button>
<a class="btn btn-ghost" href="{{ route('customer-returns.index') }}">{{ __('common.cancel') }}</a>
</div>
</form>
@endsection