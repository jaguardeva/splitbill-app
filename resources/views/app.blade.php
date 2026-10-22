<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        <!-- OpenGraph & Social Sharing Meta Tags -->
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="SplitBill">
        <meta property="og:title" content="SplitBill - Hitung Patungan Tagihan Cepat & Adil">
        <meta property="og:description" content="Aplikasi kalkulator patungan split bill terbaik di Indonesia. Hitung makanan per item atau bagi rata secara akurat, adil, transparan, dan tanpa ribet!">
        <meta property="og:image" content="{{ asset('apple-touch-icon.png') }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="SplitBill - Hitung Patungan Tagihan Cepat & Adil">
        <meta name="twitter:description" content="Hitung patungan makanan dan tagihan grup dengan adil & transparan.">
        <meta name="twitter:image" content="{{ asset('apple-touch-icon.png') }}">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts'])
        <x-inertia::head>
            <title>{{ config('app.name', 'SplitBill') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
