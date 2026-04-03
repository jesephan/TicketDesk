@extends('layouts.app')
@section('title', 'All Tickets')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h1 class="text-xl font-bold">Tickets</h1>
    <a href="{{ route('tickets.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">
        New Ticket
    </a>
</div>

<form method="GET" action="{{ route('tickets.index') }}" class="mb-4 flex gap-3 items-end flex-wrap">
    <div>
        <label class="block text-xs text-gray-500 mb-1">Status</label>
        <select name="status" class="border border-gray-300 rounded px-2 py-1 text-sm">
            <option value="">All</option>
            @foreach(['open', 'in_progress', 'resolved', 'closed'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Category</label>
        <select name="category" class="border border-gray-300 rounded px-2 py-1 text-sm">
            <option value="">All</option>
            @foreach(['bug', 'feature', 'improvement', 'support'] as $c)
                <option value="{{ $c }}" {{ request('category') === $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Priority</label>
        <select name="priority" class="border border-gray-300 rounded px-2 py-1 text-sm">
            <option value="">All</option>
            @foreach(['low', 'medium', 'high', 'critical'] as $p)
                <option value="{{ $p }}" {{ request('priority') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Per Page</label>
        <select name="per_page" class="border border-gray-300 rounded px-2 py-1 text-sm">
            @foreach([10, 15, 25, 50] as $pp)
                <option value="{{ $pp }}" {{ request('per_page', 15) == $pp ? 'selected' : '' }}>{{ $pp }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="bg-gray-800 text-white px-3 py-1 rounded text-sm hover:bg-gray-900">Filter</button>
    <a href="{{ route('tickets.index') }}" class="text-sm text-gray-500 hover:underline">Clear</a>
</form>

<div class="bg-white rounded shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="px-4 py-2 text-left">Title</th>
                <th class="px-4 py-2 text-left">Priority</th>
                <th class="px-4 py-2 text-left">Category</th>
                <th class="px-4 py-2 text-left">Status</th>
                <th class="px-4 py-2 text-left">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tickets as $ticket)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2 font-medium">
                        <a href="{{ route('tickets.show', $ticket) }}" class="text-blue-600 hover:underline">{{ $ticket->title }}</a>
                    </td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-0.5 rounded text-xs font-medium
                            {{ match($ticket->priority) {
                                'critical' => 'bg-red-100 text-red-800',
                                'high' => 'bg-orange-100 text-orange-800',
                                'medium' => 'bg-yellow-100 text-yellow-800',
                                'low' => 'bg-green-100 text-green-800',
                            } }}">
                            {{ ucfirst($ticket->priority) }}
                        </span>
                    </td>
                    <td class="px-4 py-2">{{ ucfirst($ticket->category) }}</td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-0.5 rounded text-xs font-medium
                            {{ match($ticket->status) {
                                'open' => 'bg-blue-100 text-blue-800',
                                'in_progress' => 'bg-purple-100 text-purple-800',
                                'resolved' => 'bg-green-100 text-green-800',
                                'closed' => 'bg-gray-100 text-gray-800',
                            } }}">
                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                        </span>
                    </td>
                    <td class="px-4 py-2 flex gap-2">
                        <a href="{{ route('tickets.show', $ticket) }}" class="text-green-600 hover:underline text-xs">View</a>
                        <a href="{{ route('tickets.edit', $ticket) }}" class="text-blue-600 hover:underline text-xs">Edit</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-4 py-6 text-center text-gray-500">No tickets found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4 flex justify-between items-center">
    <div>
        @if($tickets->previousPageUrl())
            <a href="{{ $tickets->withQueryString()->previousPageUrl() }}" class="bg-gray-200 text-gray-700 px-3 py-1 rounded text-sm hover:bg-gray-300">&larr; Previous</a>
        @endif
    </div>
    <span class="text-xs text-gray-500">Page {{ $tickets->currentPage() }}</span>
    <div>
        @if($tickets->hasMorePages())
            <a href="{{ $tickets->withQueryString()->nextPageUrl() }}" class="bg-gray-200 text-gray-700 px-3 py-1 rounded text-sm hover:bg-gray-300">Next &rarr;</a>
        @endif
    </div>
</div>
@endsection
