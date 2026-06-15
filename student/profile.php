<?php
require_once '../config/config.php';
requireLogin();
require_once '../config/language.php';
require_once '../includes/icons.php';
require_once '../includes/FileUpload.php';
require_once '../includes/rpg_system.php';

require_once '../models/User.php';
require_once '../models/Achievement.php';
require_once '../models/Course.php';
require_once '../models/Enrollment.php';
require_once '../models/UserProgress.php';

$page_title       = 'Profile';
$page_description = 'Kelola profil dan pengaturan akun.';
$page_css         = ['pages/profile.css', 'sidebar-island.css', 'dashboard-override.css', 'shared.css', 'rpg-system.css'];
$body_class       = trim(getThemeClass() . ' dashboard-layout');

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$user->id = $_SESSION['user_id'];
$user->readOne();

$active_tab = $_GET['tab'] ?? 'edit';

$message = '';
$message_type = '';
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    $message_type = $_SESSION['flash_type'] ?? 'success';
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once '../includes/head.php'; ?>
</head>
<body class="<?php echo $body_class; ?>">
<?php require_once 'navbar.php'; ?>

<div class="page-wrapper dashboard-main-container">
    <div class="dashboard-content">
        <div class="dashboard-header">
            <div>
                <h1><?php icon('user', 24); ?> Profile</h1>
                <p class="greeting-sub">Kelola informasi akun dan pengaturan personal</p>
            </div>
            <div class="dash-header-actions">
                <a href="../dashboard.php" class="btn btn-secondary">
                    <?php icon('arrow-left', 16); ?> Kembali
                </a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="profile-layout">
            <!-- Profile Sidebar -->
            <div class="profile-sidebar-card">
                <div class="profile-avatar-section">
                    <div class="profile-avatar">
                        <?php if (!empty($user->avatar) && file_exists('../assets/uploads/avatars/' . $user->avatar)): ?>
                            <img src="../assets/uploads/avatars/<?php echo $user->avatar; ?>" alt="Avatar">
                        <?php else: ?>
                            <div class="avatar-placeholder">
                                <?php echo strtoupper(substr($user->nama_lengkap ?? 'U', 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h3><?php echo htmlspecialchars($user->nama_lengkap ?? 'User'); ?></h3>
                    <span class="profile-level">Level <?php echo $user->level ?? 1; ?></span>
                </div>
                <div class="profile-stats">
                    <div class="profile-stat">
                        <span class="ps-value"><?php echo number_format($user->xp ?? 0); ?></span>
                        <span class="ps-label">XP</span>
                    </div>
                    <div class="profile-stat">
                        <span class="ps-value"><?php echo $user->level ?? 1; ?></span>
                        <span class="ps-label">Level</span>
                    </div>
                </div>
            </div>

            <!-- Profile Content -->
            <div class="profile-content-card">
                <form method="POST" action="../profile.php?tab=edit" enctype="multipart/form-data" class="profile-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="form-group">
                        <label for="nama_lengkap">Nama Lengkap</label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control" value="<?php echo htmlspecialchars($user->nama_lengkap ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user->email ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="avatar">Avatar</label>
                        <input type="file" id="avatar" name="avatar" class="form-control" accept="image/*">
                        <small class="form-text">Format: JPG, PNG, GIF. Maks: 2MB</small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <?php icon('save', 16); ?> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
