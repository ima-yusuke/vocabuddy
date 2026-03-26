<!DOCTYPE html>
<html lang="ja">
<x-head :title="$title">
    @if(isset($description))
        <meta name="description" content="{{ $description }}"/>
    @endif
    @vite(['resources/css/app.css','resources/css/side-menu.css','resources/js/side-menu.js','resources/js/app.js'])
</x-head>
<body id="body">
    <x-side-menu></x-side-menu>
    <x-navigation></x-navigation>
    {{ $slot }}
</body>
</html>
