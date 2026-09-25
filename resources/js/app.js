import './bootstrap';

// Toggle sidebar di layar kecil
document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-sidebar-toggle]');
    if (!toggle) return;

    document.getElementById('sidebar')?.classList.toggle('-translate-x-full');
    document.getElementById('sidebar-backdrop')?.classList.toggle('hidden');
});

// Tombol tampilkan/sembunyikan password pada form
document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-toggle-password]');
    if (!toggle) return;

    const input = document.getElementById(toggle.dataset.togglePassword);
    if (!input) return;

    const terlihat = input.type === 'text';
    input.type = terlihat ? 'password' : 'text';
    toggle.setAttribute('aria-pressed', String(!terlihat));
    toggle.setAttribute('aria-label', terlihat ? 'Tampilkan password' : 'Sembunyikan password');
});
