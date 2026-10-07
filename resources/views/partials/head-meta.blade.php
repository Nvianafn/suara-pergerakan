@php
  use App\Models\Setting;
  $__site = Setting::get('nama_rayon', 'PMII Rayon Saintek');
  $__t = (isset($pageTitle) && $pageTitle) ? $pageTitle : $__site;
  $__title = $__t . ' · Bergerak, Berpikir, Berkarya';
  $__desc = (isset($pageDesc) && $pageDesc) ? $pageDesc : Setting::get('deskripsi_singkat', 'Website resmi PMII Rayon Saintek: rumah kaderisasi, gagasan, dan karya mahasiswa Sains dan Teknologi yang progresif dan berakhlak.');
   $__img = (isset($ogImage) && $ogImage) ? $ogImage : Setting::imageUrl('og_image', 'og-image.png');
  $__url = url()->current();
@endphp
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $__title }}</title>
<meta name="description" content="{{ $__desc }}">
<meta name="keywords" content="PMII, Rayon Saintek, PMII Saintek, Pergerakan Mahasiswa Islam Indonesia, mahasiswa saintek, kaderisasi, organisasi mahasiswa, sains dan teknologi">
<meta name="robots" content="index, follow, max-image-preview:large">
<meta name="author" content="{{ $__site }}">
<meta name="theme-color" content="#002068">
<link rel="canonical" href="{{ $__url }}">

@if(Setting::get('favicon'))
<link rel="icon" type="image/webp" href="{{ Setting::imageUrl('favicon', 'favicon.ico') }}">
@else
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.ico') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32.png') }}">
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
@endif
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $__site }}">
<meta property="og:locale" content="id_ID">
<meta property="og:title" content="{{ $__title }}">
<meta property="og:description" content="{{ $__desc }}">
<meta property="og:url" content="{{ $__url }}">
<meta property="og:image" content="{{ $__img }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $__title }}">
<meta name="twitter:description" content="{{ $__desc }}">
<meta name="twitter:image" content="{{ $__img }}">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
