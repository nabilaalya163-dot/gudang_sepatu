<?php
// Menentukan menu aktif berdasarkan nama file saat ini
$current = basename($_SERVER['PHP_SELF']);
function isActive($file, $current) {
    return $file === $current ? 'active' : '';
}
?>
<aside class="sidebar">
    <div class="brand">
        <img src="image_merk.png" alt="Logo" style="width: 50px; height: 50px; object-fit: contain;">
        <div class="brand-text">
            <div class="brand-title">Solea</div>
            <div class="brand-sub">Gudang Sepatu</div>
        </div>
    </div>

    <div class="nav-label">Menu</div>
    <ul class="nav-menu">
        <li>
            <a href="index.php" class="nav-link <?= isActive('index.php', $current) ?>">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9.5 12 3l9 6.5V21a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/></svg>
                Dashboard
            </a>
        </li>
        <li>
            <a href="sepatu.php" class="nav-link <?= isActive('sepatu.php', $current) ?>">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 17v-2c0-1 .5-2 2-3l4-2.5c1-.6 2-1 3.5-1H17a3 3 0 0 1 3 3v1.5c0 .8.4 1.2 1 1.5l1 .5v3a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z"/><path d="M3 17h18"/><path d="M7 13.5V10"/></svg>
                Sepatu
            </a>
        </li>
        <li>
            <a href="barang_masuk.php" class="nav-link <?= isActive('barang_masuk.php', $current) ?>">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 3v12m0 0-4-4m4 4 4-4"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
                Barang Masuk
            </a>
        </li>
        <li>
            <a href="barang_keluar.php" class="nav-link <?= isActive('barang_keluar.php', $current) ?>">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 21V9m0 0 4 4m-4-4-4 4"/><path d="M4 7V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v2"/></svg>
                Barang Keluar
            </a>
        </li>
        <li>
            <a href="supplier.php" class="nav-link <?= isActive('supplier.php', $current) ?>">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 21V8l9-5 9 5v13"/><path d="M9 21v-7h6v7"/></svg>
                Supplier
            </a>
        </li>
        <li>
            <a href="#" class="nav-link" onclick="document.getElementById('logoutModal').classList.add('open')">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                Logout
            </a>
        </li>
    </ul>

</aside>

<div class="modal-overlay" id="logoutModal">
    <div class="modal">
        <div class="modal-head">
            <div class="modal-title">Konfirmasi Keluar</div>
            <button class="modal-close" onclick="document.getElementById('logoutModal').classList.remove('open')">&times;</button>
        </div>
        <p>Apakah Anda yakin akan keluar?</p>
        <div class="form-actions">
            <button class="btn btn-secondary" onclick="document.getElementById('logoutModal').classList.remove('open')">Tidak</button>
            <a href="logout.php" class="btn btn-delete">Ya, Keluar</a>
        </div>
    </div>
</div>
