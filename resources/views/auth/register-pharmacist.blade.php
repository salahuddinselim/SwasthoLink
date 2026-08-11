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

    <form method="POST" action="{{ route('register.pharmacist') }}" enctype="multipart/form-data">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Your Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <hr class="my-6">

        <div>
            <x-input-label for="pharmacy_name" :value="__('Pharmacy Name')" />
            <x-text-input id="pharmacy_name" class="block mt-1 w-full" type="text" name="pharmacy_name" :value="old('pharmacy_name')" required />
            <x-input-error :messages="$errors->get('pharmacy_name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="pharmacy_license_number" :value="__('Pharmacy License Number')" />
            <x-text-input id="pharmacy_license_number" class="block mt-1 w-full" type="text" name="pharmacy_license_number" :value="old('pharmacy_license_number')" required />
            <x-input-error :messages="$errors->get('pharmacy_license_number')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="address" :value="__('Address')" />
            <x-text-input id="address" class="block mt-1 w-full" type="text" name="address" :value="old('address')" />
            <x-input-error :messages="$errors->get('address')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="license_document" :value="__('License Document (PDF or image)')" />
            <input id="license_document" type="file" name="license_document" required
                class="block mt-1 w-full text-sm text-gray-700 border border-gray-300 rounded-md file:mr-4 file:py-2 file:px-4 file:border-0 file:bg-gray-100" />
            <x-input-error :messages="$errors->get('license_document')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Submit for Review') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
