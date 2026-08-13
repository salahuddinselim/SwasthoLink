<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Register your pharmacy.') }}
        {{ __('Your account will stay pending until an admin verifies your pharmacy license.') }}
        {{ __('Not a pharmacist?') }}
        <a class="underline" href="{{ route('register') }}">{{ __('Register as a patient') }}</a>,
        <a class="underline" href="{{ route('register.doctor') }}">{{ __('doctor') }}</a>,
        {{ __('or') }}
        <a class="underline" href="{{ route('register.hospital') }}">{{ __('hospital/clinic') }}</a>.
    </div>

    <p class="text-xs text-gray-500 mb-4">* required</p>

    <form method="POST" action="{{ route('register.pharmacist') }}" enctype="multipart/form-data" x-data="{ submitting: false, touched: {} }" x-on:submit="submitting = true">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Your Name *')" />
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
            <x-input-label for="pharmacy_name" :value="__('Pharmacy Name *')" />
            <x-text-input id="pharmacy_name" x-ref="pharmacy_name" x-on:blur="touched.pharmacy_name = true" class="block mt-1 w-full" type="text" name="pharmacy_name" :value="old('pharmacy_name')" required />
            <p x-show="touched.pharmacy_name && !$refs.pharmacy_name.checkValidity()" x-text="$refs.pharmacy_name.validationMessage" class="text-sm text-red-600 mt-1"></p>
            <x-input-error :messages="$errors->get('pharmacy_name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="pharmacy_license_number" :value="__('Pharmacy License Number *')" />
            <x-text-input id="pharmacy_license_number" x-ref="pharmacy_license_number" x-on:blur="touched.pharmacy_license_number = true" class="block mt-1 w-full" type="text" name="pharmacy_license_number" :value="old('pharmacy_license_number')" required />
            <p x-show="touched.pharmacy_license_number && !$refs.pharmacy_license_number.checkValidity()" x-text="$refs.pharmacy_license_number.validationMessage" class="text-sm text-red-600 mt-1"></p>
            <x-input-error :messages="$errors->get('pharmacy_license_number')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="address" :value="__('Address (optional)')" />
            <x-text-input id="address" class="block mt-1 w-full" type="text" name="address" :value="old('address')" />
            <x-input-error :messages="$errors->get('address')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="license_document" :value="__('License Document (PDF or image) *')" />
            <input id="license_document" x-ref="license_document" x-on:blur="touched.license_document = true" type="file" name="license_document" required
                class="block mt-1 w-full text-sm text-gray-700 border border-gray-300 rounded-md file:mr-4 file:py-2 file:px-4 file:border-0 file:bg-gray-100" />
            <p x-show="touched.license_document && !$refs.license_document.checkValidity()" x-text="$refs.license_document.validationMessage" class="text-sm text-red-600 mt-1"></p>
            <x-input-error :messages="$errors->get('license_document')" class="mt-2" />
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
