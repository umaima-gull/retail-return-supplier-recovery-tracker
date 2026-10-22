@extends('layouts.app')

@section('title', __('supplier_returns.add'))

@section('content')
@include('partials.topbar', ['title' => __('supplier_returns.add'), 'subtitle' => __('supplier_returns.create_sub')])

@if ($errors->any())
<div class="flash flash-error">
<ul>
@foreach ($errors->all() as $error)
<li>{{ $error }}</li>
@endforeach
</ul>
</div>
@endif

<div class="stack">

<div class="panel">
<div class="group-title" style="margin-top:0">{{ __('supplier_returns.step1') }}</div>
<form method="GET" action="{{ route('supplier-returns.create') }}" class="toolbar" style="margin-bottom:0">
<select name="supplier" class="input">
<option value="">{{ __('supplier_returns.select_supplier') }}</option>
@foreach ($suppliers as $s)
<option value="{{ $s->id }}" @selected($supplier && $supplier->id === $s->id)>{{ $s->display_name }} ({{ $counts[$s->id] }})</option>
@endforeach
</select>
<button class="btn btn-primary" type="submit">{{ __('supplier_returns.load_items') }}</button>
<a class="btn btn-ghost" href="{{ route('supplier-returns.index') }}">{{ __('common.cancel') }}</a>
</form>
@if ($suppliers->isEmpty())
<p style="color:var(--muted);margin:14px 0 0">{{ __('supplier_returns.nothing_to_claim') }}</p>
@endif
</div>

@if ($supplier)
@php
$submitted = old('supplier_id') !== null;
$groups = [
    'damaged' => ['title' => __('supplier_returns.grp_damaged'), 'rows' => $eligible['damaged'], 'date' => 'reported_at'],
    'expiry'  => ['title' => __('supplier_returns.grp_expiry'),  'rows' => $eligible['expiry'],  'date' => 'expiry_date'],
    'returns' => ['title' => __('supplier_returns.grp_returns'), 'rows' => $eligible['returns'], 'date' => 'returned_at'],
];
@endphp

<form method="POST" action="{{ route('supplier-returns.store') }}" class="panel">
@csrf
<input type="hidden" name="supplier_id" value="{{ $supplier->id }}">

<div class="group-title" style="margin-top:0">{{ __('supplier_returns.step2', ['supplier' => $supplier->display_name]) }}</div>

@foreach ($groups as $key => $group)
@if ($group['rows']->isNotEmpty())
<div class="group-title">{{ $group['title'] }}</div>
<div class="table-wrap">
<table>
<tr>
<th class="check-cell"></th>
<th>{{ __('supplier_returns.th_product') }}</th>
<th>{{ __('supplier_returns.th_qty') }}</th>
<th>{{ __('supplier_returns.th_date') }}</th>
<th>{{ __('supplier_returns.th_note') }}</th>
<th>{{ __('supplier_returns.th_value') }}</th>
</tr>
@foreach ($group['rows'] as $row)
<tr>
<td class="check-cell"><input type="checkbox" name="{{ $key }}[]" value="{{ $row->id }}" @checked(! $submitted || in_array((string) $row->id, array_map('strval', old($key, []))))></td>
<td><b><bdi>{{ $row->product?->display_name ?? '—' }}</bdi></b><span class="cell-sub" dir="ltr">{{ $row->product?->sku }}</span></td>
<td>{{ number_format($row->qty) }}</td>
<td><span dir="ltr">{{ $row->{$group['date']}->format('d-m-Y') }}</span></td>
<td><bdi>@if ($key === 'damaged'){{ $row->reason ?: '—' }}@elseif ($key === 'returns'){{ $row->return_no }}@else — @endif</bdi></td>
<td>Rs {{ number_format($row->qty * ($row->product?->cost_price ?? 0)) }}</td>
</tr>
@endforeach
</table>
</div>
@endif
@endforeach

<div class="form-grid" style="margin-top:22px">
<div class="field">
<label for="claim_date">{{ __('supplier_returns.f_claim_date') }} <i>*</i></label>
<input id="claim_date" name="claim_date" type="date" max="{{ now()->format('Y-m-d') }}" class="input" value="{{ old('claim_date', now()->format('Y-m-d')) }}">
</div>
<div class="field">
<label for="due_date">{{ __('supplier_returns.f_due_date') }}</label>
<input id="due_date" name="due_date" type="date" class="input" value="{{ old('due_date') }}">
<span class="hint">{{ __('supplier_returns.hint_due', ['days' => $supplier->response_time_days]) }}</span>
</div>
<div class="field full">
<label for="notes">{{ __('supplier_returns.f_notes') }}</label>
<textarea id="notes" name="notes" class="input">{{ old('notes') }}</textarea>
</div>
</div>

<p style="color:var(--muted);font-size:12.5px;margin:14px 0 0">{{ __('supplier_returns.hint_value') }}</p>

<div class="form-actions">
<button class="btn btn-primary" type="submit">{{ __('supplier_returns.create_claim') }}</button>
<a class="btn btn-ghost" href="{{ route('supplier-returns.index') }}">{{ __('common.cancel') }}</a>
</div>
</form>
@endif

</div>
@endsection