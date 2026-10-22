@php($isUr = app()->getLocale() === 'ur')
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isUr ? 'rtl' : 'ltr' }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Recoverly') — Retail Return &amp; Supplier Recovery Tracker</title>
<link rel="stylesheet" href="{{ asset('css/recoverly.css') }}">
<link rel="stylesheet" href="{{ asset('css/modules.css') }}">
@if ($isUr)
<link rel="stylesheet" href="{{ asset('css/recoverly-rtl.css') }}">
@endif
</head>
<body>
<div class="overlay" id="overlay"></div>
<div class="layout">
<aside class="sidebar" id="sidebar">
<div class="logo"><div class="logo-badge">R</div><div><h1>Recoverly</h1><span>{{ __('app.brand_tagline') }}</span></div></div>
<div class="nav-label">{{ __('app.menu_main') }}</div>
<nav class="nav">
<a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><span class="ico">&#9638;</span> {{ __('app.nav.dashboard') }}</a>
<a class="{{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}"><span class="ico">&#9636;</span> {{ __('app.nav.products') }}</a>
<a class="{{ request()->routeIs('suppliers.*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}"><span class="ico">&#9776;</span> {{ __('app.nav.suppliers') }}</a>
<a class="{{ request()->routeIs('damaged-products.*') ? 'active' : '' }}" href="{{ route('damaged-products.index') }}"><span class="ico">&#8617;</span> {{ __('app.nav.damaged') }}</a>
<a class="{{ request()->routeIs('expiry.*') ? 'active' : '' }}" href="{{ route('expiry.index') }}"><span class="ico">&#8987;</span> {{ __('app.nav.expiry') }}</a>
<a class="{{ request()->routeIs('customer-returns.*') ? 'active' : '' }}" href="{{ route('customer-returns.index') }}"><span class="ico">&#10227;</span> {{ __('app.nav.customer_returns') }}</a>
<a class="{{ request()->routeIs('supplier-returns.*') ? 'active' : '' }}" href="{{ route('supplier-returns.index') }}"><span class="ico">&#8646;</span> {{ __('app.nav.supplier_returns') }}</a>
<a href="#"><span class="ico">&#9678;</span> {{ __('app.nav.recovery') }}</a>
</nav>
<div class="nav-label">{{ __('app.menu_manage') }}</div>
<nav class="nav">
<a href="#"><span class="ico">&#128202;</span> {{ __('app.nav.reports') }}</a>
<a href="#"><span class="ico">&#9200;</span> {{ __('app.nav.reminders') }}</a>
<a href="#"><span class="ico">&#9881;</span> {{ __('app.nav.settings') }}</a>
</nav>
</aside>
<main class="main">
@yield('content')
</main>
</div>
<script>
const s=document.getElementById('sidebar'),o=document.getElementById('overlay');
document.getElementById('menuBtn').onclick=()=>{s.classList.add('open');o.classList.add('show')};
o.onclick=()=>{s.classList.remove('open');o.classList.remove('show')};
</script>
</body>
</html>
