<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Record Sharing') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @if (session('status'))
                <div class="bg-green-100 text-green-800 px-4 py-3 rounded-md">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 text-red-800 px-4 py-3 rounded-md">{{ session('error') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-2">{{ __('Share a Prescription With Another Hospital') }}</h3>
                <p class="text-sm text-gray-600 mb-4">
                    {{ __('Uses a fresh Diffie-Hellman key exchange with the recipient hospital to derive a one-time AES-256 key, which encrypts the record before it ever touches the database.') }}
                </p>

                @if ($prescriptions->isEmpty())
                    <p class="text-sm text-gray-500">{{ __('No prescriptions from your hospital to share yet.') }}</p>
                @elseif ($partnerHospitals->isEmpty())
                    <p class="text-sm text-gray-500">{{ __('No other approved hospitals are available to share with yet.') }}</p>
                @else
                    <form method="POST" action="{{ route('hospital.shares.store') }}" class="grid sm:grid-cols-3 gap-3 items-end">
                        @csrf
                        <div>
                            <x-input-label for="prescription_id" :value="__('Prescription *')" />
                            <select id="prescription_id" name="prescription_id" required
                                class="border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm block mt-1 w-full">
                                @foreach ($prescriptions as $prescription)
                                    <option value="{{ $prescription->id }}">{{ $prescription->lookup_code }} — {{ $prescription->patient_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="recipient_hospital_id" :value="__('Share With *')" />
                            <select id="recipient_hospital_id" name="recipient_hospital_id" required
                                class="border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm block mt-1 w-full">
                                @foreach ($partnerHospitals as $partner)
                                    <option value="{{ $partner->id }}">{{ $partner->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <x-primary-button>{{ __('Start Key Exchange') }}</x-primary-button>
                    </form>
                @endif
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">{{ __('Incoming Requests') }}</h3>
                @forelse ($incoming as $share)
                    <div class="flex justify-between items-center border-b last:border-0 py-3 text-sm">
                        <div>
                            <p class="font-medium">{{ $share->prescription->lookup_code }} {{ __('from') }} {{ $share->initiatorHospital->name }}</p>
                            <p class="text-gray-500">{{ __('Requested') }} {{ $share->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if ($share->status === 'pending')
                                <form method="POST" action="{{ route('hospital.shares.accept', $share) }}">
                                    @csrf
                                    <x-primary-button>{{ __('Accept') }}</x-primary-button>
                                </form>
                                <form method="POST" action="{{ route('hospital.shares.reject', $share) }}">
                                    @csrf
                                    <x-secondary-button>{{ __('Reject') }}</x-secondary-button>
                                </form>
                            @elseif ($share->status === 'completed')
                                <a href="{{ route('hospital.shares.show', $share) }}" class="text-brand-700 underline">{{ __('View') }}</a>
                            @else
                                <span class="text-gray-400">{{ ucfirst($share->status) }}</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">{{ __('No incoming share requests.') }}</p>
                @endforelse
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">{{ __('Outgoing Requests') }}</h3>
                @forelse ($outgoing as $share)
                    <div class="flex justify-between items-center border-b last:border-0 py-3 text-sm">
                        <div>
                            <p class="font-medium">{{ $share->prescription->lookup_code }} {{ __('to') }} {{ $share->recipientHospital->name }}</p>
                            <p class="text-gray-500">{{ __('Sent') }} {{ $share->created_at->diffForHumans() }}</p>
                        </div>
                        <div>
                            @if ($share->status === 'completed')
                                <a href="{{ route('hospital.shares.show', $share) }}" class="text-brand-700 underline">{{ __('View') }}</a>
                            @else
                                <span @class([
                                    'px-2 py-1 text-xs rounded-full font-medium',
                                    'bg-amber-100 text-amber-800' => $share->status === 'pending',
                                    'bg-red-100 text-red-800' => $share->status === 'rejected',
                                ])>{{ ucfirst($share->status) }}</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">{{ __('No outgoing share requests.') }}</p>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
