@extends('layouts.app')
@section('title', 'Login')

@section('content')
<div class="flex items-center justify-center min-h-[80vh]">
    <div class="w-full max-w-sm bg-white rounded shadow p-6">
        <h1 class="text-xl font-bold mb-4 text-center">Login</h1>

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" id="password" required
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4 flex items-center">
                <input type="checkbox" name="remember" id="remember" class="mr-2">
                <label for="remember" class="text-sm text-gray-600">Remember me</label>
            </div>

            <button type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded text-sm font-medium hover:bg-blue-700">
                Login
            </button>
        </form>
    </div>
</div>
@endsection
