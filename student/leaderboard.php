<?php
require_once '../config/config.php';
requireLogin();
requireRole(['student']);
require_once '../includes/icons.php';

require_once '../models/Leaderboard.php';

$database = new Database();
$db = $database->getConnection();

$leaderboard = new Leaderboard($db);

// Get tab (solo or clan)
$tab = $_GET['tab'] ?? 'solo';

// Get time period filter
$period = $_GET['period'] ?? 'all';

// Get solo leaderboard
$solo_leaderboard = [];
$solo_stmt = $leaderboard->getSoloLeaderboard(100);
while ($row = $solo_stmt->fetch(PDO::FETCH_ASSOC)) {
    $solo_leaderboard[] = $row;
}

// Get user rank and stats
$user_rank = 0;
$user_data = null;
$prev_user_xp = 0;
$next_user_xp = 0;
foreach ($solo_leaderboard as $index => $user) {
    if ($user['id'] == $_SESSION['user_id']) {
        $user_rank = $index + 1;
        $user_data = $user;
        // Get previous user XP (for progress to next rank)
        if ($index > 0) {
            $prev_user_xp = $solo_leaderboard[$index - 1]['total_xp'];
        }
        // Get next user XP (user behind you)
        if ($index < count($solo_leaderboard) - 1) {
            $next_user_xp = $solo_leaderboard[$index + 1]['total_xp'];
        }
        break;
    }
}

// Get streak data for current user
$streakQuery = "SELECT COUNT(DISTINCT DATE(completed_at)) as streak_days
    FROM user_progress 
    WHERE user_id = :user_id 
    AND completed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    AND status = 'completed'";
$streakStmt = $db->prepare($streakQuery);
$streakStmt->bindParam(':user_id', $_SESSION['user_id']);
$streakStmt->execute();
$streakData = $streakStmt->fetch(PDO::FETCH_ASSOC);
$user_streak = $streakData['streak_days'] ?? 0;

// Get clan leaderboard
$clan_leaderboard = [];
$clan_stmt = $leaderboard->getClanLeaderboard(50);
while ($row = $clan_stmt->fetch(PDO::FETCH_ASSOC)) {
    $clan_leaderboard[] = $row;
}

// Calculate XP needed for next rank
$xp_to_next_rank = $user_rank > 1 ? $prev_user_xp - ($user_data['total_xp'] ?? 0) : 0;
$xp_lead = ($user_data['total_xp'] ?? 0) - $next_user_xp;

$page_title = 'Leaderboard - ' . APP_NAME;
$page_css = ['pages/leaderboard.css'];
$body_class = 'dashboard-layout';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once '../includes/head.php'; ?>
</head>
<body class="<?php echo $body_class; ?>">
    <!-- Navbar -->
    <?php require_once 'navbar.php'; ?>

    <!-- Main Content -->
    <div class="page-wrapper dashboard-main-container">
        <div class="dashboard-content">
            <div class="glass-header">
                <h1><?php icon('trophy', 28); ?> Leaderboard</h1>
                <p>Bersaing dengan siswa lainnya dan raih posisi teratas!</p>
            </div>

            <!-- Your Rank Hero Section -->
            <?php if ($tab === 'solo' && $user_data): ?>
            <div class="leaderboard-hero">
                <div class="hero-content">
                    <div class="hero-rank-display">
                        <div class="hero-rank-badge">
                            <span class="hero-rank-number">#<?php echo $user_rank; ?></span>
                        </div>
                        <div class="hero-rank-label">Rank Anda</div>
                    </div>
                    <div class="hero-user-info">
                        <div class="hero-user-name"><?php echo htmlspecialchars($user_data['nama_lengkap']); ?></div>
                        <div class="hero-user-stats">
                            <div class="hero-stat">
                                <div class="hero-stat-icon xp"><?php icon('star', 18); ?></div>
                                <div>
                                    <div class="hero-stat-value"><?php echo number_format($user_data['total_xp']); ?></div>
                                    <div class="hero-stat-label">Total XP</div>
                                </div>
                            </div>
                            <div class="hero-stat">
                                <div class="hero-stat-icon level"><?php icon('book-open', 18); ?></div>
                                <div>
                                    <div class="hero-stat-value">Level <?php echo $user_data['level']; ?></div>
                                    <div class="hero-stat-label">Level</div>
                                </div>
                            </div>
                            <div class="hero-stat">
                                <div class="hero-stat-icon streak"><?php icon('flame', 18); ?></div>
                                <div>
                                    <div class="hero-stat-value"><?php echo $user_streak; ?></div>
                                    <div class="hero-stat-label">Streak</div>
                                </div>
                            </div>
                        </div>
                        <?php if ($user_rank > 1 && $xp_to_next_rank > 0): ?>
                        <div class="hero-progress">
                            <div class="hero-progress-header">
                                <span class="hero-progress-label">XP menuju Rank #<?php echo $user_rank - 1; ?></span>
                                <span class="hero-progress-value"><?php echo number_format($xp_to_next_rank); ?> XP lagi</span>
                            </div>
                            <div class="hero-progress-bar">
                                <?php 
                                $progress = 100 - (($xp_to_next_rank / ($xp_to_next_rank + $xp_lead)) * 100);
                                $progress = max(5, min(95, $progress));
                                ?>
                                <div class="hero-progress-fill" style="width: <?php echo $progress; ?>%"></div>
                            </div>
                        </div>
                        <?php elseif ($user_rank === 1): ?>
                        <div class="hero-progress" style="text-align: center;">
                            <span style="color: #F59E0B; font-weight: 600;">&#127942; Selamat! Anda berada di posisi #1!</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Motivational Banner -->
            <?php if ($tab === 'solo' && $user_rank > 10): ?>
            <div class="motivation-banner">
                <div class="motivation-icon">&#128161;</div>
                <div class="motivation-text">
                    <div class="motivation-title">Terus Belajar!</div>
                    <div class="motivation-subtitle">Selesaikan lebih banyak lesson untuk naik ke Top 10</div>
                </div>
            </div>
            <?php elseif ($tab === 'solo' && $user_rank > 3 && $user_rank <= 10): ?>
            <div class="motivation-banner">
                <div class="motivation-icon">&#128293;</div>
                <div class="motivation-text">
                    <div class="motivation-title">Hampir Sampai!</div>
                    <div class="motivation-subtitle">Sedikit lagi untuk masuk podium Top 3!</div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Tabs (Solo / Clan) -->
            <div class="glass-tabs">
                <button class="glass-tab <?php echo $tab === 'solo' ? 'active' : ''; ?>" onclick="window.location.href='?tab=solo'">
                    &#128104;&#65039; Solo
                </button>
                <button class="glass-tab <?php echo $tab === 'clan' ? 'active' : ''; ?>" onclick="window.location.href='?tab=clan'">
                    &#128101;&#65039; Clan
                </button>
            </div>

            <!-- Solo Leaderboard -->
            <?php if ($tab === 'solo'): ?>
                <!-- Top 3 Podium -->
                <?php if (count($solo_leaderboard) >= 3): ?>
                <div class="podium-section">
                    <div class="podium-title">
                        <?php icon('trophy', 22); ?> Top 3 Siswa
                    </div>
                    <div class="podium-container">
                        <!-- Second Place -->
                        <div class="podium-player second">
                            <div class="podium-avatar-wrapper">
                                <div class="podium-avatar">
                                    <?php if (!empty($solo_leaderboard[1]['avatar']) && file_exists('../assets/uploads/avatars/' . $solo_leaderboard[1]['avatar'])): ?>
                                        <img src="../assets/uploads/avatars/<?php echo htmlspecialchars($solo_leaderboard[1]['avatar']); ?>" alt="">
                                    <?php else: ?>
                                        <?php echo strtoupper(substr($solo_leaderboard[1]['nama_lengkap'], 0, 1)); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="podium-name"><?php echo htmlspecialchars($solo_leaderboard[1]['nama_lengkap']); ?></div>
                            <div class="podium-xp"><?php echo number_format($solo_leaderboard[1]['total_xp']); ?> XP</div>
                            <div class="podium-stand">2</div>
                        </div>
                        <!-- First Place -->
                        <div class="podium-player first">
                            <div class="podium-avatar-wrapper">
                                <div class="podium-crown">&#128075;</div>
                                <div class="podium-avatar">
                                    <?php if (!empty($solo_leaderboard[0]['avatar']) && file_exists('../assets/uploads/avatars/' . $solo_leaderboard[0]['avatar'])): ?>
                                        <img src="../assets/uploads/avatars/<?php echo htmlspecialchars($solo_leaderboard[0]['avatar']); ?>" alt="">
                                    <?php else: ?>
                                        <?php echo strtoupper(substr($solo_leaderboard[0]['nama_lengkap'], 0, 1)); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="podium-name"><?php echo htmlspecialchars($solo_leaderboard[0]['nama_lengkap']); ?></div>
                            <div class="podium-xp"><?php echo number_format($solo_leaderboard[0]['total_xp']); ?> XP</div>
                            <div class="podium-stand">1</div>
                        </div>
                        <!-- Third Place -->
                        <div class="podium-player third">
                            <div class="podium-avatar-wrapper">
                                <div class="podium-avatar">
                                    <?php if (!empty($solo_leaderboard[2]['avatar']) && file_exists('../assets/uploads/avatars/' . $solo_leaderboard[2]['avatar'])): ?>
                                        <img src="../assets/uploads/avatars/<?php echo htmlspecialchars($solo_leaderboard[2]['avatar']); ?>" alt="">
                                    <?php else: ?>
                                        <?php echo strtoupper(substr($solo_leaderboard[2]['nama_lengkap'], 0, 1)); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="podium-name"><?php echo htmlspecialchars($solo_leaderboard[2]['nama_lengkap']); ?></div>
                            <div class="podium-xp"><?php echo number_format($solo_leaderboard[2]['total_xp']); ?> XP</div>
                            <div class="podium-stand">3</div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Search & Filters -->
                <div class="leaderboard-controls">
                    <div class="period-filters">
                        <button class="period-btn <?php echo $period === 'weekly' ? 'active' : ''; ?>" onclick="window.location.href='?tab=solo&period=weekly'">Mingguan</button>
                        <button class="period-btn <?php echo $period === 'monthly' ? 'active' : ''; ?>" onclick="window.location.href='?tab=solo&period=monthly'">Bulanan</button>
                        <button class="period-btn <?php echo $period === 'all' ? 'active' : ''; ?>" onclick="window.location.href='?tab=solo&period=all'">Semua</button>
                    </div>
                    <div class="search-box">
                        <?php icon('search', 18); ?>
                        <input type="text" id="searchUser" placeholder="Cari nama..." oninput="filterLeaderboard(this.value)">
                    </div>
                </div>

                <div class="leaderboard-table" id="leaderboardTable">
                    <?php if (empty($solo_leaderboard)): ?>
                        <div class="leaderboard-empty">
                            <div class="leaderboard-empty-icon">&#127942;</div>
                            <div class="leaderboard-empty-title">Belum ada data leaderboard</div>
                            <div class="leaderboard-empty-text">Mulai belajar untuk tampil di leaderboard!</div>
                        </div>
                    <?php else: ?>
                        <div class="leaderboard-header">
                            <div class="rank-col">Rank</div>
                            <div class="player-col">Player</div>
                            <div class="stats-col">Stats</div>
                        </div>
                        <?php foreach ($solo_leaderboard as $index => $user): ?>
                            <?php 
                            $rank = $index + 1;
                            // Skip top 3 in main list if podium is shown
                            if ($rank <= 3 && count($solo_leaderboard) >= 3) continue;
                            $is_current_user = $user['id'] == $_SESSION['user_id'];
                            $rank_class = $rank <= 3 ? 'top-' . $rank : 'other';
                            ?>
                            <div class="leaderboard-item <?php echo $is_current_user ? 'current-user' : ''; ?>" data-name="<?php echo strtolower(htmlspecialchars($user['nama_lengkap'])); ?>">
                                <div class="rank-number <?php echo $rank_class; ?>">
                                    <?php if ($rank <= 3): ?>
                                        <?php icon('trophy', 18); ?>
                                    <?php else: ?>
                                        <?php echo $rank; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="user-info">
                                    <div class="user-avatar">
                                        <?php if (!empty($user['avatar']) && file_exists('../assets/uploads/avatars/' . $user['avatar'])): ?>
                                            <img src="../assets/uploads/avatars/<?php echo htmlspecialchars($user['avatar']); ?>" alt="">
                                        <?php else: ?>
                                            <?php echo strtoupper(substr($user['nama_lengkap'], 0, 1)); ?>
                                        <?php endif; ?>
                                        <div class="user-level-badge"><?php echo $user['level']; ?></div>
                                    </div>
                                    <div class="user-details">
                                        <div class="user-name">
                                            <?php echo htmlspecialchars($user['nama_lengkap']); ?>
                                            <?php if ($is_current_user): ?>
                                                <span class="you-badge">ANDA</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="user-meta">
                                            <span class="user-level">Level <?php echo $user['level']; ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="stats">
                                    <div class="stat-item">
                                        <div class="stat-value xp-stat"><?php echo number_format($user['total_xp']); ?></div>
                                        <div class="stat-label">XP</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-value"><?php echo $user['completed_courses']; ?></div>
                                        <div class="stat-label">Course</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-value"><?php echo $user['completed_lessons']; ?></div>
                                        <div class="stat-label">Lesson</div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Clan Leaderboard -->
                <?php if (count($clan_leaderboard) >= 3): ?>
                <div class="podium-section">
                    <div class="podium-title">
                        <?php icon('users', 22); ?> Top 3 Clan
                    </div>
                    <div class="podium-container">
                        <!-- Second Place -->
                        <div class="podium-player second">
                            <div class="podium-avatar-wrapper">
                                <div class="podium-avatar" style="border-radius: 16px; font-size: 1.25rem;">
                                    <?php icon('users', 32); ?>
                                </div>
                            </div>
                            <div class="podium-name"><?php echo htmlspecialchars($clan_leaderboard[1]['nama_clan']); ?></div>
                            <div class="podium-xp"><?php echo number_format($clan_leaderboard[1]['total_xp']); ?> XP</div>
                            <div class="podium-stand">2</div>
                        </div>
                        <!-- First Place -->
                        <div class="podium-player first">
                            <div class="podium-avatar-wrapper">
                                <div class="podium-crown">&#128075;</div>
                                <div class="podium-avatar" style="border-radius: 20px; font-size: 1.5rem;">
                                    <?php icon('users', 40); ?>
                                </div>
                            </div>
                            <div class="podium-name"><?php echo htmlspecialchars($clan_leaderboard[0]['nama_clan']); ?></div>
                            <div class="podium-xp"><?php echo number_format($clan_leaderboard[0]['total_xp']); ?> XP</div>
                            <div class="podium-stand">1</div>
                        </div>
                        <!-- Third Place -->
                        <div class="podium-player third">
                            <div class="podium-avatar-wrapper">
                                <div class="podium-avatar" style="border-radius: 16px; font-size: 1.25rem;">
                                    <?php icon('users', 32); ?>
                                </div>
                            </div>
                            <div class="podium-name"><?php echo htmlspecialchars($clan_leaderboard[2]['nama_clan']); ?></div>
                            <div class="podium-xp"><?php echo number_format($clan_leaderboard[2]['total_xp']); ?> XP</div>
                            <div class="podium-stand">3</div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Search -->
                <div class="leaderboard-controls">
                    <div></div>
                    <div class="search-box">
                        <?php icon('search', 18); ?>
                        <input type="text" id="searchClan" placeholder="Cari clan..." oninput="filterClanLeaderboard(this.value)">
                    </div>
                </div>

                <div class="leaderboard-table leaderboard-clan-table" id="clanLeaderboardTable">
                    <?php if (empty($clan_leaderboard)): ?>
                        <div class="leaderboard-empty">
                            <div class="leaderboard-empty-icon">&#128101;</div>
                            <div class="leaderboard-empty-title">Belum ada clan</div>
                            <div class="leaderboard-empty-text">Buat atau bergabung dengan clan untuk berkompetisi!</div>
                        </div>
                    <?php else: ?>
                        <div class="leaderboard-header">
                            <div class="rank-col">Rank</div>
                            <div class="player-col">Clan</div>
                            <div class="stats-col">Stats</div>
                        </div>
                        <?php foreach ($clan_leaderboard as $index => $clan_item): ?>
                            <?php 
                            $rank = $index + 1;
                            // Skip top 3 in main list if podium is shown
                            if ($rank <= 3 && count($clan_leaderboard) >= 3) continue;
                            ?>
                            <div class="clan-item" data-name="<?php echo strtolower(htmlspecialchars($clan_item['nama_clan'])); ?>">
                                <div class="rank-number <?php echo $rank <= 3 ? 'top-' . $rank : 'other'; ?>">
                                    <?php if ($rank <= 3): ?>
                                        <?php icon('trophy', 18); ?>
                                    <?php else: ?>
                                        <?php echo $rank; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="clan-avatar"><?php icon('users', 20); ?></div>
                                <div class="clan-info">
                                    <div class="clan-name"><?php echo htmlspecialchars($clan_item['nama_clan']); ?></div>
                                    <div class="clan-meta">
                                        <div class="clan-meta-item">
                                            <?php icon('users', 12); ?>
                                            <?php echo $clan_item['total_members']; ?> Members
                                        </div>
                                    </div>
                                </div>
                                <div class="stats">
                                    <div class="stat-item">
                                        <div class="stat-value xp-stat"><?php echo number_format($clan_item['total_xp']); ?></div>
                                        <div class="stat-label">Total XP</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-value"><?php echo number_format($clan_item['average_xp'], 0); ?></div>
                                        <div class="stat-label">Avg XP</div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../includes/loading.php'; ?>
    <?php include '../includes/toast.php'; ?>

    <script src="../assets/js/navbar.js"></script>
    <script>
        // Search/Filter functionality
        function filterLeaderboard(searchTerm) {
            const items = document.querySelectorAll('#leaderboardTable .leaderboard-item');
            const term = searchTerm.toLowerCase().trim();
            
            items.forEach(item => {
                const name = item.getAttribute('data-name') || '';
                if (term === '' || name.includes(term)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function filterClanLeaderboard(searchTerm) {
            const items = document.querySelectorAll('#clanLeaderboardTable .clan-item');
            const term = searchTerm.toLowerCase().trim();
            
            items.forEach(item => {
                const name = item.getAttribute('data-name') || '';
                if (term === '' || name.includes(term)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Animate progress bar on load
        document.addEventListener('DOMContentLoaded', function() {
            const progressFill = document.querySelector('.hero-progress-fill');
            if (progressFill) {
                const targetWidth = progressFill.style.width;
                progressFill.style.width = '0%';
                setTimeout(function() {
                    progressFill.style.width = targetWidth;
                }, 300);
            }
        });
    </script>
</body>
</html>
