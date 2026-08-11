<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Prescription Lookup') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-green-100 text-green-800 px-4 py-3 rounded-md">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 text-red-800 px-4 py-3 rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-600 mb-4">
                    {{ __('Ask the patient for their prescription code (printed on their slip or sent by SMS) and enter it below.') }}
                </p>

                <form method="POST" action="{{ route('pharmacist.lookup.search') }}" class="flex gap-2">
                    @csrf
                    <x-text-input name="code" class="block w-full font-mono uppercase" placeholder="RX-XXXXXX" value="{{ old('code') }}" required autofocus />
                    <x-primary-button>{{ __('Look Up') }}</x-primary-button>
                </form>
                <x-input-error :messages="$errors->get('code')" class="mt-2" />
            </div>

            @if (($searched ?? false))
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    @if ($prescription)
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <p class="text-lg font-semibold">{{ $prescription->patient_name }}</p>
                                <p class="text-sm text-gray-500 font-mono">{{ $prescription->lookup_code }}</p>
                            </div>
                            <span @class([
                                'px-3 py-1 text-sm rounded-full font-medium',
                                'bg-blue-100 text-blue-800' => $prescription->status === 'active',
                                'bg-gray-100 text-gray-600' => $prescription->status === 'dispensed',
                            ])>
                                {{ ucfirst($prescription->status) }}
                            </span>
                        </div>

                        <dl class="text-sm mb-4 space-y-1">
                            <div><dt class="inline text-gray-500">{{ __('Prescribed by:') }}</dt> <dd class="inline">{{ $prescription->doctor->name }}</dd></div>
                            @if ($prescription->doctor->doctorProfile)
                                <div><dt class="inline text-gray-500">{{ __('BMDC No.:') }}</dt> <dd class="inline">{{ $prescription->doctor->doctorProfile->bmdc_number }}</dd></div>
                            @endif
                            @if ($prescription->hospital)
                                <div><dt class="inline text-gray-500">{{ __('Hospital:') }}</dt> <dd class="inline">{{ $prescription->hospital->name }}</dd></div>
                            @endif
                            <div><dt class="inline text-gray-500">{{ __('Issued:') }}</dt> <dd class="inline">{{ $prescription->created_at->format('d M Y, h:i A') }}</dd></div>
                        </dl>

                        <div class="border-t pt-4">
                            <p class="font-medium text-sm mb-1">{{ __('Medicines') }}</p>
                            <p class="text-sm whitespace-pre-line">{{ $prescription->medicines }}</p>
                        </div>

                        @if ($prescription->notes)
                            <div class="border-t pt-4 mt-4">
                                <p class="font-medium text-sm mb-1">{{ __('Notes') }}</p>
                                <p class="text-sm whitespace-pre-line">{{ $prescription->notes }}</p>
                            </div>
                        @endif

                        @if ($prescription->status === 'dispensed')
                            <div class="border-t pt-4 mt-4 text-sm text-gray-500">
                                {{ __('Already dispensed by :name on :date.', ['name' => $prescription->dispensedBy?->name ?? '—', 'date' => $prescription->dispensed_at?->format('d M Y, h:i A')]) }}
                            </div>
                        @else
                            <form method="POST" action="{{ route('pharmacist.prescriptions.dispense', $prescription) }}" class="mt-4">
                                @csrf
                                <x-primary-button>{{ __('Mark as Dispensed') }}</x-primary-button>
                            </form>
                        @endif
                    @else
                        <p class="text-red-700">{{ __('No prescription found with that code. Double-check the code with the patient.') }}</p>
                    @endif
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
