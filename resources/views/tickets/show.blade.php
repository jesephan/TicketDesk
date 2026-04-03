@extends('layouts.app')
@section('title', $ticket->title)

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded shadow p-6">
    <div class="flex justify-between items-start mb-4">
        <h1 class="text-xl font-bold">{{ $ticket->title }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('tickets.edit', $ticket) }}" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">Edit</a>
            <a href="{{ route('tickets.index') }}" class="bg-gray-200 text-gray-700 px-3 py-1 rounded text-sm hover:bg-gray-300">&larr; Back to list</a>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
        <div>
            <span class="text-gray-500">Status:</span>
            <span class="ml-1 font-medium">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
        </div>
        <div>
            <span class="text-gray-500">Priority:</span>
            <span class="ml-1 font-medium">{{ ucfirst($ticket->priority) }}</span>
        </div>
        <div>
            <span class="text-gray-500">Category:</span>
            <span class="ml-1 font-medium">{{ ucfirst($ticket->category) }}</span>
        </div>
        <div>
            <span class="text-gray-500">Escalated:</span>
            <span class="ml-1 font-medium {{ $ticket->escalated ? 'text-red-600' : '' }}">
                {{ $ticket->escalated ? 'Yes' : 'No' }}
            </span>
        </div>
        <div>
            <span class="text-gray-500">Submitted by:</span>
            <span class="ml-1 font-medium">{{ $ticket->user->name }}</span>
        </div>
        <div>
            <span class="text-gray-500">Due date:</span>
            <span class="ml-1 font-medium">{{ $ticket->due_date?->toDateString() ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="mb-4">
        <h2 class="text-sm font-semibold text-gray-500 mb-1">Description</h2>
        <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ $ticket->description }}</p>
    </div>

    @if($ticket->ai_summary)
    <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded">
        <h2 class="text-sm font-semibold text-blue-700 mb-1">AI Summary</h2>
        <p class="text-sm text-blue-900">{{ $ticket->ai_summary }}</p>
    </div>
    @endif

    @if($ticket->ai_suggested_action)
    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded">
        <h2 class="text-sm font-semibold text-green-700 mb-1">Suggested Next Action</h2>
        <p class="text-sm text-green-900">{{ $ticket->ai_suggested_action }}</p>
    </div>
    @endif

    <div class="text-xs text-gray-400 mt-4">
        Created: {{ $ticket->created_at->toDayDateTimeString() }} |
        Updated: {{ $ticket->updated_at->toDayDateTimeString() }}
    </div>

</div>
@endsection
