@php
    $pieces = [
        'wK' => '♔', 'wQ' => '♕', 'wR' => '♖', 'wB' => '♗', 'wN' => '♘', 'wP' => '♙',
        'bK' => '♚', 'bQ' => '♛', 'bR' => '♜', 'bB' => '♝', 'bN' => '♞', 'bP' => '♟',
    ];

    $whiteName = $game->whitePlayer?->name ?? 'Putih';
    $blackName = $game->blackPlayer?->name ?? 'Hitam';

    $flipped = $myColor === 'b';
    $order = $flipped ? range(63, 0, -1) : range(0, 63);

    $lastMove = end($moves) ?: null;
    if ($replayAt !== null && isset($moves[$replayAt - 1])) {
        $lastMove = $moves[$replayAt - 1];
    }
    $fromSq = $lastMove ? \App\Support\ChessEngine::squareIndex($lastMove['from']) : null;
    $toSq = $lastMove ? \App\Support\ChessEngine::squareIndex($lastMove['to']) : null;
@endphp

<div x-data="{ showHistory: @js($showHistory) }" class="mx-auto max-w-md space-y-4 font-sans antialiased" wire:poll.1.5s>
    
    {{-- Alert --}}
    @if (session('success'))
        <x-ui.alert type="success" icon="check-circle-2">{{ session('success') }}</x-ui.alert>
    @endif


    {{-- Top Navigation & Header --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('resident.chess.index') }}" wire:navigate
            class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-500 hover:text-slate-800 transition-colors">
            <i data-lucide="arrow-left" class="h-4 w-4"></i> Lobi Catur
        </a>
        <button type="button" @click="showHistory = !showHistory"
            :class="showHistory ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
            class="inline-flex items-center gap-1.5 rounded-xl border px-3 py-1.5 text-xs font-semibold shadow-sm transition-all">
            <i data-lucide="history" class="h-4 w-4"></i>
            <span>Histori</span>
        </button>
    </div>

    {{-- Player Card: Black --}}
    <div class="flex items-center justify-between rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-sm transition-all {{ $game->turn === 'b' && $game->isActive() ? 'ring-2 ring-emerald-500/30 border-emerald-500' : '' }}">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-xl text-amber-100 shadow-inner">
                ♚
            </div>
            <div>
                <p class="text-sm font-bold text-slate-800">{{ $blackName }}</p>
                <p class="text-[11px] font-medium text-slate-400">Pemain Hitam</p>
            </div>
        </div>
        @if ($game->turn === 'b' && $game->isActive())
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-600 ring-1 ring-inset ring-emerald-600/20 animate-pulse">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Giliran
            </span>
        @endif
    </div>

    {{-- Game Status Banner --}}
    @if (! $game->isActive())
        <div class="rounded-2xl border border-slate-200 bg-gradient-to-b from-slate-900 to-slate-800 p-4 text-center text-white shadow-md">
            <p class="text-base font-extrabold tracking-wide">
                @if ($game->winner)
                    🏆 {{ $game->winner === 'w' ? $whiteName : $blackName }} Menang!
                @else
                    🤝 Permainan Seri (Remis)
                @endif
            </p>
            <p class="mt-1 text-xs text-slate-300">{{ $game->endReasonLabel() }}</p>
        </div>
    @elseif (! $myColor)
        <div class="flex items-center gap-2 rounded-xl border border-sky-200 bg-sky-50/80 px-3.5 py-2.5 text-xs font-medium text-sky-800 backdrop-blur">
            <i data-lucide="eye" class="h-4 w-4 shrink-0 text-sky-600"></i>
            <span><strong>Mode Penonton:</strong> Menonton {{ $whiteName }} vs {{ $blackName }}.</span>
        </div>
    @elseif ($game->turn !== $myColor)
        <div class="flex items-center justify-center gap-2 rounded-xl border border-amber-200/60 bg-amber-50/60 px-3.5 py-2.5 text-xs font-medium text-amber-800">
            <i data-lucide="clock" class="h-4 w-4 animate-spin text-amber-600"></i>
            <span>Menunggu langkah lawan...</span>
        </div>
    @else
        <div class="flex items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2.5 text-xs font-semibold text-emerald-800">
            <i data-lucide="sparkles" class="h-4 w-4 text-emerald-600"></i>
            <span>Giliran Anda ({{ $myColor === 'w' ? 'Putih' : 'Hitam' }})</span>
        </div>
    @endif

    {{-- Check Indicator --}}
    @if ($inCheck && $game->isActive())
        <div class="flex items-center justify-center gap-2 rounded-xl border border-red-200 bg-red-500 px-3 py-2 text-xs font-bold text-white shadow-lg shadow-red-500/20 animate-bounce">
            <i data-lucide="alert-triangle" class="h-4 w-4"></i>
            <span>SKAK! Raja {{ $game->turn === 'w' ? 'Putih' : 'Hitam' }} terancam!</span>
        </div>
    @endif

    {{-- Chessboard Wrapper --}}
    <div class="relative mx-auto w-full overflow-hidden rounded-2xl border-4 border-slate-900 bg-slate-900 shadow-2xl shadow-slate-900/40">
        <div class="grid grid-cols-8">
            @foreach ($order as $index)
                @php
                    $piece = $board[$index] ?? null;
                    $rank = intdiv($index, 8);
                    $file = $index % 8;
                    $light = ($rank + $file) % 2 === 0;
                    $isTarget = in_array($index, $targets);
                    $isSelected = $selected === $index;
                    $isLastMove = $lastMove && ($index === $fromSq || $index === $toSq);
                @endphp
                
                <button type="button" wire:click="tapSquare({{ $index }})"
                    class="relative flex aspect-square items-center justify-center overflow-hidden transition-all duration-100 select-none
                        {{ $light ? 'bg-[#F0D9B5]' : 'bg-[#B58863]' }}
                        {{ $isLastMove ? 'bg-amber-300/80 !important' : '' }}
                        {{ $isSelected ? 'bg-sky-400/90 ring-2 ring-inset ring-sky-600 !important' : '' }}
                        {{ $isTarget && $piece ? 'ring-4 ring-inset ring-red-500/80' : '' }}">
                    
                    {{-- Bidak --}}
                    @if ($piece)
                        <span class="relative z-10 text-[32px] sm:text-[36px] leading-none transition-transform duration-100 active:scale-110"
                            style="font-weight:900; -webkit-text-stroke: 1.2px {{ str_starts_with((string)$piece, 'w') ? '#334155' : '#000' }}; paint-order: stroke fill; {{ str_starts_with((string)$piece, 'w') ? 'color:#FFFFFF; filter: drop-shadow(0 2px 2px rgba(0,0,0,0.3));' : 'color:#1E293B; filter: drop-shadow(0 1px 1px rgba(255,255,255,0.2));' }}">
                            {{ $pieces[$piece] }}
                        </span>
                    @endif

                    {{-- Target Indikator (Kotak Kosong) --}}
                    @if ($isTarget && ! $piece)
                        <span class="z-10 h-3.5 w-3.5 rounded-full bg-slate-800/40 ring-2 ring-white/50"></span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    {{-- Player Card: White --}}
    <div class="flex items-center justify-between rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-sm transition-all {{ $game->turn === 'w' && $game->isActive() ? 'ring-2 ring-emerald-500/30 border-emerald-500' : '' }}">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-xl text-slate-800 shadow-sm">
                ♔
            </div>
            <div>
                <p class="text-sm font-bold text-slate-800">{{ $whiteName }}</p>
                <p class="text-[11px] font-medium text-slate-400">Pemain Putih</p>
            </div>
        </div>
        @if ($game->turn === 'w' && $game->isActive())
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-600 ring-1 ring-inset ring-emerald-600/20 animate-pulse">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Giliran
            </span>
        @endif
    </div>

    {{-- Replay Banner --}}
    @if ($replayAt !== null)
        <div class="flex items-center justify-between rounded-xl bg-amber-500/10 border border-amber-500/30 px-3.5 py-2 text-xs font-semibold text-amber-800">
            <span class="flex items-center gap-1.5">
                <i data-lucide="eye" class="h-4 w-4 text-amber-600"></i>
                Melihat langkah {{ $replayAt }} dari {{ count($moves) }}
            </span>
            <span class="text-[10px] uppercase font-bold text-amber-600">Papan Dikunci</span>
        </div>
    @endif

    {{-- History Drawer --}}
    <div x-show="showHistory" x-cloak x-transition class="space-y-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-600">Histori Langkah ({{ count($moves) }})</p>
            <span class="text-[11px] text-slate-400">
                @if ($replayAt !== null)
                    Langkah {{ $replayAt }}/{{ count($moves) }}
                @else
                    Live
                @endif
            </span>
        </div>

        {{-- Control Buttons --}}
        <div class="grid grid-cols-5 gap-1.5">
            <button type="button" wire:click="replayStart" wire:loading.attr="disabled"
                class="flex items-center justify-center rounded-xl border border-slate-200 bg-slate-50 py-2 text-slate-600 hover:bg-slate-100 active:scale-95 transition-all">
                <i data-lucide="skip-back" class="h-4 w-4"></i>
            </button>
            <button type="button" wire:click="replayPrev" wire:loading.attr="disabled"
                class="flex items-center justify-center rounded-xl border border-slate-200 bg-slate-50 py-2 text-slate-600 hover:bg-slate-100 active:scale-95 transition-all">
                <i data-lucide="chevron-left" class="h-4 w-4"></i>
            </button>
            
            <div class="col-span-1">
                @if ($replayAt !== null)
                    <button type="button" wire:click="replayLive" wire:loading.attr="disabled"
                        class="flex w-full items-center justify-center gap-1 rounded-xl bg-emerald-600 py-2 text-xs font-bold text-white shadow-md shadow-emerald-600/20 hover:bg-emerald-700 active:scale-95 transition-all">
                        <i data-lucide="play" class="h-3.5 w-3.5"></i> Live
                    </button>
                @else
                    <button type="button" wire:click="replayStart" wire:loading.attr="disabled"
                        class="flex w-full items-center justify-center gap-1 rounded-xl bg-slate-800 py-2 text-xs font-bold text-white shadow-md hover:bg-slate-900 active:scale-95 transition-all">
                        <i data-lucide="rotate-ccw" class="h-3.5 w-3.5"></i> Review
                    </button>
                @endif
            </div>

            <button type="button" wire:click="replayNext" wire:loading.attr="disabled"
                class="flex items-center justify-center rounded-xl border border-slate-200 bg-slate-50 py-2 text-slate-600 hover:bg-slate-100 active:scale-95 transition-all">
                <i data-lucide="chevron-right" class="h-4 w-4"></i>
            </button>
            <button type="button" wire:click="replayLive" wire:loading.attr="disabled"
                class="flex items-center justify-center rounded-xl border border-slate-200 bg-slate-50 py-2 text-slate-600 hover:bg-slate-100 active:scale-95 transition-all">
                <i data-lucide="skip-forward" class="h-4 w-4"></i>
            </button>
        </div>

        {{-- Move List --}}
        <div class="max-h-52 overflow-y-auto rounded-xl border border-slate-100 bg-slate-50/50 p-1.5 text-xs space-y-1 divide-y divide-slate-100">
            @forelse ($moves as $i => $move)
                @php
                    $num = $i + 1;
                    $isWhite = $move['color'] === 'w';
                    $active = $replayAt !== null ? ($replayAt === $num) : ($num === count($moves));
                @endphp
                <button type="button" wire:click="replayJump({{ $num }})"
                    class="flex w-full items-center justify-between rounded-lg px-2.5 py-1.5 text-left transition-all {{ $active ? 'bg-amber-100/80 font-bold text-amber-900 ring-1 ring-amber-300' : 'hover:bg-white text-slate-700' }}">
                    <div class="flex items-center gap-2">
                        <span class="w-5 text-[11px] text-slate-400 font-mono">{{ $num }}.</span>
                        <span class="inline-flex h-2 w-2 rounded-full {{ $isWhite ? 'bg-slate-300 ring-1 ring-slate-400' : 'bg-slate-800' }}"></span>
                        <span class="font-mono">{{ $move['from'] }}</span>
                        <i data-lucide="arrow-right" class="h-3 w-3 text-slate-400"></i>
                        <span class="font-mono">{{ $move['to'] }}</span>
                    </div>
                    @if ($move['capture'])
                        <span class="flex items-center gap-1 text-[10px] font-bold text-red-500 bg-red-50 px-1.5 py-0.5 rounded">
                            <i data-lucide="swords" class="h-3 w-3"></i> Makan
                        </span>
                    @endif
                </button>
            @empty
                <p class="py-4 text-center text-xs text-slate-400">Belum ada langkah yang dicatat.</p>
            @endforelse
        </div>
    </div>

    {{-- Actions & Footer --}}
    <div class="space-y-3 pt-1">
        @if ($game->isActive() && $myColor)
            <button wire:click="resign" wire:confirm="Yakin ingin menyerah?" wire:loading.attr="disabled"
                class="flex min-h-[44px] w-full items-center justify-center gap-2 rounded-xl border border-red-200 bg-white font-semibold text-red-600 shadow-sm hover:bg-red-50 hover:border-red-300 active:scale-[0.99] transition-all">
                <i data-lucide="flag" class="h-4 w-4"></i> Menyerah
            </button>
        @endif

        <div class="flex items-center justify-center gap-2 text-[11px] font-medium text-slate-400">
            <span>Langkah ke-{{ $game->fullmove }}</span>
            <span>•</span>
            <span class="flex items-center gap-1">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-ping"></span> Sync Otomatis
            </span>
        </div>
    </div>
</div>
