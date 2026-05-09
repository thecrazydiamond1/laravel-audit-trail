@extends('audit-trail::layout')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-sm text-gray-500 mb-1">Total changes</p>
        <p class="text-3xl font-semibold text-gray-900">{{ number_format($stats['total']) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-sm text-gray-500 mb-1">Today</p>
        <p class="text-3xl font-semibold text-indigo-600">{{ number_format($stats['today']) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-sm text-gray-500 mb-1">Deletions</p>
        <p class="text-3xl font-semibold text-red-500">{{ number_format($stats['deletions']) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-sm text-gray-500 mb-1">Active users today</p>
        <p class="text-3xl font-semibold text-green-600">{{ number_format($stats['active_users']) }}</p>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
    <form method="GET" action="{{ route('audit-trail.index') }}">
        <div class="flex flex-wrap gap-3 items-end">

            <div>
                <label class="block text-xs text-gray-500 mb-1">Model</label>
                <select name="model" class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white">
                    <option value="">All models</option>
                    @foreach($models as $model)
                        <option value="{{ $model }}" {{ request('model') == $model ? 'selected' : '' }}>
                            {{ $model }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs text-gray-500 mb-1">Action</label>
                <select name="action" class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white">
                    <option value="">All actions</option>
                    <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Created</option>
                    <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Updated</option>
                    <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                </select>
            </div>

            <div>
                <label class="block text-xs text-gray-500 mb-1">Model ID</label>
                <input type="text"
                       name="model_id"
                       value="{{ request('model_id') }}"
                       placeholder="e.g. 42"
                       class="text-sm border border-gray-200 rounded-lg px-3 py-2 w-28">
            </div>

            <div>
                <label class="block text-xs text-gray-500 mb-1">From</label>
                <input type="date"
                       name="from"
                       value="{{ request('from') }}"
                       class="text-sm border border-gray-200 rounded-lg px-3 py-2">
            </div>

            <div>
                <label class="block text-xs text-gray-500 mb-1">To</label>
                <input type="date"
                       name="to"
                       value="{{ request('to') }}"
                       class="text-sm border border-gray-200 rounded-lg px-3 py-2">
            </div>

            <button type="submit"
                    class="text-sm bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                Filter
            </button>

            @if(request()->anyFilled(['model','action','model_id','from','to']))
                <a href="{{ route('audit-trail.index') }}"
                   class="text-sm text-gray-500 px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50">
                    Clear
                </a>
            @endif

        </div>
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-200">
                <th class="text-left text-xs font-medium text-gray-500 px-5 py-3">Model</th>
                <th class="text-left text-xs font-medium text-gray-500 px-5 py-3">Action</th>
                <th class="text-left text-xs font-medium text-gray-500 px-5 py-3">Changes</th>
                <th class="text-left text-xs font-medium text-gray-500 px-5 py-3">Changed by</th>
                <th class="text-left text-xs font-medium text-gray-500 px-5 py-3">Time</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($audits as $audit)
                @include('audit-trail::partials.table-row', ['audit' => $audit])
            @empty
                <tr>
                    <td colspan="6" class="text-center text-gray-400 py-16">
                        No audit records found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    @if($audits->hasPages())
        <div class="px-5 py-4 border-t border-gray-200">
            {{ $audits->withQueryString()->links() }}
        </div>
    @endif

</div>

@endsection
