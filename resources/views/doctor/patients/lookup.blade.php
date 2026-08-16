<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Patient Records') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-600 mb-4">
                    {{ __("Look up a patient by their Patient ID (e.g. PT-000004) or account email. You'll automatically see their history from your own hospital. To see records from other hospitals/doctors, the patient must give you a temporary access code (Share Access page on their account).") }}
                </p>

                <form method="POST" action="{{ route('doctor.patients.search') }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="patient_identifier" :value="__('Patient ID or Email *')" />
                        <x-text-input id="patient_identifier" class="block mt-1 w-full" type="text" name="patient_identifier" value="{{ old('patient_identifier') }}" placeholder="PT-000004" required autofocus />
                        <x-input-error :messages="$errors->get('patient_identifier')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="access_code" :value="__('Patient Access Code (optional — unlocks other hospitals\' records)')" />
                        <x-text-input id="access_code" class="block mt-1 w-full font-mono uppercase" type="text" name="access_code" placeholder="PA-XXXXXX" />
                        <x-input-error :messages="$errors->get('access_code')" class="mt-2" />
                    </div>
                    <x-primary-button>{{ __('Search') }}</x-primary-button>
                </form>
            </div>

            @if ($searched ?? false)
                @if (! $patient)
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <p class="text-red-700 text-sm">{{ __('No patient found with that ID or email.') }}</p>
                    </div>
                @else
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <p class="text-lg font-semibold">{{ $patient->name }}</p>
                        <p class="text-sm text-gray-500 font-mono">{{ $patient->patient_code }} &middot; {{ $patient->email }}</p>
                    </div>

                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4">{{ __('History at Your Hospital') }}</h3>

                        @forelse ($sameHospitalPrescriptions as $prescription)
                            <div class="border-b last:border-0 py-3 text-sm">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="font-mono text-gray-600">{{ $prescription->lookup_code }}</span>
                                        &mdash; {{ $prescription->doctor->name }}
                                    </div>
                                    <span class="text-gray-400">{{ $prescription->created_at->format('d M Y') }}</span>
                                </div>
                                <p class="text-gray-700 mt-1 whitespace-pre-line">{{ $prescription->medicines }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">{{ __('No prior records for this patient at your hospital.') }}</p>
                        @endforelse
                    </div>

                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4">{{ __('Records from Other Hospitals') }}</h3>

                        @if ($accessError ?? false)
                            <p class="text-red-700 text-sm mb-3">{{ $accessError }}</p>
                        @endif

                        @if ($accessGranted ?? false)
                            @forelse ($externalPrescriptions as $prescription)
                                <div class="border-b last:border-0 py-3 text-sm">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <span class="font-mono text-gray-600">{{ $prescription->lookup_code }}</span>
                                            &mdash; {{ $prescription->doctor->name }}
                                            @if ($prescription->hospital)
                                                ({{ $prescription->hospital->name }})
                                            @endif
                                        </div>
                                        <span class="text-gray-400">{{ $prescription->created_at->format('d M Y') }}</span>
                                    </div>
                                    <p class="text-gray-700 mt-1 whitespace-pre-line">{{ $prescription->medicines }}</p>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">{{ __('No external records found for this patient.') }}</p>
                            @endforelse
                        @else
                            <p class="text-sm text-gray-500">{{ __('Locked. Ask the patient for a temporary access code and search again with it filled in.') }}</p>
                        @endif
                    </div>
                @endif
            @endif

        </div>
    </div>
</x-app-layout>
