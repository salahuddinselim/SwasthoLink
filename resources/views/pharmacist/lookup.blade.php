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
                    @if (($expired ?? false))
                        <p class="text-red-700">{{ __('This prescription code has expired (codes are valid for 30 days after issue). Ask the patient to contact their doctor for a new one.') }}</p>
                    @elseif ($prescription)

                        @if (($revealed ?? false))
                            {{-- Full detail, after second-factor confirmation --}}
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <p class="text-lg font-semibold">{{ $prescription->patient_name }}</p>
                                    <p class="text-sm text-gray-500 font-mono">{{ $prescription->lookup_code }}</p>
                                </div>
                                <span @class([
                                    'px-3 py-1 text-sm rounded-full font-medium',
                                    'bg-brand-100 text-brand-700' => $prescription->status === 'active',
                                    'bg-gray-100 text-gray-600' => $prescription->status === 'dispensed',
                                ])>
                                    {{ ucfirst($prescription->status) }}
                                </span>
                            </div>

                            <div @class([
                                'mb-4 px-3 py-2 rounded-md text-sm font-medium inline-flex items-center gap-2',
                                'bg-green-100 text-green-800' => $signatureValid ?? false,
                                'bg-red-100 text-red-800' => ! ($signatureValid ?? false),
                            ])>
                                @if ($signatureValid ?? false)
                                    ✓ {{ __('Doctor signature verified') }}
                                @else
                                    ⚠ {{ __('Signature verification FAILED — this prescription may be forged or tampered with') }}
                                @endif
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
                                <div><dt class="inline text-gray-500">{{ __('Expires:') }}</dt> <dd class="inline">{{ $prescription->expires_at?->format('d M Y') }}</dd></div>
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

                        @elseif ($awaitingSecondFactor ?? false)
                            {{-- Second factor gate: patient not yet confirmed, no medicines shown --}}
                            <p class="text-sm text-gray-600 mb-3">
                                {{ __('Prescription found for :name. Confirm their identity to view details.', ['name' => $prescription->patient_name]) }}
                            </p>

                            @if ($secondFactorError ?? false)
                                <p class="text-red-700 text-sm mb-3">{{ $secondFactorError }}</p>
                            @endif

                            <form method="POST" action="{{ route('pharmacist.lookup.verify') }}" class="flex gap-2 items-start">
                                @csrf
                                <input type="hidden" name="code" value="{{ $prescription->lookup_code }}">
                                <div class="flex-1">
                                    <x-input-label for="phone_last4" :value="__('Last 4 digits of patient phone *')" />
                                    <x-text-input id="phone_last4" class="block mt-1 w-full" type="text" inputmode="numeric" maxlength="4" name="phone_last4" required autofocus />
                                </div>
                                <x-primary-button class="mt-6">{{ __('Confirm') }}</x-primary-button>
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
