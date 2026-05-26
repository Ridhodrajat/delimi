<?php
session_start();
include 'koneksi.php';
if (isset($_SESSION['nim'])) { header("Location: fitur/dashboard.php"); exit; }

$msg = ""; $ok = false;

if (isset($_POST['register'])) {
    $nim = $_POST['nim']; $nama = $_POST['nama'];

    // Cek NIM & Insert Data
    if (mysqli_num_rows(mysqli_query($conn, "SELECT * FROM mahasiswa WHERE NIM = '$nim'"))) {
        $msg = "NIM $nim sudah terdaftar!";
    } elseif (mysqli_query($conn, "INSERT INTO mahasiswa (NIM, nama_mhs) VALUES ('$nim', '$nama')")) {
        $ok = true;
        // Generate Password: NamaDepan(kecil) + (NIM dari '24' ATAU 5 digit terakhir)
        $pass = strtolower(strtok($nama, " ")) . (strstr($nim, '24') ?: substr($nim, -5));
        $msg = "Berhasil! Password Anda adalah: <b>$pass</b>";
    } else {
        $msg = "Gagal mendaftar.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Akun - Delimi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-login">
    <div class="login-box">
        <div class="logo-area"><h1>Daftar</h1> <p>Bergabunglah dengan Delimi</p></div>
        
        <?php if($ok): ?>
            <div class="alert-success" style="background:#e8f5e9; color:#2e7d32; padding:15px; border-radius:10px; margin-bottom:20px; border:1px solid #c8e6c9;">
                <?= $msg ?> <br><br> <a href="index.php" style="font-weight:bold; color:#2e7d32;">Login Sekarang &rarr;</a>
            </div>
        <?php else: ?>
            <form method="POST">
                <div class="form-group"><label>NIM</label><input type="text" name="nim" placeholder="Masukkan NIM..." required></div>
                <div class="form-group"><label>Nama Lengkap</label><input type="text" name="nama" placeholder="Nama Lengkap..." required></div>
                <button type="submit" name="register">Buat Akun</button>
            </form>
            <?php if($msg): ?><div class="alert-error"><?= $msg ?></div><?php endif; ?>
            <div style="margin-top: 20px; font-size: 0.9em;">Sudah punya akun? <a href="index.php" class="link-register">Login disini</a></div>
        <?php endif; ?>
    </div>
</body>
</html>