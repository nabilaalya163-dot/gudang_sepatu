<?php
session_start();
require __DIR__ . '/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_user = $_POST['nama_user'] ?? '';
    $id_user = $_POST['id_user'] ?? '';

    if (!empty($nama_user) && !empty($id_user)) {
        $stmt = $pdo->prepare("SELECT * FROM user WHERE nama_user = ? AND id_user = ? AND status_user = 'aktif'");
        $stmt->execute([$nama_user, $id_user]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['nama_user'] = $user['nama_user'];
            $_SESSION['posisi'] = $user['posisi'];
            header("Location: index.php");
            exit;
        } else {
            $error = "nama user atau id salah, coba lagi!";
        }
    } else {
        $error = "Mohon isi nama user dan ID.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Gudang Sepatu</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .login-card { width: 400px; padding: 30px; }
        .error-msg { background: #dc3545; color: white; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; font-size: 14px;}
        h2 { font-family: 'Fraunces', serif; margin-bottom: 20px; text-align: center; }
    </style>
</head>
<body>
    <div class="glass-card login-card">
        <h2>Login</h2>
        <?php if ($error): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label>User</label>
                <input type="text" name="nama_user" class="form-control" required>
            </div>
            <div class="form-group">
                <label>ID User</label>
                <input type="password" name="id_user" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 15px;">Login</button>
        </form>
    </div>
</body>
</html>
