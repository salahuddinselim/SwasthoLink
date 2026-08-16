<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My Prescriptions') }}
            </h2>
            <a href="{{ route('doctor.prescriptions.create') }}">
                <x-primary-button>{{ __('+ New Prescription') }}</x-primary-button>
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-green-100 text-green-800 px-4 py-3 rounded-md flex items-center justify-between gap-4">
                    <span>{{ session('status') }}</span>
                    @if (session('new_lookup_code'))
                        <x-qr-code :data="session('new_lookup_code')" :size="72" class="shrink-0 bg-white p-1 rounded" />
                    @endif
                </div>
            @endif

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">{{ __('Total Written') }}</p>
                    <p class="text-3xl font-semibold text-gray-800">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">{{ __('Active') }}</p>
                    <p class="text-3xl font-semibold text-brand-700">{{ $stats['active'] }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">{{ __('Dispensed') }}</p>
                    <p class="text-3xl font-semibold text-gray-600">{{ $stats['dispensed'] }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">{{ __('Unique Patients') }}</p>
                    <p class="text-3xl font-semibold text-gray-800">{{ $stats['unique_patients'] }}</p>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="GET" class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1">
                        <x-input-label for="search" :value="__('Search patient name or lookup code')" />
                        <x-text-input id="search" name="search" class="block mt-1 w-full" value="{{ request('search') }}" />
                    </div>
                    <div>
                        <x-input-label for="from" :value="__('From')" />
                        <x-text-input id="from" name="from" type="date" class="block mt-1 w-full" value="{{ request('from') }}" />
                    </div>
                    <div>
                        <x-input-label for="to" :value="__('To')" />
                        <x-text-input id="to" name="to" type="date" class="block mt-1 w-full" value="{{ request('to') }}" />
                    </div>
                    <div class="flex items-end gap-2">
                        <x-primary-button>{{ __('Filter') }}</x-primary-button>
                        @if (request('search') || request('from') || request('to'))
                            <a href="{{ route('doctor.prescriptions.index') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">{{ __('Clear') }}</a>
                        @endif
                        <a href="{{ route('doctor.prescriptions.export', request()->query()) }}" class="ml-auto text-sm text-brand-700 hover:text-brand-900 underline">{{ __('Export CSV') }}</a>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden" x-data="{ expanded: null }">
                @forelse ($prescriptions as $prescription)
                    <div class="border-b last:border-0 p-4">
                        <div class="flex justify-between items-center">
                            <button type="button" @click="expanded = expanded === {{ $prescription->id }} ? null : {{ $prescription->id }}" class="text-left">
                                <p class="font-medium">{{ $prescription->patient_name }}</p>
                                <p class="text-sm text-gray-500">
                                    <span class="font-mono">{{ $prescription->lookup_code }}</span>
                                    &middot; {{ $prescription->created_at->format('d M Y, h:i A') }}
                                </p>
                            </button>
                            <span @class([
                                'px-2 py-1 text-xs rounded-full font-medium',
                                'bg-brand-100 text-brand-700' => $prescription->status === 'active',
                                'bg-gray-100 text-gray-600' => $prescription->status === 'dispensed',
                            ])>
                                {{ ucfirst($prescription->status) }}
                            </span>
                        </div>
                        <div x-show="expanded === {{ $prescription->id }}" x-cloak class="mt-3 pt-3 border-t flex items-start gap-4">
                            <x-qr-code :data="$prescription->lookup_code" :size="96" class="shrink-0" />
                            <div class="text-sm flex-1">
                                <p class="font-medium mb-1">{{ __('Medicines') }}</p>
                                <p class="text-gray-700 whitespace-pre-line">{{ $prescription->medicines }}</p>
                            </div>
                            <a href="{{ route('prescriptions.pdf', $prescription) }}" class="shrink-0 text-sm text-brand-700 hover:text-brand-900 underline">{{ __('Download PDF') }}</a>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-gray-500">
                        {{ __("You haven't written any prescriptions yet.") }}
                        <a href="{{ route('doctor.prescriptions.create') }}" class="underline text-brand-600">{{ __('Write your first one') }}</a>.
                    </div>
                @endforelse
            </div>

            {{ $prescriptions->links() }}
        </div>
    </div>
</x-app-layout>
