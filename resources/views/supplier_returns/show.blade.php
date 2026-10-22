@extends('layouts.app')

@section('title', $claim->claim_no)

@section('content')
@include('partials.topbar', ['title' => $claim->claim_no, 'subtitle' => __('supplier_returns.show_sub')])

@if (session('success'))
<div class="flash">{{ session('success') }}</div>
@endif

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
$statusTag = ['draft' => 't-off', 'pending' => 't-pending', 'in_progress' => 't-progress', 'approved' => 't-approved', 'rejected' => 't-rejected', 'completed' => 't-approved'];
@endphp

<div class="toolbar">
<a class="btn btn-ghost btn-sm" href="{{ route('supplier-returns.index') }}">&larr; {{ __('supplier_returns.back') }}</a>
</div>

<div class="stack">

<div class="panel">
<div class="detail-grid">
<div><div class="k">{{ __('supplier_returns.th_supplier') }}</div><div class="v"><bdi>{{ $claim->supplier?->display_name ?? '—' }}</bdi></div></div>
<div><div class="k">{{ __('supplier_returns.th_type') }}</div><div class="v">{{ __('supplier_returns.type.' . $claim->claim_type) }}</div></div>
<div><div class="k">{{ __('common.status') }}</div><div class="v"><span class="tag {{ $statusTag[$claim->status] ?? 't-pending' }}">{{ __('supplier_returns.status.' . $claim->status) }}</span></div></div>
<div><div class="k">{{ __('supplier_returns.f_claim_date') }}</div><div class="v" dir="ltr">{{ $claim->claim_date->format('d-m-Y') }}</div></div>
<div><div class="k">{{ __('supplier_returns.th_due') }}</div><div class="v"><span dir="ltr">{{ $claim->due_date?->format('d-m-Y') ?? '—' }}</span> @if ($claim->is_overdue)<span class="tag t-rejected">{{ __('supplier_returns.overdue') }}</span>@endif</div></div>
<div><div class="k">{{ __('supplier_returns.th_value') }}</div><div class="v">Rs {{ number_format($claim->total_value) }}</div></div>
<div><div class="k">{{ __('supplier_returns.f_approved_value') }}</div><div class="v">Rs {{ number_format($claim->approved_value) }}</div></div>
<div><div class="k">{{ __('supplier_returns.f_resolution_type') }}</div><div class="v">{{ $claim->resolution_type ? __('supplier_returns.resolution.' . $claim->resolution_type) : '—' }}</div></div>
<div><div class="k">{{ __('supplier_returns.recovered') }}</div><div class="v">Rs {{ number_format($recovered) }}</div></div>
</div>
@if ($claim->rejection_reason)
<p style="margin:14px 0 0;color:#B5451B;font-weight:700">{{ __('supplier_returns.f_rejection_reason') }}: <bdi>{{ $claim->rejection_reason }}</bdi></p>
@endif
@if ($claim->notes)
<p style="margin:10px 0 0;color:var(--muted)"><bdi>{{ $claim->notes }}</bdi></p>
@endif
</div>

<div class="split">

<div>
<div class="panel">
<div class="group-title" style="margin-top:0">{{ __('supplier_returns.items') }}</div>
<div class="table-wrap">
<table>
<tr>
<th>{{ __('supplier_returns.th_product') }}</th>
<th>{{ __('supplier_returns.th_qty') }}</th>
<th>{{ __('supplier_returns.th_unit') }}</th>
<th>{{ __('supplier_returns.th_value') }}</th>
</tr>
@forelse ($claim->items as $it)
<tr>
<td><b><bdi>{{ $it->product?->display_name ?? '—' }}</bdi></b><span class="cell-sub" dir="ltr">{{ $it->product?->sku }}</span></td>
<td>{{ number_format($it->qty) }}</td>
<td>Rs {{ number_format($it->unit_cost, floor($it->unit_cost) == $it->unit_cost ? 0 : 2) }}</td>
<td>Rs {{ number_format($it->line_total) }}</td>
</tr>
@empty
<tr><td colspan="4" style="text-align:center;color:var(--muted)">{{ __('common.no_records') }}</td></tr>
@endforelse
</table>
</div>
</div>

<div class="panel">
<div class="group-title" style="margin-top:0">{{ __('supplier_returns.timeline') }}</div>
<ul class="timeline">
@forelse ($claim->logs as $log)
<li>
@if ($log->from_status)<span class="tag t-off">{{ __('supplier_returns.status.' . $log->from_status) }}</span> &rarr; @endif
<span class="tag {{ $statusTag[$log->to_status] ?? 't-pending' }}">{{ __('supplier_returns.status.' . $log->to_status) }}</span>
@if ($log->comment)<bdi> {{ $log->comment }}</bdi>@endif
<small dir="ltr">{{ $log->created_at?->format('d-m-Y H:i') }}</small>
</li>
@empty
<li style="color:var(--muted)">{{ __('common.no_records') }}</li>
@endforelse
</ul>
</div>
</div>

<div>
<form method="POST" action="{{ route('supplier-returns.status', $claim) }}" class="panel">
@csrf
@method('PUT')
<div class="group-title" style="margin-top:0">{{ __('supplier_returns.update_status') }}</div>

<div class="field" style="margin-bottom:14px">
<label for="status">{{ __('supplier_returns.f_status') }} <i>*</i></label>
<select id="status" name="status" class="input">
@foreach (\App\Models\SupplierClaim::STATUSES as $st)
<option value="{{ $st }}" @selected(old('status', $claim->status) === $st)>{{ __('supplier_returns.status.' . $st) }}</option>
@endforeach
</select>
</div>

<div class="field" style="margin-bottom:14px">
<label for="resolution_type">{{ __('supplier_returns.f_resolution_type') }}</label>
<select id="resolution_type" name="resolution_type" class="input">
<option value="">—</option>
@foreach (\App\Models\SupplierClaim::RESOLUTIONS as $st)
<option value="{{ $st }}" @selected(old('resolution_type', $claim->resolution_type) === $st)>{{ __('supplier_returns.resolution.' . $st) }}</option>
@endforeach
</select>
<span class="hint">{{ __('supplier_returns.hint_resolution') }}</span>
</div>

<div class="field" style="margin-bottom:14px">
<label for="approved_value">{{ __('supplier_returns.f_approved_value') }}</label>
<input id="approved_value" name="approved_value" type="number" min="0" step="0.01" class="input" value="{{ old('approved_value') }}" placeholder="{{ number_format($claim->total_value, 2, '.', '') }}">
<span class="hint">{{ __('supplier_returns.hint_approved') }}</span>
</div>

<div class="field" style="margin-bottom:14px">
<label for="rejection_reason">{{ __('supplier_returns.f_rejection_reason') }}</label>
<input id="rejection_reason" name="rejection_reason" type="text" class="input" value="{{ old('rejection_reason', $claim->rejection_reason) }}">
<span class="hint">{{ __('supplier_returns.hint_rejection') }}</span>
</div>

<div class="field" style="margin-bottom:14px">
<label for="due_date">{{ __('supplier_returns.f_due_date') }}</label>
<input id="due_date" name="due_date" type="date" class="input" value="{{ old('due_date', $claim->due_date?->format('Y-m-d')) }}">
</div>

<div class="field" style="margin-bottom:14px">
<label for="comment">{{ __('supplier_returns.f_comment') }}</label>
<input id="comment" name="comment" type="text" class="input" value="{{ old('comment') }}">
</div>

<p style="color:var(--muted);font-size:12.5px;margin:0 0 14px">{{ __('supplier_returns.hint_status') }}</p>

<button class="btn btn-primary" type="submit">{{ __('supplier_returns.save_status') }}</button>
</form>
</div>

</div>

</div>
@endsection