@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="topbar">
<div style="display:flex;align-items:center;gap:12px">
<button class="menu-btn" id="menuBtn">&#9776;</button>
<div><h2>{{ __('app.top_title_a') }}<em>{{ __('app.top_title_em') }}</em></h2><p>{{ __('app.welcome') }}</p></div>
</div>
<div class="actions">
<a class="btn btn-ghost" href="{{ route('lang.switch', app()->getLocale() === 'ur' ? 'en' : 'ur') }}">&#127760; {{ __('app.switch_to') }}</a>
<button class="btn btn-ghost">&#11015; {{ __('app.export') }}</button><button class="btn btn-primary">&#43; {{ __('app.create_claim') }}</button>
</div>
</div>

{{-- ===== Stat cards ===== --}}
<section class="stats">
@foreach ($stats as $st)
<div class="stat" style="--accent:{{ $st['accent'] }};--accent2:{{ $st['accent2'] }}">
<div class="ico">{!! $st['icon'] !!}</div>
<h3>{{ $st['title'] }}</h3>
<div class="val">{{ $st['value'] }}</div>
<div class="trend">{{ $st['trend'] }}</div>
</div>
@endforeach
</section>

<section class="grid">
{{-- ===== Bar chart ===== --}}
<div class="panel">
<div class="panel-head"><div><h3>{{ __('app.activity_title') }}</h3><span>{{ __('app.activity_sub') }}</span></div>
<div class="legend"><span><i style="background:linear-gradient(135deg,#8B5E3C,#C08552)"></i>{{ __('app.legend_claims') }}</span><span><i style="background:linear-gradient(135deg,#7FB069,#7D5BA6)"></i>{{ __('app.legend_recovered') }}</span></div></div>
<div class="bars">
@foreach ($claimBars as $b)
<div class="bar-g"><div class="bar" style="height:{{ $b['value'] > 0 ? max(14, round($b['value'] / $barMax * 190)) : 4 }}px;background:linear-gradient(180deg,#8B5E3C,#C08552)"><span>{{ $b['value'] }}</span></div><small>{{ $b['label'] }}</small></div>
@endforeach
@foreach ($recBars as $b)
<div class="bar-g"><div class="bar" style="height:{{ $b['value'] > 0 ? max(14, round($b['value'] / $barMax * 190)) : 4 }}px;background:linear-gradient(180deg,#7FB069,#7D5BA6)"><span>{{ $b['value'] }}</span></div><small>{{ $b['label'] }}</small></div>
@endforeach
</div>
</div>

{{-- ===== Donut ===== --}}
<div class="panel">
<div class="panel-head"><div><h3>{{ __('app.summary_title') }}</h3><span>{{ __('app.summary_sub') }}</span></div></div>
<div class="donut-wrap">
<div class="donut" style="background:{{ $gradient }}"><div class="hole"><b>{{ $rate }}%</b><small>{{ __('app.recovery_rate') }}</small></div></div>
<div class="break">
@foreach ($summary as $row)
<div class="row"><span style="color:{{ $row['color'] }};font-weight:800">&#9679; {{ $row['label'] }}</span><b>{{ $row['value'] }}</b></div>
@endforeach
</div>
</div>
</div>
</section>

<section class="grid">
{{-- ===== Recent claims ===== --}}
<div class="panel">
<div class="panel-head"><div><h3>{{ __('app.claims_title') }}</h3><span>{{ __('app.claims_sub') }}</span></div><span class="pill">{{ __('app.view_all') }}</span></div>
<div class="table-wrap">
<table>
<tr><th>{{ __('app.th_product') }}</th><th>{{ __('app.th_claim') }}</th><th>{{ __('app.th_supplier') }}</th><th>{{ __('app.th_value') }}</th><th>{{ __('app.th_status') }}</th></tr>
@forelse ($claims as $c)
<tr>
<td><bdi>{{ $c->product_label }}</bdi></td>
<td><span class="pill">{{ $c->claim_no }}</span></td>
<td><bdi>{{ $c->supplier }}</bdi></td>
<td>Rs {{ number_format($c->total_value) }}</td>
<td><span class="tag {{ $c->tag_class }}">{{ $c->tag_label }}</span></td>
</tr>
@empty
<tr><td colspan="5" style="text-align:center;color:var(--muted)">{{ __('app.no_claims') }}</td></tr>
@endforelse
</table>
</div>
</div>

{{-- ===== Alerts ===== --}}
<div class="panel">
<div class="panel-head"><div><h3>{{ __('app.alerts_title') }}</h3><span>{{ __('app.alerts_sub') }}</span></div><span class="pill">{{ __('app.alerts_count', ['count' => $alertCount]) }}</span></div>
@forelse ($alerts as $a)
<div class="alert"><div class="dot {{ $a->dot_class }}">{!! $a->icon !!}</div><div><b><bdi>{{ $a->title_show }}</bdi></b><p><bdi>{{ $a->message_show }}</bdi></p></div><span class="when">{{ $a->when }}</span></div>
@empty
<p style="color:var(--muted);font-size:13px">{{ __('app.no_alerts') }}</p>
@endforelse
</div>
</section>
@endsection
