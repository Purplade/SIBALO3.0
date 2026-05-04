@php
    $onboardingRole = $onboardingRole ?? 'pegawai';
    $onboardingVersion = $onboardingVersion ?? (string) config('app.onboarding_version', '1');
    $launchBottom = $onboardingRole === 'pegawai' ? '88px' : '24px';
@endphp
<link rel="stylesheet" href="{{ asset('vendor/driver.js/driver.css') }}">
<style>
    #sibalo-onboarding-launch {
        position: fixed;
        z-index: 10050;
        right: 12px;
        bottom: {{ $launchBottom }};
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: none;
        background: #206bc4;
        color: #fff;
        font-weight: 700;
        font-size: 1rem;
        line-height: 1;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        cursor: pointer;
    }

    #sibalo-onboarding-launch:hover {
        filter: brightness(1.05);
    }

    body.driver-active #sibalo-onboarding-launch {
        display: none !important;
    }
</style>
<div id="sibalo-onboarding-config" data-role="{{ $onboardingRole }}" data-version="{{ e($onboardingVersion) }}"
    style="display:none" aria-hidden="true"></div>
<button type="button" id="sibalo-onboarding-launch" title="Tutorial / panduan aplikasi" aria-label="Buka tutorial">
    ?
</button>
<script src="{{ asset('vendor/driver.js/driver.iife.js') }}" defer></script>
<script src="{{ asset('js/sibalo-onboarding.js') }}" defer></script>
