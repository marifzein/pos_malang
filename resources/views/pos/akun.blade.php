@extends('layouts.mobile-app')

@section('title', 'Akun Saya')
@section('page_subtitle', 'Pengaturan Profil & Keamanan')

@section('content')
<div class="space-y-4">

    {{-- KARTU PROFIL PENGGUNA --}}
    <div class="bg-gradient-to-br from-indigo-600 to-blue-700 rounded-2xl p-4 text-white shadow-md flex items-center gap-3.5">
        <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-xs flex items-center justify-center text-2xl font-black flex-shrink-0">
            {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
        </div>
        <div class="min-w-0 flex-1">
            <h2 class="text-base font-black leading-tight truncate">{{ Auth::user()->name }}</h2>
            <p class="text-xs text-indigo-100 truncate mt-0.5">{{ Auth::user()->email }}</p>
            <span class="inline-block mt-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/20 uppercase tracking-wider">
                {{ Auth::user()->role ?? 'User' }}
            </span>
        </div>
    </div>

    {{-- GRUP 1: PENGATURAN (Ala BRImo) --}}
    {{-- <div>
        <h3 class="text-xs font-bold text-slate-500 px-1 mb-2">Pengaturan</h3>
        <div class="bg-white rounded-2xl shadow-xs border border-slate-100 divide-y divide-slate-100 overflow-hidden">

            <a href="{{ route('profile.edit') }}" class="flex items-center justify-between px-4 py-3.5 active:bg-slate-50 transition">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i class="ri-user-settings-line text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-700 leading-none">Ubah Data Profil</p>
                        <span class="text-[11px] text-slate-400">Nama lengkap & alamat email</span>
                    </div>
                </div>
                <i class="ri-arrow-right-s-line text-xl text-slate-400"></i>
            </a>

        </div>
    </div> --}}

    {{-- GRUP 2: KEAMANAN (Ala BRImo) --}}
    <div>
        <h3 class="text-xs font-bold text-slate-500 px-1 mb-2">Keamanan</h3>
        <div class="bg-white rounded-2xl shadow-xs border border-slate-100 divide-y divide-slate-100 overflow-hidden">

            <a href="{{ route('password.change') }}" class="flex items-center justify-between px-4 py-3.5 active:bg-slate-50 transition">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <i class="ri-lock-password-line text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-700 leading-none">Ubah Password</p>
                        <span class="text-[11px] text-slate-400">Perbarui kata sandi akun Anda</span>
                    </div>
                </div>
                <i class="ri-arrow-right-s-line text-xl text-slate-400"></i>
            </a>

        </div>
    </div>

    {{-- TOMBOL LOG OUT --}}
    <div class="pt-2">
        <form id="mobile-logout-form" action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="button" onclick="confirmLogout()"
                class="w-full bg-white border border-rose-200 text-rose-600 active:bg-rose-50 font-bold text-sm py-3.5 rounded-2xl shadow-xs flex items-center justify-center gap-2 active:scale-[0.99] transition">
                <i class="ri-logout-box-r-line text-lg"></i>
                <span>Keluar (Log Out)</span>
            </button>
        </form>
    </div>

    <p class="text-center text-[11px] text-slate-400 pt-2">
        TATAKAS Mobile &bull; v1.0
    </p>

</div>
@endsection

@push('scripts')
<script>
    function confirmLogout() {
        Swal.fire({
            title: 'Keluar dari Aplikasi?',
            text: 'Pastikan shift kerja Anda sudah ditutup jika sudah selesai bertugas.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Keluar',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('mobile-logout-form').submit();
            }
        });
    }
</script>
@endpush