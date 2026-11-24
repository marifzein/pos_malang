{{-- @extends('layouts.mobile-app') --}}
@extends(
    preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', request()->header('User-Agent')) 
    || preg_match('/(ipad|tablet|(android(?!.*mobile)))/i', request()->header('User-Agent')) 
    ? 'layouts.mobile-app' 
    : 'layouts.app'
)

@section('title', 'Ganti Password')

@section('content')

<x-page-header
    title="Ganti Password"
    subtitle="Demi keamanan, perbarui password akun Anda secara berkala"
>
    {{-- <x-slot:action>
        <a href="{{ route('dashboard') }}">
            <x-button color="gray">
                <i class="ri-arrow-left-line"></i>
                Kembali ke Dashboard
            </x-button>
        </a>
    </x-slot:action> --}}
</x-page-header>

{{-- Menampilkan Error Validasi jika ada --}}
@if($errors->any())
<x-alert type="error">
    <div class="font-semibold mb-2">
        Terdapat kesalahan:
    </div>
    <ul class="list-disc ml-5">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</x-alert>
@endif

<x-card>
    <!-- Inisialisasi Alpine.js state untuk memantau status show/hide masing-masing input -->
    <form method="POST" action="{{ route('password.password-update') }}" x-data="{ showOld: false, showNew: false, showConfirm: false }">
        @csrf
        @method('PUT')

        <div class="space-y-6 max-w-xl">
            
            {{-- Input Password Lama --}}
            <div class="relative">
                <x-input
                    label="Password Lama"
                    name="current_password"
                    ::type="showOld ? 'text' : 'password'"
                    icon="ri-lock-unlock-line"
                    placeholder="Masukkan password lama"
                    required
                />
                <!-- Tombol Mata (Eye Toggle) -->
                <button type="button" @click="showOld = !showOld" class="absolute right-4 bottom-2.5 text-slate-400 hover:text-indigo-600 focus:outline-none z-10">
                    <i :class="showOld ? 'ri-eye-off-line text-lg' : 'ri-eye-line text-lg'"></i>
                </button>
            </div>

            {{-- Input Password Baru --}}
            <div class="relative">
                <x-input
                    label="Password Baru  (min. 6 karakter)"
                    name="password"
                    ::type="showNew ? 'text' : 'password'"
                    icon="ri-lock-password-line"
                    placeholder="Masukkan password baru"
                    required
                />
                <!-- Tombol Mata (Eye Toggle) -->
                <button type="button" @click="showNew = !showNew" class="absolute right-4 bottom-2.5 text-slate-400 hover:text-indigo-600 focus:outline-none z-10">
                    <i :class="showNew ? 'ri-eye-off-line text-lg' : 'ri-eye-line text-lg'"></i>
                </button>
            </div>

            {{-- Input Konfirmasi Password Baru --}}
            <div class="relative">
                <x-input
                    label="Konfirmasi Password Baru"
                    name="password_confirmation"
                    ::type="showConfirm ? 'text' : 'password'"
                    icon="ri-lock-password-fill" 
                    placeholder="Ulangi password baru"
                    required
                />
                <!-- Tombol Mata (Eye Toggle) -->
                <button type="button" @click="showConfirm = !showConfirm" class="absolute right-4 bottom-2.5 text-slate-400 hover:text-indigo-600 focus:outline-none z-10">
                    <i :class="showConfirm ? 'ri-eye-off-line text-lg' : 'ri-eye-line text-lg'"></i>
                </button>
            </div>

        </div>

        {{-- Tombol Aksi --}}
        <div class="mt-8 flex justify-end gap-3 border-t border-slate-100 pt-6">
            <a href="{{ route('dashboard') }}">
                <x-button color="gray">
                    <i class="ri-close-line"></i>
                    Batal
                </x-button>
            </a>

            <x-button color="primary" type="submit">
                <i class="ri-save-line"></i>
                Simpan
            </x-button>
        </div>
    </form>
</x-card>

@endsection