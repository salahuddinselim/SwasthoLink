<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Two-Factor Authentication') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-green-100 text-green-800 px-4 py-3 rounded-md">{{ session('status') }}</div>
            @endif

            @if (session('two_factor_fresh_recovery_codes'))
                <div class="bg-amber-50 border border-amber-200 text-amber-900 px-4 py-3 rounded-md">
                    <p class="font-semibold text-sm mb-2">{{ __('Save your recovery codes now') }}</p>
                    <p class="text-sm mb-3">{{ __("Each code can be used once to log in if you lose access to your authenticator app. They won't be shown again.") }}</p>
                    <div class="grid grid-cols-2 gap-2 font-mono text-sm bg-white rounded p-3">
                        @foreach (session('two_factor_fresh_recovery_codes') as $code)
                            <span>{{ $code }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($user->hasTwoFactorEnabled())
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="px-2 py-1 text-xs rounded-full font-medium bg-green-100 text-green-800">{{ __('Enabled') }}</span>
                        <span class="text-sm text-gray-500">{{ __('since :date', ['date' => $user->two_factor_confirmed_at->format('d M Y')]) }}</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">
                        {{ __('You have :count unused recovery code(s) remaining.', ['count' => count($user->two_factor_recovery_codes ?? [])]) }}
                    </p>

                    <form method="POST" action="{{ route('two-factor.disable') }}" onsubmit="return confirm('{{ __('Disable two-factor authentication?') }}');">
                        @csrf
                        @method('DELETE')
                        <div class="mb-3">
                            <x-input-label for="password" :value="__('Confirm your password to disable *')" />
                            <x-text-input id="password" name="password" type="password" class="block mt-1 w-full" required />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>
                        <x-danger-button>{{ __('Disable Two-Factor Authentication') }}</x-danger-button>
                    </form>
                </div>
            @else
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-600 mb-4">
                        {{ __('Scan this QR code with an authenticator app (Google Authenticator, Authy, 1Password, etc.), then enter the 6-digit code it shows to confirm setup.') }}
                    </p>

                    <div class="flex justify-center mb-4">
                        <x-qr-code :data="$qrUri" :size="200" class="border rounded p-2" />
                    </div>

                    <p class="text-xs text-gray-500 text-center mb-6 font-mono break-all">{{ $secret }}</p>

                    <form method="POST" action="{{ route('two-factor.confirm') }}" class="flex gap-2 justify-center">
                        @csrf
                        <x-text-input name="code" class="w-40 text-center font-mono tracking-widest" inputmode="numeric" maxlength="6" placeholder="123456" required autofocus />
                        <x-primary-button>{{ __('Confirm & Enable') }}</x-primary-button>
                    </form>
                    <x-input-error :messages="$errors->get('code')" class="mt-2 text-center" />
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
