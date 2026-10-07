@extends('layouts.admin')
@section('title', 'Preview Konten')
@section('content')
<h1>{{ $content->judul }}</h1>
<p>Preview CMS — {{ $content->status }}</p>
<div>{!! $content->safe_html !!}</div>
@endsection
