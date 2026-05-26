<?php
session_start();
include '../koneksi.php';
if (!isset($_SESSION['nim'])) { header("Location: ../index.php"); exit; }

$nim = $_SESSION['nim'];

// 1. SIMPAN & UPDATE
if (isset($_POST['simpan'])) {
    $jml = str_replace('.', '', $_POST['jumlah']);
    $ket = $_POST['ket'];
    $jenis = $_POST['jenis'];

    if (!empty($_POST['id'])) {
        $q = "UPDATE keuangan SET jenis='$jenis', jumlah='$jml', keterangan='$ket' WHERE id_uang='$_POST[id]' AND NIM='$nim'";
    } else {
        $q = "INSERT INTO keuangan (NIM, jenis, jumlah, keterangan) VALUES ('$nim','$jenis','$jml','$ket')";
    }
    mysqli_query($conn, $q);
    header("Location: keuangan.php");
}

// 2. HAPUS
if (isset($_GET['hapus'])) {
    mysqli_query($conn, "DELETE FROM keuangan WHERE id_uang='$_GET[hapus]' AND NIM='$nim'");
    header("Location: keuangan.php");
}

// 3. GET DATA (EDIT, SALDO, LIST)
$edit = isset($_GET['edit']) ? mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM keuangan WHERE id_uang='$_GET[id]'")) : null;

$saldo_q = mysqli_query($conn, "SELECT jenis, SUM(jumlah) as total FROM keuangan WHERE NIM='$nim' GROUP BY jenis");
$saldo = ['Pemasukan' => 0, 'Pengeluaran' => 0];
while ($row = mysqli_fetch_assoc($saldo_q)) $saldo[$row['jenis']] = $row['total'];

$list = mysqli_query($conn, "SELECT * FROM keuangan WHERE NIM='$nim' ORDER BY tanggal DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Keuangan - Delimi</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="app-container">
    <?php include '../sidebar.php'; ?>
    <main class="content">
        <h2><?= $edit ? '✏️ Edit Catatan' : 'Catatan Keuangan 💸' ?></h2>
        
        <?php if(!$edit): ?>
        <div class="finance-summary">
            <div class="f-card in"><small>Total Pemasukan</small><h3>+Rp<?= number_format($saldo['Pemasukan'], 0, ',', '.') ?></h3></div>
            <div class="f-card out"><small>Total Pengeluaran</small><h3>-Rp<?= number_format($saldo['Pengeluaran'], 0, ',', '.') ?></h3></div>
            <div class="f-card bal"><small>Sisa Saldo</small><h3>Rp<?= number_format($saldo['Pemasukan'] - $saldo['Pengeluaran'], 0, ',', '.') ?></h3></div>
        </div>
        <?php endif; ?>

        <div class="card">
            <form method="POST" class="form-inline">
                <input type="hidden" name="id" value="<?= $edit['id_uang'] ?? '' ?>">
                
                <select name="jenis" id="jenis" style="flex:1">
                    <option value="Pengeluaran" <?= ($edit['jenis'] ?? '') == 'Pengeluaran' ? 'selected' : '' ?>>Pengeluaran</option>
                    <option value="Pemasukan" <?= ($edit['jenis'] ?? '') == 'Pemasukan' ? 'selected' : '' ?>>Pemasukan</option>
                </select>
                
                <input type="text" name="jumlah" id="uang" placeholder="Rp 0" required style="flex:1" autocomplete="off"
                       value="<?= $edit ? number_format($edit['jumlah'], 0, ',', '.') : '' ?>">
                
                <input type="text" name="ket" id="ket" placeholder="Keterangan..." required style="flex:2" 
                       value="<?= $edit['keterangan'] ?? '' ?>">
                
                <button type="submit" name="simpan"><?= $edit ? 'Update' : 'Simpan' ?></button>
                <?php if($edit): ?><a href="keuangan.php" class="btn-cancel">Batal</a><?php endif; ?>
            </form>
            
            <?php if(!$edit): ?>
            <table>
                <thead><tr><th>Tgl</th><th>Ket</th><th>Jumlah</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php while($r = mysqli_fetch_assoc($list)): 
                        $is_in = $r['jenis'] == 'Pemasukan';
                    ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($r['tanggal'])) ?></td>
                        <td><?= $r['keterangan'] ?></td>
                        <td style="color:<?= $is_in ? '#2e7d32' : '#c62828' ?>; font-weight:bold;">
                            <?= $is_in ? '+' : '-' ?> Rp<?= number_format($r['jumlah'], 0, ',', '.') ?>
                        </td>
                        <td>
                            <a href="?edit=1&id=<?= $r['id_uang'] ?>" class="btn-icon edit"><i class="fa-solid fa-pen"></i></a>
                            <a href="#" onclick="conf('?hapus=<?= $r['id_uang'] ?>')" class="btn-icon del"><i class="fa-solid fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </main>
</div>

<div id="modal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <h3>Yakin Hapus?</h3> <p>Data akan hilang permanen.</p>
        <div class="modal-actions">
            <button onclick="closeM()" class="btn-modal-no">Batal</button>
            <a id="delLink" href="#" class="btn-modal-yes">Ya, Hapus</a>
        </div>
    </div>
</div>

<script>
    // Format Rupiah
    document.getElementById('uang').addEventListener('input', function(e) {
        let v = this.value.replace(/[^0-9]/g, '');
        this.value = v ? v.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") : '';
    });

    // Label Logic
    const jenis = document.getElementById('jenis'), ket = document.getElementById('ket');
    const updateLbl = () => ket.placeholder = (jenis.value === "Pemasukan") ? "Didapat dari..." : "Untuk beli...";
    jenis.addEventListener('change', updateLbl);
    updateLbl();

    // Modal Logic
    function conf(url) { document.getElementById('delLink').href = url; document.getElementById('modal').style.display = 'flex'; }
    function closeM() { document.getElementById('modal').style.display = 'none'; }
    window.onclick = e => { if(e.target == document.getElementById('modal')) closeM(); }
</script>
</body>
</html>