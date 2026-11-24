<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'POS Kasir Mobile')</title>

    <!-- PWA Config -->
    {{-- <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#4f46e5">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="TATAKAS">
    <link rel="apple-touch-icon" href="{{ asset('images/icon-192.png') }}">
    <meta name="mobile-web-app-capable" content="yes"> --}}

    
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        input[type=number] { -moz-appearance: textfield; }
        body { background-color: #f8fafc; font-family: system-ui, -apple-system, sans-serif; }
    </style>
    @stack('styles')
</head>

<body class="bg-slate-50 text-slate-800">

    <!-- KONTEN MASTER LAYOUT UTAMA -->
    <div class="flex flex-col min-h-screen px-3 pt-3 {{ Request::routeIs('pos.mobile') ? 'pb-4' : 'pb-24' }} select-none">
        
        <!-- GLOBAL TOPBAR MINIMALIS (TANPA HAMBURGER) -->
        <div class="flex items-center justify-between bg-white p-3 rounded-2xl shadow-xs border border-slate-100 mb-3">
            <div class="flex items-center gap-2.5">
                @if(!Request::routeIs('dashboard', 'kasir.akun'))
                    <a href="{{ route('dashboard') }}" class="w-9 h-9 bg-slate-100 active:bg-slate-200 rounded-xl flex items-center justify-center text-slate-700 active:scale-95 transition">
                        <i class="ri-arrow-left-line text-xl"></i>
                    </a>
                @else
                    <div class="w-9 h-9 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center">
                        <i class="ri-store-2-fill text-xl"></i>
                    </div>
                @endif

                <div>
                    <h1 class="text-sm font-black text-slate-800 leading-none">@yield('title', 'TATAKAS')</h1>
                    <span class="text-[11px] text-slate-400 font-mono">@yield('page_subtitle', 'Mobile POS')</span>
                </div>
            </div>
        </div>

        <!-- SLOT UTAMA ISI KONTEN HALAMAN -->
        @yield('content')

    </div>

    <!-- ========================================================== -->
    <!-- BOTTOM NAVIGATION BAR (OTOMATIS SEMBUNYI DI HALAMAN POS) -->
    <!-- ========================================================== -->
    @if(!Request::routeIs('pos.mobile'))
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200/80 shadow-[0_-4px_20px_rgba(0,0,0,0.05)] z-40">
        <div class="grid grid-cols-4 h-16 max-w-md mx-auto">

            {{-- 1. Dashboard --}}
            <a href="{{ route('dashboard') }}"
               class="flex flex-col items-center justify-center gap-0.5 transition active:scale-95 {{ Request::routeIs('dashboard') ? 'text-indigo-600' : 'text-slate-400' }}">
                <i class="{{ Request::routeIs('dashboard') ? 'ri-home-5-fill' : 'ri-home-5-line' }} text-xl"></i>
                <span class="text-[10px] font-bold">Home</span>
            </a>

            {{-- 2. POS --}}
            <a href="{{ route('pos.mobile') }}"
               class="flex flex-col items-center justify-center gap-0.5 transition active:scale-95 {{ Request::routeIs('pos.mobile') ? 'text-indigo-600' : 'text-slate-400' }}">
                <i class="ri-shopping-cart-2-line text-xl"></i>
                <span class="text-[10px] font-bold">POS</span>
            </a>

            {{-- 3. Riwayat Transaksi --}}
            <a href="{{ route('transactions.index') }}"
               class="flex flex-col items-center justify-center gap-0.5 transition active:scale-95 {{ Request::is('transactions*') ? 'text-indigo-600' : 'text-slate-400' }}">
                <i class="{{ Request::is('transactions*') ? 'ri-file-list-3-fill' : 'ri-file-list-3-line' }} text-xl"></i>
                <span class="text-[10px] font-bold">Riwayat</span>
            </a>

            {{-- 4. Akun --}}
            <a href="{{ route('pos.akun') }}"
               class="flex flex-col items-center justify-center gap-0.5 transition active:scale-95 {{ Request::routeIs('pos.akun', 'profile.*', 'password.*') ? 'text-indigo-600' : 'text-slate-400' }}">
                <i class="{{ Request::routeIs('pos.akun', 'profile.*', 'password.*') ? 'ri-user-3-fill' : 'ri-user-3-line' }} text-xl"></i>
                <span class="text-[10px] font-bold">Akun</span>
            </a>

        </div>
    </nav>
    @endif

    <!-- Flash Message SweetAlert2 -->
    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Swal.fire({ icon: 'success', title: 'Sukses', text: @json(session('success')), timer: 1500, showConfirmButton: false });
        });
    </script>
    @endif

    @if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Swal.fire({ icon: 'error', title: 'Gagal', text: @json(session('error')) });
        });
    </script>
    @endif

    @stack('scripts')

    {{-- pwa --}}
    {{-- <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('PWA Service Worker aktif:', reg.scope))
                    .catch(err => console.error('PWA gagal:', err));
            });
        }
    </script> --}}
</body>
</html>