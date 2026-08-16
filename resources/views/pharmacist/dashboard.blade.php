<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pharmacist Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <div class="flex justify-end">
                <a href="{{ route('pharmacist.lookup') }}" class="inline-flex items-center px-4 py-2 bg-brand-700 text-white text-sm font-medium rounded-md hover:bg-brand-800">
                    {{ __('New Lookup') }}
                </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">{{ __('Codes Looked Up') }}</p>
                    <p class="text-3xl font-semibold text-gray-800">{{ $stats['lookups'] }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">{{ __('Signatures Verified') }}</p>
                    <p class="text-3xl font-semibold text-green-700">{{ $stats['verified'] }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">{{ __('Prescriptions Dispensed') }}</p>
                    <p class="text-3xl font-semibold text-brand-700">{{ $stats['dispensed'] }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">{{ __('Failed 2nd-Factor Checks') }}</p>
                    <p class="text-3xl font-semibold text-red-600">{{ $stats['failed_verifications'] }}</p>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">{{ __('Recently Dispensed') }}</h3>

                @forelse ($recentDispensed as $prescription)
                    <div class="flex justify-between items-center border-b last:border-0 py-3 text-sm">
                        <div>
                            <span class="font-mono text-gray-600">{{ $prescription->lookup_code }}</span>
                            &mdash; {{ $prescription->patient_name }}
                        </div>
                        <span class="text-gray-400">{{ $prescription->dispensed_at?->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">{{ __('Nothing dispensed yet.') }}</p>
                @endforelse
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">{{ __('Your Recent Activity') }}</h3>

                @forelse ($recentActivity as $log)
                    <div class="flex justify-between items-center border-b last:border-0 py-2 text-sm">
                        <span class="text-gray-600">{{ str_replace('.', ' ', $log->action) }}</span>
                        <span class="text-gray-400">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">{{ __('No activity yet.') }}</p>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
