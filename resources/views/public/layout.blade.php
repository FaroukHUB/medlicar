<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $agency->name ?? 'Location de voitures')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: {
                dz: { green: '#006233', greendark: '#004d28', red: '#D21034' },
            } } }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>body{font-family:ui-sans-serif,system-ui,-apple-system,'Segoe UI',Roboto,sans-serif}</style>
    @stack('head')
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">
    <header class="bg-dz-green text-white shadow">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ route('public.home') }}" class="flex items-center gap-3">
                @if($agency->logo)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($agency->logo) }}" alt="logo" class="h-9 w-9 rounded object-cover bg-white">
                @endif
                <span class="text-xl font-bold tracking-tight">{{ $agency->name }}</span>
            </a>
            <div class="hidden sm:flex items-center gap-4 text-sm">
                @if($agency->phone)<a href="tel:{{ $agency->phone }}" class="hover:underline">📞 {{ $agency->phone }}</a>@endif
                @if($agency->whatsapp)<a href="https://wa.me/{{ preg_replace('/\D/','',$agency->whatsapp) }}" class="bg-white/15 px-3 py-1.5 rounded-lg hover:bg-white/25">WhatsApp</a>@endif
            </div>
        </div>
    </header>

    <main class="flex-1">@yield('content')</main>

    <footer class="bg-dz-greendark text-white/80 mt-12">
        <div class="max-w-6xl mx-auto px-4 py-8 text-sm flex flex-col sm:flex-row justify-between gap-3">
            <div>
                <div class="font-semibold text-white">{{ $agency->name }}</div>
                @if($agency->address)<div>{{ $agency->address }}{{ $agency->city ? ', '.$agency->city : '' }}</div>@endif
            </div>
            <div class="text-right">
                @if($agency->email)<div>{{ $agency->email }}</div>@endif
                <div class="mt-1">© {{ date('Y') }} {{ $agency->name }}</div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/fr.js"></script>
    @stack('scripts')
</body>
</html>
