<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Gudang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        /* Efek hover halus pada menu navigasi */
        .nav-link {
            transition: all 0.2s ease-in-out;
            padding-left: 12px !important;
            padding-right: 12px !important;
        }
        .nav-link:hover {
            color: #fff !important;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 6px;
        }
        /* Penanda halaman aktif (opsional) */
        .nav-item.active .nav-link {
            color: #fff !important;
            background-color: #0d6efd;
            border-radius: 6px;
        }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 sticky-top shadow-sm py-3">
        <div class="container">
            <a class="navbar-brand fw-bold text-white d-flex align-items-center gap-2" href="/gudang-app/index.php">
                <i class="bi bi-box-seam-fill text-warning fs-4"></i> 
                <span>Logistik<span class="text-warning">Gudang</span></span>
            </a>
            
            <button class="navbar-expand shadow-none border-0 navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav gap-1 mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="/gudang-app/index.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="/gudang-app/barang/index.php">
                            <i class="bi bi-tags"></i> Data Barang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="/gudang-app/lokasi/index.php">
                            <i class="bi bi-grid-3x3-gap"></i> Lokasi Rak
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="/gudang-app/transaksi_masuk/index.php">
                            <i class="bi bi-box-arrow-in-down text-success"></i> Barang Masuk
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="/gudang-app/transaksi_keluar/index.php">
                            <i class="bi bi-box-arrow-up text-danger"></i> Barang Keluar
                        </a>
                    </li>
                    <li class="nav-item">
                        <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="/gudang-app/supplier/index.php">
                            <i class="bi bi-truck text-info"></i> Supplier
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container">