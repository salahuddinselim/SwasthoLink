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

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">{{ __('Total Prescriptions') }}</p>
                    <p class="text-3xl font-semibold text-gray-800">{{ $stats['total_prescriptions'] }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">{{ __('Patients Treated') }}</p>
                    <p class="text-3xl font-semibold text-gray-800">{{ $stats['total_patients'] }}</p>
                </div>
                <a href="{{ route('hospital.shares.index') }}" class="bg-white shadow-sm sm:rounded-lg p-5 hover:shadow-md transition">
                    <p class="text-sm text-gray-500">{{ __('Pending Shares') }}</p>
                    <p class="text-3xl font-semibold text-amber-600">{{ $stats['shares_pending'] }}</p>
                </a>
                <a href="{{ route('hospital.shares.index') }}" class="bg-white shadow-sm sm:rounded-lg p-5 hover:shadow-md transition">
                    <p class="text-sm text-gray-500">{{ __('Completed Shares') }}</p>
                    <p class="text-3xl font-semibold text-green-700">{{ $stats['shares_completed'] }}</p>
                </a>
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
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">{{ __('Patients') }} ({{ __('treated at this hospital') }})</h3>
                    @if ($patients->isNotEmpty())
                        <a href="{{ route('hospital.patients.export') }}" class="text-sm text-brand-700 hover:text-brand-900 underline">{{ __('Export CSV') }}</a>
                    @endif
                </div>

                @forelse ($patients as $patient)
                    <div class="flex justify-between items-center border-b last:border-0 py-3">
                        <div>
                            <p class="font-medium">{{ $patient->patient_name }}</p>
                            <p class="text-sm text-gray-500">
                                {{ $patient->patient_phone }}
                                @if ($patient->patient_email) &middot; {{ $patient->patient_email }} @endif
                                @if ($patient->patient_id) &middot; {{ sprintf('PT-%06d', $patient->patient_id) }} @endif
                            </p>
                        </div>
                        <div class="text-right text-sm text-gray-500">
                            <p>{{ __(':count prescription(s)', ['count' => $patient->prescription_count]) }}</p>
                            <p>{{ __('Last: :date', ['date' => \Illuminate\Support\Carbon::parse($patient->last_prescribed_at)->format('d M Y')]) }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">{{ __('No patients treated here yet.') }}</p>
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
                        <div class="flex items-center gap-3">
                            <span class="text-gray-400">{{ $prescription->created_at->diffForHumans() }}</span>
                            <a href="{{ route('prescriptions.pdf', $prescription) }}" class="text-brand-700 hover:text-brand-900 underline">{{ __('PDF') }}</a>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">{{ __('No prescriptions yet.') }}</p>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
