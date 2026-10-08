<!DOCTYPE html>
<html lang="id">
<head>
@php $pageTitle = $title ?? null; @endphp
@include('partials.head-meta')
@vite(['resources/css/app.css', 'resources/js/app.js'])
@stack('styles')
@vite(['resources/css/public.css', 'resources/css/redesign.css'])
</head>
<body class="public-body">

@include('partials.nav')

<main>
    {{ $slot }}
</main>

@include('partials.footer')

@stack('scripts')
</body>
</html>
