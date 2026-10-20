@extends('layouts.app')

@section('title', __('products.title'))

@section('content')
@include('partials.topbar', ['title' => __('products.title'), 'subtitle' => __('products.subtitle')])

@if (session('success'))
<div class="flash">{{ session('success') }}</div>
@endif

<div class="panel">
<form method="GET" action="{{ route('products.index') }}" class="toolbar">
<input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('common.search') }}" class="input">
<select name="category" class="input">
<option value="">{{ __('products.all_categories') }}</option>
@foreach ($categories as $c)
<option value="{{ $c->id }}" @selected(request('category') == $c->id)>{{ $c->display_name }}</option>
@endforeach
</select>
<select name="status" class="input">
<option value="">{{ __('common.all') }}</option>
<option value="active" @selected(request('status') === 'active')>{{ __('common.active') }}</option>
<option value="inactive" @selected(request('status') === 'inactive')>{{ __('common.inactive') }}</option>
<option value="low" @selected(request('status') === 'low')>{{ __('products.stock_low') }}</option>
</select>
<button class="btn btn-ghost" type="submit">{{ __('common.search_btn') }}</button>
<a class="btn btn-primary" href="{{ route('products.create') }}">&#43; {{ __('products.add') }}</a>
</form>

<div class="table-wrap">
<table>
<tr>
<th>{{ __('products.th_product') }}</th>
<th>{{ __('products.th_category') }}</th>
<th>{{ __('products.th_supplier') }}</th>
<th>{{ __('products.th_price') }}</th>
<th>{{ __('products.th_stock') }}</th>
<th>{{ __('common.status') }}</th>
<th>{{ __('common.actions') }}</th>
</tr>
@forelse ($products as $p)
<tr>
<td><b><bdi>{{ $p->display_name }}</bdi></b><span class="cell-sub" dir="ltr">{{ $p->sku }}</span></td>
<td><bdi>{{ $p->category?->display_name ?? '—' }}</bdi></td>
<td><bdi>{{ $p->supplier?->display_name ?? '—' }}</bdi></td>
<td>
<span class="cell-sub">{{ __('products.cost') }}: Rs {{ number_format($p->cost_price, floor($p->cost_price) == $p->cost_price ? 0 : 2) }}</span>
<b>{{ __('products.sale') }}: Rs {{ number_format($p->sale_price, floor($p->sale_price) == $p->sale_price ? 0 : 2) }}</b>
</td>
<td>
{{ number_format($p->stock_qty) }} <bdi>{{ $p->unit }}</bdi>
@if ($p->stock_status === 'out')
<span class="tag t-rejected">{{ __('products.stock_out') }}</span>
@elseif ($p->stock_status === 'low')
<span class="tag t-pending">{{ __('products.stock_low') }}</span>
@endif
</td>
<td><span class="tag {{ $p->is_active ? 't-approved' : 't-off' }}">{{ $p->is_active ? __('common.active') : __('common.inactive') }}</span></td>
<td>
<div class="row-actions">
<a class="btn btn-ghost btn-sm" href="{{ route('products.edit', $p) }}">{{ __('common.edit') }}</a>
<form method="POST" action="{{ route('products.destroy', $p) }}" onsubmit="return confirm(@js(__('common.confirm_delete')))">
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

@if ($products->hasPages())
<div class="pager">
@if ($products->onFirstPage())
<span class="btn btn-ghost btn-sm disabled">{{ __('common.prev') }}</span>
@else
<a class="btn btn-ghost btn-sm" href="{{ $products->previousPageUrl() }}">{{ __('common.prev') }}</a>
@endif
<span>{{ __('common.page_of', ['current' => $products->currentPage(), 'last' => $products->lastPage()]) }}</span>
@if ($products->hasMorePages())
<a class="btn btn-ghost btn-sm" href="{{ $products->nextPageUrl() }}">{{ __('common.next') }}</a>
@else
<span class="btn btn-ghost btn-sm disabled">{{ __('common.next') }}</span>
@endif
</div>
@endif
</div>
@endsection