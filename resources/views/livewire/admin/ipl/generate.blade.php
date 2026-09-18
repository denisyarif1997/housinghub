<div class="space-y-4">
    @if (session('success'))
        <x-ui.alert type="success" icon="check-circle-2">{{ session('success') }}</x-ui.alert>
    @endif
    @if (session('error'))
        <x-ui.alert type="danger" icon="alert-circle">{{ session('error') }}</x-ui.alert>
    @endif
    @if (session('info'))
        <x-ui.alert type="info" icon="info">{{ session('info') }}</x-ui.alert>
    @endif

    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
        <p class="font-bold">Generate Tagihan IPL</p>
        <p class="mt-1 text-[14px] text-[#64748B]">
            Sistem membuat tagihan untuk semua rumah aktif satu kali per periode. Tagihan yang sudah ada akan dilewati,
            jadi tidak akan ada data ganda.
        </p>

        <form wire:submit="generate" class="mt-4 grid gap-3 md:grid-cols-2">
            <x-ui.field label="Bulan Periode" :error="$errors->first('period_month')">
                <select wire:model.live="period_month" class="min-h-[48px] w-full rounded-xl border border-[#E2E8F0] bg-white px-3 text-[15px]">
                    @foreach (\App\Support\Currency::MONTHS as $number => $label)
                        <option value="{{ $number }}">{{ $label }}</option>
                    @endforeach
                </select>
            </x-ui.field>

            <x-ui.field label="Tahun Periode" :error="$errors->first('period_year')">
                <select wire:model.live="period_year" class="min-h-[48px] w-full rounded-xl border border-[#E2E8F0] bg-white px-3 text-[15px]">
                    @foreach (range(now()->year - 2, now()->year + 1) as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </x-ui.field>

            <x-ui.field label="Perumahan" :error="$errors->first('housing_estate_id')">
                <select wire:model.live="housing_estate_id" class="min-h-[48px] w-full rounded-xl border border-[#E2E8F0] bg-white px-3 text-[15px]">
                    <option value="">Semua Perumahan</option>
                    @foreach ($estates as $estate)
                        <option value="{{ $estate->id }}">{{ $estate->name }}</option>
                    @endforeach
                </select>
            </x-ui.field>

            <x-ui.field label="Tanggal Jatuh Tempo" :error="$errors->first('due_day')">
                <input wire:model.live="due_day" type="number" min="1" max="28" inputmode="numeric"
                    class="min-h-[48px] w-full rounded-xl border border-[#E2E8F0] px-3 text-[15px] outline-none focus:border-[#0F172A]">
            </x-ui.field>

            <div class="md:col-span-2">
                <button type="submit" wire:loading.attr="disabled"
                    class="flex min-h-[48px] w-full items-center justify-center gap-2 rounded-xl bg-[#0F172A] px-4 font-semibold text-white disabled:opacity-60">
                    <i data-lucide="calendar-plus" class="h-5 w-5"></i>
                    <span wire:loading.remove wire:target="generate">Generate Sekarang</span>
                    <span wire:loading wire:target="generate">Memproses...</span>
                </button>
            </div>
        </form>
    </div>

    <div class="grid gap-3 sm:grid-cols-3">
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
            <p class="text-[13px] text-[#64748B]">Rumah Aktif</p>
            <p class="mt-1 text-2xl font-bold">{{ $activeHouses }}</p>
        </div>
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
            <p class="text-[13px] text-[#64748B]">Sudah Ditagih</p>
            <p class="mt-1 text-2xl font-bold">{{ $alreadyBilled }}</p>
        </div>
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
            <p class="text-[13px] text-[#64748B]">Perkiraan Tagihan Baru</p>
            <p class="mt-1 text-2xl font-bold">{{ max(0, $activeHouses - $alreadyBilled) }}</p>
        </div>
    </div>

    @if ($rate)
        <x-ui.alert type="info" icon="tags">
            Tarif berlaku: <span class="font-semibold">{{ $rate->name }}</span> — @rupiah($rate->amount) ({{ $rate->periodLabel() }})
        </x-ui.alert>
    @else
        <x-ui.alert type="warning" icon="alert-triangle">
            Belum ada tarif IPL yang berlaku untuk periode ini.
            <a href="{{ route('admin.ipl.rates.index') }}" wire:navigate class="font-semibold underline">Tambahkan tarif</a> terlebih dahulu.
        </x-ui.alert>
    @endif

    @if ($result)
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
            <p class="font-bold">Hasil Generate</p>
            <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="rounded-xl bg-emerald-50 p-3">
                    <p class="text-[12px] font-semibold text-emerald-700">Dibuat</p>
                    <p class="text-xl font-bold text-emerald-800">{{ $result['created'] }}</p>
                </div>
                <div class="rounded-xl bg-sky-50 p-3">
                    <p class="text-[12px] font-semibold text-sky-700">Dipulihkan</p>
                    <p class="text-xl font-bold text-sky-800">{{ $result['restored'] }}</p>
                </div>
                <div class="rounded-xl bg-amber-50 p-3">
                    <p class="text-[12px] font-semibold text-amber-700">Dilewati</p>
                    <p class="text-xl font-bold text-amber-800">{{ $result['skipped'] }}</p>
                </div>
                <div class="rounded-xl bg-slate-100 p-3">
                    <p class="text-[12px] font-semibold text-slate-700">Tanpa Tarif</p>
                    <p class="text-xl font-bold text-slate-800">{{ $result['no_rate'] }}</p>
                </div>
            </div>
            <p class="mt-3 text-[14px] text-[#64748B]">
                Total nilai tagihan diproses: <span class="font-bold text-[#0F172A]">@rupiah($result['total'])</span>
            </p>
            <a href="{{ route('admin.ipl.billings.index') }}" wire:navigate
                class="mt-4 flex min-h-[44px] items-center justify-center gap-2 rounded-xl border border-[#E2E8F0] font-semibold">
                <i data-lucide="file-text" class="h-4 w-4"></i> Lihat Daftar Tagihan
            </a>
        </div>
    @endif

    <x-ui.alert type="info" icon="shield-check">
        Anti-duplikat aktif: kombinasi <span class="font-semibold">rumah + bulan + tahun</span> bersifat unik di database,
        sehingga generate berulang pada periode yang sama tidak akan membuat tagihan ganda.
    </x-ui.alert>
</div>