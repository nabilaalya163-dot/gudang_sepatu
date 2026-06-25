<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}
require __DIR__ . '/database.php';

$pageTitle = 'Dashboard';

/* 1) TOTAL STOCK */
$totalStock = (int) $pdo->query(
    "SELECT COALESCE(SUM(stok), 0) AS total FROM sepatu WHERE status_sepatu = 'aktif'"
)->fetchColumn();

/* 2) INBOUND */
$inboundThisMonth = (int) $pdo->query(
    "SELECT COALESCE(SUM(dbm.jumlah_masuk), 0)
     FROM detail_barang_masuk dbm
     JOIN barang_masuk bm ON bm.id_masuk = dbm.id_masuk
     WHERE bm.status_masuk = 'diterima'
       AND MONTH(bm.tanggal_masuk) = MONTH(CURDATE())
       AND YEAR(bm.tanggal_masuk) = YEAR(CURDATE())"
)->fetchColumn();

$inboundAllTime = (int) $pdo->query(
    "SELECT COALESCE(SUM(dbm.jumlah_masuk), 0)
     FROM detail_barang_masuk dbm
     JOIN barang_masuk bm ON bm.id_masuk = dbm.id_masuk
     WHERE bm.status_masuk = 'diterima'"
)->fetchColumn();
$inboundDisplay = $inboundThisMonth > 0 ? $inboundThisMonth : $inboundAllTime;

/* 3) OUTBOUND */
$outboundThisMonth = (int) $pdo->query(
    "SELECT COALESCE(SUM(dbk.jumlah_keluar), 0)
     FROM detail_barang_keluar dbk
     JOIN barang_keluar bk ON bk.id_keluar = dbk.id_keluar
     WHERE bk.status_keluar = 'selesai'
       AND MONTH(bk.tanggal_keluar) = MONTH(CURDATE())
       AND YEAR(bk.tanggal_keluar) = YEAR(CURDATE())"
)->fetchColumn();

$outboundAllTime = (int) $pdo->query(
    "SELECT COALESCE(SUM(dbk.jumlah_keluar), 0)
     FROM detail_barang_keluar dbk
     JOIN barang_keluar bk ON bk.id_keluar = dbk.id_keluar
     WHERE bk.status_keluar = 'selesai'"
)->fetchColumn();
$outboundDisplay = $outboundThisMonth > 0 ? $outboundThisMonth : $outboundAllTime;

/* 4) SUPPLIERS */
$totalSuppliers = (int) $pdo->query("SELECT COUNT(*) FROM supplier")->fetchColumn();

/* 5) RECENT LOGS */
$recentLogs = $pdo->query("
    (
        SELECT
            'in' AS jenis,
            bm.id_masuk AS ref_id,
            bm.tanggal_masuk AS tanggal,
            bm.created_at AS waktu,
            s.nama_seri,
            s.nama_brand,
            sup.nama_supplier AS pihak,
            bm.status_masuk AS status
        FROM barang_masuk bm
        JOIN detail_barang_masuk dbm ON dbm.id_masuk = bm.id_masuk
        JOIN sepatu s ON s.id_sepatu = dbm.id_sepatu
        JOIN supplier sup ON sup.id_supplier = bm.id_supplier
    )
    UNION ALL
    (
        SELECT
            'out' AS jenis,
            bk.id_keluar AS ref_id,
            bk.tanggal_keluar AS tanggal,
            bk.created_at AS waktu,
            s.nama_seri,
            s.nama_brand,
            bk.nama_toko_tujuan AS pihak,
            bk.status_keluar AS status
        FROM barang_keluar bk
        JOIN detail_barang_keluar dbk ON dbk.id_keluar = bk.id_keluar
        JOIN sepatu s ON s.id_sepatu = dbk.id_sepatu
    )
    ORDER BY tanggal DESC, waktu DESC
    LIMIT 6
")->fetchAll();

/* 6) CATEGORIES */
$categories = $pdo->query("
    SELECT kategori, SUM(stok) AS total_stok
    FROM sepatu
    WHERE status_sepatu = 'aktif'
    GROUP BY kategori
    ORDER BY total_stok DESC
")->fetchAll();

$maxCategoryStock = 1;
foreach ($categories as $c) {
    if ($c['total_stok'] > $maxCategoryStock) {
        $maxCategoryStock = $c['total_stok'];
    }
}

/* 7) TEAM */
$team = $pdo->query("
    SELECT nama_user, posisi
    FROM user
    WHERE status_user = 'aktif'
    ORDER BY
        CASE posisi
            WHEN 'Admin' THEN 1
            WHEN 'Kepala Gudang' THEN 2
            ELSE 3
        END,
        nama_user
")->fetchAll();

include __DIR__ . '/header.php';
?>

<div class="topbar">
    <div>
        <div class="topbar-eyebrow">Final Project</div>
        <div class="topbar-title">Sistem Inventaris Gudang Sepatu</div>
    </div>
    <div class="topbar-meta">
        <div class="meta-item">Dosen Pengampu<strong>Dr. Anindya Aprilianti Pravitasari, S.Si., M.Si.</strong></div>
        <div class="meta-item">Mata Kuliah<strong>Basis Data</strong></div>
    </div>
</div>

<div class="stat-grid">
    <div class="glass-card stat-card">
        <div class="stat-icon">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 17v-2c0-1 .5-2 2-3l4-2.5c1-.6 2-1 3.5-1H17a3 3 0 0 1 3 3v1.5c0 .8.4 1.2 1 1.5l1 .5v3a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z"/></svg>
        </div>
        <div class="stat-label">Total Stock</div>
        <div class="stat-value"><?= number_format($totalStock, 0, ',', '.') ?></div>
        <div class="stat-delta flat">Unit sepatu aktif</div>
    </div>

    <div class="glass-card stat-card">
        <div class="stat-icon">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 3v12m0 0-4-4m4 4 4-4"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
        </div>
        <div class="stat-label">Inbound</div>
        <div class="stat-value">+<?= number_format($inboundDisplay, 0, ',', '.') ?></div>
        <div class="stat-delta up">Barang masuk diterima</div>
    </div>

    <div class="glass-card stat-card">
        <div class="stat-icon">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 21V9m0 0 4 4m-4-4-4 4"/><path d="M4 7V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v2"/></svg>
        </div>
        <div class="stat-label">Outbound</div>
        <div class="stat-value"><?= number_format($outboundDisplay, 0, ',', '.') ?></div>
        <div class="stat-delta down">Barang keluar selesai</div>
    </div>

    <div class="glass-card stat-card">
        <div class="stat-icon">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 21V8l9-5 9 5v13"/><path d="M9 21v-7h6v7"/></svg>
        </div>
        <div class="stat-label">Suppliers</div>
        <div class="stat-value"><?= number_format($totalSuppliers, 0, ',', '.') ?></div>
        <div class="stat-delta flat">Mitra terdaftar</div>
    </div>
</div>

<div class="content-grid">
    <div class="col-main">
        <div class="glass-card panel">
            <div class="panel-head">
                <div class="panel-title">Aktivitas Terbaru</div>
                <a href="barang_masuk.php" class="panel-link">View All</a>
            </div>
            <?php if (empty($recentLogs)): ?>
                <p style="color: var(--ink-soft); font-size: 14px;">Belum ada aktivitas tercatat.</p>
            <?php else: ?>
                <?php foreach ($recentLogs as $log): ?>
                    <?php
                        $isIn = $log['jenis'] === 'in';
                        $waktuLabel = date('H:i', strtotime($log['waktu']));
                        $judul = $log['nama_seri'] . ' — ' . $log['nama_brand'];
                    ?>
                    <div class="log-item">
                        <div class="log-time"><?= $waktuLabel ?></div>
                        <div class="log-dot <?= $isIn ? 'in' : 'out' ?>"></div>
                        <div class="log-body">
                            <div class="log-title"><?= htmlspecialchars($judul) ?> <?= $isIn ? 'Masuk' : 'Keluar' ?></div>
                            <div class="log-sub">
                                <?= $isIn ? 'Supplier' : 'Tujuan' ?>: <?= htmlspecialchars($log['pihak']) ?>
                                &middot; <?= htmlspecialchars($log['ref_id']) ?>
                            </div>
                        </div>
                        <div class="log-badge <?= $isIn ? 'in' : 'out' ?>">
                            <?= $isIn ? 'Masuk' : 'Keluar' ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-side">
        <div class="glass-card panel">
            <div class="panel-head">
                <div class="panel-title">Pengguna Sistem</div>
            </div>
            <div class="artisan-list">
                <?php foreach ($team as $member): ?>
                    <div class="artisan-item">
                        <div class="artisan-avatar"><?= htmlspecialchars(substr($member['nama_user'], 0, 1)) ?></div>
                        <div>
                            <div class="artisan-name"><?= htmlspecialchars($member['nama_user']) ?></div>
                            <div class="artisan-role"><?= htmlspecialchars($member['posisi']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="glass-card panel">
            <div class="panel-head">
                <div class="panel-title">Categories</div>
            </div>
            <?php foreach ($categories as $cat): ?>
                <?php $pct = round(($cat['total_stok'] / $maxCategoryStock) * 100); ?>
                <div class="cat-item">
                    <div class="cat-top">
                        <span class="cat-name"><?= htmlspecialchars($cat['kategori']) ?></span>
                        <span class="cat-count"><?= number_format($cat['total_stok'], 0, ',', '.') ?></span>
                    </div>
                    <div class="cat-bar-track">
                        <div class="cat-bar-fill" style="width: <?= $pct ?>%;"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
