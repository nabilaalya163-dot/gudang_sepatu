<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: login.php');
    exit;
}
require __DIR__ . '/database.php';

$pageTitle = 'Supplier';
$msg = '';

// Hapus
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    try {
        $pdo->beginTransaction();
        $st = $pdo->prepare("SELECT id_masuk FROM barang_masuk WHERE id_supplier = ?");
        $st->execute([$id]);
        $ids = $st->fetchAll(PDO::FETCH_COLUMN);
        if ($ids) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $pdo->prepare("DELETE FROM detail_barang_masuk WHERE id_masuk IN ($placeholders)")->execute($ids);
            $pdo->prepare("DELETE FROM barang_masuk WHERE id_supplier = ?")->execute([$id]);
        }
        $st = $pdo->prepare("DELETE FROM supplier WHERE id_supplier = ?");
        $st->execute([$id]);
        $pdo->commit();
        $msg = '<div class="alert alert-success">Supplier berhasil dihapus.</div>';
    } catch (Exception $e) {
        $pdo->rollBack();
        $msg = '<div class="alert alert-error">Gagal menghapus: ' . $e->getMessage() . '</div>';
    }
}

// Simpan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id    = $_POST['id_supplier'] ?? '';
    $nama  = $_POST['nama_supplier'] ?? '';
    $email = $_POST['email'] ?? '';
    $edit  = $_POST['edit'] ?? '';

    try {
        if ($edit) {
            $st = $pdo->prepare("UPDATE supplier SET nama_supplier=?, email=? WHERE id_supplier=?");
            $st->execute([$nama, $email, $edit]);
            $msg = '<div class="alert alert-success">Supplier berhasil diperbarui.</div>';
        } else {
            $st = $pdo->prepare("INSERT INTO supplier (id_supplier, nama_supplier, email) VALUES (?,?,?)");
            $st->execute([$id, $nama, $email]);
            $msg = '<div class="alert alert-success">Supplier berhasil ditambahkan.</div>';
        }
    } catch (Exception $e) {
        $msg = '<div class="alert alert-error">Gagal menyimpan: ' . $e->getMessage() . '</div>';
    }
}

$rows = $pdo->query("SELECT * FROM supplier ORDER BY id_supplier")->fetchAll();

include __DIR__ . '/header.php';
?>

<div class="topbar">
    <div>
        <div class="topbar-eyebrow">Final Project</div>
        <div class="topbar-title">Data Supplier</div>
    </div>
    <img src="image_supplier.png" class="topbar-img">
</div>

<?= $msg ?>

<div class="glass-card panel">
    <div class="table-toolbar">
        <div class="panel-title" style="margin:0">Daftar Supplier</div>
        <button class="btn btn-primary" onclick="openModal('modalSupplier')">+ Tambah Supplier</button>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Nama Supplier</th><th>Email</th><th>Tanggal Daftar</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($r['id_supplier']) ?></strong></td>
                    <td><?= htmlspecialchars($r['nama_supplier']) ?></td>
                    <td><?= htmlspecialchars($r['email'] ?? '-') ?></td>
                    <td><?= $r['created_at'] ?></td>
                    <td>
                        <div class="action-group">
                            <button class="btn btn-sm btn-edit" onclick="editSupplier(<?= htmlspecialchars(json_encode($r)) ?>)">Edit</button>
                            <a href="?hapus=<?= $r['id_supplier'] ?>" class="btn btn-sm btn-delete" onclick="return confirm('Yakin hapus supplier ini?')">Hapus</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($rows)): ?>
                <tr><td colspan="5" style="text-align:center;color:var(--ink-soft);padding:30px">Belum ada data supplier.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="modalSupplier">
    <div class="modal">
        <div class="modal-head">
            <div class="modal-title" id="modalSupplierTitle">Tambah Supplier</div>
            <button class="modal-close" onclick="closeModal('modalSupplier')">&times;</button>
        </div>
        <form method="post">
            <input type="hidden" name="edit" id="edit_supplier" value="">
            <div class="form-group">
                <label>ID Supplier</label>
                <input type="text" name="id_supplier" id="f_id_supplier" class="form-control" required maxlength="6" placeholder="SUPxxx">
            </div>
            <div class="form-group">
                <label>Nama Supplier</label>
                <input type="text" name="nama_supplier" id="f_nama_supplier" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" id="f_email" class="form-control">
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalSupplier')">Batal</button>
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

function editSupplier(r) {
    document.getElementById('edit_supplier').value = r.id_supplier;
    document.getElementById('f_id_supplier').value = r.id_supplier;
    document.getElementById('f_id_supplier').readOnly = true;
    document.getElementById('f_nama_supplier').value = r.nama_supplier;
    document.getElementById('f_email').value = r.email || '';
    document.getElementById('modalSupplierTitle').textContent = 'Edit Supplier';
    openModal('modalSupplier');
}

document.getElementById('modalSupplier').addEventListener('click', function(e) {
    if (e.target === this) closeModal('modalSupplier');
});
</script>

<?php include __DIR__ . '/footer.php'; ?>
