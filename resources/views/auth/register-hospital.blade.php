<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Register your hospital or clinic.') }}
        {{ __('Your account will stay pending until an admin verifies your registration documents.') }}
        {{ __('Not a hospital?') }}
        <a class="underline" href="{{ route('register') }}">{{ __('Register as a patient') }}</a>,
        <a class="underline" href="{{ route('register.doctor') }}">{{ __('doctor') }}</a>,
        {{ __('or') }}
        <a class="underline" href="{{ route('register.pharmacist') }}">{{ __('pharmacist') }}</a>.
    </div>

    <form method="POST" action="{{ route('register.hospital') }}" enctype="multipart/form-data">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Your Name (Admin Contact)')" />
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
            <x-input-label for="hospital_name" :value="__('Hospital / Clinic Name')" />
            <x-text-input id="hospital_name" class="block mt-1 w-full" type="text" name="hospital_name" :value="old('hospital_name')" required />
            <x-input-error :messages="$errors->get('hospital_name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="registration_number" :value="__('Legal Registration Number (DGHS / Trade License)')" />
            <x-text-input id="registration_number" class="block mt-1 w-full" type="text" name="registration_number" :value="old('registration_number')" required />
            <x-input-error :messages="$errors->get('registration_number')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="address" :value="__('Address')" />
            <x-text-input id="address" class="block mt-1 w-full" type="text" name="address" :value="old('address')" />
            <x-input-error :messages="$errors->get('address')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="license_document" :value="__('Registration / License Document (PDF or image)')" />
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
