<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pending Approvals') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @if (session('status'))
                <div class="bg-green-100 text-green-800 px-4 py-3 rounded-md">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Hospitals --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">{{ __('Hospitals / Clinics') }} ({{ $hospitals->count() }})</h3>

                @forelse ($hospitals as $hospital)
                    <div class="border rounded-md p-4 mb-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-medium">{{ $hospital->name }}</p>
                                <p class="text-sm text-gray-600">{{ __('Reg. No.') }}: {{ $hospital->registration_number }}</p>
                                <p class="text-sm text-gray-600">{{ $hospital->address }}</p>
                                <p class="text-sm text-gray-600">{{ __('Contact') }}: {{ $hospital->user->name }} ({{ $hospital->user->email }})</p>
                                @if ($hospital->license_document_path)
                                    <a class="text-sm underline text-indigo-600" target="_blank"
                                       href="{{ route('admin.documents.show', ['path' => $hospital->license_document_path]) }}">
                                        {{ __('View license document') }}
                                    </a>
                                @endif
                            </div>
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('admin.hospitals.approve', $hospital) }}">
                                    @csrf
                                    <x-primary-button>{{ __('Approve') }}</x-primary-button>
                                </form>
                                <form method="POST" action="{{ route('admin.hospitals.reject', $hospital) }}" onsubmit="return collectReason(this)">
                                    @csrf
                                    <input type="hidden" name="reason" class="reason-field">
                                    <x-danger-button>{{ __('Reject') }}</x-danger-button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">{{ __('No pending hospitals.') }}</p>
                @endforelse
            </div>

            {{-- Doctors --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">{{ __('Doctors') }} ({{ $doctors->count() }})</h3>

                @forelse ($doctors as $doctor)
                    <div class="border rounded-md p-4 mb-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-medium">{{ $doctor->user->name }} @if($doctor->specialization) &mdash; {{ $doctor->specialization }} @endif</p>
                                <p class="text-sm text-gray-600">{{ __('BMDC No.') }}: {{ $doctor->bmdc_number }}</p>
                                <p class="text-sm text-gray-600">{{ __('Hospital') }}: {{ $doctor->hospital->name ?? __('Independent') }}</p>
                                <p class="text-sm text-gray-600">{{ __('Email') }}: {{ $doctor->user->email }}</p>
                                <div class="flex gap-3">
                                    @if ($doctor->bmdc_certificate_path)
                                        <a class="text-sm underline text-indigo-600" target="_blank"
                                           href="{{ route('admin.documents.show', ['path' => $doctor->bmdc_certificate_path]) }}">
                                            {{ __('View BMDC certificate') }}
                                        </a>
                                    @endif
                                    @if ($doctor->nid_document_path)
                                        <a class="text-sm underline text-indigo-600" target="_blank"
                                           href="{{ route('admin.documents.show', ['path' => $doctor->nid_document_path]) }}">
                                            {{ __('View NID') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('admin.doctors.approve', $doctor) }}">
                                    @csrf
                                    <x-primary-button>{{ __('Approve') }}</x-primary-button>
                                </form>
                                <form method="POST" action="{{ route('admin.doctors.reject', $doctor) }}" onsubmit="return collectReason(this)">
                                    @csrf
                                    <input type="hidden" name="reason" class="reason-field">
                                    <x-danger-button>{{ __('Reject') }}</x-danger-button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">{{ __('No pending doctors.') }}</p>
                @endforelse
            </div>

            {{-- Pharmacists --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">{{ __('Pharmacists') }} ({{ $pharmacists->count() }})</h3>

                @forelse ($pharmacists as $pharmacist)
                    <div class="border rounded-md p-4 mb-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-medium">{{ $pharmacist->pharmacy_name }}</p>
                                <p class="text-sm text-gray-600">{{ __('License No.') }}: {{ $pharmacist->pharmacy_license_number }}</p>
                                <p class="text-sm text-gray-600">{{ $pharmacist->address }}</p>
                                <p class="text-sm text-gray-600">{{ __('Contact') }}: {{ $pharmacist->user->name }} ({{ $pharmacist->user->email }})</p>
                                @if ($pharmacist->license_document_path)
                                    <a class="text-sm underline text-indigo-600" target="_blank"
                                       href="{{ route('admin.documents.show', ['path' => $pharmacist->license_document_path]) }}">
                                        {{ __('View license document') }}
                                    </a>
                                @endif
                            </div>
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('admin.pharmacists.approve', $pharmacist) }}">
                                    @csrf
                                    <x-primary-button>{{ __('Approve') }}</x-primary-button>
                                </form>
                                <form method="POST" action="{{ route('admin.pharmacists.reject', $pharmacist) }}" onsubmit="return collectReason(this)">
                                    @csrf
                                    <input type="hidden" name="reason" class="reason-field">
                                    <x-danger-button>{{ __('Reject') }}</x-danger-button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">{{ __('No pending pharmacists.') }}</p>
                @endforelse
            </div>

        </div>
    </div>

    <script>
        function collectReason(form) {
            const reason = prompt('Reason for rejection:');
            if (!reason) return false;
            form.querySelector('.reason-field').value = reason;
            return true;
        }
    </script>
</x-app-layout>
