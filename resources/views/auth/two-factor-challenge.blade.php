<x-guest-layout>
    <p class="text-sm text-gray-600 mb-4">
        {{ __('Enter the 6-digit code from your authenticator app. Lost access? Use one of your recovery codes instead.') }}
    </p>

    <form method="POST" action="{{ route('two-factor.challenge') }}">
        @csrf

        <div>
            <x-input-label for="code" :value="__('Authentication Code')" />
            <x-text-input id="code" class="block mt-1 w-full text-center font-mono tracking-widest" name="code" inputmode="numeric" maxlength="20" required autofocus />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>{{ __('Verify') }}</x-primary-button>
        </div>
    </form>
</x-guest-layout>
