<div class="topbar">
<div style="display:flex;align-items:center;gap:12px">
<button class="menu-btn" id="menuBtn">&#9776;</button>
<div><h2>{{ $title }}</h2><p>{{ $subtitle ?? '' }}</p></div>
</div>
<div class="actions">
<a class="btn btn-ghost" href="{{ route('lang.switch', app()->getLocale() === 'ur' ? 'en' : 'ur') }}">&#127760; {{ __('app.switch_to') }}</a>
</div>
</div>