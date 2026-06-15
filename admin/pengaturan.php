<?php
require_once '../config/config.php';
requireRole(['admin']);
require_once '../includes/icons.php';

require_once '../models/Pengaturan.php';

$database = new Database();
$db = $database->getConnection();

$pengaturan = new Pengaturan($db);

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Sesi tidak valid (CSRF Token Error). Silakan refresh halaman.';
        $message_type = 'error';
    } elseif (isset($_POST['action']) && $_POST['action'] === 'update') {
        $settings = [
        'nama_platform' => sanitizeInput($_POST['nama_platform'] ?? ''),
        'deskripsi_platform' => sanitizeInput($_POST['deskripsi_platform'] ?? ''),
        'email_platform' => sanitizeInput($_POST['email_platform'] ?? ''),
        'warna_primary' => sanitizeInput($_POST['warna_primary'] ?? '#3B82F6'),
        'warna_secondary' => sanitizeInput($_POST['warna_secondary'] ?? '#2DD4BF'),
        'warna_sidebar' => sanitizeInput($_POST['warna_sidebar'] ?? '#1a1a2e'),
        'warna_sidebar_header' => sanitizeInput($_POST['warna_sidebar_header'] ?? '#16213e'),
        'warna_success' => sanitizeInput($_POST['warna_success'] ?? '#27ae60'),
        'warna_danger' => sanitizeInput($_POST['warna_danger'] ?? '#e74c3c'),
        'warna_warning' => sanitizeInput($_POST['warna_warning'] ?? '#f39c12'),
        'warna_info' => sanitizeInput($_POST['warna_info'] ?? '#3498db')
    ];
    
    if ($pengaturan->updateAll($settings)) {
        $message = 'Pengaturan berhasil diperbarui!';
        $message_type = 'success';
    } else {
        $message = 'Gagal memperbarui pengaturan!';
        $message_type = 'error';
    }
    }
}

// Get all settings
$settings = $pengaturan->getAll();

$page_title = 'Pengaturan Aplikasi';
$page_css = ['pages/dashboard.css', 'sidebar-island.css', 'dashboard-override.css', 'admin.css', 'shared.css'];
$body_class = getThemeClass();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once '../includes/head.php'; ?>
    <style>
        .form-container {
            background: var(--bg-surface);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-lg);
            padding: 1.75rem;
            margin-bottom: 1.5rem;
        }
        .form-container h2 {
            font-size: 1.125rem;
            font-weight: 700;
            margin: 0 0 1.5rem 0;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-default);
            color: var(--text-primary);
        }
        .form-container h3 {
            font-size: 1rem;
            font-weight: 600;
            margin: 0 0 1.25rem 0;
            color: var(--text-primary);
        }
        .form-group small {
            color: var(--text-muted);
            display: block;
            margin-top: 0.35rem;
            font-size: 0.775rem;
        }
        .form-divider {
            margin: 2rem 0;
            border: none;
            border-top: 1px solid var(--border-default);
        }
        .color-input-group {
            display: flex;
            gap: 0.625rem;
            align-items: center;
        }
        .color-input-group input[type="color"] {
            width: 56px;
            height: 40px;
            padding: 2px;
            border: 1px solid var(--border-default);
            border-radius: var(--radius-md);
            cursor: pointer;
            background: var(--bg-surface);
        }
        .color-input-group input[type="text"] {
            flex: 1;
        }
        .settings-tip {
            margin-top: 1.5rem;
            padding: 1rem 1.25rem;
            background: var(--bg-subtle);
            border-radius: var(--radius-md);
            border: 1px solid var(--border-default);
            color: var(--text-secondary);
            font-size: 0.85rem;
        }
        .settings-tip strong {
            color: var(--brand);
        }
    </style>
</head>
<body class="dashboard-layout <?php echo $body_class; ?>">
    <?php include_once 'navbar.php'; ?>

    <div class="dashboard-container">
        <div class="dashboard-content">
            <div class="admin-header">
                <div>
                    <h1>Pengaturan Aplikasi</h1>
                    <p style="color:var(--text-muted);margin-top:0.25rem;">Kelola pengaturan dan konfigurasi platform</p>
                </div>
            </div>

            <!-- Content -->
            <div class="content">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <!-- Pengaturan Form -->
                <div class="form-container">
                    <h2>Pengaturan Umum</h2>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <input type="hidden" name="action" value="update">
                        
                        <div class="form-group">
                            <label for="nama_platform">Nama Platform</label>
                            <input type="text" id="nama_platform" name="nama_platform" 
                                   value="<?php echo htmlspecialchars($settings['nama_platform'] ?? APP_NAME); ?>" required>
                            <small>Nama platform yang akan ditampilkan di seluruh aplikasi</small>
                        </div>

                        <div class="form-group">
                            <label for="deskripsi_platform">Deskripsi Platform</label>
                            <textarea id="deskripsi_platform" name="deskripsi_platform" rows="3" 
                                      placeholder="Deskripsi singkat tentang platform pembelajaran coding ini"><?php echo htmlspecialchars($settings['deskripsi_platform'] ?? ''); ?></textarea>
                            <small>Deskripsi platform yang akan ditampilkan di halaman utama</small>
                        </div>

                        <div class="form-group">
                            <label for="email_platform">Email Platform</label>
                            <input type="email" id="email_platform" name="email_platform" 
                                   value="<?php echo htmlspecialchars($settings['email_platform'] ?? ''); ?>"
                                   placeholder="info@prozone.com">
                            <small>Email kontak untuk platform</small>
                        </div>

                        <hr class="form-divider">

                        <h3>Pengaturan Warna Tema</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="warna_primary">Warna Primary</label>
                                <div class="color-input-group">
                                    <input type="color" id="warna_primary" name="warna_primary" 
                                           value="<?php echo htmlspecialchars($settings['warna_primary'] ?? '#3B82F6'); ?>">
                                    <input type="text" value="<?php echo htmlspecialchars($settings['warna_primary'] ?? '#3B82F6'); ?>" 
                                           onchange="document.getElementById('warna_primary').value = this.value">
                                </div>
                                <small>Warna utama untuk tombol, link, dan elemen aktif</small>
                            </div>
                            <div class="form-group">
                                <label for="warna_secondary">Warna Secondary</label>
                                <div class="color-input-group">
                                    <input type="color" id="warna_secondary" name="warna_secondary" 
                                           value="<?php echo htmlspecialchars($settings['warna_secondary'] ?? '#2DD4BF'); ?>">
                                    <input type="text" value="<?php echo htmlspecialchars($settings['warna_secondary'] ?? '#2DD4BF'); ?>" 
                                           onchange="document.getElementById('warna_secondary').value = this.value">
                                </div>
                                <small>Warna sekunder untuk gradient dan accent</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="warna_sidebar">Warna Sidebar</label>
                                <div class="color-input-group">
                                    <input type="color" id="warna_sidebar" name="warna_sidebar" 
                                           value="<?php echo htmlspecialchars($settings['warna_sidebar'] ?? '#2c3e50'); ?>">
                                    <input type="text" value="<?php echo htmlspecialchars($settings['warna_sidebar'] ?? '#1a1a2e'); ?>" 
                                           onchange="document.getElementById('warna_sidebar').value = this.value">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="warna_sidebar_header">Warna Header Sidebar</label>
                                <div class="color-input-group">
                                    <input type="color" id="warna_sidebar_header" name="warna_sidebar_header" 
                                           value="<?php echo htmlspecialchars($settings['warna_sidebar_header'] ?? '#16213e'); ?>">
                                    <input type="text" value="<?php echo htmlspecialchars($settings['warna_sidebar_header'] ?? '#16213e'); ?>" 
                                           onchange="document.getElementById('warna_sidebar_header').value = this.value">
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="warna_success">Warna Success</label>
                                <div class="color-input-group">
                                    <input type="color" id="warna_success" name="warna_success" 
                                           value="<?php echo htmlspecialchars($settings['warna_success'] ?? '#27ae60'); ?>">
                                    <input type="text" value="<?php echo htmlspecialchars($settings['warna_success'] ?? '#27ae60'); ?>" 
                                           onchange="document.getElementById('warna_success').value = this.value">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="warna_danger">Warna Danger</label>
                                <div class="color-input-group">
                                    <input type="color" id="warna_danger" name="warna_danger" 
                                           value="<?php echo htmlspecialchars($settings['warna_danger'] ?? '#e74c3c'); ?>">
                                    <input type="text" value="<?php echo htmlspecialchars($settings['warna_danger'] ?? '#e74c3c'); ?>" 
                                           onchange="document.getElementById('warna_danger').value = this.value">
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="warna_warning">Warna Warning</label>
                                <div class="color-input-group">
                                    <input type="color" id="warna_warning" name="warna_warning" 
                                           value="<?php echo htmlspecialchars($settings['warna_warning'] ?? '#f39c12'); ?>">
                                    <input type="text" value="<?php echo htmlspecialchars($settings['warna_warning'] ?? '#f39c12'); ?>" 
                                           onchange="document.getElementById('warna_warning').value = this.value">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="warna_info">Warna Info</label>
                                <div class="color-input-group">
                                    <input type="color" id="warna_info" name="warna_info" 
                                           value="<?php echo htmlspecialchars($settings['warna_info'] ?? '#3498db'); ?>">
                                    <input type="text" value="<?php echo htmlspecialchars($settings['warna_info'] ?? '#3498db'); ?>" 
                                           onchange="document.getElementById('warna_info').value = this.value">
                                </div>
                            </div>
                        </div>

                        <div class="settings-tip">
                            <strong>Tips:</strong> Gunakan color picker untuk memilih warna atau masukkan kode hex (contoh: #14B8A6). 
                            Perubahan warna akan langsung diterapkan setelah menyimpan.
                        </div>

                        <button type="submit" class="admin-action-btn lessons" style="padding:0.6rem 1.5rem;font-size:0.85rem;">Simpan Pengaturan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Sync color picker dengan text input
        document.querySelectorAll('input[type="color"]').forEach(colorInput => {
            colorInput.addEventListener('input', function() {
                const textInput = this.parentElement.querySelector('input[type="text"]');
                if (textInput) {
                    textInput.value = this.value;
                }
            });
        });

        // Sync text input dengan color picker
        document.querySelectorAll('input[type="text"]').forEach(textInput => {
            if (textInput.previousElementSibling && textInput.previousElementSibling.type === 'color') {
                textInput.addEventListener('input', function() {
                    const colorInput = this.parentElement.querySelector('input[type="color"]');
                    if (colorInput && /^#[0-9A-F]{6}$/i.test(this.value)) {
                        colorInput.value = this.value;
                    }
                });
            }
        });
    </script>

    <?php include '../includes/loading.php'; ?>
    <?php include '../includes/toast.php'; ?>

    <script src="../assets/js/navbar.js"></script>
</body>
</html>


