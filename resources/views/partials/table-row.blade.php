<tr class="hover:bg-gray-50 transition-colors">

    {{-- Model --}}
    <td class="px-5 py-4">
        <span class="font-mono text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded">
            {{ class_basename($audit->model_type) }} #{{ $audit->model_id }}
        </span>
    </td>

    {{-- Action badge --}}
    <td class="px-5 py-4">
        @if($audit->action === 'created')
            <span class="text-xs font-medium bg-green-100 text-green-700 px-2.5 py-1 rounded-full">
                created
            </span>
        @elseif($audit->action === 'updated')
            <span class="text-xs font-medium bg-amber-100 text-amber-700 px-2.5 py-1 rounded-full">
                updated
            </span>
        @else
            <span class="text-xs font-medium bg-red-100 text-red-700 px-2.5 py-1 rounded-full">
                deleted
            </span>
        @endif
    </td>

    {{-- Changes --}}
    <td class="px-5 py-4 max-w-xs">
        @if($audit->action === 'created' && $audit->new_values)
            @foreach(array_slice($audit->new_values, 0, 2) as $key => $value)
                <div class="text-xs font-mono bg-green-50 text-green-700 px-2 py-1 rounded mb-1">
                    {{ $key }}: "{{ $value }}"
                </div>
            @endforeach
            @if(count($audit->new_values) > 2)
                <div x-data="{ open: false }">
                    <button @click="open = !open"
                            class="text-xs text-indigo-500 hover:text-indigo-700 mt-1">
                        <span x-show="!open">+{{ count($audit->new_values) - 2 }} more fields</span>
                        <span x-show="open">hide fields</span>
                    </button>
                    <div x-show="open" x-transition>
                        @foreach(array_slice($audit->new_values, 2) as $key => $value)
                            <div class="text-xs font-mono bg-green-50 text-green-700 px-2 py-1 rounded mb-1 mt-1">
                                {{ $key }}: "{{ $value }}"
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @elseif($audit->action === 'updated')
            @php
                $updatedKeys = array_keys($audit->old_values ?? []);
                $visibleKeys = array_slice($updatedKeys, 0, 2);
                $hiddenKeys  = array_slice($updatedKeys, 2);
            @endphp

            {{-- Always show first 2 --}}
            @foreach($visibleKeys as $key)
                <div class="text-xs font-mono bg-red-50 text-red-600 px-2 py-1 rounded mb-1">
                    {{ $key }}: "{{ $audit->old_values[$key] }}"
                </div>
                <div class="text-xs font-mono bg-green-50 text-green-700 px-2 py-1 rounded mb-1">
                    {{ $key }}: "{{ $audit->new_values[$key] ?? '' }}"
                </div>
            @endforeach

            {{-- Expandable hidden fields --}}
            @if(count($hiddenKeys) > 0)
                <div x-data="{ open: false }">
                    <button @click="open = !open"
                            class="text-xs text-indigo-500 hover:text-indigo-700 mt-1">
                        <span x-show="!open">+{{ count($hiddenKeys) }} more fields</span>
                        <span x-show="open">hide fields</span>
                    </button>
                    <div x-show="open" x-transition>
                        @foreach($hiddenKeys as $key)
                            <div class="text-xs font-mono bg-red-50 text-red-600 px-2 py-1 rounded mb-1 mt-1">
                                {{ $key }}: "{{ $audit->old_values[$key] }}"
                            </div>
                            <div class="text-xs font-mono bg-green-50 text-green-700 px-2 py-1 rounded mb-1">
                                {{ $key }}: "{{ $audit->new_values[$key] ?? '' }}"
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        @elseif($audit->action === 'deleted' && $audit->old_values)
            @php
                $deletedKeys    = array_keys($audit->old_values);
                $visibleKeys    = array_slice($deletedKeys, 0, 2);
                $hiddenKeys     = array_slice($deletedKeys, 2);
            @endphp

            {{-- Always show first 2 --}}
            @foreach($visibleKeys as $key)
                <div class="text-xs font-mono bg-red-50 text-red-600 px-2 py-1 rounded mb-1">
                    {{ $key }}: "{{ $audit->old_values[$key] }}"
                </div>
            @endforeach

            {{-- Expandable hidden fields --}}
            @if(count($hiddenKeys) > 0)
                <div x-data="{ open: false }">
                    <button @click="open = !open"
                            class="text-xs text-indigo-500 hover:text-indigo-700 mt-1">
                        <span x-show="!open">+{{ count($hiddenKeys) }} more fields</span>
                        <span x-show="open">hide fields</span>
                    </button>
                    <div x-show="open" x-transition>
                        @foreach($hiddenKeys as $key)
                            <div class="text-xs font-mono bg-red-50 text-red-600 px-2 py-1 rounded mb-1 mt-1">
                                {{ $key }}: "{{ $audit->old_values[$key] }}"
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </td>

    {{-- Changed by --}}
    <td class="px-5 py-4">
        @if($audit->changer)
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 text-xs font-medium flex items-center justify-center flex-shrink-0">
                    {{ strtoupper(substr($audit->changer->name, 0, 2)) }}
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-900">{{ $audit->changer->name }}</div>
                    <div class="text-xs text-gray-400">{{ $audit->ip_address }}</div>
                </div>
            </div>
        @else
            <span class="text-xs text-gray-400">System / Guest</span>
        @endif
    </td>

    {{-- Time --}}
    <td class="px-5 py-4">
        <div class="text-sm text-gray-900">{{ $audit->created_at->diffForHumans() }}</div>
        <div class="text-xs text-gray-400">{{ $audit->created_at->format('d M Y, h:i A') }}</div>
    </td>

    {{-- Revert --}}
    <td class="px-5 py-4">
        @if($audit->action !== 'created')
            <form method="POST"
                  action="{{ route('audit-trail.revert', $audit) }}"
                  onsubmit="return confirm('Revert this record to its previous state?')">
                @csrf
                <button type="submit"
                        class="text-xs text-gray-600 border border-gray-200 px-3 py-1.5 rounded-lg hover:bg-gray-50 flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                    </svg>
                    Revert
                </button>
            </form>
        @endif
    </td>

</tr>
