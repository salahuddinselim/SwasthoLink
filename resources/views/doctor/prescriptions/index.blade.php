<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My Prescriptions') }}
            </h2>
            <a href="{{ route('doctor.prescriptions.create') }}">
                <x-primary-button>{{ __('+ New Prescription') }}</x-primary-button>
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-green-100 text-green-800 px-4 py-3 rounded-md">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                @forelse ($prescriptions as $prescription)
                    <div class="flex justify-between items-center border-b last:border-0 p-4">
                        <div>
                            <p class="font-medium">{{ $prescription->patient_name }}</p>
                            <p class="text-sm text-gray-500">
                                <span class="font-mono">{{ $prescription->lookup_code }}</span>
                                &middot; {{ $prescription->created_at->format('d M Y, h:i A') }}
                            </p>
                        </div>
                        <span @class([
                            'px-2 py-1 text-xs rounded-full font-medium',
                            'bg-blue-100 text-blue-800' => $prescription->status === 'active',
                            'bg-gray-100 text-gray-600' => $prescription->status === 'dispensed',
                        ])>
                            {{ ucfirst($prescription->status) }}
                        </span>
                    </div>
                @empty
                    <div class="p-6 text-center text-gray-500">
                        {{ __("You haven't written any prescriptions yet.") }}
                        <a href="{{ route('doctor.prescriptions.create') }}" class="underline text-indigo-600">{{ __('Write your first one') }}</a>.
                    </div>
                @endforelse
            </div>

            {{ $prescriptions->links() }}
        </div>
    </div>
</x-app-layout>
