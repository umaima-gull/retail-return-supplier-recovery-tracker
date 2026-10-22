@extends('layouts.app')

@section('title', __('customer_returns.title'))

@section('content')
@include('partials.topbar', ['title' => __('customer_returns.title'), 'subtitle' => __('customer_returns.subtitle')])

@if (session('success'))
<div class="flash">{{ session('success') }}</div>
@endif

<section class="stats">
<div class="stat" style="--accent:#7D5BA6;--accent2:#B197FC">
<div class="ico">&#10227;</div><h3>{{ __('customer_returns.stat_records') }}</h3>
<div class="val">{{ number_format($totals['records']) }}</div><div class="trend">{{ __('customer_returns.stat_records_sub') }}</div>
</div>
<div class="stat" style="--accent:#D96C47;--accent2:#F08C4B">
<div class="ico">&#128176;</div><h3>{{ __('customer_returns.stat_refunded') }}</h3>
<div class="val">Rs {{ number_format($totals['refunded']) }}</div><div class="trend">{{ __('customer_returns.stat_refunded_sub') }}</div>
</div>
<div class="stat" style="--accent:#7FB069;--accent2:#A9C46C">
<div class="ico">&#128230;</div><h3>{{ __('customer_returns.stat_resalable') }}</h3>
<div class="val">{{ number_format($totals['resalable']) }}</div><div class="trend">{{ __('customer_returns.stat_resalable_sub') }}</div>
</div>
<div class="stat" style="--accent:#E0A458;--accent2:#F5C67B">
<div class="ico">&#8617;</div><h3>{{ __('customer_returns.stat_claimable') }}</h3>
<div class="val">{{ number_format($totals['claimable']) }}</div><div class="trend">{{ __('customer_returns.stat_claimable_sub') }}</div>
</div>
</section>

<div class="panel">
<form method="GET" action="{{ route('customer-returns.index') }}" class="toolbar">
<input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('common.search') }}" class="input">
<select name="reason" class="input">
<option value="">{{ __('customer_returns.all_reasons') }}</option>
@foreach (\App\Models\CustomerReturn::REASONS as $st)
<option value="{{ $st }}" @selected(request('reason') === $st)>{{ __('customer_returns.reason.' . $st) }}</option>
@endforeach
</select>
<select name="condition" class="input">
<option value="">{{ __('customer_returns.all_conditions') }}</option>
@foreach (\App\Models\CustomerReturn::CONDITIONS as $st)
<option value="{{ $st }}" @selected(request('condition') === $st)>{{ __('customer_returns.condition.' . $st) }}</option>
@endforeach
</select>
<select name="status" class="input">
<option value="">{{ __('customer_returns.all_status') }}</option>
@foreach (\App\Models\CustomerReturn::STATUSES as $st)
<option value="{{ $st }}" @selected(request('status') === $st)>{{ __('customer_returns.status.' . $st) }}</option>
@endforeach
</select>
<button class="btn btn-ghost" type="submit">{{ __('common.search_btn') }}</button>
<a class="btn btn-primary" href="{{ route('customer-returns.create') }}">&#43; {{ __('customer_returns.add') }}</a>
</form>

@php
$statusTag = ['received' => 't-pending', 'claim_raised' => 't-progress', 'closed' => 't-approved'];
@endphp

<div class="table-wrap">
<table>
<tr>
<th>{{ __('customer_returns.th_return') }}</th>
<th>{{ __('customer_returns.th_product') }}</th>
<th>{{ __('customer_returns.th_customer') }}</th>
<th>{{ __('customer_returns.th_qty') }}</th>
<th>{{ __('customer_returns.th_reason') }}</th>
<th>{{ __('customer_returns.th_refund') }}</th>
<th>{{ __('customer_returns.th_claim') }}</th>
<th>{{ __('common.status') }}</th>
<th>{{ __('common.actions') }}</th>
</tr>
@forelse ($records as $r)
<tr>
<td><span class="pill" dir="ltr">{{ $r->return_no }}</span><span class="cell-sub" dir="ltr">{{ $r->returned_at->format('d-m-Y') }}</span></td>
<td><b><bdi>{{ $r->product?->display_name ?? '—' }}</bdi></b><span class="cell-sub" dir="ltr">{{ $r->product?->sku }}</span></td>
<td><bdi>{{ $r->customer_name ?: '—' }}</bdi>@if ($r->customer_phone)<span class="cell-sub" dir="ltr">{{ $r->customer_phone }}</span>@endif</td>
<td>{{ number_format($r->qty) }}</td>
<td>{{ __('customer_returns.reason.' . $r->reason) }}<span class="cell-sub">{{ __('customer_returns.condition.' . $r->item_condition) }}</span></td>
<td>Rs {{ number_format($r->refund_amount, floor($r->refund_amount) == $r->refund_amount ? 0 : 2) }}<span class="cell-sub">{{ __('customer_returns.method.' . $r->refund_method) }}</span></td>
<td>@if ($r->claim)<span class="pill">{{ $r->claim->claim_no }}</span>@else — @endif</td>
<td><span class="tag {{ $statusTag[$r->status] ?? 't-pending' }}">{{ __('customer_returns.status.' . $r->status) }}</span></td>
<td>
<div class="row-actions">
<a class="btn btn-ghost btn-sm" href="{{ route('customer-returns.edit', $r) }}">{{ __('common.edit') }}</a>
<form method="POST" action="{{ route('customer-returns.destroy', $r) }}" onsubmit="return confirm(@js(__('common.confirm_delete')))">
@csrf
@method('DELETE')
<button class="btn btn-danger btn-sm" type="submit">{{ __('common.delete') }}</button>
</form>
</div>
</td>
</tr>
@empty
<tr><td colspan="9" style="text-align:center;color:var(--muted)">{{ __('common.no_records') }}</td></tr>
@endforelse
</table>
</div>

@if ($records->hasPages())
<div class="pager">
@if ($records->onFirstPage())
<span class="btn btn-ghost btn-sm disabled">{{ __('common.prev') }}</span>
@else
<a class="btn btn-ghost btn-sm" href="{{ $records->previousPageUrl() }}">{{ __('common.prev') }}</a>
@endif
<span>{{ __('common.page_of', ['current' => $records->currentPage(), 'last' => $records->lastPage()]) }}</span>
@if ($records->hasMorePages())
<a class="btn btn-ghost btn-sm" href="{{ $records->nextPageUrl() }}">{{ __('common.next') }}</a>
@else
<span class="btn btn-ghost btn-sm disabled">{{ __('common.next') }}</span>
@endif
</div>
@endif
</div>
@endsection