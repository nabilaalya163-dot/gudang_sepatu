<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: login.php');
    exit;
}
require __DIR__ . '/database.php';

$pageTitle = 'Barang Masuk';
$msg = '';

// Hapus
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    try {
        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM detail_barang_masuk WHERE id_masuk = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM barang_masuk WHERE id_masuk = ?")->execute([$id]);
        $pdo->commit();
        $msg = '<div class="alert alert-success">Barang masuk berhasil dihapus.</div>';
    } catch (Exception $e) {
        $pdo->rollBack();
        $msg = '<div class="alert alert-error">Gagal menghapus: ' . $e->getMessage() . '</div>';
    }
}

// Simpan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_masuk   = $_POST['id_masuk'] ?? '';
    $id_supplier = $_POST['id_supplier'] ?? '';
    $id_user    = $_POST['id_user'] ?? '';
    $tanggal    = $_POST['tanggal_masuk'] ?? date('Y-m-d');
    $id_sepatu  = $_POST['id_sepatu'] ?? [];
    $jumlah     = $_POST['jumlah_masuk'] ?? [];
    $harga      = $_POST['harga_satuan_masuk'] ?? [];

    try {
        $pdo->beginTransaction();
        $st = $pdo->prepare("INSERT INTO barang_masuk (id_masuk, id_supplier, id_user, tanggal_masuk, status_masuk) VALUES (?,?,?,?,'diterima')");
        $st->execute([$id_masuk, $id_supplier, $id_user, $tanggal]);

        $st2 = $pdo->prepare("INSERT INTO detail_barang_masuk (id_masuk, id_sepatu, jumlah_masuk, harga_satuan_masuk) VALUES (?,?,?,?)");
        $st3 = $pdo->prepare("UPDATE sepatu SET stok = stok + ? WHERE id_sepatu = ?");

        foreach ($id_sepatu as $i => $sid) {
            if (!empty($sid) && isset($jumlah[$i]) && $jumlah[$i] > 0) {
                $st2->execute([$id_masuk, $sid, (int)$jumlah[$i], (float)($harga[$i] ?? 0)]);
                $st3->execute([(int)$jumlah[$i], $sid]);
            }
        }

        $pdo->commit();
        $msg = '<div class="alert alert-success">Barang masuk berhasil dicatat.</div>';
    } catch (Exception $e) {
        $pdo->rollBack();
        $msg = '<div class="alert alert-error">Gagal menyimpan: ' . $e->getMessage() . '</div>';
    }
}

$rows = $pdo->query("
    SELECT bm.*, s.nama_supplier
    FROM barang_masuk bm
    JOIN supplier s ON s.id_supplier = bm.id_supplier
    ORDER BY bm.created_at DESC
")->fetchAll();

$suppliers = $pdo->query("SELECT * FROM supplier ORDER BY nama_supplier")->fetchAll();
$users = $pdo->query("SELECT * FROM user WHERE status_user='aktif' ORDER BY nama_user")->fetchAll();
$sepatu_list = $pdo->query("SELECT * FROM sepatu WHERE status_sepatu='aktif' ORDER BY nama_seri")->fetchAll();

include __DIR__ . '/header.php';
?>

<div class="topbar">
    <div>
        <div class="topbar-eyebrow">Final Project</div>
        <div class="topbar-title">Barang Masuk</div>
    </div>
    <img src="image_masuk.png" class="topbar-img">
</div>

<?= $msg ?>

<div class="glass-card panel">
    <div class="table-toolbar">
        <div class="panel-title" style="margin:0">Riwayat Barang Masuk</div>
        <button class="btn btn-primary" onclick="openModal('modalMasuk')">+ Tambah Barang Masuk</button>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID Masuk</th><th>Supplier</th><th>User</th><th>Tanggal</th><th>Status</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                <?php
                    $detail = $pdo->prepare("SELECT dbm.*, s.nama_seri, s.nama_brand FROM detail_barang_masuk dbm JOIN sepatu s ON s.id_sepatu = dbm.id_sepatu WHERE dbm.id_masuk = ?");
                    $detail->execute([$r['id_masuk']]);
                    $items = $detail->fetchAll();
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($r['id_masuk']) ?></strong></td>
                    <td><?= htmlspecialchars($r['nama_supplier']) ?></td>
                    <td><?php
                        $u = $pdo->prepare("SELECT nama_user FROM user WHERE id_user = ?");
                        $u->execute([$r['id_user']]);
                        $un = $u->fetchColumn();
                        echo htmlspecialchars($un ?: $r['id_user']);
                    ?></td>
                    <td><?= $r['tanggal_masuk'] ?></td>
                    <td><span class="badge badge-<?= $r['status_masuk'] ?>"><?= $r['status_masuk'] ?></span></td>
                    <td>
                        <div class="action-group">
                            <a href="?hapus=<?= $r['id_masuk'] ?>" class="btn btn-sm btn-delete" onclick="return confirm('Yakin hapus barang masuk ini?')">Hapus</a>
                        </div>
                    </td>
                </tr>
                <tr style="background:rgba(193,60,114,0.03)">
                    <td colspan="6" style="padding:6px 14px 12px;font-size:12.5px;color:var(--ink-soft)">
                        <?php foreach ($items as $it): ?>
                            &bull; <?= htmlspecialchars($it['nama_seri']) ?> (<?= htmlspecialchars($it['nama_brand']) ?>) &mdash; <?= $it['jumlah_masuk'] ?> pcs @ Rp <?= number_format($it['harga_satuan_masuk'],0,',','.') ?><br>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($rows)): ?>
                <tr><td colspan="6" style="text-align:center;color:var(--ink-soft);padding:30px">Belum ada transaksi barang masuk.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal-overlay" id="modalMasuk">
    <div class="modal" style="width:640px">
        <div class="modal-head">
            <div class="modal-title">Tambah Barang Masuk</div>
            <button class="modal-close" onclick="closeModal('modalMasuk')">&times;</button>
        </div>
        <form method="post">
            <div class="form-row">
                <div class="form-group">
                    <label>ID Transaksi</label>
                    <input type="text" name="id_masuk" class="form-control" required maxlength="10" placeholder="BM2401xxxx">
                </div>
                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal_masuk" class="form-control" required value="<?= date('Y-m-d') ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Supplier</label>
                    <select name="id_supplier" class="form-control" required>
                        <option value="">-- Pilih --</option>
                        <?php foreach ($suppliers as $s): ?>
                        <option value="<?= $s['id_supplier'] ?>"><?= htmlspecialchars($s['nama_supplier']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>User</label>
                    <select name="id_user" class="form-control" required>
                        <option value="">-- Pilih --</option>
                        <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id_user'] ?>"><?= htmlspecialchars($u['nama_user']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="font-size:13px;font-weight:600;color:var(--ink);margin:18px 0 10px">Detail Barang</div>
            <div id="detailContainer">
                <div class="form-row detail-row">
                    <div class="form-group">
                        <label>Sepatu</label>
                        <select name="id_sepatu[]" class="form-control" required>
                            <option value="">-- Pilih --</option>
                            <?php foreach ($sepatu_list as $sl): ?>
                            <option value="<?= $sl['id_sepatu'] ?>"><?= htmlspecialchars($sl['nama_seri'] . ' — ' . $sl['nama_brand']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jumlah</label>
                        <input type="number" name="jumlah_masuk[]" class="form-control" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Harga Satuan</label>
                        <input type="number" name="harga_satuan_masuk[]" class="form-control" step="0.01" min="0">
                    </div>
                    <div class="form-group" style="display:flex;align-items:flex-end">
                        <button type="button" class="btn btn-sm btn-delete" onclick="this.closest('.detail-row').remove()">X</button>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-secondary" onclick="tambahDetail()" style="margin-bottom:6px">+ Tambah Barang</button>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalMasuk')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.add('open');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('open');
}

function tambahDetail() {
    const container = document.getElementById('detailContainer');
    const first = container.querySelector('.detail-row');
    const clone = first.cloneNode(true);
    clone.querySelectorAll('select, input').forEach(el => el.value = '');
    container.appendChild(clone);
}

document.getElementById('modalMasuk').addEventListener('click', function(e) {
    if (e.target === this) closeModal('modalMasuk');
});
</script>

<?php include __DIR__ . '/footer.php'; ?>
