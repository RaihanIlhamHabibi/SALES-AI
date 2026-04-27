@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-900">
    <header class="border-b bg-white">
        <div class="mx-auto max-w-6xl px-4 py-3 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('sales-pages.create') }}" class="font-semibold tracking-tight">
                    {{ config('app.name', 'AI Sales Generator') }}
                </a>
                <span class="text-xs text-gray-500 hidden sm:inline">AI Sales Page Generator</span>
            </div>

            <nav class="flex items-center gap-2">
                @auth
                    <a href="{{ route('sales-pages.create') }}" class="px-3 py-2 rounded-md text-sm hover:bg-gray-100">Buat</a>
                    <a href="{{ route('sales-pages.index') }}" class="px-3 py-2 rounded-md text-sm hover:bg-gray-100">History</a>

                    <div class="w-px h-6 bg-gray-200 mx-1"></div>

                    <span class="text-sm text-gray-700 hidden sm:inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="px-3 py-2 rounded-md text-sm hover:bg-gray-100">Logout</button>
                    </form>
                @else
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="px-3 py-2 rounded-md text-sm hover:bg-gray-100">Login</a>
                    @endif
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="px-3 py-2 rounded-md text-sm hover:bg-gray-100">Register</a>
                    @endif
                @endauth
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8">
        @if (session('status'))
            <div class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-900">
                {{ session('status') }}
            </div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>

