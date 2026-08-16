<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Share Access') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-green-100 text-green-800 px-4 py-3 rounded-md">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-600 mb-1">{{ __('Your Patient ID (give this to a doctor so they can look you up):') }}</p>
                <p class="font-mono text-lg font-semibold text-brand-700">{{ auth()->user()->patient_code }}</p>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-600 mb-4">
                    {{ __("A doctor at your own hospital can already see the prescriptions written there. If you're seeing a new doctor — or one at a different hospital — and want them to see your older records for the same condition, generate a temporary access code and read it out to them.") }}
                </p>
                <form method="POST" action="{{ route('patient.access.store') }}">
                    @csrf
                    <x-primary-button>{{ __('Generate Access Code') }}</x-primary-button>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <h3 class="text-lg font-semibold p-6 pb-0">{{ __('Your Access Codes') }}</h3>

                @forelse ($grants as $grant)
                    <div class="flex justify-between items-center border-b last:border-0 p-6">
                        <div>
                            <p class="font-mono font-medium">{{ $grant->code }}</p>
                            <p class="text-sm text-gray-500">
                                @if ($grant->revoked_at)
                                    {{ __('Revoked :date', ['date' => $grant->revoked_at->format('d M Y, h:i A')]) }}
                                @elseif ($grant->expires_at->isPast())
                                    {{ __('Expired :date', ['date' => $grant->expires_at->format('d M Y, h:i A')]) }}
                                @else
                                    {{ __('Expires :date', ['date' => $grant->expires_at->format('d M Y, h:i A')]) }}
                                @endif
                                &middot; {{ __('used :count time(s)', ['count' => $grant->use_count]) }}
                            </p>
                        </div>

                        @if ($grant->isActive())
                            <form method="POST" action="{{ route('patient.access.revoke', $grant) }}">
                                @csrf
                                <button type="submit" class="text-sm text-red-600 hover:text-red-800 underline">{{ __('Revoke') }}</button>
                            </form>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full font-medium bg-gray-100 text-gray-600">{{ __('Inactive') }}</span>
                        @endif
                    </div>
                @empty
                    <p class="p-6 text-sm text-gray-500">{{ __('No access codes generated yet.') }}</p>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
