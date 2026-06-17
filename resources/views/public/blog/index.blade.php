@extends('public.layout')
@section('title', 'Blog — '.$agency->name)

@section('content')
<div class="max-w-6xl mx-auto px-4 py-10">
    <h1 class="text-3xl font-extrabold text-gray-900">Blog & conseils</h1>
    <p class="mt-2 text-gray-500">Actualités, guides et bons plans pour vos locations.</p>

    @if($featured)
        <a href="{{ route('public.blog.show', $featured->slug) }}" class="group mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6 rounded-3xl bg-white ring-1 ring-gray-100 shadow-sm overflow-hidden">
            <div class="aspect-[16/10] bg-gray-100 overflow-hidden">
                @if($featured->featured_image)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($featured->featured_image) }}" class="h-full w-full object-cover group-hover:scale-105 transition">
                @endif
            </div>
            <div class="p-6 flex flex-col justify-center">
                @if($featured->category)<span class="text-xs font-semibold text-brand-primary">{{ $featured->category }}</span>@endif
                <h2 class="mt-1 text-2xl font-extrabold text-gray-900">{{ $featured->title }}</h2>
                <p class="mt-2 text-gray-600">{{ $featured->excerpt }}</p>
                <span class="mt-4 text-sm font-semibold text-brand-secondary">Lire l'article →</span>
            </div>
        </a>
    @endif

    @if($posts->isEmpty() && ! $featured)
        <p class="mt-10 text-gray-500">Aucun article pour le moment.</p>
    @else
        <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($posts as $post)
                <a href="{{ route('public.blog.show', $post->slug) }}" class="group flex flex-col rounded-2xl bg-white ring-1 ring-gray-100 shadow-sm overflow-hidden hover:shadow-md transition">
                    <div class="aspect-[16/10] bg-gray-100 overflow-hidden">
                        @if($post->featured_image)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($post->featured_image) }}" class="h-full w-full object-cover group-hover:scale-105 transition">
                        @else
                            <div class="flex h-full w-full items-center justify-center text-4xl">📝</div>
                        @endif
                    </div>
                    <div class="p-5 flex flex-1 flex-col">
                        @if($post->category)<span class="text-xs font-semibold text-brand-primary">{{ $post->category }}</span>@endif
                        <h3 class="mt-1 font-bold text-gray-900">{{ $post->title }}</h3>
                        @if($post->excerpt)<p class="mt-2 text-sm text-gray-500 line-clamp-3">{{ $post->excerpt }}</p>@endif
                        <span class="mt-3 text-xs text-gray-400">{{ optional($post->published_at)->translatedFormat('d M Y') }}</span>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-8">{{ $posts->links() }}</div>
    @endif
</div>
@endsection
