<div class="max-w-7xl mx-auto p-6">
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-lg p-6 mb-6">
        <h1 class="text-3xl font-bold text-white mb-2">Activity Logs</h1>
        <p class="text-indigo-100">A record of the major actions carried out in your account</p>
    </div>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 grid grid-cols-1 md:grid-cols-5 gap-3">
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="Search description or user…"
                class="md:col-span-2 border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-800">
            <select wire:model.live="event" class="border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-800">
                <option value="">All activity</option>
                @foreach ($categories as $key => $label)
                    <option value="{{ $key }}.">{{ $label }}</option>
                @endforeach
            </select>
            <input type="date" wire:model.live="date_from" class="border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-800">
            <div class="flex gap-2">
                <input type="date" wire:model.live="date_to" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-800">
                <button type="button" wire:click="resetFilters" class="text-sm text-gray-600 hover:text-gray-900">Reset</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">When</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-gray-50" x-data="{ open: false }">
                            <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap" title="{{ $log->created_at }}">
                                {{ $log->created_at->diffForHumans() }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $log->actor_label }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div>{{ $log->description }}</div>
                                <div class="text-xs font-mono text-gray-400">{{ $log->event }}</div>
                                @if (! empty($log->properties))
                                    <button type="button" x-on:click="open = !open" class="text-xs text-indigo-600 mt-1">Details</button>
                                    <pre x-show="open" x-cloak class="mt-2 text-xs bg-gray-50 rounded p-2 overflow-x-auto max-w-xl">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $log->ip_address ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500">No activity recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4">{{ $logs->links() }}</div>
    </div>
</div>
