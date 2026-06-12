<?php
require_once 'config/config.php';
requireLogin();
require_once 'config/language.php';
require_once 'includes/icons.php';
require_once 'includes/rpg_system.php';

$page_title       = 'Character Collection';
$page_description = 'Pilih dan kumpulkan karakter RPG berdasarkan progress belajar kamu.';
$page_css         = ['pages/dashboard.css', 'sidebar-island.css', 'dashboard-override.css', 'rpg-system.css'];
$body_class       = getThemeClass();

require_once 'models/User.php';
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];

// Handle character activation
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'activate_character') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_message'] = 'Token tidak valid.';
        $_SESSION['flash_type'] = 'error';
        header('Location: characters.php');
        exit;
    }
    $slug = sanitizeInput($_POST['character_slug'] ?? '');
    if (!isValidClass($slug)) {
        $_SESSION['flash_message'] = 'Karakter tidak valid.';
        $_SESSION['flash_type'] = 'error';
        header('Location: characters.php');
        exit;
    }
    // Verify unlock
    $stmt = $db->prepare("SELECT level, total_xp FROM users WHERE id = :uid");
    $stmt->bindParam(':uid', $user_id);
    $stmt->execute();
    $ud = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!isClassUnlocked($slug, (int)($ud['level'] ?? 1), (int)($ud['total_xp'] ?? 0))) {
        $_SESSION['flash_message'] = 'Karakter ini belum terbuka!';
        $_SESSION['flash_type'] = 'error';
        header('Location: characters.php');
        exit;
    }
    $stmt2 = $db->prepare("UPDATE users SET character_class = :cls WHERE id = :uid");
    $stmt2->bindParam(':cls', $slug);
    $stmt2->bindParam(':uid', $user_id);
    $stmt2->execute();
    $_SESSION['character_class'] = $slug;
    $_SESSION['flash_message'] = 'Karakter berhasil diaktifkan!';
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
$active_class  = $user_data['character_class'] ?? 'code-warrior';

// Count unlocked
$unlocked_count = 0;
foreach (RPG_CLASSES as $slug => $cls) {
    if (isClassUnlocked($slug, $user_level, $user_xp)) $unlocked_count++;
}
$total_count = count(RPG_CLASSES);

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
        <div class="alert-rpg alert-rpg-<?php echo $message_type; ?>" style="margin-bottom:20px; padding:14px 20px; border-radius:14px; display:flex; align-items:center; gap:10px; font-weight:600; font-size:0.9rem; <?php echo $message_type==='success' ? 'background:#F0FDF4;color:#16A34A;border:1px solid #BBF7D0;' : 'background:#FEF2F2;color:#DC2626;border:1px solid #FECACA;'; ?>">
            <?php echo $message_type === 'success' ? '✅' : '❌'; ?> <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="characters-page-header">
            <div class="char-header-text">
                <div class="page-eyebrow">⚔️ RPG Character System</div>
                <h1>Character <span class="text-accent-gradient">Collection</span></h1>
                <p>Kumpulkan karakter dengan terus belajar dan naik level. Setiap karakter membuka judul dan identitas baru sebagai seorang developer!</p>
            </div>
            <div class="char-header-stats">
                <div class="char-header-stat">
                    <div class="char-header-stat-val"><?php echo $unlocked_count; ?>/<?php echo $total_count; ?></div>
                    <div class="char-header-stat-lbl">Unlocked</div>
                </div>
                <div class="char-header-stat">
                    <div class="char-header-stat-val"><?php echo $user_level; ?></div>
                    <div class="char-header-stat-lbl">Level</div>
                </div>
                <div class="char-header-stat">
                    <div class="char-header-stat-val"><?php echo number_format($user_xp); ?></div>
                    <div class="char-header-stat-lbl">Total XP</div>
                </div>
            </div>
        </div>

        <!-- Active Character Banner -->
        <?php $activeData = getClassData($active_class); ?>
        <div class="profile-char-info-card" style="margin-bottom:28px; --card-gradient: <?php echo $activeData['gradient']; ?>; border-top: 4px solid <?php echo $activeData['color']; ?>;">
            <img src="<?php echo htmlspecialchars($activeData['image']); ?>" alt="<?php echo htmlspecialchars($activeData['name']); ?>" class="profile-char-img">
            <div class="profile-char-details">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                    <span class="rarity-badge rarity-<?php echo $activeData['rarity']; ?>"><?php echo $activeData['rarity_label']; ?></span>
                    <span style="font-size:0.75rem;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:0.06em;">Active Character</span>
                </div>
                <div class="profile-char-name"><?php echo $activeData['badge']; ?> <?php echo htmlspecialchars($activeData['name']); ?></div>
                <div class="profile-char-subtitle">"<?php echo htmlspecialchars($activeData['title']); ?>"</div>
                <div class="profile-char-desc"><?php echo htmlspecialchars($activeData['description']); ?></div>
            </div>
            <?php if ($next): ?>
            <div style="text-align:center;padding:16px;background:white;border-radius:16px;min-width:140px;border:1px solid #E2E8F0;">
                <div style="font-size:0.68rem;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:8px;">Next Unlock</div>
                <div style="font-size:1.6rem;margin-bottom:4px;"><?php echo $next['badge']; ?></div>
                <div style="font-size:0.8rem;font-weight:700;color:#0F172A;"><?php echo htmlspecialchars($next['name']); ?></div>
                <div style="font-size:0.7rem;color:#6C4CFD;font-weight:600;margin-top:4px;">Lv.<?php echo $next['level_required']; ?> · <?php echo number_format($next['xp_required']); ?> XP</div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Character Grid -->
        <h2 style="font-size:1.15rem;font-weight:800;color:#0F172A;margin-bottom:18px;">Semua Karakter</h2>
        <div class="char-collection-grid">
            <?php foreach (RPG_CLASSES as $slug => $cls):
                $unlocked = isClassUnlocked($slug, $user_level, $user_xp);
                $is_active = ($slug === $active_class);
            ?>
            <div class="char-card <?php echo $unlocked ? 'unlocked' : 'locked'; ?> <?php echo $is_active ? 'active' : ''; ?>">
                <div class="char-card-band" style="background:<?php echo $cls['gradient']; ?>;"></div>
                <div class="char-card-image">
                    <img src="<?php echo htmlspecialchars($cls['image']); ?>" alt="<?php echo htmlspecialchars($cls['name']); ?>">
                    <?php if ($is_active): ?>
                    <div class="char-active-badge">✦ ACTIVE</div>
                    <?php endif; ?>
                    <?php if (!$unlocked): ?>
                    <div class="char-lock-overlay">
                        <div class="char-lock-icon">🔒</div>
                        <div class="char-lock-text">Lv.<?php echo $cls['level_required']; ?> · <?php echo number_format($cls['xp_required']); ?> XP</div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="char-card-body">
                    <div class="char-card-emoji"><?php echo $cls['badge']; ?></div>
                    <div class="char-card-name"><?php echo htmlspecialchars($cls['name']); ?></div>
                    <div class="char-card-title">"<?php echo htmlspecialchars($cls['title']); ?>"</div>
                    <div style="margin-bottom:10px;"><span class="rarity-badge rarity-<?php echo $cls['rarity']; ?>"><?php echo $cls['rarity_label']; ?></span></div>
                    <div class="char-card-req">
                        <?php icon('star', 12); ?>
                        Lv.<span><?php echo $cls['level_required']; ?></span> · <span><?php echo number_format($cls['xp_required']); ?></span> XP
                    </div>
                    <div class="char-card-actions">
                        <?php if ($is_active): ?>
                        <div class="btn-active-state">✦ Aktif</div>
                        <?php elseif ($unlocked): ?>
                        <form method="POST" style="flex:1;">
                            <input type="hidden" name="action" value="activate_character">
                            <input type="hidden" name="character_slug" value="<?php echo $slug; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <button type="submit" class="btn-activate" style="width:100%;">Aktifkan</button>
                        </form>
                        <?php else: ?>
                        <div class="btn-locked-state">🔒 Terkunci</div>
                        <?php endif; ?>
                    </div>
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
