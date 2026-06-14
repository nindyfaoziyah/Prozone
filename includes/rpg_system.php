<?php
/**
 * PROZONE RPG Character Progression System
 * Defines all character classes, unlock logic, and helper functions.
 */

// ============================================================
// CHARACTER CLASS DEFINITIONS
// ============================================================
define('RPG_CLASSES', [
    'code-warrior' => [
        'slug'           => 'code-warrior',
        'name'           => 'Code Warrior',
        'title'          => 'The Beginner Hero',
        'rarity'         => 'common',
        'rarity_label'   => 'Common',
        'level_required' => 1,
        'xp_required'    => 0,
        'description'    => 'Setiap perjalanan epik dimulai dengan satu baris kode.',
        'color'          => '#3B82F6',
        'gradient'       => 'linear-gradient(135deg, #3B82F6, #0EA5E9)',
        'badge'          => '⚔️',
        'image'          => 'assets/img/characters/code-warrior.png',
    ],
    'bug-hunter' => [
        'slug'           => 'bug-hunter',
        'name'           => 'Bug Hunter',
        'title'          => 'Defender of Clean Code',
        'rarity'         => 'uncommon',
        'rarity_label'   => 'Uncommon',
        'level_required' => 5,
        'xp_required'    => 400,
        'description'    => 'Tidak ada error yang lolos dari pengamatanmu!',
        'color'          => '#20C7B7',
        'gradient'       => 'linear-gradient(135deg, #20C7B7, #0FAE9F)',
        'badge'          => '🔍',
        'image'          => 'assets/img/characters/bug-hunter.png',
    ],
    'web-developer' => [
        'slug'           => 'web-developer',
        'name'           => 'Web Developer',
        'title'          => 'Architect of the Web',
        'rarity'         => 'uncommon',
        'rarity_label'   => 'Uncommon',
        'level_required' => 10,
        'xp_required'    => 900,
        'description'    => 'HTML, CSS, dan JavaScript adalah senjatamu!',
        'color'          => '#3B82F6',
        'gradient'       => 'linear-gradient(135deg, #3B82F6, #60A5FA)',
        'badge'          => 'ðŸŒ',
        'image'          => 'assets/img/characters/web-developer.png',
    ],
    'ai-engineer' => [
        'slug'           => 'ai-engineer',
        'name'           => 'AI Engineer',
        'title'          => 'Builder of Intelligent Systems',
        'rarity'         => 'rare',
        'rarity_label'   => 'Rare',
        'level_required' => 15,
        'xp_required'    => 1400,
        'description'    => 'Neural network dan machine learning adalah duniamu!',
        'color'          => '#14B8A6',
        'gradient'       => 'linear-gradient(135deg, #14B8A6, #2DD4BF)',
        'badge'          => 'ðŸ¤–',
        'image'          => 'assets/img/characters/ai-engineer.png',
    ],
    'data-scientist' => [
        'slug'           => 'data-scientist',
        'name'           => 'Data Scientist',
        'title'          => 'Oracle of Data',
        'rarity'         => 'rare',
        'rarity_label'   => 'Rare',
        'level_required' => 18,
        'xp_required'    => 1700,
        'description'    => 'Kamu mengubah angka-angka menjadi wawasan yang mengubah dunia!',
        'color'          => '#F59E0B',
        'gradient'       => 'linear-gradient(135deg, #F59E0B, #FBBF24)',
        'badge'          => 'ðŸ“Š',
        'image'          => 'assets/img/characters/data-scientist.png',
    ],
    'cyber-ninja' => [
        'slug'           => 'cyber-ninja',
        'name'           => 'Cyber Ninja',
        'title'          => 'Master of Digital Shadows',
        'rarity'         => 'epic',
        'rarity_label'   => 'Epic',
        'level_required' => 20,
        'xp_required'    => 1900,
        'description'    => 'Keamanan siber dan penetration testing adalah spesialisasimu!',
        'color'          => '#06B6D4',
        'gradient'       => 'linear-gradient(135deg, #0F172A, #06B6D4)',
        'badge'          => 'ðŸ¥·',
        'image'          => 'assets/img/characters/cyber-ninja.png',
    ],
    'fullstack-master' => [
        'slug'           => 'fullstack-master',
        'name'           => 'Full Stack Master',
        'title'          => 'Supreme Developer',
        'rarity'         => 'epic',
        'rarity_label'   => 'Epic',
        'level_required' => 30,
        'xp_required'    => 2900,
        'description'    => 'Frontend dan backend tunduk padamu!',
        'color'          => '#EC4899',
        'gradient'       => 'linear-gradient(135deg, #3B82F6, #EC4899)',
        'badge'          => 'ðŸ‘‘',
        'image'          => 'assets/img/characters/fullstack-master.png',
    ],
    'tech-wizard' => [
        'slug'           => 'tech-wizard',
        'name'           => 'Tech Wizard',
        'title'          => 'Conjurer of Digital Worlds',
        'rarity'         => 'legendary',
        'rarity_label'   => 'Legendary',
        'level_required' => 50,
        'xp_required'    => 4900,
        'description'    => 'Teknologi terdalam pun terbuka untukmu. Kode adalah mantramu!',
        'color'          => '#FF6B35',
        'gradient'       => 'linear-gradient(135deg, #FF6B35, #14B8A6, #06B6D4)',
        'badge'          => 'ðŸ§™',
        'image'          => 'assets/img/characters/tech-wizard.png',
    ],
]);

// ============================================================
// HELPER FUNCTIONS
// ============================================================

/**
 * Check if a specific character class is unlocked for the user.
 */
function isClassUnlocked(string $slug, int $level, int $xp): bool {
    $classes = RPG_CLASSES;
    if (!isset($classes[$slug])) return false;
    $cls = $classes[$slug];
    return $level >= $cls['level_required'] && $xp >= $cls['xp_required'];
}

/**
 * Get the highest class a user has unlocked automatically (for auto-assign).
 */
function getAutoClass(int $level, int $xp): string {
    $best = 'code-warrior';
    foreach (RPG_CLASSES as $slug => $cls) {
        if ($level >= $cls['level_required'] && $xp >= $cls['xp_required']) {
            $best = $slug;
        }
    }
    return $best;
}

/**
 * Get the next class to unlock for the user.
 */
function getNextUnlock(int $level, int $xp): ?array {
    foreach (RPG_CLASSES as $slug => $cls) {
        if ($level < $cls['level_required'] || $xp < $cls['xp_required']) {
            return $cls;
        }
    }
    return null; // Max class reached
}

/**
 * Get a class definition array safely.
 */
function getClassData(string $slug): array {
    return RPG_CLASSES[$slug] ?? RPG_CLASSES['code-warrior'];
}

/**
 * Get rarity CSS class name.
 */
function getRarityClass(string $rarity): string {
    return 'rarity-' . $rarity;
}

/**
 * Validate that a slug is a valid character class.
 */
function isValidClass(string $slug): bool {
    return isset(RPG_CLASSES[$slug]);
}

/**
 * Auto-update user's character_class in the DB based on current level/XP.
 * Called after XP is awarded to keep the class current.
 */
function syncCharacterClass($db, int $user_id, int $level, int $xp, string $current_class): string {
    $highest = getAutoClass($level, $xp);
    // Only auto-upgrade if the user hasn't manually chosen a higher one
    // Keep current if it's still unlocked and was manually chosen
    if (isClassUnlocked($current_class, $level, $xp)) {
        return $current_class;
    }
    // Current class is now unlocked or user has lower class â€” upgrade to highest
    $stmt = $db->prepare("UPDATE users SET character_class = :cls WHERE id = :uid");
    $stmt->bindParam(':cls', $highest);
    $stmt->bindParam(':uid', $user_id);
    $stmt->execute();
    return $highest;
}
