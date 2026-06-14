<?php 
include '../config/database.php';
include '../includes/header.php';

// Menarik data lengkap termasuk kolom keterangan yang baru kita buat
$sql = "SELECT tm.*, dm.jumlah, b.nama_barang, b.sku, s.nama_supplier 
        FROM Transaksi_Masuk tm
        JOIN Detail_Masuk dm ON tm.id_masuk = dm.id_masuk
        JOIN Barang b ON dm.id_barang = b.id_barang
        LEFT JOIN Supplier s ON tm.id_supplier = s.id_supplier
        ORDER BY tm.id_masuk DESC";

$query = mysqli_query($conn, $sql);
?>


<div class="p-4 mb-4 bg-dark rounded-3 shadow-sm border">
    <div class="container-fluid py-2">
        <h1 class="display-6 fw-bold text-white">Riwayat Barang Masuk & Retur </h1>
        <a href="tambah.php" class="btn btn-primary">+ Input Transaksi Baru</a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <table class="table table-hover table-striped mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Tanggal</th>
                    <th>No PO</th>
                    <th>Nama Barang</th>
                    <th>Jumlah</th>
                    <th>Supplier</th>
                    <th>Status / Jenis</th>
                    <th>Petugas</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($query) == 0): ?>
                    <tr><td colspan="7" class="text-center text-muted p-4">Belum ada transaksi masuk.</td></tr>
                <?php endif; ?>
                
                <?php while($row = mysqli_fetch_assoc($query)): ?>
                <tr>
                    <td><?= date('d-m-Y H:i', strtotime($row['tanggal_masuk'])); ?></td>
                    <td><span class="text-primary fw-bold"><?= $row['nomor_po']; ?></span></td>
                    <td><strong><?= $row['nama_barang']; ?></strong> <small class="text-muted">(<?= $row['sku']; ?>)</small></td>
                    
                    <td>
                        <?php if($row['keterangan'] == 'Retur Supplier'): ?>
                            <span class="badge bg-danger">- <?= $row['jumlah']; ?></span>
                        <?php else: ?>
                            <span class="badge bg-success">+ <?= $row['jumlah']; ?></span>
                        <?php endif; ?>
                    </td>
                    
                    <td><?= $row['nama_supplier'] ? $row['nama_supplier'] : '-'; ?></td>
                    
                    <td>
                        <?php if($row['keterangan'] == 'Retur Konsumen'): ?>
                            <span class="badge bg-warning text-dark">Retur Konsumen (Stok +)</span>
                        <?php elseif($row['keterangan'] == 'Retur Supplier'): ?>
                            <span class="badge bg-danger">Retur Supplier (Stok -)</span>
                        <?php else: ?>
                            <span class="badge bg-info text-dark">Normal (Stok +)</span>
                        <?php endif; ?>
                    </td>
                    
                    <td><?= $row['petugas']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>