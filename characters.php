<?php
require_once 'config/config.php';
requireLogin();
require_once 'config/language.php';
require_once 'includes/icons.php';
require_once 'includes/rpg_system.php';

$page_title       = 'Achievements';
$page_description = 'Kumpulkan trophy achievement dengan terus belajar dan naik level.';
$page_css         = ['pages/dashboard.css', 'sidebar-island.css', 'dashboard-override.css', 'rpg-system.css'];
$body_class       = getThemeClass();

require_once 'models/User.php';
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];

// Handle rank activation
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'activate_rank') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_message'] = 'Token tidak valid.';
        $_SESSION['flash_type'] = 'error';
        header('Location: characters.php');
        exit;
    }
    $slug = sanitizeInput($_POST['rank_slug'] ?? '');
    if (!isValidClass($slug)) {
        $_SESSION['flash_message'] = 'Rank tidak valid.';
        $_SESSION['flash_type'] = 'error';
        header('Location: characters.php');
        exit;
    }
    $stmt = $db->prepare("SELECT level, total_xp FROM users WHERE id = :uid");
    $stmt->bindParam(':uid', $user_id);
    $stmt->execute();
    $ud = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!isClassUnlocked($slug, (int)($ud['level'] ?? 1), (int)($ud['total_xp'] ?? 0))) {
        $_SESSION['flash_message'] = 'Rank ini belum terbuka!';
        $_SESSION['flash_type'] = 'error';
        header('Location: characters.php');
        exit;
    }
    $stmt2 = $db->prepare("UPDATE users SET character_class = :cls WHERE id = :uid");
    $stmt2->bindParam(':cls', $slug);
    $stmt2->bindParam(':uid', $user_id);
    $stmt2->execute();
    $_SESSION['character_class'] = $slug;
    $_SESSION['flash_message'] = 'Rank berhasil diaktifkan!';
    $_SESSION['flash_type'] = 'success';
    header('Location: characters.php');
    exit;
}

// Fetch user data
$stmt = $db->prepare("SELECT total_xp, level, character_class FROM users WHERE id = :uid");
$stmt->bindParam(':uid', $user_id);
$stmt->execute();
$user_data     = $stmt->fetch(PDO::FETCH_ASSOC);
$user_xp       = (int)($user_data['total_xp'] ?? 0);
$user_level    = (int)($user_data['level'] ?? 1);
$active_rank   = $user_data['character_class'] ?? 'code-warrior';

// Count earned
$earned_count = 0;
foreach (RPG_CLASSES as $slug => $cls) {
    if (isClassUnlocked($slug, $user_level, $user_xp)) $earned_count++;
}
$total_achievements = count(RPG_CLASSES);
$progress_percent = $total_achievements > 0 ? round(($earned_count / $total_achievements) * 100) : 0;

// Flash message
$message = '';
$message_type = '';
if (isset($_SESSION['flash_message'])) {
    $message      = $_SESSION['flash_message'];
    $message_type = $_SESSION['flash_type'] ?? 'success';
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
}

// Next unlock
$next = getNextUnlock($user_level, $user_xp);

// Map class rarity to trophy tier rarity
function mapRarity($rarity) {
    $map = [
        'common' => 'bronze',
        'uncommon' => 'silver',
        'rare' => 'gold',
        'epic' => 'platinum',
        'legendary' => 'diamond',
    ];
    return $map[$rarity] ?? 'bronze';
}

// Trophy emoji per tier
function trophyIcon($rarity) {
    $icons = [
        'common' => '🥉',
        'uncommon' => '🥈',
        'rare' => '🥇',
        'epic' => '🏆',
        'legendary' => '👑',
    ];
    return $icons[$rarity] ?? '🏅';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once 'includes/head.php'; ?>
    <link rel="stylesheet" href="assets/css/rpg-system.css">
</head>
<body class="<?php echo trim($body_class . ' dashboard-layout'); ?>">
<?php require_once 'navbar.php'; ?>

<div class="page-wrapper dashboard-main-container">
    <div class="dashboard-content">

        <?php if ($message): ?>
        <div class="rpg-alert rpg-alert-<?php echo $message_type; ?>">
            <span><?php echo $message_type === 'success' ? '✅' : '❌'; ?></span>
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="achievement-header">
            <div class="ach-header-text">
                <div class="page-eyebrow">🏆 Achievement System</div>
                <h1>Trophy <span class="text-accent-gradient">Collection</span></h1>
                <p>Kumpulkan trophy dengan terus belajar dan naik level. Setiap trophy menandai pencapaian baru dalam perjalanan coding-mu!</p>
            </div>
            <div class="ach-header-stats">
                <div class="ach-header-stat">
                    <div class="ach-header-stat-val"><?php echo $earned_count; ?>/<?php echo $total_achievements; ?></div>
                    <div class="ach-header-stat-lbl">Earned</div>
                </div>
                <div class="ach-header-stat">
                    <div class="ach-header-stat-val"><?php echo $user_level; ?></div>
                    <div class="ach-header-stat-lbl">Level</div>
                </div>
                <div class="ach-header-stat">
                    <div class="ach-header-stat-val"><?php echo number_format($user_xp); ?></div>
                    <div class="ach-header-stat-lbl">Total XP</div>
                </div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="ach-progress-section">
            <div class="ach-progress-header">
                <h3>🏅 Overall Progress</h3>
                <strong><?php echo $progress_percent; ?>%</strong>
            </div>
            <div class="ach-progress-track">
                <div class="ach-progress-fill" style="width: <?php echo $progress_percent; ?>%"></div>
            </div>
        </div>

        <!-- Current Rank Banner -->
        <?php $activeData = getClassData($active_rank); ?>
        <div class="ach-current-rank" style="--ach-accent: <?php echo $activeData['gradient']; ?>;">
            <div class="ach-rank-icon-wrap">
                <img src="<?php echo htmlspecialchars($activeData['image']); ?>" alt="<?php echo htmlspecialchars($activeData['name']); ?>" class="ach-rank-icon">
            </div>
            <div class="ach-rank-details">
                <div class="ach-rank-header">
                    <span class="rarity-badge rarity-<?php echo mapRarity($activeData['rarity']); ?>"><?php echo $activeData['rarity_label']; ?></span>
                    <span class="ach-rank-label">Current Rank</span>
                </div>
                <div class="ach-rank-name"><?php echo $activeData['badge']; ?> <?php echo htmlspecialchars($activeData['name']); ?></div>
                <div class="ach-rank-subtitle">"<?php echo htmlspecialchars($activeData['title']); ?>"</div>
                <div class="ach-rank-desc"><?php echo htmlspecialchars($activeData['description']); ?></div>
            </div>
            <?php if ($next): ?>
            <div class="ach-next-unlock">
                <div class="ach-next-card">
                    <div class="ach-next-label">Next Trophy</div>
                    <div class="ach-next-badge"><?php echo trophyIcon($next['rarity']); ?></div>
                    <div class="ach-next-name"><?php echo htmlspecialchars($next['name']); ?></div>
                    <div class="ach-next-req">Lv.<?php echo $next['level_required']; ?> · <?php echo number_format($next['xp_required']); ?> XP</div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Achievement Grid -->
        <div class="ach-section-title">🏆 All Trophies</div>
        <div class="ach-grid">
            <?php foreach (RPG_CLASSES as $slug => $cls):
                $unlocked = isClassUnlocked($slug, $user_level, $user_xp);
                $is_active = ($slug === $active_rank);
                $is_met = ($user_level >= $cls['level_required'] && $user_xp >= $cls['xp_required']);
            ?>
            <div class="ach-card <?php echo $unlocked ? 'earned' : 'locked'; ?> <?php echo $is_active ? 'active-rank' : ''; ?>">
                <div class="ach-card-band" style="background:<?php echo $cls['gradient']; ?>;"></div>
                <div class="ach-card-image">
                    <div class="ach-trophy-icon"><?php echo trophyIcon($cls['rarity']); ?></div>
                    <?php if ($is_active): ?>
                    <div class="ach-earned-badge" style="background:linear-gradient(135deg,#6C4CFD,#5A3FF5);">✦ ACTIVE</div>
                    <?php elseif ($unlocked): ?>
                    <div class="ach-earned-badge">✓ EARNED</div>
                    <?php endif; ?>
                    <?php if (!$unlocked): ?>
                    <div class="ach-lock-overlay">
                        <div class="ach-lock-icon">🔒</div>
                        <div class="ach-lock-text">Lv.<?php echo $cls['level_required']; ?> · <?php echo number_format($cls['xp_required']); ?> XP</div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="ach-card-body">
                    <div class="ach-card-rarity"><span class="rarity-badge rarity-<?php echo mapRarity($cls['rarity']); ?>"><?php echo $cls['rarity_label']; ?></span></div>
                    <div class="ach-card-name"><?php echo htmlspecialchars($cls['name']); ?></div>
                    <div class="ach-card-title">"<?php echo htmlspecialchars($cls['title']); ?>"</div>
                    <div class="ach-card-desc"><?php echo htmlspecialchars($cls['description']); ?></div>
                    <div class="ach-card-req <?php echo $is_met ? 'met' : ''; ?>">
                        <?php icon('star', 12); ?>
                        <?php if ($is_met): ?>✅<?php endif; ?>
                        Lv.<span><?php echo $cls['level_required']; ?></span> · <span><?php echo number_format($cls['xp_required']); ?></span> XP
                    </div>

                    <?php if ($is_active): ?>
                    <div class="ach-card-status ach-status-active">✦ Current Rank</div>
                    <?php elseif ($unlocked): ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="activate_rank">
                        <input type="hidden" name="rank_slug" value="<?php echo $slug; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <button type="submit" class="ach-card-status ach-status-unlocked">Activate Rank</button>
                    </form>
                    <?php else: ?>
                    <div class="ach-card-status ach-status-locked">🔒 Locked</div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
</div>

<?php include 'includes/loading.php'; ?>
<?php include 'includes/toast.php'; ?>
<script src="assets/js/navbar.js"></script>
</body>
</html>
