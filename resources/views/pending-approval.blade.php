<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ auth()->user()->status === 'rejected' ? __('Registration Not Approved') : __('Account Pending Approval') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-700">
                @if (auth()->user()->status === 'rejected')
                    <p class="mb-2 text-red-700 font-medium">
                        {{ __('Your registration was not approved by an admin.') }}
                    </p>
                    @if ($rejectionReason)
                        <p class="mb-2">
                            <span class="font-medium">{{ __('Reason given:') }}</span> {{ $rejectionReason }}
                        </p>
                    @endif
                    <p>
                        {{ __('If you believe this is a mistake, please contact support with your registration details.') }}
                    </p>
                @else
                    <p class="mb-2">
                        {{ __('Your account has been submitted and is waiting for an admin to verify your documents.') }}
                    </p>
                    <p>
                        {{ __("You'll be able to access your dashboard once your registration is approved. This usually involves checking your submitted registration number/license against the official records.") }}
                    </p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
