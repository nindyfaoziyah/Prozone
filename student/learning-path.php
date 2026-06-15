<?php
require_once '../config/config.php';
requireLogin();
require_once '../includes/icons.php';

$page_title       = 'Learning Path';
$page_description = 'Pilih role tujuanmu, lalu ikuti jalur belajar yang sesuai.';
$page_css         = ['sidebar-island.css', 'dashboard-override.css', 'pages/learning-path.css'];
$body_class       = trim(getThemeClass() . ' dashboard-layout');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once '../includes/head.php'; ?>
</head>
<body class="<?php echo $body_class; ?>">
<?php require_once 'navbar.php'; ?>

<div class="page-wrapper">
    <div class="dashboard-content">
        <div class="dashboard-header">
            <div>
                <h1><?php icon('map', 24); ?> Learning Path</h1>
                <p class="greeting-sub">Pilih role tujuanmu, lalu ikuti jalur belajar yang sesuai.</p>
            </div>
            <div class="dash-header-actions">
                <a href="../courses.php" class="btn btn-secondary">
                    <?php icon('book', 16); ?> Semua Kursus
                </a>
            </div>
        </div>

        <div class="learning-path-placeholder">
            <iframe src="../learning-path.php" frameborder="0" class="full-iframe"></iframe>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
