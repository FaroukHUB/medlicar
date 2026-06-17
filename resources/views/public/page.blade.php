@extends('public.layout')
@section('title', ($page->meta_title ?: $page->title).' — '.$agency->name)

@section('content')
<div class="max-w-3xl mx-auto px-4 py-12">
    <a href="{{ route('public.home') }}" class="text-sm text-gray-500 hover:underline">← Accueil</a>
    <h1 class="mt-3 text-3xl font-extrabold text-gray-900">{{ $page->title }}</h1>
    <div class="prose mt-6 max-w-none text-gray-700 leading-relaxed">{!! $page->content !!}</div>
</div>
@endsection
