<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            @if (auth()->user()->canUseTwoFactor())
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <h2 class="text-lg font-medium text-gray-900">{{ __('Two-Factor Authentication') }}</h2>
                        <p class="mt-1 text-sm text-gray-600">
                            @if (auth()->user()->hasTwoFactorEnabled())
                                {{ __('Enabled — an authenticator app code is required at login, on top of your password.') }}
                            @else
                                {{ __('Not enabled. As an :role account, enabling this significantly reduces the risk of a compromised password taking over the account.', ['role' => auth()->user()->role]) }}
                            @endif
                        </p>
                        <a href="{{ route('two-factor.show') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-brand-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-800">
                            {{ auth()->user()->hasTwoFactorEnabled() ? __('Manage Two-Factor Authentication') : __('Enable Two-Factor Authentication') }}
                        </a>
                    </div>
                </div>
            @endif

            @if (auth()->user()->role === 'patient')
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            @else
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl text-sm text-gray-500">
                        {{ __('Account deletion is not available for :role accounts, since deleting one could remove records other people depend on (prescriptions, approvals, audit history). Contact an administrator if you need this account removed.', ['role' => auth()->user()->role]) }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
