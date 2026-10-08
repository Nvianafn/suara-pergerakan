<!DOCTYPE html>
<html lang="id">
<head>
@php
  $pageTitle = trim($__env->yieldContent('title'));
  $pageDesc = trim($__env->yieldContent('meta_description')) ?: null;
@endphp
@include('partials.head-meta')
@vite(['resources/css/app.css', 'resources/js/app.js'])
@stack('styles')
@vite('resources/css/public.css')
</head>
<body @class(['public-body', 'home-page' => request()->routeIs('home')])>

@include('partials.nav')

<main>
    @yield('content')
</main>

@include('partials.footer')

@stack('scripts')
</body>
</html>
