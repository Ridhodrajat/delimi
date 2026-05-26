<?php
$page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-content">
        <div class="brand"><h2>Delimi</h2></div>
        
        <nav>
            <a href="dashboard.php" class="<?= $page=='dashboard.php'?'active':'' ?>">
                <i class="fa-solid fa-house"></i> <span>Beranda</span>
            </a>
            
            <a href="tugas.php" class="<?= $page=='tugas.php'?'active':'' ?>">
                <i class="fa-solid fa-calendar-check"></i> <span>Tugas</span>
            </a>
            
            <a href="keuangan.php" class="<?= $page=='keuangan.php'?'active':'' ?>">
                <i class="fa-solid fa-wallet"></i> <span>Keuangan</span>
            </a>
            
            <a href="jurnal.php" class="<?= $page=='jurnal.php'?'active':'' ?>">
                <i class="fa-solid fa-book-open"></i> <span>Jurnal</span>
            </a>
            
            <a href="../logout.php" class="logout">
                <i class="fa-solid fa-right-from-bracket"></i> <span>Keluar</span>
            </a>
        </nav>
        
    </div>
</aside>