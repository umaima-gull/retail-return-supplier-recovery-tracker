@extends('layouts.app')

@section('title', __('damaged.title'))

@section('content')
@include('partials.topbar', ['title' => __('damaged.title'), 'subtitle' => __('damaged.subtitle')])

@if (session('success'))
<div class="flash">{{ session('success') }}</div>
@endif

<section class="stats">
<div class="stat" style="--accent:#7D5BA6;--accent2:#B197FC">
<div class="ico">&#128221;</div><h3>{{ __('damaged.stat_records') }}</h3>
<div class="val">{{ number_format($totals['records']) }}</div><div class="trend">{{ __('damaged.stat_records_sub') }}</div>
</div>
<div class="stat" style="--accent:#E0A458;--accent2:#F5C67B">
<div class="ico">&#128230;</div><h3>{{ __('damaged.stat_units') }}</h3>
<div class="val">{{ number_format($totals['units']) }}</div><div class="trend">{{ __('damaged.stat_units_sub') }}</div>
</div>
<div class="stat" style="--accent:#D96C47;--accent2:#F08C4B">
<div class="ico">&#128176;</div><h3>{{ __('damaged.stat_loss') }}</h3>
<div class="val">Rs {{ number_format($totals['loss']) }}</div><div class="trend">{{ __('damaged.stat_loss_sub') }}</div>
</div>
<div class="stat" style="--accent:#7FB069;--accent2:#A9C46C">
<div class="ico">&#8617;</div><h3>{{ __('damaged.stat_unclaimed') }}</h3>
<div class="val">{{ number_format($totals['unclaimed']) }}</div><div class="trend">{{ __('damaged.stat_unclaimed_sub') }}</div>
</div>
</section>

<div class="panel">
<form method="GET" action="{{ route('damaged-products.index') }}" class="toolbar">
<input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('common.search') }}" class="input">
<select name="stage" class="input">
<option value="">{{ __('damaged.all_stages') }}</option>
@foreach (\App\Models\DamagedProduct::STAGES as $st)
<option value="{{ $st }}" @selected(request('stage') === $st)>{{ __('damaged.stage.' . $st) }}</option>
@endforeach
</select>
<select name="status" class="input">
<option value="">{{ __('damaged.all_status') }}</option>
@foreach (\App\Models\DamagedProduct::STATUSES as $st)
<option value="{{ $st }}" @selected(request('status') === $st)>{{ __('damaged.status.' . $st) }}</option>
@endforeach
</select>
<button class="btn btn-ghost" type="submit">{{ __('common.search_btn') }}</button>
<a class="btn btn-primary" href="{{ route('damaged-products.create') }}">&#43; {{ __('damaged.add') }}</a>
</form>

@php
$statusTag = ['reported' => 't-pending', 'claim_raised' => 't-progress', 'recovered' => 't-approved', 'written_off' => 't-off'];
@endphp

<div class="table-wrap">
<table>
<tr>
<th>{{ __('damaged.th_product') }}</th>
<th>{{ __('damaged.th_qty') }}</th>
<th>{{ __('damaged.th_stage') }}</th>
<th>{{ __('damaged.th_reason') }}</th>
<th>{{ __('damaged.th_loss') }}</th>
<th>{{ __('damaged.th_date') }}</th>
<th>{{ __('damaged.th_claim') }}</th>
<th>{{ __('common.status') }}</th>
<th>{{ __('common.actions') }}</th>
</tr>
@forelse ($records as $r)
<tr>
<td>
<div class="prod">
@if ($r->photo_url)
<a href="{{ $r->photo_url }}" target="_blank"><img class="thumb" src="{{ $r->photo_url }}" alt=""></a>
@endif
<div><b><bdi>{{ $r->product?->display_name ?? '—' }}</bdi></b><span class="cell-sub" dir="ltr">{{ $r->product?->sku }}</span></div>
</div>
</td>
<td>{{ number_format($r->qty) }}</td>
<td>{{ __('damaged.stage.' . $r->damage_stage) }}</td>
<td><bdi>{{ $r->reason ?: '—' }}</bdi></td>
<td>Rs {{ number_format($r->loss_value, floor($r->loss_value) == $r->loss_value ? 0 : 2) }}</td>
<td><span dir="ltr">{{ $r->reported_at->format('d-m-Y') }}</span></td>
<td>@if ($r->claim)<span class="pill">{{ $r->claim->claim_no }}</span>@else — @endif</td>
<td><span class="tag {{ $statusTag[$r->status] ?? 't-pending' }}">{{ __('damaged.status.' . $r->status) }}</span></td>
<td>
<div class="row-actions">
<a class="btn btn-ghost btn-sm" href="{{ route('damaged-products.edit', $r) }}">{{ __('common.edit') }}</a>
<form method="POST" action="{{ route('damaged-products.destroy', $r) }}" onsubmit="return confirm(@js(__('common.confirm_delete')))">
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