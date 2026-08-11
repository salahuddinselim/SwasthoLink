<x-guest-layout>
    <div class="max-w-xl mx-auto text-center py-4">
        <h1 class="text-2xl font-semibold text-brand-900 mb-2">{{ __('Secure e-prescriptions for Bangladesh') }}</h1>
        <p class="text-gray-600 mb-8">
            {{ __('Doctors write signed prescriptions, patients get a simple lookup code, and pharmacists verify authenticity before dispensing.') }}
        </p>

        @if (Route::has('login'))
            <div class="flex justify-center gap-3 mb-10">
                @auth
                    <a href="{{ url('/dashboard') }}" class="px-5 py-2 bg-brand-600 text-white rounded-md text-sm font-medium hover:bg-brand-700">
                        {{ __('Go to Dashboard') }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-5 py-2 border border-gray-300 rounded-md text-sm font-medium hover:bg-gray-50">
                        {{ __('Log in') }}
                    </a>
                    <a href="{{ route('register') }}" class="px-5 py-2 bg-brand-600 text-white rounded-md text-sm font-medium hover:bg-brand-700">
                        {{ __('Register as Patient') }}
                    </a>
                @endauth
            </div>
        @endif

        @guest
            <div class="text-sm text-gray-500 border-t pt-6">
                {{ __('Are you a healthcare provider?') }}
                <div class="flex justify-center gap-4 mt-2">
                    <a href="{{ route('register.doctor') }}" class="underline text-brand-600">{{ __('Register as Doctor') }}</a>
                    <a href="{{ route('register.hospital') }}" class="underline text-brand-600">{{ __('Register as Hospital/Clinic') }}</a>
                    <a href="{{ route('register.pharmacist') }}" class="underline text-brand-600">{{ __('Register as Pharmacist') }}</a>
                </div>
            </div>
        @endguest
    </div>
</x-guest-layout>
