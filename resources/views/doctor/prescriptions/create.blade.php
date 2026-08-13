<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New Prescription') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                <form method="POST" action="{{ route('doctor.prescriptions.store') }}" x-data="{ touched: {} }">
                    @csrf

                    <div>
                        <x-input-label for="patient_name" :value="__('Patient Name *')" />
                        <x-text-input id="patient_name" x-ref="patient_name" x-on:blur="touched.patient_name = true" class="block mt-1 w-full" type="text" name="patient_name" :value="old('patient_name')" required autofocus />
                        <p x-show="touched.patient_name && !$refs.patient_name.checkValidity()" x-text="$refs.patient_name.validationMessage" class="text-sm text-red-600 mt-1"></p>
                        <x-input-error :messages="$errors->get('patient_name')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="patient_phone" :value="__('Patient Phone *')" />
                        <x-text-input id="patient_phone" x-ref="patient_phone" x-on:blur="touched.patient_phone = true" class="block mt-1 w-full" type="tel" name="patient_phone" :value="old('patient_phone')" required />
                        <p class="text-xs text-gray-500 mt-1">{{ __('Used as a second factor when a pharmacist looks up this prescription.') }}</p>
                        <p x-show="touched.patient_phone && !$refs.patient_phone.checkValidity()" x-text="$refs.patient_phone.validationMessage" class="text-sm text-red-600 mt-1"></p>
                        <x-input-error :messages="$errors->get('patient_phone')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="patient_email" :value="__('Patient Email (optional — links to their account if registered)')" />
                        <x-text-input id="patient_email" x-ref="patient_email" x-on:blur="touched.patient_email = true" class="block mt-1 w-full" type="email" name="patient_email" :value="old('patient_email')" />
                        <p x-show="touched.patient_email && !$refs.patient_email.checkValidity()" x-text="$refs.patient_email.validationMessage" class="text-sm text-red-600 mt-1"></p>
                        <x-input-error :messages="$errors->get('patient_email')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="medicines" :value="__('Medicines & Dosage *')" />
                        <textarea id="medicines" x-ref="medicines" x-on:blur="touched.medicines = true" name="medicines" rows="6" required
                            class="border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm block mt-1 w-full"
                            placeholder="e.g.&#10;Napa Extra 500mg — 1 tablet 3x daily after meals, 5 days&#10;Seclo 20mg — 1 capsule before breakfast, 5 days">{{ old('medicines') }}</textarea>
                        <p x-show="touched.medicines && !$refs.medicines.checkValidity()" x-text="$refs.medicines.validationMessage" class="text-sm text-red-600 mt-1"></p>
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
