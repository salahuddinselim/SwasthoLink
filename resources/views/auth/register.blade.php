<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Registering as a patient.') }}
        {{ __('Are you a') }}
        <a class="underline" href="{{ route('register.doctor') }}">{{ __('doctor') }}</a>,
        <a class="underline" href="{{ route('register.hospital') }}">{{ __('hospital/clinic') }}</a>,
        {{ __('or') }}
        <a class="underline" href="{{ route('register.pharmacist') }}">{{ __('pharmacist') }}</a>?
    </div>

    <p class="text-xs text-gray-500 mb-4">* required</p>

    <form method="POST" action="{{ route('register') }}" x-data="{ touched: {} }">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name *')" />
            <x-text-input id="name" x-ref="name" x-on:blur="touched.name = true" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <p x-show="touched.name && !$refs.name.checkValidity()" x-text="$refs.name.validationMessage" class="text-sm text-red-600 mt-1"></p>
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email *')" />
            <x-text-input id="email" x-ref="email" x-on:blur="touched.email = true" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <p x-show="touched.email && !$refs.email.checkValidity()" x-text="$refs.email.validationMessage" class="text-sm text-red-600 mt-1"></p>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password *')" />

            <x-text-input id="password" x-ref="password" x-on:blur="touched.password = true" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <p x-show="touched.password && !$refs.password.checkValidity()" x-text="$refs.password.validationMessage" class="text-sm text-red-600 mt-1"></p>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password *')" />

            <x-text-input id="password_confirmation" x-ref="password_confirmation" x-on:blur="touched.password_confirmation = true" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <p x-show="touched.password_confirmation && !$refs.password_confirmation.checkValidity()" x-text="$refs.password_confirmation.validationMessage" class="text-sm text-red-600 mt-1"></p>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
