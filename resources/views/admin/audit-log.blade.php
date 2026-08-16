<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Audit Log') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="GET" class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1">
                        <x-input-label for="action" :value="__('Action contains')" />
                        <x-text-input id="action" name="action" class="block mt-1 w-full" value="{{ request('action') }}" placeholder="e.g. prescription" list="action-options" />
                        <datalist id="action-options">
                            @foreach ($actions as $action)
                                <option value="{{ $action }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    <div class="flex-1">
                        <x-input-label for="user" :value="__('User name contains')" />
                        <x-text-input id="user" name="user" class="block mt-1 w-full" value="{{ request('user') }}" placeholder="e.g. Farhana" />
                    </div>
                    <div class="flex items-end gap-2">
                        <x-primary-button>{{ __('Filter') }}</x-primary-button>
                        @if (request('action') || request('user'))
                            <a href="{{ route('admin.audit-log') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">{{ __('Clear') }}</a>
                        @endif
                        <a href="{{ route('admin.audit-log.export', request()->query()) }}" class="ml-auto text-sm text-brand-700 hover:text-brand-900 underline">{{ __('Export CSV') }}</a>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left text-gray-500">
                            <tr>
                                <th class="px-4 py-3 font-medium">{{ __('When') }}</th>
                                <th class="px-4 py-3 font-medium">{{ __('User') }}</th>
                                <th class="px-4 py-3 font-medium">{{ __('Action') }}</th>
                                <th class="px-4 py-3 font-medium">{{ __('Target') }}</th>
                                <th class="px-4 py-3 font-medium">{{ __('IP') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($logs as $log)
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-500">{{ $log->created_at->format('d M Y, h:i A') }}</td>
                                    <td class="px-4 py-3">{{ $log->user?->name ?? __('System') }}</td>
                                    <td class="px-4 py-3 font-mono text-xs">{{ $log->action }}</td>
                                    <td class="px-4 py-3 text-gray-500">
                                        @if ($log->target_type)
                                            {{ class_basename($log->target_type) }} #{{ $log->target_id }}
                                        @else
                                            &mdash;
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-400">{{ $log->ip_address }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">{{ __('No matching activity.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{ $logs->links() }}

        </div>
    </div>
</x-app-layout>
