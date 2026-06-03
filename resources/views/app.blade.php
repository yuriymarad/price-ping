<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => in_array($appearance ?? 'obsidian-chrome', ['dark', 'midnight-neon', 'obsidian-chrome']), 'midnight-neon' => ($appearance ?? 'obsidian-chrome') === 'midnight-neon', 'obsidian-chrome' => ($appearance ?? 'obsidian-chrome') === 'obsidian-chrome'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "obsidian-chrome" }}';

                if (appearance === 'midnight-neon') {
                    document.documentElement.classList.add('dark', 'midnight-neon');
                } else if (appearance === 'obsidian-chrome') {
                    document.documentElement.classList.add('dark', 'obsidian-chrome');
                } else if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }

            html.midnight-neon {
                background-color: oklch(0.07 0.01 270);
            }

            html.obsidian-chrome {
                background-color: oklch(0.07 0.005 220);
            }
        </style>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
