@extends('layouts.app')

@section('title', __('suppliers.title'))

@section('content')
@include('partials.topbar', ['title' => __('suppliers.title'), 'subtitle' => __('suppliers.subtitle')])

@if (session('success'))
<div class="flash">{{ session('success') }}</div>
@endif

<div class="panel">
<form method="GET" action="{{ route('suppliers.index') }}" class="toolbar">
<input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('common.search') }}" class="input">
<select name="status" class="input">
<option value="">{{ __('common.all') }}</option>
<option value="active" @selected(request('status') === 'active')>{{ __('common.active') }}</option>
<option value="inactive" @selected(request('status') === 'inactive')>{{ __('common.inactive') }}</option>
</select>
<button class="btn btn-ghost" type="submit">{{ __('common.search_btn') }}</button>
<a class="btn btn-primary" href="{{ route('suppliers.create') }}">&#43; {{ __('suppliers.add') }}</a>
</form>

<div class="table-wrap">
<table>
<tr>
<th>{{ __('suppliers.th_name') }}</th>
<th>{{ __('suppliers.th_contact') }}</th>
<th>{{ __('suppliers.th_city') }}</th>
<th>{{ __('suppliers.th_return') }}</th>
<th>{{ __('suppliers.th_response') }}</th>
<th>{{ __('common.status') }}</th>
<th>{{ __('common.actions') }}</th>
</tr>
@forelse ($suppliers as $s)
<tr>
<td><b><bdi>{{ $s->display_name }}</bdi></b></td>
<td><bdi>{{ $s->contact_person ?: '—' }}</bdi>@if ($s->phone)<span class="cell-sub" dir="ltr">{{ $s->phone }}</span>@endif</td>
<td><bdi>{{ $s->city ?: '—' }}</bdi></td>
<td>{{ trans_choice('common.days', $s->return_policy_days, ['count' => $s->return_policy_days]) }}</td>
<td>{{ trans_choice('common.days', $s->response_time_days, ['count' => $s->response_time_days]) }}</td>
<td><span class="tag {{ $s->is_active ? 't-approved' : 't-off' }}">{{ $s->is_active ? __('common.active') : __('common.inactive') }}</span></td>
<td>
<div class="row-actions">
<a class="btn btn-ghost btn-sm" href="{{ route('suppliers.edit', $s) }}">{{ __('common.edit') }}</a>
<form method="POST" action="{{ route('suppliers.destroy', $s) }}" onsubmit="return confirm(@js(__('common.confirm_delete')))">
@csrf
@method('DELETE')
<button class="btn btn-danger btn-sm" type="submit">{{ __('common.delete') }}</button>
</form>
</div>
</td>
</tr>
@empty
<tr><td colspan="7" style="text-align:center;color:var(--muted)">{{ __('common.no_records') }}</td></tr>
@endforelse
</table>
</div>

@if ($suppliers->hasPages())
<div class="pager">
@if ($suppliers->onFirstPage())
<span class="btn btn-ghost btn-sm disabled">{{ __('common.prev') }}</span>
@else
<a class="btn btn-ghost btn-sm" href="{{ $suppliers->previousPageUrl() }}">{{ __('common.prev') }}</a>
@endif
<span>{{ __('common.page_of', ['current' => $suppliers->currentPage(), 'last' => $suppliers->lastPage()]) }}</span>
@if ($suppliers->hasMorePages())
<a class="btn btn-ghost btn-sm" href="{{ $suppliers->nextPageUrl() }}">{{ __('common.next') }}</a>
@else
<span class="btn btn-ghost btn-sm disabled">{{ __('common.next') }}</span>
@endif
</div>
@endif
</div>
@endsection