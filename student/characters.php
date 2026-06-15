<?php
require_once '../config/config.php';
requireLogin();
require_once '../config/language.php';
require_once '../includes/icons.php';
require_once '../includes/rpg_system.php';

$page_title       = 'Achievements';
$page_description = 'Kumpulkan trophy achievement dengan terus belajar dan naik level.';
$page_css         = ['pages/dashboard.css', 'sidebar-island.css', 'dashboard-override.css', 'rpg-system.css'];
$body_class       = getThemeClass();

require_once '../models/User.php';
require_once '../config/database.php';

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
    <?php require_once '../includes/head.php'; ?>
    <link rel="stylesheet" href="../assets/css/rpg-system.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body class="<?php echo trim($body_class . ' dashboard-layout'); ?>">
<?php require_once 'navbar.php'; ?>

<div id="certificate-template" style="position:fixed;top:-9999px;left:-9999px;width:1056px;height:816px;background:#fff;font-family:Georgia,'Times New Roman',serif;padding:40px;box-sizing:border-box;">
    <!-- Outer ornate border -->
    <div style="position:absolute;top:16px;left:16px;right:16px;bottom:16px;border:3px solid #b8860b;border-radius:12px;"></div>
    <div style="position:absolute;top:24px;left:24px;right:24px;bottom:24px;border:1px solid #d4a843;border-radius:8px;"></div>
    <!-- Corner ornaments -->
    <div style="position:absolute;top:24px;left:24px;width:50px;height:50px;border-top:4px solid #b8860b;border-left:4px solid #b8860b;border-radius:4px 0 0 0;"></div>
    <div style="position:absolute;top:24px;right:24px;width:50px;height:50px;border-top:4px solid #b8860b;border-right:4px solid #b8860b;border-radius:0 4px 0 0;"></div>
    <div style="position:absolute;bottom:24px;left:24px;width:50px;height:50px;border-bottom:4px solid #b8860b;border-left:4px solid #b8860b;border-radius:0 0 0 4px;"></div>
    <div style="position:absolute;bottom:24px;right:24px;width:50px;height:50px;border-bottom:4px solid #b8860b;border-right:4px solid #b8860b;border-radius:0 0 4px 0;"></div>

    <!-- Content -->
    <div style="text-align:center;position:relative;z-index:1;padding:50px 60px 40px;display:flex;flex-direction:column;align-items:center;min-height:100%;box-sizing:border-box;">
        <!-- Top watermark -->
        <div style="font-size:56px;margin-bottom:4px;line-height:1;">🏆</div>

        <!-- Organization name -->
        <div style="font-size:22px;font-weight:700;color:#8b6914;letter-spacing:4px;text-transform:uppercase;margin-bottom:4px;"><?php echo APP_NAME; ?></div>
        <div style="font-size:11px;color:#a0883a;letter-spacing:6px;text-transform:uppercase;margin-bottom:16px;">— Learning Platform —</div>

        <!-- Decorative divider -->
        <div style="display:flex;align-items:center;gap:16px;width:300px;margin-bottom:20px;">
            <div style="flex:1;height:1px;background:linear-gradient(90deg,transparent,#b8860b);"></div>
            <div style="width:6px;height:6px;background:#b8860b;transform:rotate(45deg);"></div>
            <div style="flex:1;height:1px;background:linear-gradient(90deg,#b8860b,transparent);"></div>
        </div>

        <!-- Certificate title -->
        <div style="font-size:13px;color:#6b5b2e;letter-spacing:3px;text-transform:uppercase;margin-bottom:4px;">Certificate of Achievement</div>
        <div style="font-size:28px;font-weight:700;color:#1a1a2e;margin-bottom:24px;font-family:Georgia,'Times New Roman',serif;">SERTIFIKAT</div>

        <!-- Body text -->
        <div style="font-size:15px;color:#555;margin-bottom:6px;line-height:1.6;">Diberikan kepada</div>

        <!-- Recipient name -->
        <div id="cert-name" style="font-size:36px;font-weight:800;color:#1c1917;margin-bottom:12px;font-family:Georgia,'Times New Roman',serif;border-bottom:2px solid #e8d5a3;padding-bottom:8px;display:inline-block;"><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></div>

        <div style="font-size:14px;color:#555;margin-bottom:4px;">Atas pencapaiannya sebagai</div>

        <!-- Achievement name -->
        <div id="cert-achievement" style="font-size:22px;font-weight:700;color:#b8860b;margin:4px 0 20px;font-style:italic;font-family:Georgia,'Times New Roman',serif;"></div>

        <div style="font-size:13px;color:#777;margin-bottom:20px;max-width:500px;line-height:1.5;">Dengan ini dinyatakan bahwa yang bersangkutan telah berhasil menyelesaikan seluruh persyaratan dan dinyatakan kompeten dalam bidang tersebut.</div>

        <!-- Certificate number & date -->
        <div style="font-size:11px;color:#999;margin-bottom:24px;">
            <span id="cert-number"></span>
            <span style="margin:0 10px;">|</span>
            <span>Diterbitkan: <span id="cert-date"></span></span>
        </div>

        <!-- Signatures -->
        <div style="display:flex;justify-content:space-between;width:100%;max-width:700px;margin-top:auto;padding-top:20px;">
            <div style="text-align:center;flex:1;">
                <div style="font-family:'Brush Script MT',cursive;font-size:28px;color:#333;margin-bottom:-6px;min-height:40px;">A. Santoso</div>
                <div style="width:160px;height:1px;background:#b8860b;margin:4px auto;"></div>
                <div style="font-size:11px;color:#666;font-weight:600;margin-top:4px;">Direktur Utama</div>
                <div style="font-size:10px;color:#999;"><?php echo APP_NAME; ?></div>
            </div>
            <div style="text-align:center;flex:1;">
                <div style="font-family:'Brush Script MT',cursive;font-size:28px;color:#333;margin-bottom:-6px;min-height:40px;">D. Wijaya</div>
                <div style="width:160px;height:1px;background:#b8860b;margin:4px auto;"></div>
                <div style="font-size:11px;color:#666;font-weight:600;margin-top:4px;">Kepala Divisi Akademik</div>
                <div style="font-size:10px;color:#999;"><?php echo APP_NAME; ?></div>
            </div>
        </div>

        <!-- Gold seal -->
        <div style="position:absolute;bottom:60px;right:50px;width:80px;height:80px;border-radius:50%;border:3px solid #b8860b;display:flex;align-items:center;justify-content:center;flex-direction:column;background:linear-gradient(135deg, #fef3c7, #fde68a);">
            <div style="font-size:18px;">🏅</div>
            <div style="font-size:7px;font-weight:700;color:#92400e;text-transform:uppercase;letter-spacing:1px;margin-top:2px;">Verified</div>
        </div>
    </div>
</div>

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
                    <div class="ach-earned-badge" style="background:linear-gradient(135deg,#3B82F6,#2563EB);">✦ ACTIVE</div>
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
                    <button onclick="downloadAchievementCert('<?php echo htmlspecialchars($cls['name']); ?>')" class="ach-card-status ach-status-unlocked" style="margin-top:6px;background:linear-gradient(135deg,#d97706,#b45309);font-size:0.75rem;">📜 Download Certificate</button>
                    <?php elseif ($unlocked): ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="activate_rank">
                        <input type="hidden" name="rank_slug" value="<?php echo $slug; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <button type="submit" class="ach-card-status ach-status-unlocked">Activate Rank</button>
                    </form>
                    <button onclick="downloadAchievementCert('<?php echo htmlspecialchars($cls['name']); ?>')" class="ach-card-status ach-status-unlocked" style="margin-top:6px;background:linear-gradient(135deg,#d97706,#b45309);font-size:0.75rem;">📜 Download Certificate</button>
                    <?php else: ?>
                    <div class="ach-card-status ach-status-locked">🔒 Locked</div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
</div>

<?php include '../includes/loading.php'; ?>
<?php include '../includes/toast.php'; ?>
    <script src="../assets/js/navbar.js"></script>
<script>
window.jsPDF = window.jspdf.jsPDF;

function downloadAchievementCert(achievementName) {
    const template = document.getElementById('certificate-template');

    document.getElementById('cert-achievement').textContent = achievementName;
    const now = new Date();
    document.getElementById('cert-date').textContent = now.toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });
    document.getElementById('cert-number').textContent = 'No. Sertifikat: ' + now.getFullYear() + '/PRZ/ACH/' + String(now.getTime()).slice(-6);

    template.style.position = 'absolute';
    template.style.top = '0';
    template.style.left = '0';
    template.style.zIndex = '-9999';

    html2canvas(template, {
        scale: 2,
        useCORS: true,
        backgroundColor: '#ffffff',
        logging: false
    }).then(canvas => {
        template.style.position = 'fixed';
        template.style.top = '-9999px';
        template.style.left = '-9999px';

        const imgData = canvas.toDataURL('image/png');
        const pdf = new jsPDF({
            orientation: 'landscape',
            unit: 'px',
            format: [1056, 816]
        });

        pdf.addImage(imgData, 'PNG', 0, 0, 1056, 816);
        pdf.save('Sertifikat-' + achievementName.replace(/[^a-zA-Z0-9]/g, '-') + '.pdf');

        if (typeof showToast === 'function') {
            showToast('Certificate berhasil diunduh!', 'success');
        }
    }).catch(error => {
        console.error('Error generating certificate:', error);
        if (typeof showToast === 'function') {
            showToast('Gagal membuat certificate', 'error');
        }
    });
}
</script>
</body>
</html>
