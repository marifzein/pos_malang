@extends(\App\Helpers\DeviceHelper::isMobile() ? 'layouts.mobile-app' : 'layouts.app')

@section('title', 'Tutup Shift Kasir')

@section('page_title', 'TUTUP SHIFT')
@section('page_subtitle', 'Finalisasi laporan harian')

@section('content')
<div class="max-w-4xl mx-auto p-0 md:p-8 md:px-4 pb-24">

    <!-- Header (Hanya tampil di Desktop) -->
    <div class="mb-8 border-b border-slate-200 pb-5 hidden md:block">
        <h1 class="text-3xl font-bold text-slate-800">Finalisasi & Tutup Shift</h1>
        <p class="text-sm text-slate-500 mt-1">Lakukan hitung ulang uang fisik di laci kasir secara teliti sebelum menutup sesi kerja.</p>
    </div>

    <!-- Grid: Stacking di HP, 3 kolom di PC -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
        
        <!-- KIRI: RINGKASAN DATA SISTEM (2 Kolom) -->
        <div class="md:col-span-2 space-y-4 md:space-y-6">
            
            <!-- Info General -->
            <div class="bg-white rounded-xl shadow-xs border border-slate-100 p-5 md:p-6">
                <h3 class="text-sm md:text-base font-bold text-slate-700 mb-4 flex items-center gap-2">
                    <i class="ri-user-received-2-line text-indigo-600 text-lg"></i>
                    Informasi Sesi Shift
                </h3>
                <div class="grid grid-cols-2 gap-x-3 gap-y-3 text-xs md:text-sm">
                    <div>
                        <span class="text-slate-400 block">Operator Kasir</span>
                        <strong class="text-slate-700 text-base">{{ auth()->user()->name }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Waktu Mulai (Opened At)</span>
                        <strong class="text-slate-700">{{ $activeShift->opened_at->format('d M Y - H:i') }}</strong>
                    </div>
                </div>
            </div>

            <!-- Rincian Kas / Keuangan -->
            <div class="bg-white rounded-xl shadow-xs border border-slate-100 p-5 md:p-6">
                <h3 class="text-sm md:text-base font-bold text-slate-700 mb-4 flex items-center gap-2">
                    <i class="ri-bar-chart-box-line text-indigo-600 text-lg"></i>
                    Kalkulasi Saldo Laci (Sistem)
                </h3>
                
                <div class="space-y-3 text-xs md:text-sm">
                    <div class="flex justify-between items-center pb-2.5 border-b border-slate-100">
                        <span class="text-slate-500">1. Uang Modal Awal (Starting Cash)</span>
                        <span class="font-semibold text-slate-800">Rp {{ number_format($activeShift->starting_cash, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-2.5 border-b border-slate-100">
                        <span class="text-slate-500">2. (+) Penjualan Tunai Bersih (Cash Sales)</span>
                        <span class="font-semibold text-emerald-600">+ Rp {{ number_format($netCashSales, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-2.5 border-b border-slate-100">
                        <span class="text-slate-500">3. (-) Biaya Operasional Laci (Expense)</span>
                        <span class="font-semibold text-rose-600">- Rp {{ number_format($activeShift->operational_expense, 0, ',', '.') }}</span>
                    </div>
                    
                    <!-- Grand Total Ekspektasi -->
                    <div class="flex justify-between items-center pt-3 bg-indigo-50/50 p-4 rounded-xl border border-indigo-100/70 mt-4">
                        <div>
                            <span class="text-xs font-bold text-indigo-800 block uppercase tracking-wider">Total Ekspektasi Saldo</span>
                            <span class="text-xs text-indigo-500">(Uang yang Seharusnya di Laci)</span>
                        </div>
                        <span class="text-lg md:text-xl font-extrabold text-indigo-700" id="expectedCashRaw" data-value="{{ $expectedCash }}">
                            Rp {{ number_format($expectedCash, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- KANAN: INPUT UANG FISIK AKTUAL (1 Kolom, ikut scroll normal di HP) -->
        <div class="bg-white rounded-xl shadow-xs md:shadow-md border border-slate-200/80 p-5 md:p-6 h-fit md:sticky md:top-6">
            <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
                <i class="ri-money-dollar-box-line text-indigo-600 text-lg"></i>
                Verifikasi Uang Fisik
            </h3>

            <form action="{{ route('pos.store-close') }}" method="POST" id="formCloseShift">
                @csrf
                
                <!-- Input Uang Aktual -->
                <div class="mb-5">
                    <label for="ending_cash_actual" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                        Total Uang Fisik Nyata
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <span class="text-slate-400 font-semibold text-sm">Rp</span>
                        </div>
                        <input 
                            type="number" 
                            name="ending_cash_actual" 
                            id="ending_cash_actual" 
                            class="block w-full pl-12 pr-4 py-4 md:py-3 border-2 border-indigo-200 md:border-slate-300 rounded-xl bg-indigo-50/30 md:bg-slate-50 text-slate-800 font-black focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-xl md:text-lg text-center md:text-left"
                            placeholder="0"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <!-- Live Perhitungan Selisih (JS-Driven) -->
                <div id="varianceBox" class="mb-4 md:mb-5 p-3.5 rounded-xl hidden text-xs md:text-sm border">
                    <div class="flex justify-between items-center">
                        <span class="font-medium">Selisih (Variance):</span>
                        <span id="varianceText" class="font-bold text-base">Rp 0</span>
                    </div>
                </div>

                <!-- Input Alasan Selisih (Muncul dinamis jika ada selisih) -->
                <div class="mb-4 md:mb-5 hidden" id="reasonGroup">
                    <label for="variance_reason" class="block text-xs font-bold uppercase tracking-wider text-amber-800 mb-2">
                        <i class="ri-alert-line mr-1"></i> Alasan Selisih Kas
                    </label>
                    <textarea 
                        name="variance_reason" 
                        id="variance_reason" 
                        rows="3" 
                        class="block w-full px-3 py-2 border border-amber-300 rounded-xl bg-amber-50/30 text-slate-700 text-sm focus:bg-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none transition placeholder:text-slate-400"
                        placeholder="Wajib diisi! Jelaskan kenapa uang laci bisa kurang/lebih..."
                    ></textarea>
                </div>

                <!-- Tombol Submit Akhir -->
                <button 
                    type="submit" 
                    class="w-full bg-slate-800 hover:bg-slate-900 active:scale-[0.99] text-white font-bold py-3.5 px-4 rounded-xl shadow-lg transition duration-200 text-sm uppercase tracking-wider flex items-center justify-center gap-2"
                >
                    <i class="ri-lock-2-line text-base"></i>
                    <span>Kunci & Tutup Shift</span>
                </button>
            </form>
        </div>

    </div>
</div>

<!-- JAVASCRIPT LIVE CALCULATION & VALIDASI -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputActual = document.getElementById('ending_cash_actual');
    const expectedCash = parseFloat(document.getElementById('expectedCashRaw').getAttribute('data-value'));
    const varianceBox = document.getElementById('varianceBox');
    const varianceText = document.getElementById('varianceText');
    const reasonGroup = document.getElementById('reasonGroup');
    const reasonInput = document.getElementById('variance_reason');
    const form = document.getElementById('formCloseShift');

    function formatRupiah(angka) {
        const isMinus = angka < 0 ? '-' : '';
        const absoluteVal = Math.abs(angka);
        return isMinus + 'Rp ' + absoluteVal.toLocaleString('id-ID');
    }

    inputActual.addEventListener('input', function () {
        const actualValue = parseFloat(this.value) || 0;
        const variance = actualValue - expectedCash;

        if (this.value !== '') {
            varianceBox.classList.remove('hidden');
            varianceText.textContent = formatRupiah(variance);

            if (variance === 0) {
                varianceBox.className = "mb-5 p-3.5 rounded-xl text-sm border bg-emerald-50 border-emerald-200 text-emerald-800";
                reasonGroup.classList.add('hidden');
                reasonInput.removeAttribute('required');
            } else if (variance < 0) {
                varianceBox.className = "mb-5 p-3.5 rounded-xl text-sm border bg-rose-50 border-rose-200 text-rose-800";
                reasonGroup.classList.remove('hidden');
                reasonInput.setAttribute('required', 'required');
            } else {
                varianceBox.className = "mb-5 p-3.5 rounded-xl text-sm border bg-amber-50 border-amber-200 text-amber-800";
                reasonGroup.classList.remove('hidden');
                reasonInput.setAttribute('required', 'required');
            }
        } else {
            varianceBox.classList.add('hidden');
            reasonGroup.classList.add('hidden');
            reasonInput.removeAttribute('required');
        }
    });

    form.addEventListener('submit', function (e) {
        const actualValue = parseFloat(inputActual.value) || 0;
        const variance = actualValue - expectedCash;

        if (variance !== 0 && !reasonInput.value.trim()) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Alasan Wajib Diisi',
                text: 'Ada selisih kas sebesar ' + formatRupiah(variance) + '. Harap tuliskan alasannya terlebih dahulu.',
                confirmButtonColor: '#f59e0b'
            });
        }
    });
});
</script>
@endsection