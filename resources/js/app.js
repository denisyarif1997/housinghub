import { createIcons, icons } from 'lucide';

/**
 * Render semua ikon Lucide (<i data-lucide="...">) pada dokumen.
 * Aman dipanggil berulang kali: elemen yang sudah dikonversi ke SVG
 * akan dibuat ulang dengan hasil yang identik (tidak ada duplikasi ikon).
 */
function renderIcons() {
    try {
        createIcons({ icons, attrs: { 'stroke-width': 1.75 } });
    } catch (error) {
        console.error('[lucide] gagal merender ikon:', error);
    }
}

// Render pertama setelah DOM siap.
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', renderIcons, { once: true });
} else {
    renderIcons();
}

// Dirender ulang setiap kali halaman dimuat via wire:navigate
// (event ini juga terpicu pada load pertama di Livewire 3).
document.addEventListener('livewire:navigated', renderIcons);

// Render ulang ikon setelah setiap pembaruan DOM Livewire
// (baris tabel baru, form edit, hasil filter, dsb).
document.addEventListener('livewire:init', () => {
    Livewire.hook('commit', ({ succeed }) => {
        succeed(() => renderIcons());
    });
});
