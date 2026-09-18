@props(['icon' => 'inbox', 'title' => 'Belum ada data', 'subtitle' => ''])

<div class="rounded-2xl border border-[#E2E8F0] bg-white p-8 text-center">
    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100">
        <i data-lucide="{{ $icon }}" class="h-6 w-6 text-[#64748B]"></i>
    </div>
    <p class="font-semibold">{{ $title }}</p>
    @if ($subtitle)
        <p class="mt-1 text-[14px] text-[#64748B]">{{ $subtitle }}</p>
    @endif
    @if (trim($slot ?? '') !== '')
        <div class="mt-4">{{ $slot }}</div>
    @endif
</div>
