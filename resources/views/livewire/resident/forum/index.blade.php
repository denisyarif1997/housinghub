<div class="space-y-4">
    @if (session('success'))
        <x-ui.alert type="success" icon="check-circle-2">{{ session('success') }}</x-ui.alert>
    @endif
    @if (session('error'))
        <x-ui.alert type="danger" icon="alert-circle">{{ session('error') }}</x-ui.alert>
    @endif

    <div class="flex items-center justify-between gap-3">
        <div>
            <h1 class="text-[22px] font-bold">Forum Warga</h1>
            <p class="text-[14px] text-[#64748B]">Diskusi, tanya jawab, dan informasi antar warga</p>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-2">
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-3 text-center">
            <p class="text-xl font-bold text-slate-800">{{ $summary['total'] }}</p>
            <p class="text-[12px] text-[#64748B]">Total</p>
        </div>
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-3 text-center">
            <p class="text-xl font-bold text-sky-700">{{ $summary['mine'] }}</p>
            <p class="text-[12px] text-[#64748B]">Saya</p>
        </div>
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-3 text-center">
            <p class="text-xl font-bold text-emerald-700">{{ $summary['today'] }}</p>
            <p class="text-[12px] text-[#64748B]">Hari ini</p>
        </div>
    </div>

    <button wire:click="openForm" class="flex min-h-[48px] w-full items-center justify-center gap-2 rounded-xl bg-[#0F172A] font-semibold text-white">
        <i data-lucide="plus" class="h-5 w-5"></i> Buat Postingan
    </button>

    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-3">
        <div class="flex flex-col gap-2 sm:flex-row">
            <div class="flex-1">
                <input wire:model.live.debounce.300ms="search" type="search" placeholder="Cari judul atau isi postingan..."
                    class="min-h-[46px] w-full rounded-xl border border-[#E2E8F0] px-3 text-[15px] outline-none focus:border-[#0F172A]">
            </div>
            <select wire:model.live="categoryFilter" class="min-h-[46px] rounded-xl border border-[#E2E8F0] bg-white px-3 text-[15px]">
                <option value="">Semua kategori</option>
                @foreach ($categories as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="space-y-3">
        @forelse ($posts as $post)
            <a href="{{ route('resident.forum.show', $post) }}" wire:navigate class="block rounded-2xl border border-[#E2E8F0] bg-white p-4">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-ui.badge color="{{ $post->categoryColor() }}">{{ $post->categoryLabel() }}</x-ui.badge>
                        @if ($post->is_pinned)
                            <x-ui.badge color="sky"><i data-lucide="pin" class="h-3 w-3"></i> Disematkan</x-ui.badge>
                        @endif
                    </div>
                    <span class="text-[12px] text-[#64748B]">{{ $post->comments_count ?? $post->comments()->count() }} komentar</span>
                </div>

                <h2 class="mt-2 text-[16px] font-bold leading-snug">{{ $post->title }}</h2>
                <p class="mt-1 line-clamp-3 text-[14px] text-[#64748B]">{{ $post->body }}</p>

                <div class="mt-4 border-t border-[#E2E8F0] pt-3">
                    <div class="flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <p class="truncate text-[13px] font-semibold">{{ $post->authorName() }}
                                @if ($house = $post->authorHouseLabel())
                                    <span class="font-normal text-[#64748B]">· {{ $house }}</span>
                                @endif
                            </p>
                            <p class="text-[12px] text-[#64748B]">{{ $post->created_at->format('d/m/Y H:i') }}</p>
                        </div>

                        @if (auth()->user()->can('delete', $post))
                            <button type="button" wire:click.stop="delete({{ $post->id }})" wire:confirm="Hapus postingan ini beserta semua komentarnya?"
                                class="flex h-9 w-9 items-center justify-center rounded-xl border border-red-200 text-red-700">
                                <i data-lucide="trash-2" class="h-4 w-4"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </a>
        @empty
            <x-ui.empty-state icon="messages-square" title="Belum ada postingan" subtitle="Jadilah yang pertama membuka diskusi di forum warga.">
                <button wire:click="openForm" class="flex min-h-[44px] w-full items-center justify-center gap-2 rounded-xl bg-[#0F172A] px-6 font-semibold text-white">
                    <i data-lucide="plus" class="h-4 w-4"></i> Buat Postingan Pertama
                </button>
            </x-ui.empty-state>
        @endforelse
    </div>

    <div>{{ $posts->links() }}</div>

    @if ($showForm)
        <div class="fixed inset-0 z-50 flex items-end justify-center sm:items-center">
            <div wire:click="closeForm" class="absolute inset-0 bg-black/40"></div>
            <div class="relative flex max-h-[92dvh] w-full max-w-lg flex-col overflow-hidden rounded-t-3xl bg-white sm:rounded-3xl">
                <div class="flex shrink-0 items-center justify-between p-5 pb-3">
                    <p class="text-lg font-bold">Postingan Baru</p>
                    <button type="button" wire:click="closeForm" class="flex h-10 w-10 items-center justify-center rounded-xl border border-[#E2E8F0]">
                        <i data-lucide="x" class="h-5 w-5"></i>
                    </button>
                </div>

                <form wire:submit="submit" class="min-h-0 flex-1 space-y-3 overflow-y-auto px-5 pb-8">
                    <x-ui.field label="Judul" :error="$errors->first('title')">
                        <input wire:model="title" placeholder="Judul postingan"
                            class="min-h-[48px] w-full rounded-xl border border-[#E2E8F0] px-3 text-[15px] outline-none focus:border-[#0F172A]">
                    </x-ui.field>

                    <x-ui.field label="Kategori" :error="$errors->first('category')">
                        <select wire:model="category" class="min-h-[48px] w-full rounded-xl border border-[#E2E8F0] bg-white px-3 text-[15px]">
                            @foreach ($categories as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>

                    <x-ui.field label="Isi Postingan" :error="$errors->first('body')">
                        <textarea wire:model="body" rows="6" placeholder="Ceritakan sesuatu yang ingin dibagikan ke warga..."
                            class="w-full rounded-xl border border-[#E2E8F0] px-3 py-2.5 text-[15px] outline-none focus:border-[#0F172A]"></textarea>
                    </x-ui.field>

                    <div class="grid grid-cols-2 gap-2 pb-safe">
                        <button type="button" wire:click="closeForm"
                            class="flex min-h-[48px] items-center justify-center rounded-xl border border-[#E2E8F0] bg-white font-semibold">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="flex min-h-[48px] items-center justify-center gap-2 rounded-xl bg-[#0F172A] font-semibold text-white disabled:opacity-60">
                            <span wire:loading.remove wire:target="submit">Bagikan</span>
                            <span wire:loading wire:target="submit">Mengirim...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
