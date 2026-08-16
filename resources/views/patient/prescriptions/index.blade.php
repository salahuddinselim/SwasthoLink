<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Prescriptions') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                @forelse ($prescriptions as $prescription)
                    <div class="border-b last:border-0 p-4">
                        <div class="flex justify-between items-start gap-4">
                            <div class="flex-1">
                                <p class="font-medium">{{ $prescription->doctor->name }}</p>
                                <p class="text-sm text-gray-500">
                                    <span class="font-mono">{{ $prescription->lookup_code }}</span>
                                    &middot; {{ $prescription->created_at->format('d M Y, h:i A') }}
                                </p>
                                <p class="text-sm text-gray-700 mt-2 whitespace-pre-line">{{ $prescription->medicines }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <span @class([
                                    'px-2 py-1 text-xs rounded-full font-medium inline-block mb-2',
                                    'bg-brand-100 text-brand-700' => $prescription->status === 'active',
                                    'bg-gray-100 text-gray-600' => $prescription->status === 'dispensed',
                                ])>
                                    {{ ucfirst($prescription->status) }}
                                </span>
                                @if ($prescription->status === 'active')
                                    <x-qr-code :data="$prescription->lookup_code" :size="72" />
                                @endif
                                <a href="{{ route('prescriptions.pdf', $prescription) }}" class="block mt-2 text-sm text-brand-700 hover:text-brand-900 underline">{{ __('Download PDF') }}</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-gray-500">
                        {{ __('No prescriptions yet. Once a doctor writes you a prescription using this email, it will show up here.') }}
                    </div>
                @endforelse
            </div>

            {{ $prescriptions->links() }}
        </div>
    </div>
</x-app-layout>
