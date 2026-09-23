    {{-- Papan Catur (Dengan Koordinat A-H dan 1-8 ala Chess.com) --}}
    @php
        $flipped = $myColor === 'b';
        $order = $flipped ? range(63, 0, -1) : range(0, 63);

        $lastMove = end($moves) ?: null;
        if ($replayAt !== null && isset($moves[$replayAt - 1])) {
            $lastMove = $moves[$replayAt - 1];
        }
        $fromSq = $lastMove ? \App\Support\ChessEngine::squareIndex($lastMove['from']) : null;
        $toSq = $lastMove ? \App\Support\ChessEngine::squareIndex($lastMove['to']) : null;
    @endphp
    
    <div class="mx-auto w-full max-w-md overflow-hidden rounded-2xl border-4 border-[#0F172A] bg-white select-none">
        <div class="grid grid-cols-8">
            @foreach ($order as $index)
                @php
                    $piece = $board[$index] ?? null;
                    $rank = intdiv($index, 8); // 0 = baris ke-8 (top), 7 = baris ke-1 (bottom)
                    $file = $index % 8;        // 0 = kolom 'a' (left), 7 = kolom 'h' (right)
                    $light = ($rank + $file) % 2 === 0;
                    $isTarget = in_array($index, $targets);
                    $isSelected = $selected === $index;
                    $isLastMove = $lastMove && ($index === $fromSq || $index === $toSq);

                    // Konversi koordinat standar catur
                    $fileName = chr(97 + $file); // 'a' - 'h'
                    $rankName = (string) (8 - $rank); // '1' - '8'

                    // Tentukan kapan angka & huruf harus muncul di tepi papan
                    $showRank = $flipped ? ($file === 7) : ($file === 0);
                    $showFile = $flipped ? ($rank === 0) : ($rank === 7);
                @endphp
                <button type="button" wire:click="tapSquare({{ $index }})" style="font-size:32px;line-height:0.85"
                    class="relative flex aspect-square items-center justify-center overflow-hidden
                        {{ $light ? 'bg-[#F0D9B5]' : 'bg-[#B58863]' }}
                        {{ $isLastMove ? 'ring-4 ring-inset ring-amber-400' : '' }}
                        {{ $isSelected ? 'ring-4 ring-inset ring-sky-500' : '' }}
                        {{ $isTarget && $piece ? 'ring-4 ring-inset ring-red-500/70' : '' }}">
                    
                    {{-- Angka (Baris) di pojok kiri atas kotak tepi --}}
                    @if ($showRank)
                        <span class="absolute left-0.5 top-0.5 text-[9px] font-bold opacity-75 pointer-events-none {{ $light ? 'text-[#B58863]' : 'text-[#F0D9B5]' }}">
                            {{ $rankName }}
                        </span>
                    @endif

                    {{-- Huruf (Kolom) di pojok kanan bawah kotak tepi --}}
                    @if ($showFile)
                        <span class="absolute right-0.5 bottom-0.5 text-[9px] font-bold opacity-75 pointer-events-none {{ $light ? 'text-[#B58863]' : 'text-[#F0D9B5]' }}">
                            {{ $fileName }}
                        </span>
                    @endif

                    @if ($piece)
                        <span style="font-weight:900;-webkit-text-stroke:1.5px currentColor;paint-order:stroke;{{ str_starts_with((string) $piece, 'w') ? 'color:#fff;text-shadow:0 0 2px #1e293b,0 1px 2px rgba(30,41,59,.8);' : 'color:#1e293b;text-shadow:0 1px 1px rgba(255,255,255,.4);' }}">
                            {{ $pieces[$piece] }}
                        </span>
                    @endif
                    @if ($isTarget && ! $piece)
                        <span class="absolute h-4 w-4 rounded-full bg-teal-700/30"></span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>
