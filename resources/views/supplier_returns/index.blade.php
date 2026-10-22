@extends('layouts.app')

@section('title', __('supplier_returns.title'))

@section('content')
@include('partials.topbar', ['title' => __('supplier_returns.title'), 'subtitle' => __('supplier_returns.subtitle')])

@if (session('success'))
<div class="flash">{{ session('success') }}</div>
@endif
@if (session('error'))
<div class="flash flash-error">{{ session('error') }}</div>
@endif

<section class="stats">
<div class="stat" style="--accent:#E0A458;--accent2:#F5C67B">
<div class="ico">&#8617;</div><h3>{{ __('supplier_returns.stat_open') }}</h3>
<div class="val">{{ number_format($totals['open']) }}</div><div class="trend">{{ __('supplier_returns.stat_open_sub') }}</div>
</div>
<div class="stat" style="--accent:#D96C47;--accent2:#F08C4B">
<div class="ico">&#9888;</div><h3>{{ __('supplier_returns.stat_overdue') }}</h3>
<div class="val">{{ number_format($totals['overdue']) }}</div><div class="trend">{{ __('supplier_returns.stat_overdue_sub') }}</div>
</div>
<div class="stat" style="--accent:#7D5BA6;--accent2:#B197FC">
<div class="ico">&#128221;</div><h3>{{ __('supplier_returns.stat_claimed') }}</h3>
<div class="val">Rs {{ number_format($totals['claimed']) }}</div><div class="trend">{{ __('supplier_returns.stat_claimed_sub') }}</div>
</div>
<div class="stat" style="--accent:#7FB069;--accent2:#A9C46C">
<div class="ico">&#128176;</div><h3>{{ __('supplier_returns.stat_recovered') }}</h3>
<div class="val">Rs {{ number_format($totals['recovered']) }}</div><div class="trend">{{ __('supplier_returns.stat_recovered_sub') }}</div>
</div>
</section>

<div class="panel">
<form method="GET" action="{{ route('supplier-returns.index') }}" class="toolbar">
<input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('common.search') }}" class="input">
<select name="supplier" class="input">
<option value="">{{ __('supplier_returns.all_suppliers') }}</option>
@foreach ($suppliers as $s)
<option value="{{ $s->id }}" @selected(request('supplier') == $s->id)>{{ $s->display_name }}</option>
@endforeach
</select>
<select name="status" class="input">
<option value="">{{ __('supplier_returns.all_status') }}</option>
@foreach (\App\Models\SupplierClaim::STATUSES as $st)
<option value="{{ $st }}" @selected(request('status') === $st)>{{ __('supplier_returns.status.' . $st) }}</option>
@endforeach
</select>
<select name="type" class="input">
<option value="">{{ __('supplier_returns.all_types') }}</option>
@foreach (['expired', 'damaged', 'customer_return', 'other'] as $st)
<option value="{{ $st }}" @selected(request('type') === $st)>{{ __('supplier_returns.type.' . $st) }}</option>
@endforeach
</select>
<button class="btn btn-ghost" type="submit">{{ __('common.search_btn') }}</button>
<a class="btn btn-primary" href="{{ route('supplier-returns.create') }}">&#43; {{ __('supplier_returns.add') }}</a>
</form>

@php
$statusTag = ['draft' => 't-off', 'pending' => 't-pending', 'in_progress' => 't-progress', 'approved' => 't-approved', 'rejected' => 't-rejected', 'completed' => 't-approved'];
@endphp

<div class="table-wrap">
<table>
<tr>
<th>{{ __('supplier_returns.th_claim') }}</th>
<th>{{ __('supplier_returns.th_supplier') }}</th>
<th>{{ __('supplier_returns.th_type') }}</th>
<th>{{ __('supplier_returns.th_items') }}</th>
<th>{{ __('supplier_returns.th_value') }}</th>
<th>{{ __('supplier_returns.th_due') }}</th>
<th>{{ __('common.status') }}</th>
<th>{{ __('common.actions') }}</th>
</tr>
@forelse ($claims as $c)
<tr>
<td><span class="pill" dir="ltr">{{ $c->claim_no }}</span><span class="cell-sub" dir="ltr">{{ $c->claim_date->format('d-m-Y') }}</span></td>
<td><b><bdi>{{ $c->supplier?->display_name ?? '—' }}</bdi></b></td>
<td>{{ __('supplier_returns.type.' . $c->claim_type) }}</td>
<td>{{ number_format($c->items_count) }}</td>
<td>Rs {{ number_format($c->total_value) }}</td>
<td>
<span dir="ltr">{{ $c->due_date?->format('d-m-Y') ?? '—' }}</span>
@if ($c->is_overdue)<span class="cell-sub" style="color:#B5451B;font-weight:800">{{ __('supplier_returns.overdue') }}</span>@endif
</td>
<td><span class="tag {{ $statusTag[$c->status] ?? 't-pending' }}">{{ __('supplier_returns.status.' . $c->status) }}</span></td>
<td>
<div class="row-actions">
<a class="btn btn-ghost btn-sm" href="{{ route('supplier-returns.show', $c) }}">{{ __('supplier_returns.view') }}</a>
<form method="POST" action="{{ route('supplier-returns.destroy', $c) }}" onsubmit="return confirm(@js(__('supplier_returns.confirm_delete')))">
@csrf
@method('DELETE')
<button class="btn btn-danger btn-sm" type="submit">{{ __('common.delete') }}</button>
</form>
</div>
</td>
</tr>
@empty
<tr><td colspan="8" style="text-align:center;color:var(--muted)">{{ __('common.no_records') }}</td></tr>
@endforelse
</table>
</div>

@if ($claims->hasPages())
<div class="pager">
@if ($claims->onFirstPage())
<span class="btn btn-ghost btn-sm disabled">{{ __('common.prev') }}</span>
@else
<a class="btn btn-ghost btn-sm" href="{{ $claims->previousPageUrl() }}">{{ __('common.prev') }}</a>
@endif
<span>{{ __('common.page_of', ['current' => $claims->currentPage(), 'last' => $claims->lastPage()]) }}</span>
@if ($claims->hasMorePages())
<a class="btn btn-ghost btn-sm" href="{{ $claims->nextPageUrl() }}">{{ __('common.next') }}</a>
@else
<span class="btn btn-ghost btn-sm disabled">{{ __('common.next') }}</span>
@endif
</div>
@endif
</div>
@endsection