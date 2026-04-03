@extends('layouts.app')
@section('title', 'Edit Ticket')

@section('content')
<div class="max-w-lg mx-auto bg-white rounded shadow p-6">
    <div class="flex justify-between items-start mb-4">
        <h1 class="text-xl font-bold">Edit Ticket #{{ $ticket->id }}</h1>
        <a href="{{ route('tickets.index') }}" class="bg-gray-200 text-gray-700 px-3 py-1 rounded text-sm hover:bg-gray-300">&larr; Back to list</a>
    </div>

    <form method="POST" action="{{ route('tickets.update', $ticket) }}">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
            <input type="text" name="title" id="title" value="{{ old('title', $ticket->title) }}" required
                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-4">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" id="description" rows="4" required
                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $ticket->description) }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                <select name="priority" id="priority"
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(['low', 'medium', 'high', 'critical'] as $p)
                        <option value="{{ $p }}" {{ old('priority', $ticket->priority) === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <select name="category" id="category"
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(['bug', 'feature', 'improvement', 'support'] as $c)
                        <option value="{{ $c }}" {{ old('category', $ticket->category) === $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mb-4">
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select name="status" id="status"
                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @foreach(['open', 'in_progress', 'resolved', 'closed'] as $s)
                    <option value="{{ $s }}" {{ old('status', $ticket->status) === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label for="due_date" class="block text-sm font-medium text-gray-700 mb-1">Due Date (optional)</label>
            <input type="date" name="due_date" id="due_date" value="{{ old('due_date', $ticket->due_date?->toDateString()) }}"
                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">Update Ticket</button>
            <a href="{{ route('tickets.show', $ticket) }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm hover:bg-gray-300">Cancel</a>
        </div>
    </form>
</div>
@endsection
