<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New Prescription') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                <form method="POST" action="{{ route('doctor.prescriptions.store') }}">
                    @csrf

                    <div>
                        <x-input-label for="patient_name" :value="__('Patient Name')" />
                        <x-text-input id="patient_name" class="block mt-1 w-full" type="text" name="patient_name" :value="old('patient_name')" required autofocus />
                        <x-input-error :messages="$errors->get('patient_name')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="patient_email" :value="__('Patient Email (optional — links to their account if registered)')" />
                        <x-text-input id="patient_email" class="block mt-1 w-full" type="email" name="patient_email" :value="old('patient_email')" />
                        <x-input-error :messages="$errors->get('patient_email')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="medicines" :value="__('Medicines & Dosage')" />
                        <textarea id="medicines" name="medicines" rows="6" required
                            class="border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm block mt-1 w-full"
                            placeholder="e.g.&#10;Napa Extra 500mg — 1 tablet 3x daily after meals, 5 days&#10;Seclo 20mg — 1 capsule before breakfast, 5 days">{{ old('medicines') }}</textarea>
                        <x-input-error :messages="$errors->get('medicines')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="notes" :value="__('Notes (optional)')" />
                        <textarea id="notes" name="notes" rows="3"
                            class="border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm block mt-1 w-full">{{ old('notes') }}</textarea>
                        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <a class="underline text-sm text-gray-600 hover:text-gray-900 me-4" href="{{ route('doctor.prescriptions.index') }}">
                            {{ __('Cancel') }}
                        </a>
                        <x-primary-button>{{ __('Save Prescription') }}</x-primary-button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
