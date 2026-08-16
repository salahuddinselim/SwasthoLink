@auth
<div x-data="{ open: false }" class="relative">
    <button @click="open = ! open" @click.outside="open = false" class="relative p-2 text-gray-500 hover:text-gray-800" aria-label="{{ __('Notifications') }}">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
        </svg>
        @if ($unreadCount > 0)
            <span class="absolute top-0.5 right-0.5 min-w-[16px] h-4 px-1 flex items-center justify-center rounded-full bg-red-600 text-white text-[10px] font-semibold leading-none">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div x-show="open" x-cloak x-transition
         class="absolute right-0 mt-2 w-80 max-h-96 overflow-y-auto bg-white shadow-lg rounded-md ring-1 ring-black ring-opacity-5 z-50">
        <div class="flex items-center justify-between px-4 py-3 border-b">
            <span class="font-semibold text-sm">{{ __('Notifications') }}</span>
            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="text-xs text-brand-700 hover:text-brand-900 underline">{{ __('Mark all read') }}</button>
                </form>
            @endif
        </div>

        @forelse ($notifications as $notification)
            <form method="POST" action="{{ route('notifications.read', $notification) }}" class="block border-b last:border-0">
                @csrf
                <button type="submit" class="w-full text-left px-4 py-3 hover:bg-gray-50 {{ $notification->read_at ? '' : 'bg-brand-50' }}">
                    <p class="text-sm font-medium text-gray-800">{{ $notification->title }}</p>
                    @if ($notification->body)
                        <p class="text-xs text-gray-500 mt-0.5">{{ $notification->body }}</p>
                    @endif
                    <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                </button>
            </form>
        @empty
            <p class="px-4 py-6 text-sm text-gray-500 text-center">{{ __('No notifications yet.') }}</p>
        @endforelse

        @if ($notifications->isNotEmpty())
            <a href="{{ route('notifications.index') }}" class="block text-center py-2 text-sm text-brand-700 hover:text-brand-900 underline">{{ __('View all') }}</a>
        @endif
    </div>
</div>
@endauth
