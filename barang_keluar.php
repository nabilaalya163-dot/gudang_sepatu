<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: login.php');
    exit;
}
require __DIR__ . '/database.php';

$pageTitle = 'Barang Keluar';
$msg = '';

// Hapus
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    try {
        $pdo->beginTransaction();
        // Kembalikan stok
        $st = $pdo->prepare("SELECT id_sepatu, jumlah_keluar FROM detail_barang_keluar WHERE id_keluar = ?");
        $st->execute([$id]);
        $items = $st->fetchAll();
        $st2 = $pdo->prepare("UPDATE sepatu SET stok = stok + ? WHERE id_sepatu = ?");
        foreach ($items as $it) {
            $st2->execute([$it['jumlah_keluar'], $it['id_sepatu']]);
        }
        $pdo->prepare("DELETE FROM detail_barang_keluar WHERE id_keluar = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM barang_keluar WHERE id_keluar = ?")->execute([$id]);
        $pdo->commit();
        $msg = '<div class="alert alert-success">Barang keluar berhasil dihapus (stok dikembalikan).</div>';
    } catch (Exception $e) {
        $pdo->rollBack();
        $msg = '<div class="alert alert-error">Gagal menghapus: ' . $e->getMessage() . '</div>';
    }
}

// Simpan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_keluar = $_POST['id_keluar'] ?? '';
    $id_user   = $_POST['id_user'] ?? '';
    $tanggal   = $_POST['tanggal_keluar'] ?? date('Y-m-d');
    $toko      = $_POST['nama_toko_tujuan'] ?? '';
    $id_sepatu = $_POST['id_sepatu'] ?? [];
    $jumlah    = $_POST['jumlah_keluar'] ?? [];
    $harga     = $_POST['harga_satuan_keluar'] ?? [];

    try {
        $pdo->beginTransaction();
        $st = $pdo->prepare("INSERT INTO barang_keluar (id_keluar, id_user, tanggal_keluar, nama_toko_tujuan, status_keluar) VALUES (?,?,?,?,'selesai')");
        $st->execute([$id_keluar, $id_user, $tanggal, $toko]);

        $st2 = $pdo->prepare("INSERT INTO detail_barang_keluar (id_keluar, id_sepatu, jumlah_keluar, harga_satuan_keluar) VALUES (?,?,?,?)");
        $st3 = $pdo->prepare("UPDATE sepatu SET stok = stok - ? WHERE id_sepatu = ? AND stok >= ?");

        foreach ($id_sepatu as $i => $sid) {
            if (!empty($sid) && isset($jumlah[$i]) && $jumlah[$i] > 0) {
                $st2->execute([$id_keluar, $sid, (int)$jumlah[$i], (float)($harga[$i] ?? 0)]);
                $st3->execute([(int)$jumlah[$i], $sid, (int)$jumlah[$i]]);
            }
        }

        $pdo->commit();
        $msg = '<div class="alert alert-success">Barang keluar berhasil dicatat.</div>';
    } catch (Exception $e) {
        $pdo->rollBack();
        $msg = '<div class="alert alert-error">Gagal menyimpan: ' . $e->getMessage() . '</div>';
    }
}

$rows = $pdo->query("
    SELECT * FROM barang_keluar
    ORDER BY created_at DESC
")->fetchAll();

$users = $pdo->query("SELECT * FROM user WHERE status_user='aktif' ORDER BY nama_user")->fetchAll();
$sepatu_list = $pdo->query("SELECT * FROM sepatu WHERE status_sepatu='aktif' ORDER BY nama_seri")->fetchAll();

include __DIR__ . '/header.php';
?>

<div class="topbar">
    <div>
        <div class="topbar-eyebrow">Final Project</div>
        <div class="topbar-title">Barang Keluar</div>
    </div>
    <img src="image_keluar.png" class="topbar-img">
</div>

<?= $msg ?>

<div class="glass-card panel">
    <div class="table-toolbar">
        <div class="panel-title" style="margin:0">Riwayat Barang Keluar</div>
        <button class="btn btn-primary" onclick="openModal('modalKeluar')">+ Tambah Barang Keluar</button>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID Keluar</th><th>User</th><th>Tanggal</th><th>Toko Tujuan</th><th>Status</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                <?php
                    $detail = $pdo->prepare("SELECT dbk.*, s.nama_seri, s.nama_brand FROM detail_barang_keluar dbk JOIN sepatu s ON s.id_sepatu = dbk.id_sepatu WHERE dbk.id_keluar = ?");
                    $detail->execute([$r['id_keluar']]);
                    $items = $detail->fetchAll();
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($r['id_keluar']) ?></strong></td>
                    <td><?php
                        $u = $pdo->prepare("SELECT nama_user FROM user WHERE id_user = ?");
                        $u->execute([$r['id_user']]);
                        $un = $u->fetchColumn();
                        echo htmlspecialchars($un ?: $r['id_user']);
                    ?></td>
                    <td><?= $r['tanggal_keluar'] ?></td>
                    <td><?= htmlspecialchars($r['nama_toko_tujuan']) ?></td>
                    <td><span class="badge badge-<?= $r['status_keluar'] ?>"><?= $r['status_keluar'] ?></span></td>
                    <td>
                        <div class="action-group">
                            <a href="?hapus=<?= $r['id_keluar'] ?>" class="btn btn-sm btn-delete" onclick="return confirm('Yakin hapus barang keluar ini? Stok akan dikembalikan.')">Hapus</a>
                        </div>
                    </td>
                </tr>
                <tr style="background:rgba(193,60,114,0.03)">
                    <td colspan="6" style="padding:6px 14px 12px;font-size:12.5px;color:var(--ink-soft)">
                        <?php foreach ($items as $it): ?>
                            &bull; <?= htmlspecialchars($it['nama_seri']) ?> (<?= htmlspecialchars($it['nama_brand']) ?>) &mdash; <?= $it['jumlah_keluar'] ?> pcs @ Rp <?= number_format($it['harga_satuan_keluar'],0,',','.') ?><br>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($rows)): ?>
                <tr><td colspan="6" style="text-align:center;color:var(--ink-soft);padding:30px">Belum ada transaksi barang keluar.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal-overlay" id="modalKeluar">
    <div class="modal" style="width:640px">
        <div class="modal-head">
            <div class="modal-title">Tambah Barang Keluar</div>
            <button class="modal-close" onclick="closeModal('modalKeluar')">&times;</button>
        </div>
        <form method="post">
            <div class="form-row">
                <div class="form-group">
                    <label>ID Transaksi</label>
                    <input type="text" name="id_keluar" class="form-control" required maxlength="10" placeholder="BK2401xxxx">
                </div>
                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal_keluar" class="form-control" required value="<?= date('Y-m-d') ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>User</label>
                    <select name="id_user" class="form-control" required>
                        <option value="">-- Pilih --</option>
                        <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id_user'] ?>"><?= htmlspecialchars($u['nama_user']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Nama Toko Tujuan</label>
                    <input type="text" name="nama_toko_tujuan" class="form-control" required placeholder="Nama toko">
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
                            <option value="<?= $sl['id_sepatu'] ?>"><?= htmlspecialchars($sl['nama_seri'] . ' — ' . $sl['nama_brand']) ?> (stok: <?= $sl['stok'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jumlah</label>
                        <input type="number" name="jumlah_keluar[]" class="form-control" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Harga Satuan</label>
                        <input type="number" name="harga_satuan_keluar[]" class="form-control" step="0.01" min="0">
                    </div>
                    <div class="form-group" style="display:flex;align-items:flex-end">
                        <button type="button" class="btn btn-sm btn-delete" onclick="this.closest('.detail-row').remove()">X</button>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-secondary" onclick="tambahDetail()" style="margin-bottom:6px">+ Tambah Barang</button>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalKeluar')">Batal</button>
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

document.getElementById('modalKeluar').addEventListener('click', function(e) {
    if (e.target === this) closeModal('modalKeluar');
});
</script>

<?php include __DIR__ . '/footer.php'; ?>
