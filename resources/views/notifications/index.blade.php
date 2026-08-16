<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Notifications') }}
            </h2>
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <x-secondary-button>{{ __('Mark all read') }}</x-secondary-button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                @forelse ($notifications as $notification)
                    <form method="POST" action="{{ route('notifications.read', $notification) }}">
                        @csrf
                        <button type="submit" @class([
                            'w-full text-left border-b last:border-0 p-4 hover:bg-gray-50',
                            'bg-brand-50' => ! $notification->read_at,
                        ])>
                            <div class="flex justify-between items-start">
                                <p class="font-medium text-gray-800">{{ $notification->title }}</p>
                                <span class="text-xs text-gray-400 shrink-0 ml-4">{{ $notification->created_at->diffForHumans() }}</span>
                            </div>
                            @if ($notification->body)
                                <p class="text-sm text-gray-500 mt-1">{{ $notification->body }}</p>
                            @endif
                        </button>
                    </form>
                @empty
                    <p class="p-6 text-center text-gray-500">{{ __('No notifications yet.') }}</p>
                @endforelse
            </div>

            {{ $notifications->links() }}
        </div>
    </div>
</x-app-layout>
