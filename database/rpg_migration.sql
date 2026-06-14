-- ============================================================
-- PROZONE RPG CHARACTER PROGRESSION SYSTEM - Migration
-- Run this once on your Laragon MySQL database
-- ============================================================

-- 1. Add character_class column to users table
ALTER TABLE users
    ADD COLUMN character_class VARCHAR(30) NOT NULL DEFAULT 'code-warrior';

-- 2. Create character_classes lookup table
CREATE TABLE IF NOT EXISTS `character_classes` (
    `id`              INT          NOT NULL AUTO_INCREMENT,
    `slug`            VARCHAR(30)  NOT NULL UNIQUE,
    `name`            VARCHAR(50)  NOT NULL,
    `title`           VARCHAR(80)  NOT NULL,
    `rarity`          ENUM('common','uncommon','rare','epic','legendary') NOT NULL DEFAULT 'common',
    `level_required`  INT          NOT NULL DEFAULT 1,
    `xp_required`     INT          NOT NULL DEFAULT 0,
    `description`     TEXT,
    `color_hex`       VARCHAR(7)   NOT NULL DEFAULT '#3B82F6',
    `badge_emoji`     VARCHAR(10)  NOT NULL DEFAULT 'âš”ï¸',
    `image_file`      VARCHAR(60)  NOT NULL DEFAULT 'code-warrior.png',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Seed character class data
INSERT IGNORE INTO `character_classes`
    (`slug`,`name`,`title`,`rarity`,`level_required`,`xp_required`,`description`,`color_hex`,`badge_emoji`,`image_file`)
VALUES
    ('code-warrior',    'Code Warrior',      'The Beginner Hero',                  'common',    1,  0,    'Setiap perjalanan epik dimulai dengan satu baris kode. Kamu adalah pejuang yang baru memulai petualangan coding!',                     '#3B82F6', 'âš”ï¸',  'code-warrior.png'),
    ('bug-hunter',      'Bug Hunter',        'Defender of Clean Code',             'uncommon',  5,  400,  'Kamu telah menguasai seni menemukan dan menghancurkan bug. Tidak ada error yang lolos dari pengamatanmu!',                          '#20C7B7', 'ðŸ”',  'bug-hunter.png'),
    ('web-developer',   'Web Developer',     'Architect of the Web',               'uncommon',  10, 900,  'Kamu membangun pengalaman web yang menakjubkan. HTML, CSS, dan JavaScript adalah senjatamu!',                                       '#3B82F6', 'ðŸŒ',  'web-developer.png'),
    ('ai-engineer',     'AI Engineer',       'Builder of Intelligent Systems',     'rare',      15, 1400, 'Kamu melatih mesin untuk berpikir. Neural network, machine learning, dan AI adalah duniamu!',                                       '#14B8A6', 'ðŸ¤–',  'ai-engineer.png'),
    ('data-scientist',  'Data Scientist',    'Oracle of Data',                     'rare',      18, 1700, 'Data berbicara kepadamu. Kamu mengubah angka-angka menjadi wawasan yang mengubah dunia!',                                           '#F59E0B', 'ðŸ“Š',  'data-scientist.png'),
    ('cyber-ninja',     'Cyber Ninja',       'Master of Digital Shadows',          'epic',      20, 1900, 'Kamu bergerak diam-diam di dunia digital. Keamanan siber dan penetration testing adalah spesialisasimu!',                          '#06B6D4', 'ðŸ¥·',  'cyber-ninja.png'),
    ('fullstack-master','Full Stack Master', 'Supreme Developer',                   'epic',      30, 2900, 'Frontend dan backend tunduk padamu. Kamu membangun sistem lengkap dari nol hingga produksi!',                                       '#EC4899', 'ðŸ‘‘',  'fullstack-master.png'),
    ('tech-wizard',     'Tech Wizard',       'Conjurer of Digital Worlds',         'legendary', 50, 4900, 'Kamu telah mencapai puncak! Teknologi terdalam pun terbuka untukmu. Kode adalah mantramu, komputer adalah tongkat sihirmu!',        '#FF6B35', 'ðŸ§™',  'tech-wizard.png');

-- 4. Set character_class for existing users based on their current level
UPDATE users SET character_class = CASE
    WHEN level >= 50 THEN 'tech-wizard'
    WHEN level >= 30 THEN 'fullstack-master'
    WHEN level >= 20 THEN 'cyber-ninja'
    WHEN level >= 18 THEN 'data-scientist'
    WHEN level >= 15 THEN 'ai-engineer'
    WHEN level >= 10 THEN 'web-developer'
    WHEN level >= 5  THEN 'bug-hunter'
    ELSE 'code-warrior'
END;
