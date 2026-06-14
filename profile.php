<?php
require_once 'config/config.php';
requireLogin();
require_once 'config/language.php';
require_once 'includes/icons.php';
require_once 'includes/FileUpload.php';
require_once 'includes/rpg_system.php';

require_once 'models/User.php';
require_once 'models/Achievement.php';
require_once 'models/Course.php';
require_once 'models/Enrollment.php';
require_once 'models/UserProgress.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$user->id = $_SESSION['user_id'];
$user->readOne();

// Get active tab from URL
$active_tab = $_GET['tab'] ?? 'edit';

// Get flash message from session (PRG pattern)
$message = '';
$message_type = '';
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    $message_type = $_SESSION['flash_type'] ?? 'success';
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
}

// CSRF Check for all POST requests
if ($_POST) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_message'] = 'Sesi tidak valid (CSRF Token Error). Silakan refresh halaman.';
        $_SESSION['flash_type'] = 'error';
        header('Location: profile.php?tab=' . $active_tab);
        exit;
    }
}

// Handle update profile
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $user->id = $_SESSION['user_id'];
    $user->nama_lengkap = sanitizeInput($_POST['nama_lengkap']);
    $user->email = sanitizeInput($_POST['email']);
    
    $upload_error = false;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $upload = FileUpload::uploadAvatar($_FILES['avatar'], $_SESSION['user_id']);
        
        if ($upload['success']) {
            $user->avatar = $upload['filename'];
            $_SESSION['avatar'] = $upload['filename'];
        } else {
            $_SESSION['flash_message'] = $upload['error'];
            $_SESSION['flash_type'] = 'error';
            $upload_error = true;
        }
    }
    
    if (!$upload_error) {
        if ($user->update()) {
            $_SESSION['nama_lengkap'] = $user->nama_lengkap;
            $_SESSION['email'] = $user->email;
            $_SESSION['flash_message'] = 'Profile berhasil diperbarui!';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Gagal memperbarui profile!';
            $_SESSION['flash_type'] = 'error';
        }
    }
    header('Location: profile.php?tab=' . $active_tab);
    exit;
}

// Handle change password
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    
    if ($user->login($_SESSION['username'], $old_password)) {
        if ($user->changePassword($new_password)) {
            $_SESSION['flash_message'] = 'Password berhasil diubah!';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Gagal mengubah password!';
            $_SESSION['flash_type'] = 'error';
        }
    } else {
        $_SESSION['flash_message'] = 'Password lama salah!';
        $_SESSION['flash_type'] = 'error';
    }
    header('Location: profile.php?tab=' . $active_tab);
    exit;
}

// Handle change language
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'change_language') {
    $language = sanitizeInput($_POST['language']);
    setLanguage($language);
    $_SESSION['flash_message'] = 'Bahasa berhasil diubah!';
    $_SESSION['flash_type'] = 'success';
    header('Location: profile.php?tab=settings');
    exit;
}

// Handle change theme
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'change_theme') {
    $theme = sanitizeInput($_POST['theme']);
    $_SESSION['theme'] = $theme;
    
    $query = "UPDATE users SET theme_preference = :theme WHERE id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':theme', $theme);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    
    $_SESSION['flash_message'] = 'Tema berhasil diubah!';
    $_SESSION['flash_type'] = 'success';
    header('Location: profile.php?tab=settings');
    exit;
}

// Get current preferences
$query_pref = "SELECT language_preference, theme_preference, created_at, character_class FROM users WHERE id = :user_id";
$stmt_pref = $db->prepare($query_pref);
$stmt_pref->bindParam(':user_id', $_SESSION['user_id']);
$stmt_pref->execute();
$preferences = $stmt_pref->fetch(PDO::FETCH_ASSOC);
$current_language = $preferences['language_preference'] ?? 'id';
$current_theme = $preferences['theme_preference'] ?? 'dark';
$join_date = $preferences['created_at'] ?? date('Y-m-d');
$active_char_slug = $preferences['character_class'] ?? 'code-warrior';
$active_char_data = getClassData($active_char_slug);

// Handle select_character POST
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'select_character') {
    $slug = sanitizeInput($_POST['character_slug'] ?? '');
    if (isValidClass($slug) && isClassUnlocked($slug, $user_xp > 0 ? (int)floor($user_xp / 100) + 1 : 1, $user_xp)) {
        $s = $db->prepare("UPDATE users SET character_class = :cls WHERE id = :uid");
        $s->bindParam(':cls', $slug);
        $s->bindParam(':uid', $_SESSION['user_id']);
        $s->execute();
        $_SESSION['character_class'] = $slug;
        $active_char_slug = $slug;
        $active_char_data = getClassData($slug);
        $_SESSION['flash_message'] = 'Karakter berhasil diaktifkan!';
        $_SESSION['flash_type'] = 'success';
    }
    header('Location: profile.php?tab=character');
    exit;
}

// Get data for Achievements tab
$achievement = new Achievement($db);
$achievements_stmt = $achievement->getUserAchievements($_SESSION['user_id']);
$achievements = [];
while ($row = $achievements_stmt->fetch(PDO::FETCH_ASSOC)) {
    $achievements[] = $row;
}
$total_achievements = count($achievements);
$earned_count = 0;
foreach ($achievements as $ach) {
    if ($ach['earned_at']) {
        $earned_count++;
    }
}
$progress_percent = $total_achievements > 0 ? ($earned_count / $total_achievements) * 100 : 0;

// Get data for Analytics tab
$course = new Course($db);
$enrollment = new Enrollment($db);
$user_progress = new UserProgress($db);
$user_id = $_SESSION['user_id'];

$enrollment_stmt = $enrollment->getUserEnrollments($user_id);
$enrolled_courses = [];
$total_progress = 0;
$completed_courses = 0;
$total_lessons_completed = 0;

while ($row = $enrollment_stmt->fetch(PDO::FETCH_ASSOC)) {
    $enrolled_courses[] = $row;
    $total_progress += $row['progress_percent'];
    if ($row['status'] == 'completed') {
        $completed_courses++;
    }
    $total_lessons_completed += $row['completed_lessons'];
}

$avg_progress = count($enrolled_courses) > 0 ? $total_progress / count($enrolled_courses) : 0;

$query_user = "SELECT total_xp, level FROM users WHERE id = :user_id";
$stmt_user = $db->prepare($query_user);
$stmt_user->bindParam(':user_id', $user_id);
$stmt_user->execute();
$user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);
$user_xp = $user_data['total_xp'] ?? 0;
$user_level = $user_data['level'] ?? 1;


$xp_for_current_level = ($user_level - 1) * 100;
$xp_for_next_level = $user_level * 100;
$xp_progress = $xp_for_next_level - $xp_for_current_level;
$xp_current = $user_xp - $xp_for_current_level;
$xp_percent = $xp_progress > 0 ? ($xp_current / $xp_progress) * 100 : 0;

$achievements_stmt2 = $achievement->getUserAchievements($user_id);
$total_achievements_analytics = 0;
$earned_achievements = 0;
while ($row = $achievements_stmt2->fetch(PDO::FETCH_ASSOC)) {
    $total_achievements_analytics++;
    if ($row['earned_at']) {
        $earned_achievements++;
    }
}

$query_streak = "SELECT COUNT(DISTINCT DATE(completed_at)) as streak_days
                 FROM user_progress
                 WHERE user_id = :user_id 
                 AND status = 'completed'
                 AND completed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$stmt_streak = $db->prepare($query_streak);
$stmt_streak->bindParam(':user_id', $user_id);
$stmt_streak->execute();
$streak_data = $stmt_streak->fetch(PDO::FETCH_ASSOC);
$learning_streak = $streak_data['streak_days'] ?? 0;

// Get leaderboard rank
$query_rank = "SELECT COUNT(*) + 1 as user_rank FROM users WHERE total_xp > (SELECT total_xp FROM users WHERE id = :user_id) AND role = 'student'";
$stmt_rank = $db->prepare($query_rank);
$stmt_rank->bindParam(':user_id', $user_id);
$stmt_rank->execute();
$rank_data = $stmt_rank->fetch(PDO::FETCH_ASSOC);
$user_rank = $rank_data['user_rank'] ?? 1;

// Get data for Certificates tab
$query_cert = "SELECT c.*, e.completed_at, e.progress_percent
              FROM enrollments e
              JOIN courses c ON e.course_id = c.id
              WHERE e.user_id = :user_id 
              AND e.status = 'completed'
              ORDER BY e.completed_at DESC";
$stmt_cert = $db->prepare($query_cert);
$stmt_cert->bindParam(':user_id', $_SESSION['user_id']);
$stmt_cert->execute();
$certificates = [];
while ($row = $stmt_cert->fetch(PDO::FETCH_ASSOC)) {
    $certificates[] = $row;
}
?>

<!DOCTYPE html>
<html lang="<?php echo $current_language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'includes/favicon.php'; ?>
    <?php include 'includes/seo.php'; echo seo_meta('Profile - ' . APP_NAME, 'Kelola profil dan lihat progress belajar Anda', 'profile, user, settings'); ?>
    <title>Profile - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/dark-theme.css">
    <link rel="stylesheet" href="assets/css/tokens.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/ui-enhancements.css">
    <link rel="stylesheet" href="assets/css/glassmorphism.css">
    <link rel="stylesheet" href="assets/css/components/button.css">
    <link rel="stylesheet" href="assets/css/components/badge.css">
    <style>
        /* Profile Hero Custom Adjustments */
        .profile-xp-bar {
            background: rgba(30, 30, 55, 0.5);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            max-width: 400px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .profile-xp-progress {
            height: 8px;
            background: rgba(20, 184, 166, 0.2);
            border-radius: 4px;
            overflow: hidden;
        }
        .profile-xp-fill {
            height: 100%;
            background: linear-gradient(90deg, #14B8A6, #2DD4BF);
            border-radius: 4px;
            transition: width 1s ease-out;
        }
    </style>
    <style>
        /* Profile Hero Card */
        .profile-hero {
            background: linear-gradient(135deg, 
                rgba(20, 184, 166, 0.15) 0%, 
                rgba(59, 130, 246, 0.1) 50%,
                rgba(236, 72, 153, 0.08) 100%);
            border-radius: 24px;
            padding: 2rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(20, 184, 166, 0.2);
        }
        .profile-hero::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(20, 184, 166, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }
        .profile-hero-content {
            position: relative;
            z-index: 1;
            display: flex;
            gap: 2rem;
            align-items: center;
            flex-wrap: wrap;
        }
        .profile-avatar-section {
            position: relative;
        }
        .profile-avatar-large {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: linear-gradient(135deg, #14B8A6 0%, #2DD4BF 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3.5rem;
            font-weight: bold;
            position: relative;
            box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.3), 0 10px 40px rgba(20, 184, 166, 0.3);
            animation: avatar-glow 3s ease-in-out infinite;
        }
        .profile-avatar-large img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }
        @keyframes avatar-glow {
            0%, 100% { box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.3), 0 10px 40px rgba(20, 184, 166, 0.3); }
            50% { box-shadow: 0 0 0 6px rgba(20, 184, 166, 0.4), 0 10px 50px rgba(20, 184, 166, 0.4); }
        }
        .level-badge {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1rem;
            color: #1a1a2e;
            border: 3px solid #1a1a2e;
            box-shadow: 0 4px 15px rgba(251, 191, 36, 0.4);
        }
        .profile-info-section {
            flex: 1;
            min-width: 250px;
        }
        .profile-name {
            font-size: 2rem;
            font-weight: 800;
            color: #e2e8f0;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .verified-badge {
            background: linear-gradient(135deg, #3b82f6, #60a5fa);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            font-size: 0.65rem;
            font-weight: 600;
        }
        .profile-username {
            color: #14B8A6;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }
        .profile-meta {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        .profile-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #94a3b8;
            font-size: 0.875rem;
        }
        .profile-meta-item svg {
            color: #64748b;
        }
        .profile-xp-bar {
            background: rgba(30, 30, 55, 0.5);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            max-width: 400px;
        }
        .profile-xp-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        .profile-xp-label {
            color: #94a3b8;
            font-size: 0.8rem;
        }
        .profile-xp-value {
            color: #2DD4BF;
            font-weight: 600;
            font-size: 0.8rem;
        }
        .profile-xp-progress {
            height: 8px;
            background: rgba(20, 184, 166, 0.2);
            border-radius: 4px;
            overflow: hidden;
        }
        .profile-xp-fill {
            height: 100%;
            background: linear-gradient(90deg, #14B8A6, #2DD4BF);
            border-radius: 4px;
            transition: width 1s ease-out;
        }
        .profile-quick-stats {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .quick-stat {
            background: rgba(30, 30, 55, 0.5);
            border: 1px solid rgba(20, 184, 166, 0.2);
            border-radius: 12px;
            padding: 1rem 1.25rem;
            text-align: center;
            min-width: 100px;
            transition: all 0.3s ease;
        }
        .quick-stat:hover {
            border-color: rgba(20, 184, 166, 0.4);
            transform: translateY(-2px);
        }
        .quick-stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #2DD4BF 0%, #14B8A6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .quick-stat-label {
            color: #64748b;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 0.25rem;
        }

        /* Tab Navigation */
        .profile-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            background: rgba(30, 30, 55, 0.5);
            padding: 0.5rem;
            border-radius: 16px;
            border: 1px solid rgba(20, 184, 166, 0.1);
            overflow-x: auto;
            scrollbar-width: none;
        }
        .profile-tabs::-webkit-scrollbar {
            display: none;
        }
        .profile-tab {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            background: transparent;
            border: none;
            color: #94a3b8;
            font-weight: 500;
            font-size: 0.9rem;
            cursor: pointer;
            border-radius: 10px;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        .profile-tab:hover {
            color: #e2e8f0;
            background: rgba(20, 184, 166, 0.1);
        }
        .profile-tab.active {
            color: white;
            background: linear-gradient(135deg, #14B8A6, #2DD4BF);
            box-shadow: 0 4px 15px rgba(20, 184, 166, 0.3);
        }
        .profile-tab svg {
            width: 18px;
            height: 18px;
        }

        .tab-content {
            display: none;
            animation: fadeSlideIn 0.4s ease;
        }
        .tab-content.active {
            display: block;
        }
        @keyframes fadeSlideIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Profile Section Card */
        .profile-section {
            background: rgba(30, 30, 55, 0.5);
            backdrop-filter: blur(12px);
            border-radius: 20px;
            padding: 1.75rem;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(20, 184, 166, 0.15);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        .profile-section h2 {
            color: #e2e8f0;
            margin-bottom: 1.5rem;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .profile-section h2 svg {
            color: #14B8A6;
        }

        /* Form Styling */
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            color: #e2e8f0;
            font-weight: 500;
            font-size: 0.9rem;
        }
        .form-label svg {
            color: #14B8A6;
            width: 16px;
            height: 16px;
        }
        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1px solid rgba(20, 184, 166, 0.2);
            border-radius: 12px;
            background: rgba(15, 15, 35, 0.6);
            color: #e2e8f0;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        .form-input:focus {
            outline: none;
            border-color: rgba(20, 184, 166, 0.5);
            box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.1);
        }
        .form-input::placeholder {
            color: #64748b;
        }
        .form-hint {
            color: #64748b;
            font-size: 0.8rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        /* Avatar Upload */
        .avatar-upload-wrapper {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            flex-wrap: wrap;
        }
        .avatar-preview {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #14B8A6 0%, #2DD4BF 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 2rem;
            box-shadow: 0 4px 20px rgba(20, 184, 166, 0.3);
            flex-shrink: 0;
        }
        .avatar-preview img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }
        .avatar-upload-area {
            flex: 1;
            min-width: 200px;
            padding: 1.25rem;
            border: 2px dashed rgba(20, 184, 166, 0.3);
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: rgba(20, 184, 166, 0.05);
        }
        .avatar-upload-area:hover {
            border-color: rgba(20, 184, 166, 0.5);
            background: rgba(20, 184, 166, 0.1);
        }
        .avatar-upload-area input[type="file"] {
            display: none;
        }
        .upload-icon {
            color: #14B8A6;
            margin-bottom: 0.5rem;
        }
        .upload-text {
            color: #94a3b8;
            font-size: 0.85rem;
        }
        .upload-text span {
            color: #14B8A6;
            font-weight: 600;
        }

        /* Password Strength */
        .password-strength {
            margin-top: 0.5rem;
        }
        .strength-bar {
            height: 4px;
            background: rgba(20, 184, 166, 0.2);
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 0.375rem;
        }
        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }
        .strength-fill.weak { width: 25%; background: #ef4444; }
        .strength-fill.fair { width: 50%; background: #f59e0b; }
        .strength-fill.good { width: 75%; background: #3b82f6; }
        .strength-fill.strong { width: 100%; background: #10b981; }
        .strength-text {
            font-size: 0.75rem;
            font-weight: 500;
        }
        .strength-text.weak { color: #ef4444; }
        .strength-text.fair { color: #f59e0b; }
        .strength-text.good { color: #3b82f6; }
        .strength-text.strong { color: #10b981; }

        /* Setting Item */
        .setting-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem;
            background: rgba(15, 15, 35, 0.5);
            border-radius: 14px;
            border: 1px solid rgba(20, 184, 166, 0.1);
            transition: all 0.3s ease;
        }
        .setting-item:hover {
            border-color: rgba(20, 184, 166, 0.3);
        }
        .setting-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .setting-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        .setting-icon.lang { background: linear-gradient(135deg, #3b82f6, #60a5fa); }
        .setting-icon.theme { background: linear-gradient(135deg, #14B8A6, #2DD4BF); }
        .setting-label {
            color: #e2e8f0;
            font-weight: 600;
            font-size: 0.95rem;
        }
        .setting-desc {
            color: #64748b;
            font-size: 0.8rem;
        }
        .toggle-group {
            display: flex;
            gap: 0.375rem;
            background: rgba(15, 15, 35, 0.8);
            padding: 0.25rem;
            border-radius: 10px;
            border: 1px solid rgba(20, 184, 166, 0.2);
        }
        .toggle-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 1rem;
            border: none;
            background: transparent;
            color: #94a3b8;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .toggle-btn svg {
            flex-shrink: 0;
        }
        .toggle-btn.active {
            background: linear-gradient(135deg, #14B8A6, #2DD4BF);
            color: white;
            box-shadow: 0 2px 10px rgba(20, 184, 166, 0.3);
        }

        /* Submit Button */
        .btn-submit {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.75rem;
            background: linear-gradient(135deg, #14B8A6 0%, #2DD4BF 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(20, 184, 166, 0.3);
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(20, 184, 166, 0.4);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-card {
            background: rgba(15, 15, 35, 0.5);
            border: 1px solid rgba(20, 184, 166, 0.15);
            border-radius: 16px;
            padding: 1.25rem;
            text-align: center;
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            border-color: rgba(20, 184, 166, 0.4);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(20, 184, 166, 0.15);
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.75rem;
            color: white;
        }
        .stat-icon.xp { background: linear-gradient(135deg, #14B8A6, #2DD4BF); }
        .stat-icon.courses { background: linear-gradient(135deg, #3b82f6, #60a5fa); }
        .stat-icon.lessons { background: linear-gradient(135deg, #10b981, #34d399); }
        .stat-icon.streak { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
        .stat-icon.achievements { background: linear-gradient(135deg, #ec4899, #f472b6); }
        .stat-icon.rank { background: linear-gradient(135deg, #3B82F6, #818cf8); }
        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            background: linear-gradient(135deg, #e2e8f0 0%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.25rem;
        }
        .stat-label {
            color: #64748b;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Level Progress Card */
        .level-card {
            background: linear-gradient(135deg, rgba(20, 184, 166, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%);
            border: 1px solid rgba(20, 184, 166, 0.2);
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .level-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        .level-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .level-circle {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, #14B8A6, #2DD4BF);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 30px rgba(20, 184, 166, 0.4);
        }
        .level-circle span {
            color: white;
            font-size: 1.75rem;
            font-weight: 800;
        }
        .level-text h3 {
            color: #e2e8f0;
            font-size: 1.25rem;
            margin-bottom: 0.25rem;
        }
        .level-text p {
            color: #64748b;
            font-size: 0.85rem;
        }
        .level-xp-needed {
            text-align: right;
        }
        .level-xp-needed .xp-value {
            color: #2DD4BF;
            font-size: 1.25rem;
            font-weight: 700;
        }
        .level-xp-needed .xp-label {
            color: #64748b;
            font-size: 0.8rem;
        }
        .level-progress-bar {
            height: 12px;
            background: rgba(20, 184, 166, 0.2);
            border-radius: 6px;
            overflow: hidden;
        }
        .level-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #14B8A6, #2DD4BF, #5EEAD4);
            border-radius: 6px;
            transition: width 1s ease-out;
            position: relative;
        }
        .level-progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shimmer 2s infinite;
        }
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        /* Course Progress */
        .courses-progress-section {
            background: rgba(15, 15, 35, 0.5);
            border: 1px solid rgba(20, 184, 166, 0.15);
            border-radius: 16px;
            padding: 1.5rem;
        }
        .courses-progress-section h3 {
            color: #e2e8f0;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .course-item {
            padding: 1rem;
            background: rgba(20, 184, 166, 0.05);
            border-radius: 12px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        .course-item:hover {
            background: rgba(20, 184, 166, 0.1);
        }
        .course-item:last-child {
            margin-bottom: 0;
        }
        .course-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }
        .course-name {
            color: #e2e8f0;
            font-weight: 600;
            font-size: 0.95rem;
        }
        .course-percent {
            color: #2DD4BF;
            font-weight: 700;
            font-size: 0.95rem;
        }
        .course-progress-bar {
            height: 8px;
            background: rgba(20, 184, 166, 0.2);
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 0.5rem;
        }
        .course-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #14B8A6, #2DD4BF);
            border-radius: 4px;
            transition: width 0.5s ease;
        }
        .course-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
            color: #64748b;
        }
        .course-completed-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            color: #10b981;
            font-weight: 500;
        }

        /* Achievement Grid */
        .achievements-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }
        .achievement-card {
            background: rgba(15, 15, 35, 0.5);
            border: 1px solid rgba(20, 184, 166, 0.15);
            border-radius: 16px;
            padding: 1.25rem;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .achievement-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #14B8A6, #2DD4BF);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        .achievement-card:hover {
            transform: translateY(-5px);
            border-color: rgba(20, 184, 166, 0.4);
            box-shadow: 0 10px 30px rgba(20, 184, 166, 0.2);
        }
        .achievement-card:hover::before {
            transform: scaleX(1);
        }
        .achievement-card.earned {
            border-color: rgba(20, 184, 166, 0.4);
            background: linear-gradient(135deg, rgba(20, 184, 166, 0.1) 0%, rgba(15, 15, 35, 0.5) 100%);
        }
        .achievement-card.earned::before {
            transform: scaleX(1);
        }
        .achievement-icon {
            font-size: 2.5rem;
            margin-bottom: 0.75rem;
            filter: grayscale(1);
            transition: filter 0.3s;
            line-height: 1;
        }
        .achievement-icon svg {
            width: 40px;
            height: 40px;
        }
        .achievement-card.earned .achievement-icon {
            filter: grayscale(0);
        }
        .achievement-name {
            color: #e2e8f0;
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 0.375rem;
        }
        .achievement-desc {
            color: #64748b;
            font-size: 0.75rem;
            margin-bottom: 0.75rem;
            line-height: 1.4;
        }
        .achievement-xp {
            display: inline-block;
            background: linear-gradient(135deg, #14B8A6, #2DD4BF);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.75rem;
        }
        .achievement-badge {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            background: #10b981;
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 8px;
            font-size: 0.65rem;
            font-weight: 600;
        }
        .achievement-card:not(.earned) .achievement-badge {
            display: none;
        }

        /* Certificate Grid */
        .certificates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1rem;
        }
        .certificate-card {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.1) 0%, rgba(15, 15, 35, 0.5) 100%);
            border: 1px solid rgba(251, 191, 36, 0.2);
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .certificate-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #fbbf24, #f59e0b);
        }
        .certificate-card:hover {
            transform: translateY(-5px);
            border-color: rgba(251, 191, 36, 0.4);
            box-shadow: 0 10px 30px rgba(251, 191, 36, 0.15);
        }
        .certificate-icon {
            font-size: 2.5rem;
            margin-bottom: 0.75rem;
            color: #fbbf24;
        }
        .certificate-icon svg {
            width: 36px;
            height: 36px;
        }
        .certificate-title {
            color: #e2e8f0;
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .certificate-info {
            color: #64748b;
            font-size: 0.8rem;
            margin-bottom: 0.25rem;
        }
        .certificate-date {
            color: #fbbf24;
            font-weight: 600;
            font-size: 0.85rem;
            margin: 0.75rem 0;
        }
        .btn-cert-download {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: #1a1a2e;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-cert-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(251, 191, 36, 0.4);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #94a3b8;
        }
        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
            color: #14B8A6;
        }
        .empty-icon svg {
            width: 48px;
            height: 48px;
        }
        .empty-title {
            color: #e2e8f0;
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .empty-text {
            color: #64748b;
            margin-bottom: 1.5rem;
        }

        /* Alert */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideDown 0.3s ease;
        }
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #6ee7b7;
        }
        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .profile-hero {
                padding: 1.5rem;
            }
            .profile-hero-content {
                flex-direction: column;
                text-align: center;
            }
            .profile-avatar-large {
                width: 120px;
                height: 120px;
                font-size: 3rem;
            }
            .profile-name {
                justify-content: center;
                font-size: 1.5rem;
            }
            .profile-meta {
                justify-content: center;
            }
            .profile-quick-stats {
                justify-content: center;
            }
            .profile-xp-bar {
                max-width: 100%;
                margin: 0 auto 1rem;
            }
            .profile-tabs {
                padding: 0.375rem;
            }
            .profile-tab {
                padding: 0.625rem 1rem;
                font-size: 0.8rem;
            }
            .profile-tab span {
                display: none;
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .achievements-grid,
            .certificates-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="<?php echo getThemeClass(); ?> page-profile">
    <?php include 'navbar.php'; ?>

    <main class="page-wrapper dashboard-main-container">
        <div class="dashboard-content">
            <!-- Profile Hero -->
            <section class="glass-header mb-8">
                <div class="flex items-center gap-8 flex-wrap">
                    <div class="profile-avatar-section relative">
                        <div class="glass-avatar-lg w-32 h-32 text-4xl" id="avatar-preview-container">
                            <?php if (!empty($user->avatar) && file_exists('assets/uploads/avatars/' . $user->avatar)): ?>
                                <img src="assets/uploads/avatars/<?php echo $user->avatar; ?>" alt="Avatar" id="avatar-preview-img" class="rounded-full w-full h-full object-cover">
                            <?php else: ?>
                                <span class="font-bold text-white"><?php echo strtoupper(substr($user->nama_lengkap, 0, 1)); ?></span>
                            <?php endif; ?>
                            <div class="level-badge absolute -bottom-2 -right-2 w-10 h-10 bg-warm rounded-full border-2 border-bg-elevated flex items-center justify-center font-bold text-sm text-white shadow-lg">
                                <?php echo $user_level; ?>
                            </div>
                        </div>
                    </div>

                    <div class="profile-info-section flex-1 min-w-[300px]">
                        <h1 class="text-3xl font-bold text-white flex items-center gap-3">
                            <?php echo htmlspecialchars($user->nama_lengkap); ?>
                            <span class="glass-badge glass-badge-primary text-xs py-1 px-2">Verified</span>
                        </h1>
                        <p class="text-brand font-medium mb-4">@<?php echo htmlspecialchars($user->username); ?></p>
                        
                        <div class="flex gap-6 flex-wrap mb-4 text-sm text-gray-400">
                            <div class="flex items-center gap-2"><?php icon('mail', 14); ?> <?php echo htmlspecialchars($user->email); ?></div>
                            <div class="flex items-center gap-2"><?php icon('calendar', 14); ?> Bergabung <?php echo date('M Y', strtotime($join_date)); ?></div>
                        </div>

                        <div class="profile-xp-bar">
                            <div class="flex justify-between text-xs mb-2">
                                <span class="text-gray-400">Level <?php echo $user_level; ?> Progress</span>
                                <span class="text-brand font-bold"><?php echo number_format($xp_current); ?> / <?php echo number_format($xp_progress); ?> XP</span>
                            </div>
                            <div class="profile-xp-progress">
                                <div class="profile-xp-fill" style="width: <?php echo $xp_percent; ?>%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <div class="glass-stat-card text-center p-4 min-w-[100px]">
                            <div class="text-2xl font-bold text-warm">#<?php echo $user_rank; ?></div>
                            <div class="text-[10px] uppercase tracking-wider text-gray-500">Global Rank</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Tab Navigation -->
            <nav class="glass-tabs mb-8">
                <a href="?tab=edit" class="glass-tab <?php echo $active_tab === 'edit' ? 'active' : ''; ?>">
                    <?php icon('user', 18); ?> Profil
                </a>
                <a href="?tab=analytics" class="glass-tab <?php echo $active_tab === 'analytics' ? 'active' : ''; ?>">
                    <?php icon('trending-up', 18); ?> Analytics
                </a>
                <a href="?tab=character" class="glass-tab <?php echo $active_tab === 'character' ? 'active' : ''; ?>" style="position:relative">
                    ⚔️ Karakter
                </a>
                <a href="?tab=achievements" class="glass-tab <?php echo $active_tab === 'achievements' ? 'active' : ''; ?>">
                    <?php icon('award', 18); ?> Achievement
                </a>
                <a href="?tab=certificates" class="glass-tab <?php echo $active_tab === 'certificates' ? 'active' : ''; ?>">
                    <?php icon('file-text', 18); ?> Sertifikat
                </a>
                <a href="?tab=settings" class="glass-tab <?php echo $active_tab === 'settings' ? 'active' : ''; ?>">
                    <?php icon('settings', 18); ?> Pengaturan
                </a>
            </nav>

            <!-- Tab Contents -->
            <div class="tab-contents">
                <!-- Edit Profile Tab -->
                <div class="tab-pane <?php echo $active_tab === 'edit' ? 'active' : ''; ?>">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <div class="lg:col-span-2">
                            <form action="" method="POST" enctype="multipart/form-data" class="glass-section">
                                <input type="hidden" name="action" value="update_profile">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                
                                <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
                                    <?php icon('edit-3', 20); ?> Edit Informasi Profil
                                </h2>

                                <div class="mb-8">
                                    <label class="block text-sm font-medium text-gray-300 mb-4">Foto Profil</label>
                                    <div class="flex items-center gap-6 flex-wrap">
                                        <div class="glass-avatar-lg w-20 h-20 text-2xl" id="avatar-preview-container-edit">
                                            <?php if (!empty($user->avatar) && file_exists('assets/uploads/avatars/' . $user->avatar)): ?>
                                                <img src="assets/uploads/avatars/<?php echo $user->avatar; ?>" alt="Avatar" id="avatar-preview-img-edit" class="rounded-full w-full h-full object-cover">
                                            <?php else: ?>
                                                <span class="text-white font-bold"><?php echo strtoupper(substr($user->nama_lengkap, 0, 1)); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-1 min-w-[200px]">
                                            <div class="glass-upload-area p-6 text-center cursor-pointer border-2 border-dashed border-gray-700 hover:border-brand transition rounded-xl bg-gray-900/30">
                                                <input type="file" name="avatar" id="avatar-input" class="hidden" accept="image/*">
                                                <label for="avatar-input" class="cursor-pointer">
                                                    <div class="text-brand mb-2"><?php icon('upload', 24); ?></div>
                                                    <p class="text-sm text-gray-400">Klik untuk unggah atau seret foto ke sini</p>
                                                    <p class="text-xs text-gray-500 mt-1">JPG, PNG atau WebP (Max. 2MB)</p>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                    <div class="form-group">
                                        <label class="block text-sm font-medium text-gray-300 mb-2">Nama Lengkap</label>
                                        <input type="text" name="nama_lengkap" class="glass-input" value="<?php echo htmlspecialchars($user->nama_lengkap); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                                        <input type="email" name="email" class="glass-input" value="<?php echo htmlspecialchars($user->email); ?>" required>
                                    </div>
                                </div>

                                <button type="submit" class="glass-btn glass-btn-primary glass-btn-lg">
                                    <?php icon('save', 18); ?> Simpan Perubahan
                                </button>
                            </form>
                        </div>

                        <div class="lg:col-span-1">
                            <form action="" method="POST" class="glass-section" id="passwordForm">
                                <input type="hidden" name="action" value="change_password">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

                                <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
                                    <?php icon('lock', 20); ?> Keamanan
                                </h2>

                                <div class="form-group mb-4">
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Password Lama</label>
                                    <input type="password" name="old_password" class="glass-input" required>
                                </div>
                                <div class="form-group mb-4">
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Password Baru</label>
                                    <input type="password" name="new_password" id="newPassword" class="glass-input" required>
                                    <div id="passwordStrength" class="mt-2" style="display: none;">
                                        <div class="h-1 w-full bg-gray-800 rounded-full overflow-hidden">
                                            <div id="strengthFill" class="h-full transition-all duration-300"></div>
                                        </div>
                                        <p id="strengthText" class="text-[10px] mt-1 font-bold uppercase"></p>
                                    </div>
                                </div>
                                <div class="form-group mb-6">
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Konfirmasi Password</label>
                                    <input type="password" name="confirm_password" class="glass-input" required>
                                </div>

                                <button type="submit" class="glass-btn glass-btn-secondary w-full">
                                    Ganti Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Analytics Tab -->
                <div class="tab-pane <?php echo $active_tab === 'analytics' ? 'active' : ''; ?>">
                    <div class="glass-stats-grid mb-8">
                        <div class="glass-stat-card">
                            <div class="text-3xl font-bold text-brand mb-1"><?php echo $completed_courses; ?></div>
                            <div class="text-xs uppercase tracking-wider text-gray-500">Kursus Selesai</div>
                        </div>
                        <div class="glass-stat-card">
                            <div class="text-3xl font-bold text-accent mb-1"><?php echo $total_lessons_completed; ?></div>
                            <div class="text-xs uppercase tracking-wider text-gray-500">Lesson Selesai</div>
                        </div>
                        <div class="glass-stat-card">
                            <div class="text-3xl font-bold text-warm mb-1"><?php echo $learning_streak; ?></div>
                            <div class="text-xs uppercase tracking-wider text-gray-500">Day Streak</div>
                        </div>
                        <div class="glass-stat-card">
                            <div class="text-3xl font-bold text-info mb-1"><?php echo number_format($avg_progress, 0); ?>%</div>
                            <div class="text-xs uppercase tracking-wider text-gray-500">Avg Progress</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div class="glass-section">
                            <h2 class="text-lg font-bold text-white mb-6 flex items-center gap-3">
                                <?php icon('activity', 18); ?> Progress Belajar
                            </h2>
                            <div class="space-y-6">
                                <?php if (empty($enrolled_courses)): ?>
                                    <p class="text-gray-500 text-center py-4">Belum ada progress belajar.</p>
                                <?php else: ?>
                                    <?php foreach ($enrolled_courses as $prog): ?>
                                    <div class="course-progress-item">
                                        <div class="flex justify-between text-sm mb-2">
                                            <span class="text-gray-300 font-medium"><?php echo htmlspecialchars($prog['judul_course']); ?></span>
                                            <span class="text-brand font-bold"><?php echo number_format($prog['progress_percent'], 0); ?>%</span>
                                        </div>
                                        <div class="glass-progress">
                                            <div class="glass-progress-bar" style="width: <?php echo $prog['progress_percent']; ?>%"></div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="glass-section">
                            <h2 class="text-lg font-bold text-white mb-6 flex items-center gap-3">
                                <?php icon('award', 18); ?> Ringkasan Achievement
                            </h2>
                            <div class="flex items-center gap-6 mb-8 p-6 bg-gray-900/30 rounded-2xl border border-gray-800">
                                <div class="w-16 h-16 bg-brand-subtle text-brand rounded-full flex items-center justify-center text-3xl">
                                    <?php icon('award', 32); ?>
                                </div>
                                <div>
                                    <div class="text-2xl font-bold text-white"><?php echo $earned_achievements; ?> / <?php echo $total_achievements_analytics; ?></div>
                                    <div class="text-sm text-gray-500">Achievement Terbuka</div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <?php foreach (array_slice($achievements, 0, 4) as $ach): ?>
                                <div class="glass-card-compact p-3 flex items-center gap-3 <?php echo $ach['earned_at'] ? '' : 'opacity-40 grayscale'; ?>">
                                    <div class="w-8 h-8 rounded-lg bg-gray-800 flex items-center justify-center text-brand">
                                        <?php icon('star', 14); ?>
                                    </div>
                                    <div class="text-xs font-bold text-gray-300 truncate"><?php echo htmlspecialchars($ach['judul_achievement']); ?></div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Achievements Tab -->
                <div class="tab-pane <?php echo $active_tab === 'achievements' ? 'active' : ''; ?>">
                    <div class="glass-section">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            <?php foreach ($achievements as $ach): ?>
                            <div class="glass-card <?php echo $ach['earned_at'] ? '' : 'opacity-40 grayscale'; ?>">
                                <div class="text-center p-6">
                                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-900/50 text-brand rounded-full flex items-center justify-center text-3xl">
                                        <?php icon($ach['earned_at'] ? 'award' : 'lock', 32); ?>
                                    </div>
                                    <h3 class="font-bold text-white mb-1 text-sm"><?php echo htmlspecialchars($ach['judul_achievement']); ?></h3>
                                    <p class="text-[10px] text-gray-500 mb-4 leading-relaxed"><?php echo htmlspecialchars($ach['deskripsi']); ?></p>
                                    <?php if ($ach['earned_at']): ?>
                                        <div class="text-[10px] text-brand font-bold uppercase tracking-wider">
                                            Diterima: <?php echo date('d M Y', strtotime($ach['earned_at'])); ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-[10px] text-gray-600 font-bold uppercase tracking-wider">Terkunci</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Certificates Tab -->
                <div class="tab-pane <?php echo $active_tab === 'certificates' ? 'active' : ''; ?>">
                    <div class="glass-section">
                        <h2 class="text-xl font-bold text-white mb-8 flex items-center gap-3">
                            <?php icon('file-text', 20); ?> Sertifikat Kelulusan
                        </h2>
                        
                        <?php if (empty($certificates)): ?>
                            <div class="text-center py-12">
                                <div class="mb-4 opacity-30"><?php icon('award', 48); ?></div>
                                <p class="text-gray-500 mb-6 font-medium">Selesaikan kursus Anda untuk mendapatkan sertifikat resmi.</p>
                                <a href="courses.php" class="glass-btn glass-btn-primary">Cari Kursus</a>
                            </div>
                        <?php else: ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <?php foreach ($certificates as $cert): ?>
                                    <div class="glass-card overflow-hidden transition-all hover:scale-[1.02]">
                                        <div class="aspect-[1.4/1] bg-gradient-to-br from-gray-900 to-gray-800 p-4 border-b border-gray-800 flex items-center justify-center relative">
                                            <div class="w-[90%] h-[90%] border border-brand/20 relative flex flex-col items-center justify-center text-center p-2">
                                                <div class="absolute top-1 left-1 text-brand/30"><?php icon('award', 16); ?></div>
                                                <div class="text-[8px] tracking-[4px] text-brand uppercase font-bold mb-1">CERTIFICATE</div>
                                                <div class="text-[10px] font-bold text-white mb-2 leading-tight"><?php echo htmlspecialchars($cert['judul_course']); ?></div>
                                                <div class="text-[6px] text-gray-500">PROZONE ACADEMY â€¢ <?php echo date('M Y', strtotime($cert['completed_at'])); ?></div>
                                            </div>
                                        </div>
                                        <div class="p-5">
                                            <h3 class="text-sm font-bold text-white mb-2 truncate"><?php echo htmlspecialchars($cert['judul_course']); ?></h3>
                                            <div class="flex gap-2">
                                                <a href="view_certificate.php?id=<?php echo $cert['id']; ?>" class="glass-btn glass-btn-primary flex-1 text-xs py-2">Lihat</a>
                                                <a href="download_certificate.php?id=<?php echo $cert['id']; ?>" class="glass-btn glass-btn-secondary flex-1 text-xs py-2">Unduh</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Settings Tab -->
                <div class="tab-pane <?php echo $active_tab === 'settings' ? 'active' : ''; ?>">
                    <div class="max-w-2xl mx-auto space-y-6">
                        <div class="glass-section">
                            <h2 class="text-xl font-bold text-white mb-6">Preferensi Pengguna</h2>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-4 bg-gray-900/30 rounded-xl border border-gray-800">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-blue-500/20 text-blue-400 rounded-lg flex items-center justify-center">
                                            <?php icon('globe', 20); ?>
                                        </div>
                                        <div>
                                            <div class="font-bold text-white text-sm">Bahasa</div>
                                            <div class="text-xs text-gray-500">Pilih bahasa antarmuka</div>
                                        </div>
                                    </div>
                                    <form action="" method="POST">
                                        <input type="hidden" name="action" value="change_language">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                        <div class="flex bg-gray-900 p-1 rounded-lg border border-gray-800">
                                            <button name="language" value="id" class="px-3 py-1 text-[10px] rounded-md <?php echo $current_language === 'id' ? 'bg-brand text-white font-bold' : 'text-gray-500 hover:text-gray-300'; ?>">ID</button>
                                            <button name="language" value="en" class="px-3 py-1 text-[10px] rounded-md <?php echo $current_language === 'en' ? 'bg-brand text-white font-bold' : 'text-gray-500 hover:text-gray-300'; ?>">EN</button>
                                        </div>
                                    </form>
                                </div>

                                <div class="flex items-center justify-between p-4 bg-gray-900/30 rounded-xl border border-gray-800">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-purple-500/20 text-purple-400 rounded-lg flex items-center justify-center">
                                            <?php icon('moon', 20); ?>
                                        </div>
                                        <div>
                                            <div class="font-bold text-white text-sm">Tema Visual</div>
                                            <div class="text-xs text-gray-500">Aktifkan mode gelap atau terang</div>
                                        </div>
                                    </div>
                                    <form action="" method="POST">
                                        <input type="hidden" name="action" value="change_theme">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                        <div class="flex bg-gray-900 p-1 rounded-lg border border-gray-800">
                                            <button name="theme" value="light" class="px-3 py-1 text-[10px] rounded-md <?php echo $current_theme === 'light' ? 'bg-brand text-white font-bold' : 'text-gray-500 hover:text-gray-300'; ?>"><?php icon('sun', 12); ?></button>
                                            <button name="theme" value="dark" class="px-3 py-1 text-[10px] rounded-md <?php echo $current_theme === 'dark' ? 'bg-brand text-white font-bold' : 'text-gray-500 hover:text-gray-300'; ?>"><?php icon('moon', 12); ?></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="glass-section border-red-900/30">
                            <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
                                <?php icon('alert-octagon', 20); ?> Zona Bahaya
                            </h2>
                            <p class="text-sm text-gray-400 mb-6">Tindakan ini bersifat permanen dan tidak dapat dibatalkan.</p>
                            <button class="glass-btn border-red-500/50 text-red-500 hover:bg-red-500 hover:text-white w-full py-3">
                                Nonaktifkan Akun Saya
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/toast.php'; ?>

    <script src="assets/js/navbar.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Avatar preview
            const avatarInput = document.getElementById('avatar-input');
            const avatarImg = document.getElementById('avatar-preview-img-edit');
            const avatarContainer = document.getElementById('avatar-preview-container-edit');

            if (avatarInput) {
                avatarInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(event) {
                            if (avatarImg) {
                                avatarImg.src = event.target.result;
                            } else {
                                avatarContainer.innerHTML = `<img src="${event.target.result}" id="avatar-preview-img-edit" class="rounded-full w-full h-full object-cover">`;
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            // Password strength indicator
            const pwdInput = document.getElementById('newPassword');
            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');
            const strengthContainer = document.getElementById('passwordStrength');

            if (pwdInput) {
                pwdInput.addEventListener('input', function() {
                    const val = this.value;
                    if (val.length === 0) {
                        strengthContainer.style.display = 'none';
                        return;
                    }
                    strengthContainer.style.display = 'block';

                    let score = 0;
                    if (val.length >= 8) score++;
                    if (/[A-Z]/.test(val)) score++;
                    if (/[0-9]/.test(val)) score++;
                    if (/[^A-Za-z0-9]/.test(val)) score++;

                    const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-blue-500', 'bg-brand'];
                    const labels = ['Sangat Lemah', 'Lemah', 'Cukup', 'Kuat', 'Sangat Kuat'];
                    const textColors = ['text-red-500', 'text-orange-500', 'text-yellow-500', 'text-blue-500', 'text-brand'];
                    
                    strengthFill.className = 'h-full transition-all duration-300 ' + colors[score];
                    strengthFill.style.width = ((score + 1) * 20) + '%';
                    strengthText.textContent = labels[score];
                    strengthText.className = 'text-[10px] mt-1 font-bold uppercase ' + textColors[score];
                });
            }

            // Password confirmation
            document.getElementById('passwordForm')?.addEventListener('submit', function(e) {
                const p1 = this.querySelector('input[name="new_password"]').value;
                const p2 = this.querySelector('input[name="confirm_password"]').value;
                if (p1 !== p2) {
                    e.preventDefault();
                    if (window.showToast) showToast('Konfirmasi password tidak cocok!', 'error');
                    else alert('Konfirmasi password tidak cocok!');
                }
            });

            // Animate progress on load
            setTimeout(() => {
                document.querySelectorAll('.profile-xp-fill, .glass-progress-bar').forEach(bar => {
                    const w = bar.style.width;
                    bar.style.width = '0%';
                    setTimeout(() => bar.style.width = w, 100);
                });
            }, 300);
        });
    </script>
</body>
    <?php require_once 'navbar.php'; ?>

    <div class="dashboard-main-container">
</html>
