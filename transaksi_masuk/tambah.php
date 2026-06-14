<?php 
include '../config/database.php';
include '../includes/header.php';

// 1. Ambil data untuk Pilihan (Dropdown) di dalam Form
$barang_query   = mysqli_query($conn, "SELECT * FROM Barang");
$lokasi_query   = mysqli_query($conn, "SELECT * FROM Lokasi");
$supplier_query = mysqli_query($conn, "SELECT * FROM Supplier");

// 2. Proses saat tombol "Konfirmasi" diklik
if (isset($_POST['submit'])) {
    $nomor_po    = $_POST['nomor_po'];
    $id_supplier = $_POST['id_supplier'] ? $_POST['id_supplier'] : 'NULL';
    $petugas     = $_POST['petugas'];
    $id_barang   = $_POST['id_barang'];
    $id_lokasi   = $_POST['id_lokasi'];
    $jumlah      = $_POST['jumlah'];
    $keterangan  = $_POST['keterangan']; 

    // LANGKAH A: Ambil data kapasitas maksimal dari tabel Lokasi untuk Validasi
    $query_lokasi_cek = mysqli_query($conn, "SELECT kapasitas_maksimal FROM Lokasi WHERE id_lokasi = $id_lokasi");
    $data_lokasi      = mysqli_fetch_assoc($query_lokasi_cek);
    $kapasitas_maks   = $data_lokasi['kapasitas_maksimal'];

    // Cek jumlah stok yang SUDAH ADA di rak tersebut saat ini
    $cek_stok  = mysqli_query($conn, "SELECT jumlah_stok FROM Stok_Lokasi WHERE id_barang = $id_barang AND id_lokasi = $id_lokasi");
    $data_stok = mysqli_fetch_assoc($cek_stok);
    $stok_sekarang = isset($data_stok['jumlah_stok']) ? $data_stok['jumlah_stok'] : 0;

    // VALIDASI KAPASITAS: Jika transaksi NORMAL/RETUR KONSUMEN, cek apakah rak muat?
    if ($keterangan != 'Retur Supplier' && ($stok_sekarang + $jumlah) > $kapasitas_maks) {
        echo "<script>
                alert('Gagal! Kapasitas Rak Tidak Muat. Maksimal kapasitas: $kapasitas_maks item. Stok saat ini di rak: $stok_sekarang item.'); 
                window.history.back();
              </script>";
        exit; 
    }

    // LANGKAH B: Simpan Nota Utama ke tabel Transaksi_Masuk jika lolos validasi
    $query_masuk = mysqli_query($conn, "INSERT INTO Transaksi_Masuk (id_supplier, nomor_po, petugas, keterangan) 
                                        VALUES ($id_supplier, '$nomor_po', '$petugas', '$keterangan')");
    
    if ($query_masuk) {
        $id_masuk_terakhir = mysqli_insert_id($conn); 

        // LANGKAH C: Simpan Item Barang ke tabel Detail_Masuk
        mysqli_query($conn, "INSERT INTO Detail_Masuk (id_masuk, id_barang, jumlah) 
                             VALUES ($id_masuk_terakhir, $id_barang, $jumlah)");

        // LANGKAH D: EKSEKUSI STOK PINTAR
        if ($keterangan == 'Retur Supplier') {
            // JIKA RETUR KE SUPPLIER -> STOK BERKURANG (-)
            if (mysqli_num_rows($cek_stok) > 0) {
                mysqli_query($conn, "UPDATE Stok_Lokasi SET jumlah_stok = jumlah_stok - $jumlah 
                                     WHERE id_barang = $id_barang AND id_lokasi = $id_lokasi");
            }
        } else {
            // JIKA NORMAL / RETUR KONSUMEN -> STOK BERTAMBAH (+)
            if (mysqli_num_rows($cek_stok) > 0) {
                mysqli_query($conn, "UPDATE Stok_Lokasi SET jumlah_stok = jumlah_stok + $jumlah 
                                     WHERE id_barang = $id_barang AND id_lokasi = $id_lokasi");
            } else {
                mysqli_query($conn, "INSERT INTO Stok_Lokasi (id_barang, id_lokasi, jumlah_stok) 
                                     VALUES ($id_barang, $id_lokasi, $jumlah)");
            }
        }

        echo "<script>alert('Berhasil disimpan! Stok otomatis terupdate.'); window.location='index.php';</script>";
    } else {
        echo "<div class='alert alert-danger'>Gagal: " . mysqli_error($conn) . "</div>";
    }
}
?>

<!-- KODE HTML UNTUK FORM INPUT DI BAWAH INI AMAN KARENA BERADA DI LUAR TAG PHP -->
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="card-title mb-0">📥 Form Input Barang Masuk & Retur (Inbound)</h5>
            </div>
            <div class="card-body p-4">
                <form action="" method="POST">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nomor PO (Purchase Order)</label>
                            <input type="text" name="nomor_po" class="form-control" placeholder="Contoh: PO-001" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nama Petugas Gudang</label>
                            <input type="text" name="petugas" class="form-control" placeholder="Nama Anda" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Pilih Supplier (Pemasok)</label>
                            <select name="id_supplier" class="form-select">
                                <option value="">-- Tanpa Supplier --</option>
                                <?php while($s = mysqli_fetch_assoc($supplier_query)): ?>
                                    <option value="<?= $s['id_supplier']; ?>"><?= $s['nama_supplier']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Jenis Kedatangan Barang</label>
                            <select name="keterangan" class="form-select" required>
                                <option value="Normal">Normal (Stok Masuk Baru)</option>
                                <option value="Retur Konsumen">Retur Konsumen (Barang Masuk Lagi, Stok +)</option>
                                <option value="Retur Supplier">Retur Ke Supplier (Barang Dikembalikan, Stok -)</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Barang yang Masuk</label>
                        <select name="id_barang" class="form-select" required>
                            <option value="">-- Pilih Produk --</option>
                            <?php while($b = mysqli_fetch_assoc($barang_query)): ?>
                                <option value="<?= $b['id_barang']; ?>"><?= $b['nama_barang']; ?> (SKU: <?= $b['sku']; ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Simpan di Rak / Lokasi</label>
                            <select name="id_lokasi" class="form-select" required>
                                <option value="">-- Pilih Rak Gudang --</option>
                                <?php while($l = mysqli_fetch_assoc($lokasi_query)): ?>
                                    <option value="<?= $l['id_lokasi']; ?>"><?= $l['nama_zona']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Jumlah Item</label>
                            <input type="number" name="jumlah" class="form-control" min="1" placeholder="0" required>
                        </div>
                    </div>

                    <button type="submit" name="submit" class="btn btn-primary w-100 py-2 fw-bold">Konfirmasi Simpan Barang</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>