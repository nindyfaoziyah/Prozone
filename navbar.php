<?php
// navbar.php - Floating Header, includes sidebar island
include_once 'sidebar.php';

// Get user data for header display
$first_name = explode(' ', $_SESSION['nama_lengkap'] ?? 'User')[0];
$user_level = $level ?? 1;
$user_xp    = $total_xp ?? 0;
$user_streak = $streakDays ?? 0;
?>
<header class="header-floating">
    <div class="header-inner">
        <!-- Floating Search Bar -->
        <div class="header-search">
            <div class="search-container">
                <span class="search-icon"><?php icon('search', 16); ?></span>
                <input type="text" id="global-search" placeholder="Cari kursus, materi, atau topik...">
                <kbd class="search-shortcut">Ctrl K</kbd>
            </div>
        </div>

        <!-- Status Indicators -->
        <div class="header-actions">
            <div class="header-stat-pill xp-pill" title="Total XP">
                <?php icon('zap', 14); ?>
                <span class="pill-value"><?php echo number_format($user_xp); ?> XP</span>
            </div>
            <div class="header-stat-pill streak-pill" title="Daily Streak">
                <?php icon('trending-up', 14); ?>
                <span class="pill-value"><?php echo $user_streak; ?> Day Streak</span>
            </div>
            <button class="header-btn notification-btn" title="Notifikasi">
                <?php icon('bell', 18); ?>
                <span class="notification-badge"></span>
            </button>
            <div class="header-user-card">
                <div class="user-meta">
                    <span class="user-name"><?php echo htmlspecialchars($first_name); ?></span>
                    <span class="user-level">Level <?php echo $user_level; ?></span>
                </div>
                <div class="user-avatar-wrapper">
                    <?php if (!empty($avatar) && file_exists('assets/uploads/avatars/' . $avatar)): ?>
                        <img src="assets/uploads/avatars/<?php echo $avatar; ?>" alt="Avatar" class="user-avatar">
                    <?php else: ?>
                        <div class="user-avatar" style="background:linear-gradient(135deg,#3B82F6,#20C7B7);display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:1rem;">
                            <?php echo strtoupper(substr($_SESSION['nama_lengkap'] ?? 'U', 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    <div class="online-indicator"></div>
                </div>
            </div>
            <a href="logout.php" class="header-btn" title="Logout" style="color:#EF4444;border-color:#FEE2E2;">
                <?php icon('log-out', 18); ?>
            </a>
        </div>
    </div>
</header>
