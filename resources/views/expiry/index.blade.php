@extends('layouts.app')

@section('title', __('expiry.title'))

@section('content')
@include('partials.topbar', ['title' => __('expiry.title'), 'subtitle' => __('expiry.subtitle')])

@if (session('success'))
<div class="flash">{{ session('success') }}</div>
@endif

<section class="stats">
<div class="stat" style="--accent:#D96C47;--accent2:#F08C4B">
<div class="ico">&#9888;</div><h3>{{ __('expiry.stat_expired') }}</h3>
<div class="val">{{ __('expiry.units', ['count' => number_format($totals['expired'])]) }}</div><div class="trend">{{ __('expiry.stat_expired_sub') }}</div>
</div>
<div class="stat" style="--accent:#E0A458;--accent2:#F5C67B">
<div class="ico">&#8987;</div><h3>{{ __('expiry.stat_soon') }}</h3>
<div class="val">{{ __('expiry.units', ['count' => number_format($totals['soon'])]) }}</div><div class="trend">{{ __('expiry.stat_soon_sub', ['days' => \App\Models\ExpiryRecord::SOON_DAYS]) }}</div>
</div>
<div class="stat" style="--accent:#7D5BA6;--accent2:#B197FC">
<div class="ico">&#128221;</div><h3>{{ __('expiry.stat_pending') }}</h3>
<div class="val">{{ number_format($totals['pending']) }}</div><div class="trend">{{ __('expiry.stat_pending_sub') }}</div>
</div>
<div class="stat" style="--accent:#7FB069;--accent2:#A9C46C">
<div class="ico">&#128176;</div><h3>{{ __('expiry.stat_risk') }}</h3>
<div class="val">Rs {{ number_format($totals['at_risk']) }}</div><div class="trend">{{ __('expiry.stat_risk_sub') }}</div>
</div>
</section>

<div class="panel">
<form method="GET" action="{{ route('expiry.index') }}" class="toolbar">
<input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('common.search') }}" class="input">
<select name="state" class="input">
<option value="">{{ __('expiry.all_dates') }}</option>
@foreach (['expired', 'urgent', 'soon', 'ok'] as $st)
<option value="{{ $st }}" @selected(request('state') === $st)>{{ __('expiry.state_' . $st) }}</option>
@endforeach
</select>
<select name="action" class="input">
<option value="">{{ __('expiry.all_actions') }}</option>
@foreach (\App\Models\ExpiryRecord::ACTIONS as $st)
<option value="{{ $st }}" @selected(request('action') === $st)>{{ __('expiry.action.' . $st) }}</option>
@endforeach
</select>
<button class="btn btn-ghost" type="submit">{{ __('common.search_btn') }}</button>
<a class="btn btn-primary" href="{{ route('expiry.create') }}">&#43; {{ __('expiry.add') }}</a>
</form>

@php
$stateTag  = ['expired' => 't-rejected', 'urgent' => 't-pending', 'soon' => 't-progress', 'ok' => 't-approved'];
$actionTag = ['pending' => 't-pending', 'discounted' => 't-progress', 'claim_raised' => 't-progress', 'recovered' => 't-approved', 'disposed' => 't-off'];
@endphp

<div class="table-wrap">
<table>
<tr>
<th>{{ __('expiry.th_product') }}</th>
<th>{{ __('expiry.th_batch') }}</th>
<th>{{ __('expiry.th_qty') }}</th>
<th>{{ __('expiry.th_date') }}</th>
<th>{{ __('expiry.th_left') }}</th>
<th>{{ __('expiry.th_loss') }}</th>
<th>{{ __('expiry.th_claim') }}</th>
<th>{{ __('expiry.th_action') }}</th>
<th>{{ __('common.actions') }}</th>
</tr>
@forelse ($records as $r)
@php
$d = $r->days_left;
@endphp
<tr>
<td><b><bdi>{{ $r->product?->display_name ?? '—' }}</bdi></b><span class="cell-sub" dir="ltr">{{ $r->product?->sku }}</span></td>
<td><span dir="ltr">{{ $r->batch?->batch_no ?? '—' }}</span></td>
<td>{{ number_format($r->qty) }}</td>
<td><span dir="ltr">{{ $r->expiry_date->format('d-m-Y') }}</span></td>
<td>
<span class="tag {{ $stateTag[$r->state] }}">
@if ($d < 0){{ trans_choice('expiry.expired_ago', abs($d), ['count' => abs($d)]) }}
@elseif ($d === 0){{ __('expiry.today') }}
@else{{ trans_choice('expiry.days_left', $d, ['count' => $d]) }}
@endif
</span>
</td>
<td>Rs {{ number_format($r->loss_value, floor($r->loss_value) == $r->loss_value ? 0 : 2) }}</td>
<td>@if ($r->claim)<span class="pill">{{ $r->claim->claim_no }}</span>@else — @endif</td>
<td><span class="tag {{ $actionTag[$r->action_status] ?? 't-pending' }}">{{ __('expiry.action.' . $r->action_status) }}</span></td>
<td>
<div class="row-actions">
<a class="btn btn-ghost btn-sm" href="{{ route('expiry.edit', $r) }}">{{ __('common.edit') }}</a>
<form method="POST" action="{{ route('expiry.destroy', $r) }}" onsubmit="return confirm(@js(__('common.confirm_delete')))">
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