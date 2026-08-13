<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Shared Record') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="mb-4 px-3 py-2 rounded-md text-sm font-medium inline-flex items-center gap-2 bg-green-100 text-green-800">
                    ✓ {{ __('Decrypted with your hospital\'s RSA private key — envelope key derived from a Diffie-Hellman exchange with :other', ['other' => $otherHospital->name]) }}
                </div>

                <dl class="text-sm mb-4 space-y-1">
                    <div><dt class="inline text-gray-500">{{ __('Lookup Code:') }}</dt> <dd class="inline font-mono">{{ $payload['lookup_code'] }}</dd></div>
                    <div><dt class="inline text-gray-500">{{ __('Patient:') }}</dt> <dd class="inline">{{ $payload['patient_name'] }}</dd></div>
                    <div><dt class="inline text-gray-500">{{ __('Prescribed by:') }}</dt> <dd class="inline">{{ $payload['doctor_name'] }}</dd></div>
                    <div><dt class="inline text-gray-500">{{ __('Issued:') }}</dt> <dd class="inline">{{ \Illuminate\Support\Carbon::parse($payload['issued_at'])->format('d M Y, h:i A') }}</dd></div>
                </dl>

                <div class="border-t pt-4">
                    <p class="font-medium text-sm mb-1">{{ __('Medicines') }}</p>
                    <p class="text-sm whitespace-pre-line">{{ $payload['medicines'] }}</p>
                </div>

                @if ($payload['notes'])
                    <div class="border-t pt-4 mt-4">
                        <p class="font-medium text-sm mb-1">{{ __('Notes') }}</p>
                        <p class="text-sm whitespace-pre-line">{{ $payload['notes'] }}</p>
                    </div>
                @endif

                <div class="border-t pt-4 mt-4 text-xs text-gray-400 font-mono break-all">
                    {{ __('Shared-secret fingerprint (SHA-256, not reversible to the key):') }} {{ $share->shared_secret_fingerprint }}
                </div>
            </div>

            <a href="{{ route('hospital.shares.index') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">
                {{ __('← Back to Record Sharing') }}
            </a>
        </div>
    </div>
</x-app-layout>
