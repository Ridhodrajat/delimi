<?php
session_start();
include '../koneksi.php';
if (!isset($_SESSION['nim'])) { header("Location: ../index.php"); exit; }

// Ambil Data User
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM mahasiswa WHERE NIM = '$_SESSION[nim]'"));
$nama = $user['nama_mhs'];

// Logika Pendek (Ternary Operator)
$sapaan = !empty($user['nama_panggilan']) ? $user['nama_panggilan'] : explode(' ', $nama)[0];
$foto   = $user['foto'] ? "../uploads/$user[foto]" : "https://via.placeholder.com/150/A5D6A7/ffffff?text=".strtoupper($nama[0]);

// Data Kartu Menu (Disimpan dalam Array agar HTML tidak berulang)
$menus = [
    ['green',  '📝', 'Progress Tugas',     'Cek daftar tugasmu yang belum selesai.', 'tugas.php',    'Lihat Tugas'],
    ['yellow', '💰', 'Catatan Keuangan',   'Pantau pengeluaran & pemasukanmu.',      'keuangan.php', 'Cek Dompet'],
    ['pink',   '💖', 'Self Care',          'Bagaimana perasaanmu hari ini?',         'jurnal.php',   'Tulis Jurnal']
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Beranda - Delimi</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="app-container">
    <?php include '../sidebar.php'; ?>

    <main class="content">
        <div class="profile-header">
            <div class="welcome-text">
                <h1>Halo, <?= $sapaan ?>! ✨</h1>
                <p>Semangat produktif hari ini!</p>
            </div>
            <a href="profile.php" class="profile-card">
                <div class="text-right">
                    <span class="p-name"><?= $nama ?></span>
                    <span class="p-nim"><?= $_SESSION['nim'] ?></span>
                </div>
                <img src="<?= $foto ?>" alt="Profile" class="avatar">
            </a>
        </div>

        <div class="grid-cards">
            <?php foreach($menus as $m): ?>
            <div class="card summary <?= $m[0] ?>">
                <div class="icon-bg"><?= $m[1] ?></div>
                <h3><?= $m[2] ?></h3>
                <p><?= $m[3] ?></p>
                <a href="<?= $m[4] ?>" class="btn-card btn-<?= $m[0] ?>"><?= $m[5] ?></a>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>

</body>
</html>