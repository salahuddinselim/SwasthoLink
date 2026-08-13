<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Register as a doctor.') }}
        {{ __('Your account will stay pending until an admin verifies your BMDC registration.') }}
        {{ __('Not a doctor?') }}
        <a class="underline" href="{{ route('register') }}">{{ __('Register as a patient') }}</a>,
        <a class="underline" href="{{ route('register.hospital') }}">{{ __('hospital/clinic') }}</a>,
        {{ __('or') }}
        <a class="underline" href="{{ route('register.pharmacist') }}">{{ __('pharmacist') }}</a>.
    </div>

    <p class="text-xs text-gray-500 mb-4">* required</p>

    <form method="POST" action="{{ route('register.doctor') }}" enctype="multipart/form-data" x-data="{ submitting: false, touched: {} }" x-on:submit="submitting = true">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Full Name *')" />
            <x-text-input id="name" x-ref="name" x-on:blur="touched.name = true" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <p x-show="touched.name && !$refs.name.checkValidity()" x-text="$refs.name.validationMessage" class="text-sm text-red-600 mt-1"></p>
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email *')" />
            <x-text-input id="email" x-ref="email" x-on:blur="touched.email = true" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <p x-show="touched.email && !$refs.email.checkValidity()" x-text="$refs.email.validationMessage" class="text-sm text-red-600 mt-1"></p>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password *')" />
            <x-text-input id="password" x-ref="password" x-on:blur="touched.password = true" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <p x-show="touched.password && !$refs.password.checkValidity()" x-text="$refs.password.validationMessage" class="text-sm text-red-600 mt-1"></p>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password *')" />
            <x-text-input id="password_confirmation" x-ref="password_confirmation" x-on:blur="touched.password_confirmation = true" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <p x-show="touched.password_confirmation && !$refs.password_confirmation.checkValidity()" x-text="$refs.password_confirmation.validationMessage" class="text-sm text-red-600 mt-1"></p>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <hr class="my-6">

        <div>
            <x-input-label for="bmdc_number" :value="__('BMDC Registration Number *')" />
            <x-text-input id="bmdc_number" x-ref="bmdc_number" x-on:blur="touched.bmdc_number = true" class="block mt-1 w-full" type="text" name="bmdc_number" :value="old('bmdc_number')" required />
            <p x-show="touched.bmdc_number && !$refs.bmdc_number.checkValidity()" x-text="$refs.bmdc_number.validationMessage" class="text-sm text-red-600 mt-1"></p>
            <x-input-error :messages="$errors->get('bmdc_number')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="specialization" :value="__('Specialization (optional)')" />
            <x-text-input id="specialization" class="block mt-1 w-full" type="text" name="specialization" :value="old('specialization')" />
            <x-input-error :messages="$errors->get('specialization')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="hospital_id" :value="__('Affiliated Hospital (optional)')" />
            <select id="hospital_id" name="hospital_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                <option value="">{{ __('— Independent practice —') }}</option>
                @foreach ($hospitals as $hospital)
                    <option value="{{ $hospital->id }}" @selected(old('hospital_id') == $hospital->id)>{{ $hospital->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('hospital_id')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="bmdc_certificate" :value="__('BMDC Certificate (PDF or image) *')" />
            <input id="bmdc_certificate" x-ref="bmdc_certificate" x-on:blur="touched.bmdc_certificate = true" type="file" name="bmdc_certificate" required
                class="block mt-1 w-full text-sm text-gray-700 border border-gray-300 rounded-md file:mr-4 file:py-2 file:px-4 file:border-0 file:bg-gray-100" />
            <p x-show="touched.bmdc_certificate && !$refs.bmdc_certificate.checkValidity()" x-text="$refs.bmdc_certificate.validationMessage" class="text-sm text-red-600 mt-1"></p>
            <x-input-error :messages="$errors->get('bmdc_certificate')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="nid_document" :value="__('National ID (PDF or image) *')" />
            <input id="nid_document" x-ref="nid_document" x-on:blur="touched.nid_document = true" type="file" name="nid_document" required
                class="block mt-1 w-full text-sm text-gray-700 border border-gray-300 rounded-md file:mr-4 file:py-2 file:px-4 file:border-0 file:bg-gray-100" />
            <p x-show="touched.nid_document && !$refs.nid_document.checkValidity()" x-text="$refs.nid_document.validationMessage" class="text-sm text-red-600 mt-1"></p>
            <x-input-error :messages="$errors->get('nid_document')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4" x-bind:disabled="submitting">
                <span x-show="!submitting">{{ __('Submit for Review') }}</span>
                <span x-show="submitting" x-cloak>{{ __('Submitting…') }}</span>
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
