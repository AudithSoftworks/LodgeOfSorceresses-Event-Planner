<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <title>Lodge of Sorceresses Guild Planner</title>
        <meta name="description" content="Lodge of Sorceresses Guild Planner">
        <meta charset="utf-8">

        <meta name="csrf-token" content="{{ @csrf_token() }}">

        {{-- Viewport metatags --}}
        <meta name="HandheldFriendly" content="true">
        <meta name="MobileOptimized" content="480">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        {{-- iOS webapp metatags --}}
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">

        <link rel="icon" type="image/png" href="/images/favicon.png">
        <link rel="apple-touch-icon" href="/images/touch-icon-iphone.png">
        <link rel="apple-touch-icon" sizes="152x152" href="/images/touch-icon-ipad.png">
        <link rel="apple-touch-icon" sizes="167x167" href="/images/touch-icon-ipad-retina.png">
        <link rel="apple-touch-icon" sizes="180x180" href="/images/touch-icon-iphone-retina.png">
        <link rel="icon" sizes="192x192" href="/images/android-icon.png">

        <script type="text/javascript" src="{{ mix('vendors~main.js', 'build') }}" defer async></script>
        <script type="text/javascript" src="{{ mix('main.js', 'build') }}" defer async></script>
    </head>
    <body id="root" class="theme-default" data-flash-messages="{{ $errors }}" />
</html>
