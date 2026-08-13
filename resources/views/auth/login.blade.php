<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <p class="text-xs text-gray-500 mb-4">* required</p>

    <form method="POST" action="{{ route('login') }}" x-data="{ touched: {} }">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email *')" />
            <x-text-input id="email" x-ref="email" x-on:blur="touched.email = true" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <p x-show="touched.email && !$refs.email.checkValidity()" x-text="$refs.email.validationMessage" class="text-sm text-red-600 mt-1"></p>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password *')" />

            <x-text-input id="password" x-ref="password" x-on:blur="touched.password = true" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <p x-show="touched.password && !$refs.password.checkValidity()" x-text="$refs.password.validationMessage" class="text-sm text-red-600 mt-1"></p>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
