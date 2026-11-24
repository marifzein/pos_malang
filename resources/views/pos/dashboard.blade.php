@extends('layouts.mobile-app')

@section('title', 'Beranda Kasir')
@section('page_subtitle', 'TATAKAS Mobile')

@section('content')
<div class="space-y-4 pb-10">

    {{-- 1. HERO CARD SAPAAN & STATUS SHIFT (Ala BRImo) --}}
    <div class="bg-gradient-to-br from-indigo-600 to-blue-700 rounded-2xl p-4 text-white shadow-lg">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-xs text-indigo-100">Hai, semoga hari ini pernuh berkah ya!</p>
                <h2 class="text-lg font-black tracking-tight">{{ Auth::user()->name }}</h2>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-white/20 backdrop-blur-xs">
                <i class="ri-user-star-line mr-0.5"></i> KASIR
            </span>
        </div>

        {{-- Shortcut Cepat Utama (POS & Shift) --}}
        <div class="grid grid-cols-3 gap-2 mt-4 pt-3 border-t border-white/15">
            <a href="{{ route('pos.mobile') }}" class="flex flex-col items-center justify-center bg-white/15 active:bg-white/25 rounded-xl py-2.5 transition">
                <i class="ri-shopping-cart-2-fill text-xl mb-1"></i>
                <span class="text-[11px] font-bold">Mulai POS</span>
            </a>
            <a href="{{ route('pos.open-shift') }}" class="flex flex-col items-center justify-center bg-white/15 active:bg-white/25 rounded-xl py-2.5 transition">
                <i class="ri-door-open-fill text-xl mb-1"></i>
                <span class="text-[11px] font-bold">Buka Shift</span>
            </a>
            <a href="{{ route('pos.close-shift') }}" class="flex flex-col items-center justify-center bg-white/15 active:bg-white/25 rounded-xl py-2.5 transition">
                <i class="ri-lock-password-fill text-xl mb-1"></i>
                <span class="text-[11px] font-bold">Tutup Shift</span>
            </a>
        </div>
    </div>

    {{-- 2. GRID MENU SHORTCUT (7 MENU UTAMA) --}}
    <div class="bg-white rounded-2xl p-4 shadow-xs border border-slate-100">
        <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-4">Menu Kasir</h3>

        <div class="grid grid-cols-3 gap-y-5 gap-x-3 text-center">

            {{-- 1. POS --}}
            <a href="{{ route('pos.mobile') }}" class="flex flex-col items-center group active:scale-95 transition">
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-2xs mb-1.5">
                    <i class="ri-computer-line text-2xl"></i>
                </div>
                <span class="text-xs font-bold text-slate-700 leading-tight">POS Kasir</span>
            </a>

            {{-- 2. Open Shift --}}
            <a href="{{ route('pos.open-shift') }}" class="flex flex-col items-center group active:scale-95 transition">
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-2xs mb-1.5">
                    <i class="ri-login-circle-line text-2xl"></i>
                </div>
                <span class="text-xs font-bold text-slate-700 leading-tight">Open Shift</span>
            </a>

            {{-- 3. Tutup Shift --}}
            <a href="{{ route('pos.close-shift') }}" class="flex flex-col items-center group active:scale-95 transition">
                <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center shadow-2xs mb-1.5">
                    <i class="ri-lock-password-line text-2xl"></i>
                </div>
                <span class="text-xs font-bold text-slate-700 leading-tight">Tutup Shift</span>
            </a>

            {{-- 4. Riwayat Transaksi --}}
            <a href="/transactions" class="flex flex-col items-center group active:scale-95 transition">
                <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shadow-2xs mb-1.5">
                    <i class="ri-history-line text-2xl"></i>
                </div>
                <span class="text-xs font-bold text-slate-700 leading-tight">Riwayat Transaksi</span>
            </a>

            {{-- 5. Master Pelanggan --}}
            <a href="{{ route('customers.index') }}" class="flex flex-col items-center group active:scale-95 transition">
                <div class="w-14 h-14 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center shadow-2xs mb-1.5">
                    <i class="ri-user-shared-line text-2xl"></i>
                </div>
                <span class="text-xs font-bold text-slate-700 leading-tight">Pelanggan</span>
            </a>

            {{-- 6. Kartu Stok --}}
            {{-- <a href="{{ route('stock-cards.index') }}" class="flex flex-col items-center group active:scale-95 transition">
                <div class="w-14 h-14 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center shadow-2xs mb-1.5">
                    <i class="ri-file-list-3-line text-2xl"></i>
                </div>
                <span class="text-xs font-bold text-slate-700 leading-tight">Kartu Stok</span>
            </a> --}}

            {{-- 7. Laporan Penjualan Kasir --}}
            <a href="{{ route('laporan.penjualan-kasir') }}" class="flex flex-col items-center group active:scale-95 transition">
                <div class="w-14 h-14 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center shadow-2xs mb-1.5">
                    <i class="ri-pie-chart-line text-2xl"></i>
                </div>
                <span class="text-xs font-bold text-slate-700 leading-tight">Laporan Kasir</span>
            </a>

        </div>
    </div>

</div>
@endsection