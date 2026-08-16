@php
    $accountUsable = Auth::user()->role === 'patient' || Auth::user()->isActive();

    $homeRoute = $accountUsable
        ? match (Auth::user()->role) {
            'admin' => 'admin.dashboard',
            'hospital' => 'hospital.dashboard',
            'doctor' => 'doctor.prescriptions.index',
            'pharmacist' => 'pharmacist.dashboard',
            default => 'patient.prescriptions.index',
        }
        : 'pending-approval';

    $navItems = [];

    if ($accountUsable && Auth::user()->isAdmin()) {
        $navItems = [
            ['route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'home'],
            ['route' => 'admin.approvals', 'pattern' => 'admin.approvals', 'label' => 'Approvals', 'icon' => 'check-badge'],
            ['route' => 'admin.audit-log', 'pattern' => 'admin.audit-log', 'label' => 'Audit Log', 'icon' => 'list'],
        ];
    } elseif ($accountUsable && Auth::user()->isHospital()) {
        $navItems = [
            ['route' => 'hospital.dashboard', 'pattern' => 'hospital.dashboard', 'label' => 'Dashboard', 'icon' => 'home'],
            ['route' => 'hospital.shares.index', 'pattern' => 'hospital.shares.*', 'label' => 'Record Sharing', 'icon' => 'share'],
        ];
    } elseif ($accountUsable && Auth::user()->isDoctor()) {
        $navItems = [
            ['route' => 'doctor.prescriptions.index', 'pattern' => 'doctor.prescriptions.index', 'label' => 'My Prescriptions', 'icon' => 'home'],
            ['route' => 'doctor.prescriptions.create', 'pattern' => 'doctor.prescriptions.create', 'label' => 'New Prescription', 'icon' => 'plus'],
            ['route' => 'doctor.patients.index', 'pattern' => 'doctor.patients.*', 'label' => 'Patient Records', 'icon' => 'users'],
        ];
    } elseif ($accountUsable && Auth::user()->isPharmacist()) {
        $navItems = [
            ['route' => 'pharmacist.dashboard', 'pattern' => 'pharmacist.dashboard', 'label' => 'Dashboard', 'icon' => 'home'],
            ['route' => 'pharmacist.lookup', 'pattern' => 'pharmacist.lookup*', 'label' => 'Prescription Lookup', 'icon' => 'search'],
        ];
    } elseif ($accountUsable && Auth::user()->isPatient()) {
        $navItems = [
            ['route' => 'patient.prescriptions.index', 'pattern' => 'patient.prescriptions.index', 'label' => 'My Prescriptions', 'icon' => 'home'],
            ['route' => 'patient.access.index', 'pattern' => 'patient.access.*', 'label' => 'Share Access', 'icon' => 'key'],
        ];
    } else {
        $navItems = [
            ['route' => 'pending-approval', 'pattern' => 'pending-approval', 'label' => 'Dashboard', 'icon' => 'home'],
        ];
    }

    $icons = [
        'home' => 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
        'check-badge' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'list' => 'M8.25 6.75h12M8.25 12h12M8.25 17.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z',
        'share' => 'M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z',
        'plus' => 'M12 4.5v15m7.5-7.5h-15',
        'users' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
        'search' => 'M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z',
        'key' => 'M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z',
        'logout' => 'M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75',
        'profile' => 'M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z',
    ];
@endphp

<div class="flex flex-col h-full">
    <div class="flex items-center h-16 px-6 shrink-0 border-b border-brand-800">
        <a href="{{ route($homeRoute) }}" class="font-bold text-lg text-white">
            SwasthoLink
        </a>
    </div>

    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        @foreach ($navItems as $item)
            <a href="{{ route($item['route']) }}"
               @class([
                   'flex items-center gap-3 px-3 py-2.5 rounded-md text-sm font-medium transition',
                   'bg-brand-700 text-white' => request()->routeIs($item['pattern']),
                   'text-brand-100 hover:bg-brand-800 hover:text-white' => ! request()->routeIs($item['pattern']),
               ])>
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$item['icon']] }}" />
                </svg>
                <span>{{ __($item['label']) }}</span>
            </a>
        @endforeach
    </nav>

    <div class="px-3 py-4 border-t border-brand-800 space-y-1">
        <div class="px-3 pb-2">
            <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name }}</p>
            <span class="inline-block mt-1 px-2 py-0.5 text-xs font-medium uppercase tracking-wide text-brand-800 bg-brand-100 rounded-full">{{ Auth::user()->role }}</span>
        </div>

        <button type="button"
                x-data="{ dark: document.documentElement.classList.contains('dark') }"
                x-init="$watch('dark', value => { document.documentElement.classList.toggle('dark', value); localStorage.setItem('theme', value ? 'dark' : 'light'); })"
                @click="dark = ! dark"
                class="flex items-center gap-3 px-3 py-2.5 rounded-md text-sm font-medium text-brand-100 hover:bg-brand-800 hover:text-white transition w-full">
            <svg x-show="!dark" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-6.364-.386 1.591-1.591M3 12h2.25m.386-6.364 1.591 1.591M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <svg x-show="dark" x-cloak class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
            </svg>
            <span x-text="dark ? '{{ __('Light Mode') }}' : '{{ __('Dark Mode') }}'"></span>
        </button>

        <a href="{{ route('profile.edit') }}"
           @class([
               'flex items-center gap-3 px-3 py-2.5 rounded-md text-sm font-medium transition',
               'bg-brand-700 text-white' => request()->routeIs('profile.edit'),
               'text-brand-100 hover:bg-brand-800 hover:text-white' => ! request()->routeIs('profile.edit'),
           ])>
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['profile'] }}" />
            </svg>
            <span>{{ __('Profile') }}</span>
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <a href="{{ route('logout') }}"
               onclick="event.preventDefault(); this.closest('form').submit();"
               class="flex items-center gap-3 px-3 py-2.5 rounded-md text-sm font-medium text-brand-100 hover:bg-brand-800 hover:text-white transition cursor-pointer">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['logout'] }}" />
                </svg>
                <span>{{ __('Log Out') }}</span>
            </a>
        </form>
    </div>
</div>
