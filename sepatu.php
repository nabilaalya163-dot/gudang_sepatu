<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: login.php');
    exit;
}
require __DIR__ . '/database.php';

$pageTitle = 'Sepatu';
$msg = '';

// Hapus
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    try {
        $pdo->beginTransaction();
        $st = $pdo->prepare("DELETE FROM detail_barang_keluar WHERE id_sepatu = ?");
        $st->execute([$id]);
        $st = $pdo->prepare("DELETE FROM detail_barang_masuk WHERE id_sepatu = ?");
        $st->execute([$id]);
        $st = $pdo->prepare("DELETE FROM sepatu WHERE id_sepatu = ?");
        $st->execute([$id]);
        $pdo->commit();
        $msg = '<div class="alert alert-success">Sepatu berhasil dihapus.</div>';
    } catch (Exception $e) {
        $pdo->rollBack();
        $msg = '<div class="alert alert-error">Gagal menghapus: ' . $e->getMessage() . '</div>';
    }
}

// Simpan (Tambah / Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = $_POST['id_sepatu'] ?? '';
    $seri   = $_POST['nama_seri'] ?? '';
    $brand  = $_POST['nama_brand'] ?? '';
    $kategori = $_POST['kategori'] ?? '';
    $harga_beli = (float) ($_POST['harga_beli'] ?? 0);
    $harga_jual = (float) ($_POST['harga_jual'] ?? 0);
    $warna  = $_POST['warna'] ?? '';
    $ukuran = (int) ($_POST['ukuran'] ?? 0);
    $stok   = (int) ($_POST['stok'] ?? 0);
    $status = $_POST['status_sepatu'] ?? 'aktif';
    $edit   = $_POST['edit'] ?? '';

    try {
        if ($edit) {
            $st = $pdo->prepare("UPDATE sepatu SET nama_seri=?,nama_brand=?,kategori=?,harga_beli=?,harga_jual=?,warna=?,ukuran=?,stok=?,status_sepatu=? WHERE id_sepatu=?");
            $st->execute([$seri, $brand, $kategori, $harga_beli, $harga_jual, $warna, $ukuran, $stok, $status, $edit]);
            $msg = '<div class="alert alert-success">Sepatu berhasil diperbarui.</div>';
        } else {
            $st = $pdo->prepare("INSERT INTO sepatu (id_sepatu,nama_seri,nama_brand,kategori,harga_beli,harga_jual,warna,ukuran,stok,status_sepatu) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $st->execute([$id, $seri, $brand, $kategori, $harga_beli, $harga_jual, $warna, $ukuran, $stok, $status]);
            $msg = '<div class="alert alert-success">Sepatu berhasil ditambahkan.</div>';
        }
    } catch (Exception $e) {
        $msg = '<div class="alert alert-error">Gagal menyimpan: ' . $e->getMessage() . '</div>';
    }
}

$rows = $pdo->query("SELECT * FROM sepatu ORDER BY id_sepatu")->fetchAll();

include __DIR__ . '/header.php';
?>

<div class="topbar">
    <div>
        <div class="topbar-eyebrow">Final Project</div>
        <div class="topbar-title">Data Sepatu</div>
    </div>
    <img src="image_shoes.png" class="topbar-img">
</div>

<?= $msg ?>

<div class="glass-card panel">
    <div class="table-toolbar">
        <div class="panel-title" style="margin:0">Daftar Sepatu</div>
        <button class="btn btn-primary" onclick="openModal('modalSepatu')">+ Tambah Sepatu</button>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Seri</th><th>Brand</th><th>Kategori</th>
                    <th>Harga Beli</th><th>Harga Jual</th><th>Warna</th>
                    <th>Ukuran</th><th>Stok</th><th>Status</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($r['id_sepatu']) ?></strong></td>
                    <td><?= htmlspecialchars($r['nama_seri']) ?></td>
                    <td><?= htmlspecialchars($r['nama_brand']) ?></td>
                    <td><?= htmlspecialchars($r['kategori']) ?></td>
                    <td>Rp <?= number_format($r['harga_beli'],0,',','.') ?></td>
                    <td>Rp <?= number_format($r['harga_jual'],0,',','.') ?></td>
                    <td><?= htmlspecialchars($r['warna']) ?></td>
                    <td><?= $r['ukuran'] ?></td>
                    <td><?= $r['stok'] ?></td>
                    <td><span class="badge badge-<?= $r['status_sepatu'] ?>"><?= $r['status_sepatu'] ?></span></td>
                    <td>
                        <div class="action-group">
                            <button class="btn btn-sm btn-edit" onclick="editSepatu(<?= htmlspecialchars(json_encode($r)) ?>)">Edit</button>
                            <a href="?hapus=<?= $r['id_sepatu'] ?>" class="btn btn-sm btn-delete" onclick="return confirm('Yakin hapus sepatu ini?')">Hapus</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($rows)): ?>
                <tr><td colspan="11" style="text-align:center;color:var(--ink-soft);padding:30px">Belum ada data sepatu.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah / Edit -->
<div class="modal-overlay" id="modalSepatu">
    <div class="modal">
        <div class="modal-head">
            <div class="modal-title" id="modalSepatuTitle">Tambah Sepatu</div>
            <button class="modal-close" onclick="closeModal('modalSepatu')">&times;</button>
        </div>
        <form method="post">
            <input type="hidden" name="edit" id="edit_sepatu" value="">
            <div class="form-row">
                <div class="form-group">
                    <label>ID Sepatu</label>
                    <input type="text" name="id_sepatu" id="f_id_sepatu" class="form-control" required maxlength="6" placeholder="SEPxxx">
                </div>
                <div class="form-group">
                    <label>Nama Seri</label>
                    <input type="text" name="nama_seri" id="f_nama_seri" class="form-control" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Nama Brand</label>
                    <input type="text" name="nama_brand" id="f_nama_brand" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Kategori</label>
                    <input type="text" name="kategori" id="f_kategori" class="form-control" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Harga Beli</label>
                    <input type="number" name="harga_beli" id="f_harga_beli" class="form-control" step="0.01" min="0">
                </div>
                <div class="form-group">
                    <label>Harga Jual</label>
                    <input type="number" name="harga_jual" id="f_harga_jual" class="form-control" step="0.01" min="0">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Warna</label>
                    <input type="text" name="warna" id="f_warna" class="form-control">
                </div>
                <div class="form-group">
                    <label>Ukuran</label>
                    <input type="number" name="ukuran" id="f_ukuran" class="form-control" min="0">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Stok</label>
                    <input type="number" name="stok" id="f_stok" class="form-control" min="0" value="0">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status_sepatu" id="f_status_sepatu" class="form-control">
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalSepatu')">Batal</button>
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

function editSepatu(r) {
    document.getElementById('edit_sepatu').value = r.id_sepatu;
    document.getElementById('f_id_sepatu').value = r.id_sepatu;
    document.getElementById('f_id_sepatu').readOnly = true;
    document.getElementById('f_nama_seri').value = r.nama_seri;
    document.getElementById('f_nama_brand').value = r.nama_brand;
    document.getElementById('f_kategori').value = r.kategori;
    document.getElementById('f_harga_beli').value = r.harga_beli;
    document.getElementById('f_harga_jual').value = r.harga_jual;
    document.getElementById('f_warna').value = r.warna;
    document.getElementById('f_ukuran').value = r.ukuran;
    document.getElementById('f_stok').value = r.stok;
    document.getElementById('f_status_sepatu').value = r.status_sepatu;
    document.getElementById('modalSepatuTitle').textContent = 'Edit Sepatu';
    openModal('modalSepatu');
}

// Reset form saat modal ditutup
document.getElementById('modalSepatu').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal('modalSepatu');
    }
});
</script>

<?php include __DIR__ . '/footer.php'; ?>
