<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $hospital->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-2">{{ __('Hospital Profile') }}</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-sm">
                    <div><dt class="text-gray-500">{{ __('Registration No.') }}</dt><dd>{{ $hospital->registration_number }}</dd></div>
                    <div><dt class="text-gray-500">{{ __('Address') }}</dt><dd>{{ $hospital->address ?: '—' }}</dd></div>
                    <div><dt class="text-gray-500">{{ __('Verified') }}</dt><dd>{{ $hospital->verified_at?->format('d M Y') ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">{{ __('Affiliated Doctors') }}</dt><dd>{{ $hospital->doctorProfiles->count() }}</dd></div>
                </dl>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">{{ __('Affiliated Doctors') }}</h3>

                @forelse ($hospital->doctorProfiles as $doctor)
                    <div class="flex justify-between items-center border-b last:border-0 py-3">
                        <div>
                            <p class="font-medium">{{ $doctor->user->name }} @if($doctor->specialization) &mdash; {{ $doctor->specialization }} @endif</p>
                            <p class="text-sm text-gray-500">{{ __('BMDC') }}: {{ $doctor->bmdc_number }} &middot; {{ $doctor->user->email }}</p>
                        </div>
                        <span @class([
                            'px-2 py-1 text-xs rounded-full font-medium',
                            'bg-green-100 text-green-800' => $doctor->user->status === 'active',
                            'bg-amber-100 text-amber-800' => $doctor->user->status === 'pending',
                            'bg-red-100 text-red-800' => $doctor->user->status === 'rejected',
                        ])>
                            {{ ucfirst($doctor->user->status) }}
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">{{ __('No doctors have registered under this hospital yet. Doctors can select your hospital when they register.') }}</p>
                @endforelse
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">{{ __('Recent Prescriptions') }} ({{ __('by affiliated doctors') }})</h3>

                @forelse ($hospital->prescriptions as $prescription)
                    <div class="flex justify-between items-center border-b last:border-0 py-2 text-sm">
                        <div>
                            <span class="font-mono text-gray-600">{{ $prescription->lookup_code }}</span>
                            &mdash; {{ $prescription->patient_name }}
                        </div>
                        <span class="text-gray-400">{{ $prescription->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">{{ __('No prescriptions yet.') }}</p>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
