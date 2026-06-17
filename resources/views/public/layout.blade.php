@php
    $metaTitle = $agency->meta_title ?: ($agency->name . ' — Location de voitures');
    $metaDesc = $agency->meta_description ?: ('Louez votre voiture facilement chez ' . $agency->name . '. Réservation en ligne, tarifs clairs.');
    $faviconUrl = $agency->favicon ? \Illuminate\Support\Facades\Storage::url($agency->favicon)
        : ($agency->logo ? \Illuminate\Support\Facades\Storage::url($agency->logo) : null);
    $ogImageUrl = $agency->og_image ? \Illuminate\Support\Facades\Storage::url($agency->og_image)
        : ($agency->cover_image ? \Illuminate\Support\Facades\Storage::url($agency->cover_image) : null);
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO --}}
    <title>@yield('title', $metaTitle)</title>
    <meta name="description" content="{{ $metaDesc }}">
    @if($agency->meta_keywords)<meta name="keywords" content="{{ $agency->meta_keywords }}">@endif
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDesc }}">
    @if($ogImageUrl)<meta property="og:image" content="{{ $ogImageUrl }}">@endif
    <meta name="twitter:card" content="summary_large_image">
    @if($faviconUrl)<link rel="icon" href="{{ $faviconUrl }}">@endif

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: {
                brand: {
                    primary: 'var(--brand-primary)',
                    secondary: 'var(--brand-secondary)',
                },
            } } }
        }
    </script>
    {{-- Thème : 2 couleurs administrables pilotent tout le site --}}
    <style>
        :root {
            --brand-primary: {{ $agency->colorPrimary() }};
            --brand-secondary: {{ $agency->colorSecondary() }};
            --brand-primary-dark: color-mix(in srgb, var(--brand-primary) 82%, #000);
        }
        body{font-family:ui-sans-serif,system-ui,-apple-system,'Segoe UI',Roboto,sans-serif}
        .bg-brand-primary-dark{background-color:var(--brand-primary-dark)}
        .hover\:bg-brand-secondary-dark:hover{filter:brightness(.9)}
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    @stack('head')
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">
    <header class="bg-brand-primary text-white shadow">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ route('public.home') }}" class="flex items-center gap-3">
                @if($agency->logo)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($agency->logo) }}" alt="{{ $agency->name }}" class="h-10 w-auto rounded object-contain bg-white/90 p-1">
                @endif
                <span class="text-xl font-bold tracking-tight">{{ $agency->name }}</span>
            </a>
            <div class="hidden sm:flex items-center gap-4 text-sm">
                @if($agency->section_blog)<a href="{{ route('public.blog') }}" class="hover:underline">Blog</a>@endif
                @if($agency->phone)<a href="tel:{{ $agency->phone }}" class="hover:underline">📞 {{ $agency->phone }}</a>@endif
                @if($agency->whatsapp)<a href="https://wa.me/{{ preg_replace('/\D/','',$agency->whatsapp) }}" class="bg-white/15 px-3 py-1.5 rounded-lg hover:bg-white/25">WhatsApp</a>@endif
            </div>
        </div>
    </header>

    <main class="flex-1">@yield('content')</main>

    <footer class="bg-brand-primary-dark text-white/80 mt-12">
        @if($agency->newsletter_enabled)
            <div class="border-b border-white/10">
                <div class="max-w-6xl mx-auto px-4 py-6 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="text-white font-semibold">📬 Recevez nos offres par email</div>
                    @if(session('newsletter_success'))
                        <div class="text-sm text-white">Merci, vous êtes inscrit ✅</div>
                    @else
                        <form method="POST" action="{{ route('public.newsletter') }}" class="flex gap-2 w-full sm:w-auto">
                            @csrf
                            <input type="email" name="email" required placeholder="Votre email" class="flex-1 sm:w-64 rounded-lg px-3 py-2 text-gray-800">
                            <button class="rounded-lg bg-brand-secondary px-4 py-2 font-semibold text-white hover:bg-brand-secondary-dark">S'inscrire</button>
                        </form>
                    @endif
                </div>
            </div>
        @endif
        @php $footerPages = \App\Models\Page::where('is_published', true)->where('show_in_footer', true)->orderBy('sort_order')->get(['title','slug']); @endphp
        <div class="max-w-6xl mx-auto px-4 py-8 text-sm flex flex-col sm:flex-row justify-between gap-3">
            <div>
                <div class="font-semibold text-white">{{ $agency->name }}</div>
                @if($agency->slogan)<div class="text-white/70">{{ $agency->slogan }}</div>@endif
                @if($agency->address)<div class="mt-1">{{ $agency->address }}{{ $agency->city ? ', '.$agency->city : '' }}</div>@endif
                @if($footerPages->isNotEmpty())
                    <div class="mt-3 flex flex-wrap gap-3">
                        @foreach($footerPages as $p)<a href="{{ route('public.page', $p->slug) }}" class="hover:text-white underline">{{ $p->title }}</a>@endforeach
                    </div>
                @endif
            </div>
            <div class="text-right">
                @if($agency->phone)<div>📞 {{ $agency->phone }}</div>@endif
                @if($agency->email)<div>{{ $agency->email }}</div>@endif
                <div class="mt-2 flex justify-end gap-3">
                    @if($agency->facebook)<a href="{{ $agency->facebook }}" target="_blank" class="hover:text-white">Facebook</a>@endif
                    @if($agency->instagram)<a href="{{ $agency->instagram }}" target="_blank" class="hover:text-white">Instagram</a>@endif
                    @if($agency->tiktok)<a href="{{ $agency->tiktok }}" target="_blank" class="hover:text-white">TikTok</a>@endif
                </div>
                <div class="mt-2">© {{ date('Y') }} {{ $agency->name }}</div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/fr.js"></script>
    @stack('scripts')
</body>
</html>
