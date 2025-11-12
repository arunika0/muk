<?php
session_start();
require_once 'config.php';

checkLogin();

$conn = getConnection();

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$query = "SELECT 
            p.id_pegawai,
            p.nama,
            CASE 
                WHEN p.jenis_kelamin = 'L' THEN 'Laki-laki'
                WHEN p.jenis_kelamin = 'P' THEN 'Perempuan'
            END as jenis_kelamin,
            p.tanggal_lahir,
            j.nama_jabatan,
            d.nama_departemen,
            d.lokasi,
            p.gaji
          FROM pegawai p
          LEFT JOIN jabatan j ON p.id_jabatan = j.id_jabatan
          LEFT JOIN departemen d ON p.id_departemen = d.id_departemen";

if (!empty($search)) {
    $query .= " WHERE p.nama LIKE ?";
}

$query .= " ORDER BY p.nama ASC";

if (!empty($search)) {
    $stmt = $conn->prepare($query);
    $search_param = "%" . $search . "%";
    $stmt->bind_param("s", $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($query);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pegawai - Sistem Manajemen</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <div class="navbar">
        <div class="navbar-content">
            <h1>👥 Sistem Manajemen Pegawai</h1>
            <div class="user-info">
                <span>Selamat datang, <strong><?php echo sanitize($_SESSION['nama_lengkap']); ?></strong></span>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        
        <div class="card">
            <div class="card-header">
                <h2>📋 Daftar Pegawai</h2>
                <div class="search-container">
                    <form method="GET" action="" class="search-form">
                        <input type="text" name="search" placeholder="Cari nama pegawai..." value="<?php echo htmlspecialchars($search); ?>" class="search-input">
                        <button type="submit" class="search-btn">🔍 Cari</button>
                        <?php if (!empty($search)): ?>
                            <a href="index.php" class="clear-search">❌ Hapus Pencarian</a>
                        <?php endif; ?>
                    </form>
                    <?php if (!empty($search)): ?>
                        <div class="search-info">
                            Menampilkan <?php echo $result->num_rows; ?> hasil untuk "<?php echo htmlspecialchars($search); ?>"
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Pegawai</th>
                                <th>Jenis Kelamin</th>
                                <th>Tanggal Lahir</th>
                                <th>Jabatan</th>
                                <th>Departemen</th>
                                <th>Lokasi</th>
                                <th>Gaji</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            while ($row = $result->fetch_assoc()): 
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><strong><?php echo sanitize($row['nama']); ?></strong></td>
                                <td>
                                    <span class="badge <?php echo ($row['jenis_kelamin'] == 'Laki-laki') ? 'badge-male' : 'badge-female'; ?>">
                                        <?php echo sanitize($row['jenis_kelamin']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($row['tanggal_lahir'])); ?></td>
                                <td><?php echo sanitize($row['nama_jabatan']); ?></td>
                                <td><?php echo sanitize($row['nama_departemen']); ?></td>
                                <td><?php echo sanitize($row['lokasi']); ?></td>
                                <td><strong><?php echo formatRupiah($row['gaji']); ?></strong></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="no-data">
                    📭 <?php echo !empty($search) ? 'Tidak ada pegawai dengan nama "' . htmlspecialchars($search) . '"' : 'Tidak ada data pegawai'; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>
