<?php
require_once 'config/config.php';
requireLogin();
require_once 'includes/icons.php';
require_once 'includes/rpg_system.php';

$page_title       = 'CodeQuest Arena';
$page_description = 'Multiplayer Coding Battle Prozone.';
$page_css         = ['sidebar-island.css', 'dashboard-override.css', 'rpg-system.css', 'arena-v2.css', 'arena-battle-result.css'];
// By injecting `arena-active`, we override the body to be overflow:hidden
$body_class       = getThemeClass() . ' arena-active';

$database = new Database();
$db = $database->getConnection();

// Get user data
$query_user = "SELECT total_xp, level, character_class FROM users WHERE id = :user_id";
$stmt_user = $db->prepare($query_user);
$stmt_user->bindParam(':user_id', $_SESSION['user_id']);
$stmt_user->execute();
$user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);

$level = $user_data['level'] ?? 1;
$total_xp = $user_data['total_xp'] ?? 0;
$char_slug = $user_data['character_class'] ?? 'code-warrior';
$you_char = getClassData($char_slug);
$first_name = explode(' ', $_SESSION['nama_lengkap'] ?? 'User')[0];

// Dummy Enemy Data
$enemy_name = "CodeBreaker99";
$enemy_char = getClassData('cyber-ninja');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once 'includes/head.php'; ?>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700;800&family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body class="<?php echo $body_class; ?>">

<!-- Dark Mode Ambient Lighting -->
<div class="neon-glow-bg"></div>

<!-- True Fullscreen Layout Wrapper -->
<div class="arena-fullscreen-wrapper">
    
    <!-- ======================= -->
    <!-- CENTER PANEL (~70%)     -->
    <!-- ======================= -->
    <div class="arena-panel-dark arena-center">
        
        <!-- Challenge Header -->
        <div class="arena-challenge-header">
            <div class="challenge-title-group">
                <a href="dashboard.php" class="btn-back-home" aria-label="Kembali" style="display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; margin-right:12px; color:#818cf8; text-decoration:none; font-weight:800; font-size:0.9rem; padding: 0; background:rgba(99,102,241,0.1); border-radius:12px; border:1px solid rgba(99,102,241,0.2); transition:all 0.2s;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15 18L9 12L15 6" stroke="#4F46E5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
                <h1 class="challenge-title">Array Profit Maximization</h1>
                <span class="difficulty-badge">HARD</span>
            </div>
            <div class="challenge-timer" id="arena-timer">15:00</div>
        </div>

        <div class="challenge-description">
            <p><strong>Problem Statement:</strong> You are given an array <code>prices</code> where <code>prices[i]</code> is the price of a given stock on the <code>i<sup>th</sup></code> day. Find the maximum profit you can achieve.</p>
            <p style="margin-top:8px; color:#94a3b8; font-size:0.8rem;">Expected Time Complexity: O(N) | Space Complexity: O(1)</p>
        </div>

        <!-- Splittable Editor Area -->
        <div class="arena-editor-container">
            <div class="editor-header">
                <div>WORKSPACE</div>
                <div>LANG: JAVASCRIPT</div>
            </div>

            <div class="split-view">
                <!-- User Editor -->
                <div class="editor-pane you">
                    <div class="editor-pane-label">MAINFRAME UPLINK: <?php echo htmlspecialchars($first_name); ?></div>
                    <textarea class="editor-textarea" spellcheck="false" placeholder="Tulis solusimu di sini...">function calculateMaxProfit(prices) {
  // Your code here
  
}
</textarea>
                </div>
                <!-- Enemy Editor -->
                <div class="editor-pane enemy">
                    <div class="editor-pane-label">ENEMY SIGNAL: <?php echo $enemy_name; ?></div>
                    <div class="editor-textarea" id="enemy-code">
                        <!-- JS injects code here to simulate typing -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions Bar -->
        <div class="arena-actions-bar">
            <div class="action-buttons">
                <button class="btn-neon-fill" id="btn-submit-code">Submit Solution</button>
            </div>
        </div>

    </div>

    <!-- ======================= -->
    <!-- RIGHT PANEL (~30%)      -->
    <!-- ======================= -->
    <div class="arena-right">
        
        <!-- Global Ranking -->
        <div class="rp-box rp-leaderboard">
            <div class="rp-header">
                🏆 LIVE GLOBAL RANKING
            </div>
            <div class="lb-list">
                <div class="lb-row" id="enemy-lb-item">
                    <div class="lb-num">1</div>
                    <img src="<?php echo htmlspecialchars($enemy_char['image']); ?>" class="lb-img" alt="Avatar">
                    <div class="lb-name"><?php echo $enemy_name; ?></div>
                    <div class="lb-score">1450 XP</div>
                </div>
                <div class="lb-row">
                    <div class="lb-num">2</div>
                    <img src="assets/img/characters/web-developer.png" class="lb-img" alt="Avatar">
                    <div class="lb-name">Alya_Dev</div>
                    <div class="lb-score">1200 XP</div>
                </div>
                <div class="lb-row active-user" id="you-lb-item">
                    <div class="lb-num">3</div>
                    <img src="<?php echo htmlspecialchars($you_char['image']); ?>" class="lb-img" alt="Avatar">
                    <div class="lb-name"><?php echo htmlspecialchars($first_name); ?></div>
                    <div class="lb-score">1000 XP</div>
                </div>
                <div class="lb-row">
                    <div class="lb-num">4</div>
                    <img src="assets/img/characters/bug-hunter.png" class="lb-img" alt="Avatar">
                    <div class="lb-name">Zhafir</div>
                    <div class="lb-score">900 XP</div>
                </div>
            </div>
        </div>

        <!-- Matches Stats -->
        <div class="rp-box rp-stats">
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-lbl">Win Probability</div>
                    <div class="stat-val accent">42.5%</div>
                </div>
                <div class="stat-box">
                    <div class="stat-lbl">Accuracy</div>
                    <div class="stat-val">98%</div>
                </div>
            </div>
        </div>

        <!-- Live Chat -->
        <div class="rp-box rp-chat">
            <div class="rp-header">
                💬 BATTLE CHAT
            </div>
            <div class="chat-scroll" id="chat-feed">
                <!-- Default message -->
                <div class="chat-msg">
                    <img src="assets/img/characters/code-warrior.png" class="chat-avatar" alt="Sys">
                    <div class="chat-body">
                        <div class="chat-author">System</div>
                        <div class="chat-text">Welcome to CodeQuest Arena! Good luck.</div>
                    </div>
                </div>
            </div>
            <div class="chat-input-box">
                <input type="text" id="chat-input" class="chat-input" placeholder="Type a message..." autocomplete="off">
                <button id="chat-send-btn" class="chat-btn">➤</button>
            </div>
        </div>

    </div>

</div>

<script>
    window.battleUserName = <?php echo json_encode($first_name); ?>;
    window.battleUserAvatar = <?php echo json_encode($you_char['image']); ?>;
</script>
<script src="assets/js/arena.js"></script> <!-- Reuses existing JS timer & typing logic -->

<!-- Battle Result Analysis Modal -->
<?php require_once 'includes/battle-result-modal.php'; ?>

</body>
</html>
