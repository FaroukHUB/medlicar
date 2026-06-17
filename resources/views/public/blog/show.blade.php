@extends('public.layout')
@section('title', ($post->meta_title ?: $post->title).' — '.$agency->name)

@section('content')
<article class="max-w-3xl mx-auto px-4 py-10">
    <a href="{{ route('public.blog') }}" class="text-sm text-gray-500 hover:underline">← Tous les articles</a>

    @if($post->category)<div class="mt-4 text-sm font-semibold text-brand-primary">{{ $post->category }}</div>@endif
    <h1 class="mt-1 text-3xl font-extrabold text-gray-900">{{ $post->title }}</h1>
    <div class="mt-2 text-sm text-gray-400">
        {{ optional($post->published_at)->translatedFormat('d F Y') }} · {{ $post->views_count }} vues
    </div>

    @if($post->featured_image)
        <img src="{{ \Illuminate\Support\Facades\Storage::url($post->featured_image) }}" class="mt-6 w-full rounded-2xl object-cover">
    @endif

    <div class="prose mt-6 max-w-none text-gray-700 leading-relaxed">
        {!! $post->content !!}
    </div>

    @if(!empty($post->tags))
        <div class="mt-6 flex flex-wrap gap-2">
            @foreach($post->tags as $tag)
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-600">#{{ $tag }}</span>
            @endforeach
        </div>
    @endif

    @if($related->isNotEmpty())
        <div class="mt-12 border-t pt-8">
            <h2 class="text-xl font-bold text-gray-900">À lire aussi</h2>
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach($related as $r)
                    <a href="{{ route('public.blog.show', $r->slug) }}" class="group rounded-2xl bg-white ring-1 ring-gray-100 shadow-sm overflow-hidden hover:shadow-md transition">
                        <div class="aspect-[16/10] bg-gray-100 overflow-hidden">
                            @if($r->featured_image)<img src="{{ \Illuminate\Support\Facades\Storage::url($r->featured_image) }}" class="h-full w-full object-cover group-hover:scale-105 transition">@endif
                        </div>
                        <div class="p-3"><h3 class="text-sm font-semibold text-gray-900">{{ $r->title }}</h3></div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</article>
@endsection
