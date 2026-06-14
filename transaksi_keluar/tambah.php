<?php 
include '../config/database.php';
include '../includes/header.php';

// 1. Ambil data barang dan lokasi untuk dropdown
$barang_query = mysqli_query($conn, "SELECT * FROM Barang");
$lokasi_query = mysqli_query($conn, "SELECT * FROM Lokasi");

if (isset($_POST['submit'])) {
    $nomor_do   = $_POST['nomor_do'];
    $tujuan     = $_POST['tujuan'];
    $petugas    = $_POST['petugas'];
    $id_barang  = $_POST['id_barang'];
    $id_lokasi  = $_POST['id_lokasi'];
    $jumlah     = $_POST['jumlah'];
    $keterangan = $_POST['keterangan']; // Mengambil status Normal atau Retur

    // Cek kondisi stok saat ini di rak terpilih
    $cek_stok_query = mysqli_query($conn, "SELECT jumlah_stok FROM Stok_Lokasi WHERE id_barang = $id_barang AND id_lokasi = $id_lokasi");
    $data_stok = mysqli_fetch_assoc($cek_stok_query);
    $stok_tersisa = isset($data_stok['jumlah_stok']) ? $data_stok['jumlah_stok'] : 0;

    // VALIDASI KHUSUS: Jika transaksi NORMAL, stok di rak tidak boleh kurang dari jumlah yang diminta
    if ($keterangan == 'Normal' && $stok_tersisa < $jumlah) {
        echo "<div class='alert alert-danger'><strong>Gagal!</strong> Stok di rak tersebut tidak mencukupi untuk dikeluarkan. (Stok saat ini: $stok_tersisa)</div>";
    } else {
        // 2. Simpan ke tabel induk: Transaksi_Keluar (tambahkan kolom keterangan)
        $query_keluar = mysqli_query($conn, "INSERT INTO Transaksi_Keluar (tujuan, nomor_do, petugas, keterangan) 
                                             VALUES ('$tujuan', '$nomor_do', '$petugas', '$keterangan')");
        
        if ($query_keluar) {
            $id_keluar_terakhir = mysqli_insert_id($conn);

            // 3. Simpan ke tabel detail: Detail_Keluar
            mysqli_query($conn, "INSERT INTO Detail_Keluar (id_keluar, id_barang, jumlah) 
                                 VALUES ($id_keluar_terakhir, $id_barang, $jumlah)");

            // 4. LOGIKA STOK PINTAR
            if ($keterangan == 'Retur Konsumen') {
                // JIKA RETUR KONSUMEN -> BARANG MASUK BALIK -> STOK BERTAMBAH (+)
                if (mysqli_num_rows($cek_stok_query) > 0) {
                    mysqli_query($conn, "UPDATE Stok_Lokasi SET jumlah_stok = jumlah_stok + $jumlah 
                                         WHERE id_barang = $id_barang AND id_lokasi = $id_lokasi");
                } else {
                    mysqli_query($conn, "INSERT INTO Stok_Lokasi (id_barang, id_lokasi, jumlah_stok) 
                                         VALUES ($id_barang, $id_lokasi, $jumlah)");
                }
            } else {
                // JIKA TRANSAKSI NORMAL -> BARANG KELUAR -> STOK BERKURANG (-)
                mysqli_query($conn, "UPDATE Stok_Lokasi SET jumlah_stok = jumlah_stok - $jumlah 
                                     WHERE id_barang = $id_barang AND id_lokasi = $id_lokasi");
            }

            echo "<script>alert('Transaksi Keluar Berhasil dicatat!'); window.location='index.php';</script>";
        } else {
            echo "<div class='alert alert-danger'>Gagal mencatat transaksi: " . mysqli_error($conn) . "</div>";
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-danger text-white py-3">
                <h5 class="card-title mb-0">📤 Form Input Barang Keluar & Retur (Outbound)</h5>
            </div>
            <div class="card-body p-4">
                <form action="" method="POST">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nomor DO (Delivery Order)</label>
                            <input type="text" name="nomor_do" class="form-control" placeholder="Contoh: DO-001" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nama Petugas</label>
                            <input type="text" name="petugas" class="form-control" placeholder="Nama Anda" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tujuan Distribusi / Konsumen</label>
                            <input type="text" name="tujuan" class="form-control" placeholder="Contoh: Toko Cabang A" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Jenis Pengeluaran Barang</label>
                            <select name="keterangan" class="form-select" required>
                                <option value="Normal">Normal (Barang Keluar/Dijual)</option>
                                <option value="Retur Konsumen">Retur Konsumen (Pembatalan Keluar, Stok +)</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Barang yang Keluar</label>
                        <select name="id_barang" class="form-select" required>
                            <option value="">-- Pilih Produk --</option>
                            <?php while($b = mysqli_fetch_assoc($barang_query)): ?>
                                <option value="<?= $b['id_barang']; ?>"><?= $b['nama_barang']; ?> (SKU: <?= $b['sku']; ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ambil / Kembalikan ke Rak</label>
                            <select name="id_lokasi" class="form-select" required>
                                <option value="">-- Pilih Rak --</option>
                                <?php while($l = mysqli_fetch_assoc($lokasi_query)): ?>
                                    <option value="<?= $l['id_lokasi']; ?>"><?= $l['nama_zona']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Jumlah Keluar</label>
                            <input type="number" name="jumlah" class="form-control" min="1" placeholder="0" required>
                        </div>
                    </div>

                    <button type="submit" name="submit" class="btn bg-danger text-white w-100 py-2 fw-bold">Konfirmasi Transaksi Keluar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
