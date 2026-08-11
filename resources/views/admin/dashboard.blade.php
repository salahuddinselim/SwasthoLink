<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @php
                $pendingTotal = $stats['pending_hospitals'] + $stats['pending_doctors'] + $stats['pending_pharmacists'];
            @endphp

            @if ($pendingTotal > 0)
                <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-md flex items-center justify-between">
                    <span>{{ $pendingTotal }} {{ Str::plural('registration', $pendingTotal) }} waiting for review.</span>
                    <a href="{{ route('admin.approvals') }}" class="underline font-medium">{{ __('Go to Approvals') }} &rarr;</a>
                </div>
            @endif

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="{{ route('admin.approvals') }}" class="bg-white shadow-sm sm:rounded-lg p-5 hover:shadow-md transition">
                    <p class="text-sm text-gray-500">{{ __('Pending Hospitals') }}</p>
                    <p class="text-3xl font-semibold text-gray-800">{{ $stats['pending_hospitals'] }}</p>
                </a>
                <a href="{{ route('admin.approvals') }}" class="bg-white shadow-sm sm:rounded-lg p-5 hover:shadow-md transition">
                    <p class="text-sm text-gray-500">{{ __('Pending Doctors') }}</p>
                    <p class="text-3xl font-semibold text-gray-800">{{ $stats['pending_doctors'] }}</p>
                </a>
                <a href="{{ route('admin.approvals') }}" class="bg-white shadow-sm sm:rounded-lg p-5 hover:shadow-md transition">
                    <p class="text-sm text-gray-500">{{ __('Pending Pharmacists') }}</p>
                    <p class="text-3xl font-semibold text-gray-800">{{ $stats['pending_pharmacists'] }}</p>
                </a>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">{{ __('Total Prescriptions') }}</p>
                    <p class="text-3xl font-semibold text-gray-800">{{ $stats['total_prescriptions'] }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">{{ __('Active Hospitals') }}</p>
                    <p class="text-2xl font-semibold text-green-700">{{ $stats['active_hospitals'] }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">{{ __('Active Doctors') }}</p>
                    <p class="text-2xl font-semibold text-green-700">{{ $stats['active_doctors'] }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">{{ __('Active Pharmacists') }}</p>
                    <p class="text-2xl font-semibold text-green-700">{{ $stats['active_pharmacists'] }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">{{ __('Registered Patients') }}</p>
                    <p class="text-2xl font-semibold text-gray-800">{{ $stats['total_patients'] }}</p>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">{{ __('Recent Activity') }}</h3>

                @forelse ($recentActivity as $log)
                    <div class="flex justify-between items-center border-b last:border-0 py-2 text-sm">
                        <div>
                            <span class="font-medium">{{ $log->user?->name ?? __('System') }}</span>
                            <span class="text-gray-500">&mdash; {{ str_replace('.', ' ', $log->action) }}</span>
                            @if ($log->target_type)
                                <span class="text-gray-400">({{ class_basename($log->target_type) }} #{{ $log->target_id }})</span>
                            @endif
                        </div>
                        <span class="text-gray-400">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">{{ __('No activity yet.') }}</p>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
