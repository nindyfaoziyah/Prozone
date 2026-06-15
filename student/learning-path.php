<?php
require_once '../config/config.php';
requireLogin();
require_once '../includes/icons.php';

$page_title       = 'Learning Path';
$page_description = 'Pilih role tujuanmu, lalu ikuti jalur belajar RPG.';
$page_css         = ['sidebar-island.css', 'dashboard-override.css', 'pages/learning-path.css'];
$body_class       = trim(getThemeClass() . ' dashboard-layout rpg-wrapper');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once '../includes/head.php'; ?>
    <style>
        /* Dark RPG theme for wrapper */
        .rpg-wrapper {
            background: #0B0D1E !important;
        }
        .rpg-wrapper .dashboard-content {
            max-width: 100%;
            padding: 0;
        }
        .rpg-wrapper .page-wrapper {
            padding: 0;
            max-width: 100%;
        }
        .rpg-wrapper .dashboard-header {
            background: linear-gradient(135deg, rgba(99,102,241,0.12) 0%, rgba(139,92,246,0.08) 50%, rgba(6,182,212,0.04) 100%);
            border: 1px solid rgba(99,102,241,0.15);
            border-radius: 0;
            padding: 20px 28px;
            margin: 0 0 2px 0;
        }
        .rpg-wrapper .dashboard-header::before {
            background: linear-gradient(90deg, transparent 0%, #818CF8 20%, #A78BFA 50%, #2DD4BF 80%, transparent 100%);
            height: 2px;
        }
        .rpg-wrapper .dashboard-header::after {
            background: radial-gradient(circle, rgba(99,102,241,0.08) 0%, transparent 70%);
        }
        .rpg-wrapper .dashboard-header h1 {
            background: linear-gradient(135deg, #E2E8F0 0%, #A5B4FC 50%, #2DD4BF 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 800;
        }
        .rpg-wrapper .dashboard-header p {
            color: #64748B;
        }
        .rpg-wrapper .btn-secondary {
            background: rgba(99,102,241,0.1);
            border: 1px solid rgba(99,102,241,0.2);
            color: #A5B4FC;
        }
        .rpg-wrapper .btn-secondary:hover {
            background: rgba(99,102,241,0.2);
            border-color: rgba(99,102,241,0.4);
        }

        /* Iframe container */
        .rpg-iframe-container {
            position: relative;
            width: 100%;
            min-height: calc(100vh - 100px);
            background: #0B0D1E;
        }
        .rpg-iframe-container iframe {
            width: 100%;
            height: calc(100vh - 100px);
            border: none;
            display: block;
        }

        /* Clean up heading icon color */
        .rpg-wrapper .dashboard-header .icon-map {
            color: #818CF8;
        }
    </style>
</head>
<body class="<?php echo $body_class; ?>">
<?php require_once 'navbar.php'; ?>

<div class="page-wrapper dashboard-main-container" style="padding:0;max-width:100%;background:#0B0D1E">
    <div class="dashboard-content" style="max-width:100%;padding:0">
        <div class="dashboard-header">
            <div>
                <h1><span style="color:#818CF8;background:none;-webkit-text-fill-color:initial">🗺️</span> Learning Path</h1>
                <p class="greeting-sub">Pilih role tujuanmu, lalu ikuti jalur belajar RPG.</p>
            </div>
            <div class="dash-header-actions">
                <a href="../courses.php" class="btn btn-secondary">
                    📚 Semua Kursus
                </a>
            </div>
        </div>

        <div class="rpg-iframe-container">
            <iframe src="../learning-path.php?embed=1" loading="lazy"></iframe>
        </div>
    </div>
</div>
</body>
</html>
