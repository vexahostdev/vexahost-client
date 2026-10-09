<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="user-id" content="{{ auth()->id() }}">

        <title>{{ config('app.name', 'VexaHost Client') }}</title>
        @include('partials.favicon')

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="preload" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
        <noscript><link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet"></noscript>
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@400&display=swap" rel="stylesheet" />
        <script>
            // Inline theme check to prevent flickering (FOUC)
            (function() {
                var theme = localStorage.getItem('theme');
                var isDark = theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches);
                if (isDark) {
                    document.documentElement.classList.add('dark');
                    document.documentElement.style.colorScheme = 'dark';
                } else {
                    document.documentElement.classList.remove('dark');
                    document.documentElement.style.colorScheme = 'light';
                }
            })();
        </script>
        
        <style>
            [x-cloak] { display: none !important; }
            html.dark { color-scheme: dark; background-color: #09090b; }
            html.dark body { background-color: #09090b; color: #fafafa; }
        </style>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    
    <body class="font-sans antialiased overflow-x-hidden bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-50" 
          x-data="appLayout">
        
        <div class="min-h-screen bg-zinc-50 dark:bg-zinc-950">
            
            @if(auth()->user()?->isClient())
                @include('layouts.navigation-client')
            @else
                @include('layouts.navigation-admin')
            @endif
            
            <div class="lg:pl-64 pt-16 min-h-screen flex flex-col w-full">
                
                @isset($header)
                    <div class="bg-white dark:bg-zinc-950 border-b border-zinc-200 dark:border-zinc-800 shadow-sm relative z-20">
                        <div class="max-w-[1720px] mx-auto py-5 px-4 sm:px-8">
                            {{ $header }}
                        </div>
                    </div>
                @endisset

                <main class="flex-1 px-4 sm:px-6 lg:px-8 py-5 pb-8 relative w-full max-w-[1720px] mx-auto">
                    {{ $slot }}
                </main>

            </div>
            
        </div>
        
        <div id="toast-container" class="fixed bottom-5 right-5 z-[60] pointer-events-none flex flex-col items-end w-full sm:max-w-sm"></div>
        @if(auth()->user()?->isStaff())
            <x-quick-snippets-modal />
        @endif
        @stack('scripts')
    </body>
</html>