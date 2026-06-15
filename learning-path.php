<?php
require_once 'config/config.php';
requireLogin();
require_once 'includes/icons.php';

$page_title       = 'Learning Path';
$page_description = 'Pilih role tujuanmu, lalu ikuti jalur belajar yang sesuai.';
$page_css         = ['pages/learning-path.css'];
$body_class       = 'rpg-journey';
$force_theme      = 'dark';
$isEmbed          = isset($_GET['embed']) && $_GET['embed'] === '1';

$roles = [
    'frontend' => [
        'name' => 'Frontend Developer',
        'icon' => '🎨',
        'desc' => 'Bangun antarmuka web yang indah, interaktif, dan responsif.',
        'color' => '#0EA5E9',
        'glow' => 'rgba(14,165,233,0.3)',
        'accent' => '#06B6D4',
        'levels' => [
            ['level' => 1, 'name' => 'HTML Forest', 'emoji' => '🌳', 'desc' => 'Kuasi fondasi markup semantic dan struktur dokumen web.', 'xp' => 100, 'materi' => 8, 'boss' => 'Buat halaman profil pribadi', 'quests' => ['Apa itu HTML', 'Struktur HTML', 'Heading & Paragraph', 'Link & Image', 'List', 'Table', 'Form Dasar', 'Semantic HTML']],
            ['level' => 2, 'name' => 'CSS Desert', 'emoji' => '🏜️', 'desc' => 'Ciptakan visual memukau dengan layout dan styling modern.', 'xp' => 150, 'materi' => 8, 'boss' => 'Layout landing page kreatif', 'quests' => ['CSS Introduction', 'Selector', 'Color & Typography', 'Box Model', 'Flexbox', 'Grid', 'Animation', 'Responsive Design']],
            ['level' => 3, 'name' => 'JavaScript City', 'emoji' => '🌆', 'desc' => 'Hidupkan web dengan logika interaktif dan manipulasi DOM.', 'xp' => 200, 'materi' => 8, 'boss' => 'Buat kalkulator pintar interaktif', 'quests' => ['Variable & Data Type', 'Function', 'Conditional', 'Loop', 'DOM Manipulation', 'Event Handling', 'Array & Object', 'Local Storage']],
            ['level' => 4, 'name' => 'React Kingdom', 'emoji' => '⚛️', 'desc' => 'Bangun SPA modern dengan komponen reusable dan state management.', 'xp' => 250, 'materi' => 7, 'boss' => 'Dashboard aplikasi React dinamis', 'quests' => ['JSX & Component', 'Props & State', 'Hooks', 'Event Handler', 'Conditional Rendering', 'List & Key', 'Routing']],
            ['level' => 5, 'name' => 'API Ocean', 'emoji' => '🌊', 'desc' => 'Hubungkan frontend dengan layanan backend dan API eksternal.', 'xp' => 220, 'materi' => 6, 'boss' => 'Aplikasi cuaca realtime', 'quests' => ['HTTP & Fetch API', 'RESTful API', 'Axios', 'Async/Await', 'Error Handling', 'API Authentication']],
            ['level' => 6, 'name' => 'NextJS Citadel', 'emoji' => '🏰', 'desc' => 'Kuasai framework React production-grade dengan SSR dan SSG.', 'xp' => 280, 'materi' => 7, 'boss' => 'Blog platform dengan NextJS', 'quests' => ['Pages & Routing', 'SSR & SSG', 'API Routes', 'Dynamic Routes', 'Image Optimization', 'Middleware', 'Deployment']],
            ['level' => 7, 'name' => 'Performance Mountain', 'emoji' => '⛰️', 'desc' => 'Optimasi performa web untuk pengalaman pengguna terbaik.', 'xp' => 230, 'materi' => 6, 'boss' => 'Audit & optimasi website', 'quests' => ['Core Web Vitals', 'Lazy Loading', 'Code Splitting', 'Caching Strategy', 'Bundle Optimization', 'Monitoring']],
            ['level' => 8, 'name' => 'Frontend Master Castle', 'emoji' => '🏆', 'desc' => 'Menjadi master frontend dengan portofolio siap produksi.', 'xp' => 350, 'materi' => 6, 'boss' => 'Portofolio frontend masterpiece', 'quests' => ['Advanced Patterns', 'Testing', 'CI/CD', 'Monorepo', 'Accessibility', 'Portofolio Build']],
        ],
    ],
    'backend' => [
        'name' => 'Backend Developer',
        'icon' => '⚙️',
        'desc' => 'Kuasai logika server, API, database, dan arsitektur aplikasi.',
        'color' => '#22C55E',
        'glow' => 'rgba(34,197,94,0.3)',
        'accent' => '#10B981',
        'levels' => [
            ['level' => 1, 'name' => 'PHP Valley', 'emoji' => '🏞️', 'desc' => 'Mulai perjalanan backend dengan PHP fundamental.', 'xp' => 100, 'materi' => 8, 'boss' => 'Sistem registrasi user sederhana', 'quests' => ['PHP Introduction', 'Variable & Tipe Data', 'Array & Loops', 'Function', 'GET & POST', 'Session & Cookie', 'File Handling', 'Error Handling']],
            ['level' => 2, 'name' => 'Database Cave', 'emoji' => '🗄️', 'desc' => 'Rancang dan kelola database relasional dengan SQL.', 'xp' => 150, 'materi' => 7, 'boss' => 'Schema database e-commerce', 'quests' => ['Database Design', 'CREATE & INSERT', 'SELECT & WHERE', 'JOIN', 'Index & Constraint', 'Migration', 'Backup & Restore']],
            ['level' => 3, 'name' => 'API Bridge', 'emoji' => '🌉', 'desc' => 'Bangun REST API yang aman, cepat, dan terstruktur.', 'xp' => 200, 'materi' => 7, 'boss' => 'REST API untuk blog platform', 'quests' => ['RESTful Design', 'Routing & Controller', 'Request & Response', 'Validation', 'Authentication JWT', 'Middleware', 'Documentation']],
            ['level' => 4, 'name' => 'Laravel Fortress', 'emoji' => '🏛️', 'desc' => 'Framework PHP modern dengan fitur lengkap siap produksi.', 'xp' => 250, 'materi' => 8, 'boss' => 'Aplikasi manajemen tugas', 'quests' => ['Artisan & Routing', 'Blade Templating', 'Eloquent ORM', 'Relationships', 'Authentication', 'Authorization', 'Testing', 'Queues']],
            ['level' => 5, 'name' => 'Microservices Ocean', 'emoji' => '🌊', 'desc' => 'Arsitektur skalabel dengan microservices dan message broker.', 'xp' => 280, 'materi' => 6, 'boss' => 'Sistem notifikasi microservices', 'quests' => ['Microservices Concept', 'API Gateway', 'RabbitMQ', 'Docker', 'Service Discovery', 'Logging & Monitoring']],
            ['level' => 6, 'name' => 'Security Keep', 'emoji' => '🔒', 'desc' => 'Lindungi aplikasi dari ancaman dan kerentanan keamanan.', 'xp' => 230, 'materi' => 6, 'boss' => 'Security audit & penetration test', 'quests' => ['SQL Injection', 'XSS & CSRF', 'Encryption', 'OAuth 2.0', 'Rate Limiting', 'Security Headers']],
            ['level' => 7, 'name' => 'DevOps Mountain', 'emoji' => '⛰️', 'desc' => 'Automasi deployment dan infrastructure sebagai kode.', 'xp' => 300, 'materi' => 7, 'boss' => 'CI/CD pipeline full automation', 'quests' => ['CI/CD Pipeline', 'Docker Compose', 'Kubernetes', 'Cloud Deployment', 'Monitoring', 'Logging', 'Scaling']],
            ['level' => 8, 'name' => 'Backend Master Throne', 'emoji' => '👑', 'desc' => 'Capai puncak karir sebagai backend arsitek.', 'xp' => 400, 'materi' => 6, 'boss' => 'Arsitektur backend enterprise', 'quests' => ['System Design', 'Caching Strategy', 'Database Sharding', 'Load Balancing', 'Disaster Recovery', 'Performance Tuning']],
        ],
    ],
    'fullstack' => [
        'name' => 'Full Stack Developer',
        'icon' => '🚀',
        'desc' => 'Kuasai frontend hingga backend, jadi developer serba bisa!',
        'color' => '#A855F7',
        'glow' => 'rgba(168,85,247,0.3)',
        'accent' => '#8B5CF6',
        'levels' => [
            ['level' => 1, 'name' => 'Web Foundation', 'emoji' => '🧱', 'desc' => 'Fondasi web: HTML, CSS, dan dasar pemrograman.', 'xp' => 100, 'materi' => 8, 'boss' => 'Halaman web portofolio statis', 'quests' => ['HTML Semantic', 'CSS Layout', 'Responsive Design', 'CSS Grid', 'Flexbox', 'CSS Variables', 'Basic JS', 'DOM']],
            ['level' => 2, 'name' => 'Frontend Core', 'emoji' => '⚛️', 'desc' => 'JavaScript modern dan framework React untuk antarmuka dinamis.', 'xp' => 200, 'materi' => 8, 'boss' => 'Aplikasi todo dengan React', 'quests' => ['ES6+ Syntax', 'React Component', 'State & Props', 'Hooks', 'Routing', 'API Integration', 'State Management', 'Testing']],
            ['level' => 3, 'name' => 'Backend Core', 'emoji' => '⚙️', 'desc' => 'PHP, database, dan REST API untuk logika server.', 'xp' => 200, 'materi' => 8, 'boss' => 'REST API dengan autentikasi', 'quests' => ['PHP OOP', 'Laravel Basics', 'Eloquent ORM', 'REST API', 'JWT Auth', 'Validation', 'Middleware', 'Testing']],
            ['level' => 4, 'name' => 'Database Realm', 'emoji' => '🗄️', 'desc' => 'SQL, NoSQL, dan strategi penyimpanan data.', 'xp' => 180, 'materi' => 6, 'boss' => 'Schema & query optimization', 'quests' => ['Relational DB', 'SQL Advanced', 'Indexing', 'NoSQL MongoDB', 'Redis Cache', 'Data Modeling']],
            ['level' => 5, 'name' => 'API Integration', 'emoji' => '🔗', 'desc' => 'Hubungkan frontend-backend dalam ekosistem penuh.', 'xp' => 220, 'materi' => 6, 'boss' => 'Full stack blog platform', 'quests' => ['REST API Design', 'GraphQL Basics', 'WebSocket', 'File Upload', 'API Security', 'Documentation']],
            ['level' => 6, 'name' => 'DevOps & Deploy', 'emoji' => '🚢', 'desc' => 'Deployment, CI/CD, dan infrastructure management.', 'xp' => 250, 'materi' => 6, 'boss' => 'Deploy full stack app ke cloud', 'quests' => ['Docker', 'CI/CD', 'Cloud Deployment', 'Domain & SSL', 'Monitoring', 'Logging']],
            ['level' => 7, 'name' => 'Performance Peak', 'emoji' => '⛰️', 'desc' => 'Optimasi performa frontend dan backend.', 'xp' => 250, 'materi' => 6, 'boss' => 'Performance audit & optimization', 'quests' => ['Frontend Optimization', 'Backend Caching', 'Database Optimization', 'CDN', 'Load Testing', 'Profiling']],
            ['level' => 8, 'name' => 'Full Stack Empire', 'emoji' => '🏰', 'desc' => 'Arsitek aplikasi end-to-end siap produksi.', 'xp' => 400, 'materi' => 6, 'boss' => 'Aplikasi full stack enterprise', 'quests' => ['System Design', 'Microservices', 'Security', 'Testing', 'Scaling', 'Portofolio']],
        ],
    ],
    'mobile' => [
        'name' => 'Mobile Developer',
        'icon' => '📱',
        'desc' => 'Kembangkan aplikasi mobile native dan cross-platform.',
        'color' => '#F97316',
        'glow' => 'rgba(249,115,22,0.3)',
        'accent' => '#FB923C',
        'levels' => [
            ['level' => 1, 'name' => 'Kotlin Valley', 'emoji' => '🏞️', 'desc' => 'Dasar Kotlin untuk pengembangan Android modern.', 'xp' => 100, 'materi' => 7, 'boss' => 'Hello World Android app', 'quests' => ['Kotlin Basics', 'Variables & Types', 'Functions', 'Classes', 'Collections', 'Coroutines', 'Null Safety']],
            ['level' => 2, 'name' => 'Layout Designer', 'emoji' => '🎨', 'desc' => 'Bangun antarmuka mobile yang intuitif dan responsif.', 'xp' => 150, 'materi' => 7, 'boss' => 'Login screen dengan Jetpack Compose', 'quests' => ['Jetpack Compose', 'Layouts', 'Theme & Style', 'State', 'Navigation', 'Material Design', 'Animation']],
            ['level' => 3, 'name' => 'Data Storage', 'emoji' => '💾', 'desc' => 'Kelola data lokal dan remote di aplikasi mobile.', 'xp' => 180, 'materi' => 6, 'boss' => 'Note taking app offline', 'quests' => ['Room Database', 'SharedPreferences', 'DataStore', 'File Storage', 'ViewModel', 'Repository Pattern']],
            ['level' => 4, 'name' => 'Network Island', 'emoji' => '🌐', 'desc' => 'Koneksikan aplikasi dengan API dan layanan cloud.', 'xp' => 200, 'materi' => 6, 'boss' => 'Aplikasi cuaca mobile', 'quests' => ['Retrofit', 'REST API', 'JSON Parsing', 'Image Loading', 'Error Handling', 'Offline Cache']],
            ['level' => 5, 'name' => 'Firebase Peak', 'emoji' => '⛰️', 'desc' => 'Layanan cloud untuk autentikasi, database, dan push notification.', 'xp' => 220, 'materi' => 6, 'boss' => 'Chat app realtime', 'quests' => ['Firebase Auth', 'Firestore', 'Realtime DB', 'Cloud Messaging', 'Analytics', 'Crashlytics']],
            ['level' => 6, 'name' => 'Testing Canyon', 'emoji' => '🧪', 'desc' => 'Testing aplikasi untuk kualitas dan stabilitas.', 'xp' => 200, 'materi' => 5, 'boss' => 'Test suite coverage >80%', 'quests' => ['Unit Testing', 'Integration Test', 'UI Test', 'Mocking', 'Test Coverage']],
            ['level' => 7, 'name' => 'Play Store Gate', 'emoji' => '🏛️', 'desc' => 'Publikasi dan maintain aplikasi di Google Play Store.', 'xp' => 250, 'materi' => 5, 'boss' => 'Publikasi app ke Play Store', 'quests' => ['App Signing', 'Versioning', 'Release Bundle', 'Store Listing', 'In-App Updates']],
            ['level' => 8, 'name' => 'Mobile Master Tower', 'emoji' => '🏆', 'desc' => 'Arsitek aplikasi mobile kompleks siap produksi.', 'xp' => 350, 'materi' => 6, 'boss' => 'Aplikasi mobile production-grade', 'quests' => ['Architecture', 'Dependency Injection', 'Modularization', 'Security', 'Performance', 'Portofolio']],
        ],
    ],
    'uiux' => [
        'name' => 'UI/UX Designer',
        'icon' => '🖌️',
        'desc' => 'Ciptakan pengalaman digital yang indah dan user-friendly.',
        'color' => '#EC4899',
        'glow' => 'rgba(236,72,153,0.3)',
        'accent' => '#F472B6',
        'levels' => [
            ['level' => 1, 'name' => 'Design Fundamentals', 'emoji' => '🎯', 'desc' => 'Prinsip dasar desain visual dan komposisi.', 'xp' => 100, 'materi' => 7, 'boss' => 'Poster digital dengan prinsip desain', 'quests' => ['Color Theory', 'Typography', 'Layout & Grid', 'Hierarchy', 'Balance & Contrast', 'Gestalt Principles', 'Design Thinking']],
            ['level' => 2, 'name' => 'Figma Forest', 'emoji' => '🌲', 'desc' => 'Kuasi tools desain modern dengan Figma.', 'xp' => 150, 'materi' => 7, 'boss' => 'Wireframe aplikasi mobile', 'quests' => ['Figma Interface', 'Shapes & Vectors', 'Auto Layout', 'Components', 'Variants', 'Prototyping', 'Collaboration']],
            ['level' => 3, 'name' => 'User Research Lab', 'emoji' => '🔬', 'desc' => 'Pahami pengguna melalui riset dan validasi.', 'xp' => 180, 'materi' => 6, 'boss' => 'User research report lengkap', 'quests' => ['User Interview', 'Survey Design', 'Persona', 'Journey Map', 'Empathy Map', 'Usability Testing']],
            ['level' => 4, 'name' => 'Visual Design Studio', 'emoji' => '🎨', 'desc' => 'Ciptakan visual identitas yang konsisten dan menarik.', 'xp' => 200, 'materi' => 7, 'boss' => 'Design system komponen', 'quests' => ['Mood Board', 'Style Guide', 'Icon Design', 'Illustration', 'Micro-interaction', 'Motion Design', 'Design System']],
            ['level' => 5, 'name' => 'Prototype Peak', 'emoji' => '⛰️', 'desc' => 'High-fidelity prototype dengan interaksi kompleks.', 'xp' => 200, 'materi' => 5, 'boss' => 'Prototype aplikasi fintech', 'quests' => ['Interactive Components', 'Smart Animate', 'Overflow', 'Conditional Logic', 'User Flow']],
            ['level' => 6, 'name' => 'UX Strategy Tower', 'emoji' => '🗼', 'desc' => 'Strategi UX untuk produk digital yang sukses.', 'xp' => 230, 'materi' => 6, 'boss' => 'UX strategy untuk produk digital', 'quests' => ['Information Architecture', 'Accessibility', 'Design System Ops', 'Metrics & KPI', 'A/B Testing', 'Design critique']],
            ['level' => 7, 'name' => 'Portfolio Castle', 'emoji' => '🏰', 'desc' => 'Bangun portofolio desain yang memukau.', 'xp' => 250, 'materi' => 5, 'boss' => 'Case study portofolio lengkap', 'quests' => ['Case Study Structure', 'Visual Storytelling', 'Presentation', 'Dribbble/Behance', 'Personal Branding']],
            ['level' => 8, 'name' => 'Design Master Hall', 'emoji' => '👑', 'desc' => 'Puncak karir sebagai principal designer.', 'xp' => 350, 'materi' => 5, 'boss' => 'Design leadership portfolio', 'quests' => ['Design Leadership', 'Design Ops', 'Mentoring', 'Innovation', 'Industry Impact']],
        ],
    ],
    'data' => [
        'name' => 'Data Science',
        'icon' => '📊',
        'desc' => 'Olah data, temukan insight, dan buat keputusan berbasis data.',
        'color' => '#06B6D4',
        'glow' => 'rgba(6,182,212,0.3)',
        'accent' => '#22D3EE',
        'levels' => [
            ['level' => 1, 'name' => 'Python Forest', 'emoji' => '🐍', 'desc' => 'Python sebagai fondasi data science modern.', 'xp' => 100, 'materi' => 7, 'boss' => 'Analisis dataset CSV pertama', 'quests' => ['Python Basics', 'Data Types', 'Control Flow', 'Functions', 'Pandas Intro', 'NumPy', 'File I/O']],
            ['level' => 2, 'name' => 'Statistics Canyon', 'emoji' => '📐', 'desc' => 'Statistik deskriptif dan inferensial untuk analisis data.', 'xp' => 180, 'materi' => 7, 'boss' => 'Analisis statistik dataset', 'quests' => ['Descriptive Stats', 'Probability', 'Distribution', 'Hypothesis Testing', 'Correlation', 'Regression', 'Bayesian']],
            ['level' => 3, 'name' => 'SQL Desert', 'emoji' => '🏜️', 'desc' => 'Query dan manipulasi data di database relasional.', 'xp' => 150, 'materi' => 6, 'boss' => 'Data warehouse query', 'quests' => ['SQL SELECT', 'JOIN & Subquery', 'Aggregation', 'Window Functions', 'CTE', 'Query Optimization']],
            ['level' => 4, 'name' => 'Visualization Island', 'emoji' => '📈', 'desc' => 'Visualisasikan data untuk storytelling yang powerful.', 'xp' => 180, 'materi' => 6, 'boss' => 'Dashboard interaktif Tableau', 'quests' => ['Matplotlib', 'Seaborn', 'Plotly', 'Tableau', 'Dashboard Design', 'Storytelling']],
            ['level' => 5, 'name' => 'Machine Learning Mountain', 'emoji' => '🤖', 'desc' => 'Algoritma ML untuk prediksi dan klasifikasi.', 'xp' => 250, 'materi' => 7, 'boss' => 'Model prediksi harga rumah', 'quests' => ['Supervised Learning', 'Classification', 'Regression', 'Decision Tree', 'Random Forest', 'SVM', 'Model Evaluation']],
            ['level' => 6, 'name' => 'Deep Learning Ocean', 'emoji' => '🌊', 'desc' => 'Neural network untuk problem kompleks.', 'xp' => 300, 'materi' => 6, 'boss' => 'Image classifier CNN', 'quests' => ['Neural Networks', 'TensorFlow', 'Keras', 'CNN', 'RNN', 'Transfer Learning']],
            ['level' => 7, 'name' => 'Big Data Peak', 'emoji' => '⛰️', 'desc' => 'Tools big data dan distributed computing.', 'xp' => 280, 'materi' => 6, 'boss' => 'Pipeline data processing', 'quests' => ['Hadoop', 'Spark', 'Data Pipeline', 'ETL', 'Data Lake', 'Cloud Platform']],
            ['level' => 8, 'name' => 'Data Science Citadel', 'emoji' => '🏆', 'desc' => 'Arsitek solusi data end-to-end.', 'xp' => 400, 'materi' => 6, 'boss' => 'Enterprise data platform', 'quests' => ['MLOps', 'Model Deployment', 'A/B Testing', 'Data Governance', 'Ethics & Bias', 'Portofolio']],
        ],
    ],
    'cyber' => [
        'name' => 'Cyber Security',
        'icon' => '🛡️',
        'desc' => 'Lindungi sistem dan data dari ancaman siber.',
        'color' => '#EF4444',
        'glow' => 'rgba(239,68,68,0.3)',
        'accent' => '#F87171',
        'levels' => [
            ['level' => 1, 'name' => 'Network Foundation', 'emoji' => '🌐', 'desc' => 'Dasar jaringan komputer dan protokol komunikasi.', 'xp' => 100, 'materi' => 7, 'boss' => 'Network topology mapping', 'quests' => ['OSI Layer', 'TCP/IP', 'DNS & DHCP', 'Subnetting', 'Routing', 'Firewall', 'VPN']],
            ['level' => 2, 'name' => 'Cryptography Cave', 'emoji' => '🔐', 'desc' => 'Enkripsi dan keamanan data untuk komunikasi aman.', 'xp' => 180, 'materi' => 6, 'boss' => 'Implementasi enkripsi file', 'quests' => ['Symmetric Encryption', 'Asymmetric', 'Hash Function', 'Digital Signature', 'PKI', 'SSL/TLS']],
            ['level' => 3, 'name' => 'Web Security Fort', 'emoji' => '🏰', 'desc' => 'Keamanan aplikasi web dari serangan umum.', 'xp' => 200, 'materi' => 7, 'boss' => 'Security audit website', 'quests' => ['OWASP Top 10', 'SQL Injection', 'XSS', 'CSRF', 'SSRF', 'Authentication', 'Secure Headers']],
            ['level' => 4, 'name' => 'Pentest Arena', 'emoji' => '⚔️', 'desc' => 'Teknik penetration testing dan ethical hacking.', 'xp' => 250, 'materi' => 7, 'boss' => 'Penetration test report', 'quests' => ['Reconnaissance', 'Scanning', 'Exploitation', 'Privilege Escalation', 'Post-exploitation', 'Metasploit', 'Reporting']],
            ['level' => 5, 'name' => 'Forensics Lab', 'emoji' => '🔬', 'desc' => 'Investigasi digital dan incident response.', 'xp' => 230, 'materi' => 6, 'boss' => 'Digital forensics analysis', 'quests' => ['Disk Forensics', 'Memory Forensics', 'Network Forensics', 'Malware Analysis', 'Log Analysis', 'Incident Response']],
            ['level' => 6, 'name' => 'Compliance Tower', 'emoji' => '📜', 'desc' => 'Standar keamanan dan regulasi kepatuhan.', 'xp' => 200, 'materi' => 5, 'boss' => 'Compliance audit checklist', 'quests' => ['ISO 27001', 'GDPR', 'PCI DSS', 'SOC 2', 'Risk Assessment']],
            ['level' => 7, 'name' => 'Cloud Security', 'emoji' => '☁️', 'desc' => 'Keamanan infrastruktur cloud dan DevOps.', 'xp' => 250, 'materi' => 6, 'boss' => 'Cloud security architecture', 'quests' => ['IAM', 'Cloud Network Security', 'Container Security', 'Kubernetes Security', 'Secrets Management', 'Compliance']],
            ['level' => 8, 'name' => 'Security Master Vault', 'emoji' => '🏆', 'desc' => 'Puncak karir sebagai CISO dan security architect.', 'xp' => 400, 'materi' => 6, 'boss' => 'Enterprise security program', 'quests' => ['Security Architecture', 'Threat Modeling', 'Red Team', 'Security Awareness', 'Crisis Management', 'Portofolio']],
        ],
    ],
    'ai' => [
        'name' => 'AI Engineer',
        'icon' => '🤖',
        'desc' => 'Kembangkan sistem cerdas dengan machine learning dan AI.',
        'color' => '#8B5CF6',
        'glow' => 'rgba(139,92,246,0.3)',
        'accent' => '#A78BFA',
        'levels' => [
            ['level' => 1, 'name' => 'Python Mountains', 'emoji' => '🐍', 'desc' => 'Python untuk AI dengan libraries dan best practices.', 'xp' => 100, 'materi' => 7, 'boss' => 'Python data pipeline', 'quests' => ['Python Advanced', 'OOP', 'Decorators', 'Generators', 'NumPy', 'Pandas', 'Data Pipeline']],
            ['level' => 2, 'name' => 'Math & Stats Valley', 'emoji' => '📐', 'desc' => 'Matematika dan statistik untuk machine learning.', 'xp' => 200, 'materi' => 7, 'boss' => 'Mathematical model implementation', 'quests' => ['Linear Algebra', 'Calculus', 'Probability', 'Optimization', 'Statistics', 'Information Theory', 'Bayesian']],
            ['level' => 3, 'name' => 'ML Fundamentals', 'emoji' => '🧠', 'desc' => 'Algoritma machine learning klasik dan implementasi.', 'xp' => 250, 'materi' => 8, 'boss' => 'ML model pipeline lengkap', 'quests' => ['Supervised Learning', 'Unsupervised Learning', 'Feature Engineering', 'Model Selection', 'Cross Validation', 'Hyperparameter Tuning', 'Ensemble', 'XGBoost']],
            ['level' => 4, 'name' => 'Deep Learning Ocean', 'emoji' => '🌊', 'desc' => 'Deep neural networks untuk problem kompleks.', 'xp' => 300, 'materi' => 7, 'boss' => 'Image recognition model', 'quests' => ['Neural Networks', 'TensorFlow', 'PyTorch', 'CNN', 'RNN & LSTM', 'Transformers', 'GANs']],
            ['level' => 5, 'name' => 'NLP Forest', 'emoji' => '🌲', 'desc' => 'Natural Language Processing untuk teks dan bahasa.', 'xp' => 280, 'materi' => 6, 'boss' => 'Chatbot NLP berbasis transformer', 'quests' => ['Tokenization', 'Word Embeddings', 'RNN for NLP', 'Transformer', 'BERT', 'LLM Fine-tuning']],
            ['level' => 6, 'name' => 'Computer Vision', 'emoji' => '👁️', 'desc' => 'Image processing dan computer vision untuk aplikasi visual.', 'xp' => 280, 'materi' => 6, 'boss' => 'Object detection system', 'quests' => ['Image Processing', 'CNN Architecture', 'Object Detection', 'Segmentation', 'YOLO', 'Face Recognition']],
            ['level' => 7, 'name' => 'MLOps Peak', 'emoji' => '⛰️', 'desc' => 'Deployment dan monitoring model ML di production.', 'xp' => 300, 'materi' => 6, 'boss' => 'ML pipeline production-grade', 'quests' => ['Model Serving', 'Docker for ML', 'MLflow', 'Pipeline Automation', 'Monitoring', 'A/B Testing']],
            ['level' => 8, 'name' => 'AI Mastermind', 'emoji' => '🏆', 'desc' => 'Puncak karir sebagai AI architect dan researcher.', 'xp' => 450, 'materi' => 6, 'boss' => 'AI system architecture', 'quests' => ['Reinforcement Learning', 'Multi-modal AI', 'AI Ethics', 'Research', 'Paper Implementation', 'Portofolio']],
        ],
    ],
];

// Progress simulation (from DB in production)
// For demo: mark first 2 levels of frontend as completed, level 3 as in-progress
$userProgress = [];
$userProgress['frontend'] = [
    1 => 'completed', 2 => 'completed', 3 => 'in-progress',
];
$userProgress['backend'] = [
    1 => 'completed',
];
$userProgress['fullstack'] = [];
$userProgress['mobile'] = [];
$userProgress['uiux'] = [];
$userProgress['data'] = [];
$userProgress['cyber'] = [];
$userProgress['ai'] = [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once 'includes/head.php'; ?>
    <style>
        html, body { background: #0B0D1E !important; color: #E2E8F0; }
        .page-wrapper.dashboard-main-container { background: #0B0D1E !important; }
    </style>
</head>
<body class="rpg-journey">
<?php if (!$isEmbed) require_once 'navbar.php'; ?>

<div class="page-wrapper dashboard-main-container" style="background:#0B0D1E">
    <div class="rpg-world">

        <!-- ========== STEP 1: ROLE SELECTION ========== -->
        <div class="rpg-step" id="rpg-step-role">
            <section class="rpg-hero">
                <div class="rpg-hero-bg">
                    <div class="rpg-particle" style="top:10%;left:5%;width:4px;height:4px;animation-delay:0s"></div>
                    <div class="rpg-particle" style="top:20%;left:90%;width:3px;height:3px;animation-delay:1.2s"></div>
                    <div class="rpg-particle" style="top:50%;left:12%;width:5px;height:5px;animation-delay:0.6s"></div>
                    <div class="rpg-particle" style="top:70%;left:80%;width:4px;height:4px;animation-delay:0.3s"></div>
                    <div class="rpg-particle" style="top:85%;left:40%;width:3px;height:3px;animation-delay:1.8s"></div>
                    <div class="rpg-particle" style="top:35%;left:95%;width:4px;height:4px;animation-delay:0.9s"></div>
                    <div class="rpg-particle" style="top:60%;left:3%;width:3px;height:3px;animation-delay:2.1s"></div>
                    <div class="rpg-particle" style="top:15%;left:50%;width:5px;height:5px;animation-delay:1.5s"></div>
                </div>
                <div class="rpg-hero-content">
                    <div class="rpg-hero-badge">&#9733; PILIH ROLE-MU</div>
                    <h1>Choose Your <span class="rpg-highlight">Destiny</span></h1>
                    <p>Setiap role memiliki jalur petualangan berbeda. Pilih karir impianmu dan mulai perjalanan epic-mu!</p>
                    <div class="rpg-hero-stats">
                        <div class="rpg-hero-stat">
                            <span class="rpg-hero-stat-num">8</span>
                            <span class="rpg-hero-stat-label">Role Tersedia</span>
                        </div>
                        <div class="rpg-hero-stat">
                            <span class="rpg-hero-stat-num">64</span>
                            <span class="rpg-hero-stat-label">Total Level</span>
                        </div>
                        <div class="rpg-hero-stat">
                            <span class="rpg-hero-stat-num">+5000</span>
                            <span class="rpg-hero-stat-label">Max XP</span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rpg-roles">
                <div class="rpg-roles-header">
                    <h2>&#127758; Pilih Petualanganmu</h2>
                    <p>Klik salah satu role untuk memulai perjalanan belajar.</p>
                </div>
                <div class="rpg-roles-grid">
                    <?php foreach ($roles as $id => $role):
                        $totalLevels = count($role['levels']);
                        $totalXP = array_sum(array_column($role['levels'], 'xp'));
                        $totalMateri = array_sum(array_column($role['levels'], 'materi'));
                    ?>
                    <div class="rpg-role-card" data-role="<?php echo $id; ?>"
                         style="--role-color: <?php echo $role['color']; ?>; --role-glow: <?php echo $role['glow']; ?>">
                        <div class="rpg-role-glow-bg"></div>
                        <div class="rpg-role-visual">
                            <span class="rpg-role-emoji"><?php echo $role['icon']; ?></span>
                        </div>
                        <div class="rpg-role-info">
                            <h3><?php echo $role['name']; ?></h3>
                            <p><?php echo $role['desc']; ?></p>
                        </div>
                        <div class="rpg-role-meta">
                            <span><span class="rpg-meta-icon">&#127891;</span> <?php echo $totalLevels; ?> Level</span>
                            <span><span class="rpg-meta-icon">&#9733;</span> +<?php echo $totalXP; ?> XP</span>
                            <span><span class="rpg-meta-icon">&#128218;</span> <?php echo $totalMateri; ?> Materi</span>
                        </div>
                        <div class="rpg-role-action">
                            <span class="rpg-role-btn">Mulai Petualangan &#10140;</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>

        <!-- ========== STEP 2: RPG WORLD MAP ========== -->
        <div class="rpg-step" id="rpg-step-map" style="display:none">
            <!-- Top Bar -->
            <div class="rpg-topbar">
                <div class="rpg-topbar-left">
                    <button class="rpg-back-btn" id="rpg-back-btn">&#8592; Ganti Role</button>
                    <div class="rpg-topbar-role">
                        <span class="rpg-topbar-emoji" id="rpg-topbar-emoji"></span>
                        <div>
                            <span class="rpg-topbar-label">RPG Journey</span>
                            <h2 id="rpg-topbar-name"></h2>
                        </div>
                    </div>
                </div>
                <div class="rpg-topbar-right" id="rpg-topbar-stats"></div>
            </div>

            <!-- Progress Bar -->
            <div class="rpg-progress-rail">
                <div class="rpg-progress-track" id="rpg-progress-track">
                    <div class="rpg-progress-fill" id="rpg-progress-fill" style="width:0%"></div>
                </div>
                <div class="rpg-progress-info">
                    <span id="rpg-progress-pct">0%</span>
                    <span id="rpg-progress-text">Complete</span>
                </div>
            </div>

            <!-- Main Map Area -->
            <div class="rpg-map-layout">
                <!-- Left: Journey Path -->
                <div class="rpg-journey" id="rpg-journey">
                    <!-- SVG Connection Lines -->
                    <svg class="rpg-connections" id="rpg-connections" aria-hidden="true"></svg>
                    <!-- Level Nodes -->
                    <div class="rpg-nodes" id="rpg-nodes"></div>
                </div>

                <!-- Right: Quest Detail Panel -->
                <div class="rpg-panel" id="rpg-panel">
                    <div class="rpg-panel-placeholder" id="rpg-panel-placeholder">
                        <span class="rpg-panel-placeholder-icon">&#127758;</span>
                        <h3>Pilih Quest</h3>
                        <p>Klik salah satu node level untuk melihat detail quest, skill, dan misi yang harus diselesaikan.</p>
                    </div>

                    <div class="rpg-panel-content" id="rpg-panel-content" style="display:none">
                        <div class="rpg-panel-head">
                            <div class="rpg-panel-icon-wrap">
                                <span class="rpg-panel-icon" id="rpg-panel-icon"></span>
                            </div>
                            <div class="rpg-panel-head-info">
                                <span class="rpg-panel-level" id="rpg-panel-level"></span>
                                <h2 id="rpg-panel-name"></h2>
                                <p id="rpg-panel-desc"></p>
                            </div>
                        </div>

                        <div class="rpg-panel-ring-section">
                            <svg class="rpg-panel-ring" viewBox="0 0 120 120">
                                <circle cx="60" cy="60" r="52" fill="none" stroke="#1E293B" stroke-width="8"/>
                                <circle cx="60" cy="60" r="52" fill="none" stroke="var(--role-color)" stroke-width="8"
                                    stroke-dasharray="326.73" stroke-dashoffset="326.73" stroke-linecap="round"
                                    id="rpg-panel-ring-circle" transform="rotate(-90 60 60)"/>
                                <text x="60" y="54" text-anchor="middle" fill="#E2E8F0" font-size="20" font-weight="800" id="rpg-panel-ring-text">0%</text>
                                <text x="60" y="72" text-anchor="middle" fill="#64748B" font-size="10" font-weight="600">PROGRESS</text>
                            </svg>
                        </div>

                        <div class="rpg-panel-stats">
                            <div class="rpg-panel-stat">
                                <span class="rpg-panel-stat-icon">&#9733;</span>
                                <span class="rpg-panel-stat-label">XP Reward</span>
                                <strong id="rpg-panel-xp">0</strong>
                            </div>
                            <div class="rpg-panel-stat">
                                <span class="rpg-panel-stat-icon">&#128218;</span>
                                <span class="rpg-panel-stat-label">Materi</span>
                                <strong id="rpg-panel-materi">0</strong>
                            </div>
                            <div class="rpg-panel-stat">
                                <span class="rpg-panel-stat-icon">&#127942;</span>
                                <span class="rpg-panel-stat-label">Status</span>
                                <strong id="rpg-panel-status">-</strong>
                            </div>
                        </div>

                        <div class="rpg-panel-section">
                            <div class="rpg-panel-section-title">&#128161; Boss Quest</div>
                            <div class="rpg-panel-boss" id="rpg-panel-boss"></div>
                        </div>

                        <div class="rpg-panel-section">
                            <div class="rpg-panel-section-title">&#127991; Quest List</div>
                            <div class="rpg-panel-quests" id="rpg-panel-quests"></div>
                        </div>

                        <button class="rpg-panel-btn" id="rpg-panel-btn">Mulai Quest &#10140;</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== ACHIEVEMENT TOAST ========== -->
        <div class="rpg-toast" id="rpg-toast">
            <div class="rpg-toast-icon" id="rpg-toast-icon">&#127942;</div>
            <div class="rpg-toast-msg">
                <strong id="rpg-toast-title">Achievement Unlocked!</strong>
                <span id="rpg-toast-desc"></span>
            </div>
        </div>

    </div>
</div>

<?php include 'includes/loading.php'; ?>
<?php include 'includes/toast.php'; ?>
<script src="assets/js/navbar.js"></script>
<script>
(function() {
    const roles = <?php echo json_encode($roles, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    const userProgress = <?php echo json_encode($userProgress, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    const circumference = 2 * Math.PI * 52;
    let currentRole = null;
    let currentLevel = null;

    // DOM refs
    const stepRole = document.getElementById('rpg-step-role');
    const stepMap = document.getElementById('rpg-step-map');
    const backBtn = document.getElementById('rpg-back-btn');
    const roleCards = document.querySelectorAll('.rpg-role-card');
    const nodesContainer = document.getElementById('rpg-nodes');
    const connections = document.getElementById('rpg-connections');
    const progressFill = document.getElementById('rpg-progress-fill');
    const progressPct = document.getElementById('rpg-progress-pct');
    const progressText = document.getElementById('rpg-progress-text');
    const topbarEmoji = document.getElementById('rpg-topbar-emoji');
    const topbarName = document.getElementById('rpg-topbar-name');
    const topbarStats = document.getElementById('rpg-topbar-stats');
    const panelPlaceholder = document.getElementById('rpg-panel-placeholder');
    const panelContent = document.getElementById('rpg-panel-content');
    const panelIcon = document.getElementById('rpg-panel-icon');
    const panelLevel = document.getElementById('rpg-panel-level');
    const panelName = document.getElementById('rpg-panel-name');
    const panelDesc = document.getElementById('rpg-panel-desc');
    const panelRingCircle = document.getElementById('rpg-panel-ring-circle');
    const panelRingText = document.getElementById('rpg-panel-ring-text');
    const panelXp = document.getElementById('rpg-panel-xp');
    const panelMateri = document.getElementById('rpg-panel-materi');
    const panelStatus = document.getElementById('rpg-panel-status');
    const panelBoss = document.getElementById('rpg-panel-boss');
    const panelQuests = document.getElementById('rpg-panel-quests');
    const panelBtn = document.getElementById('rpg-panel-btn');
    const toast = document.getElementById('rpg-toast');

    // ======== ROLE SELECTION ========
    roleCards.forEach(function(card) {
        card.addEventListener('click', function() {
            const roleId = this.dataset.role;
            enterWorld(roleId);
        });
    });

    backBtn.addEventListener('click', function() {
        stepMap.style.display = 'none';
        stepRole.style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    function enterWorld(roleId) {
        const role = roles[roleId];
        if (!role) return;
        currentRole = roleId;
        buildRPGMap(role);
    }

    // ======== BUILD RPG MAP ========
    function buildRPGMap(role) {
        const levels = role.levels;
        const progress = userProgress[currentRole] || {};
        const completedCount = levels.filter(function(l) { return progress[l.level] === 'completed'; }).length;
        const overallPct = Math.round((completedCount / levels.length) * 100);

        // Topbar
        topbarEmoji.textContent = role.icon;
        topbarName.textContent = role.name;
        topbarStats.innerHTML =
            '<div class="rpg-topbar-stat"><strong>' + completedCount + '/' + levels.length + '</strong><span>Done</span></div>' +
            '<div class="rpg-topbar-stat"><strong>' + overallPct + '%</strong><span>Progress</span></div>' +
            '<div class="rpg-topbar-stat"><strong>+' + levels.reduce(function(s,l){return s+l.xp;},0) + '</strong><span>Total XP</span></div>';

        // Progress bar
        progressFill.style.width = overallPct + '%';
        progressPct.textContent = overallPct + '%';
        progressText.textContent = completedCount === levels.length ? 'Selesai!' : completedCount + '/' + levels.length + ' Level';

        // Build nodes with zigzag map layout
        nodesContainer.innerHTML = '';
        connections.innerHTML = '';
        const roleColor = role.color;
        const roleGlow = role.glow;
        document.documentElement.style.setProperty('--role-color', roleColor);
        document.documentElement.style.setProperty('--role-glow', roleGlow);

        // Determine grid column count based on level count
        var gridCols = levels.length <= 6 ? 3 : 5;
        nodesContainer.style.setProperty('--map-cols', gridCols);

        levels.forEach(function(level, idx) {
            const status = progress[level.level] || (idx === 0 ? 'available' : 'locked');
            let actualStatus = status;
            if (status === 'locked' && idx > 0) {
                const prevProgress = progress[levels[idx-1].level];
                if (prevProgress === 'completed') actualStatus = 'available';
            }
            if (idx === 0 && status !== 'completed' && status !== 'in-progress') actualStatus = 'available';

            // Zigzag: odd idx → left side (col 1), even idx → right side (col 3)
            var side = (idx % 2 === 0) ? 'left' : 'right';
            if (gridCols === 5) {
                side = (idx % 2 === 0) ? 'left' : 'right';
            }
            var col = side === 'left' ? 1 : gridCols;

            const node = document.createElement('div');
            node.className = 'rpg-node rpg-node--' + actualStatus + ' rpg-node--' + side;
            node.dataset.level = level.level;
            node.dataset.idx = idx;
            node.style.setProperty('--node-color', roleColor);
            node.style.setProperty('--node-glow', roleGlow);
            node.style.setProperty('--node-col', col);
            node.style.animationDelay = (idx * 0.1) + 's';

            const pct = actualStatus === 'completed' ? 100 : actualStatus === 'in-progress' ? Math.floor(Math.random() * 50) + 20 : 0;

            node.innerHTML =
                '<div class="rpg-node-glow"></div>' +
                '<div class="rpg-node-dot"><div class="rpg-node-dot-inner"></div></div>' +
                '<div class="rpg-node-card">' +
                    '<span class="rpg-node-level-badge">Level ' + level.level + '</span>' +
                    '<div class="rpg-node-card-head">' +
                        '<span class="rpg-node-card-emoji">' + (actualStatus === 'locked' ? '&#128274;' : level.emoji) + '</span>' +
                        '<div>' +
                            '<h4>' + level.name + '</h4>' +
                            '<span class="rpg-node-card-xp">&#9733; ' + level.xp + ' XP</span>' +
                        '</div>' +
                    '</div>' +
                    '<span class="rpg-node-stamp rpg-node-stamp--' + actualStatus + '">' +
                        (actualStatus === 'completed' ? '&#10004; Selesai' : actualStatus === 'in-progress' ? '&#9878; Active' : actualStatus === 'available' ? '&#9654; Buka' : '&#128274;') +
                    '</span>' +
                '</div>';

            node.addEventListener('click', function() {
                if (actualStatus === 'locked') {
                    showToast('&#128274;', 'Terkunci', 'Selesaikan level sebelumnya untuk membuka ini!');
                    return;
                }
                renderPanel(level, actualStatus, pct);
            });

            nodesContainer.appendChild(node);
        });

        // Draw connection lines after DOM paints
        requestAnimationFrame(function() {
            drawConnections(roleColor, progress, levels, gridCols);
        });

        // Redraw connections on resize
        var onResize = function() { drawConnections(roleColor, progress, levels, gridCols); };
        window.removeEventListener('resize', window.rpgResizeHandler);
        window.rpgResizeHandler = onResize;
        window.addEventListener('resize', onResize);

        // Switch view
        stepRole.style.display = 'none';
        stepMap.style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });

        // Reset panel
        panelPlaceholder.style.display = 'flex';
        panelContent.style.display = 'none';

        // Auto-select first available/completed
        const firstNode = nodesContainer.querySelector('.rpg-node--available') || nodesContainer.querySelector('.rpg-node--in-progress') || nodesContainer.querySelector('.rpg-node--completed');
        if (firstNode) {
            const lvl = levels.find(function(l) { return l.level === Number(firstNode.dataset.level); });
            if (lvl) {
                const st = progress[lvl.level] || 'available';
                const p = st === 'completed' ? 100 : st === 'in-progress' ? 50 : 0;
                renderPanel(lvl, st, p);
            }
        }

        // Check for achievements
        if (completedCount === levels.length) {
            setTimeout(function() {
                showToast('&#127942;', 'Role Completed!', 'Selamat! Kamu menyelesaikan semua level ' + role.name + '!');
            }, 800);
        } else if (completedCount >= Math.floor(levels.length / 2)) {
            setTimeout(function() {
                showToast('&#127775;', 'Halfway There!', 'Kamu sudah menyelesaikan ' + completedCount + ' dari ' + levels.length + ' level!');
            }, 800);
        }
    }

    // ======== CONNECTION LINES (Curved SVG Paths) ========
    function drawConnections(color, progress, levels, gridCols) {
        const nodes = nodesContainer.querySelectorAll('.rpg-node');
        if (nodes.length < 2) return;

        const containerRect = nodesContainer.getBoundingClientRect();
        if (containerRect.width === 0) return;
        const midCol = Math.ceil(gridCols / 2);
        const colWidth = containerRect.width / gridCols;
        const midX = containerRect.left + (midCol - 0.5) * colWidth;
        let svgHTML = '';

        for (let i = 0; i < nodes.length - 1; i++) {
            const a = nodes[i];
            const b = nodes[i+1];

            // Use dot position instead of node center (lines must not cut through cards)
            const dotA = a.querySelector('.rpg-node-dot');
            const dotB = b.querySelector('.rpg-node-dot');
            if (!dotA || !dotB) continue;

            const rectA = dotA.getBoundingClientRect();
            const rectB = dotB.getBoundingClientRect();

            const x1 = rectA.left - containerRect.left + rectA.width / 2;
            const y1 = rectA.top - containerRect.top + rectA.height / 2;
            const x2 = rectB.left - containerRect.left + rectB.width / 2;
            const y2 = rectB.top - containerRect.top + rectB.height / 2;

            const cx1 = midX - containerRect.left;
            const cx2 = midX - containerRect.left;

            var prevDone = i === 0 || progress[levels[i]?.level] === 'completed';
            var thisStat = progress[levels[i]?.level];
            var nextStat = progress[levels[i+1]?.level];
            var isComplete = thisStat === 'completed';
            var isActive = thisStat === 'in-progress';
            var nextAvail = nextStat === 'available' || nextStat === 'in-progress';

            let lineClass = 'rpg-line';
            if (isComplete) lineClass += ' rpg-line--completed';
            else if (isActive || (prevDone && nextAvail)) lineClass += ' rpg-line--active';

            var path = 'M ' + x1 + ' ' + y1 +
                       ' C ' + cx1 + ' ' + y1 +
                       ', ' + cx2 + ' ' + y2 +
                       ', ' + x2 + ' ' + y2;

            svgHTML += '<path d="' + path + '" class="' + lineClass + '" style="--line-color:' + color + ';stroke:' + color + ';opacity:0.2;fill:none" />';
        }

        connections.innerHTML = svgHTML;

        setTimeout(function() {
            connections.querySelectorAll('.rpg-line--completed').forEach(function(line) {
                line.style.opacity = '1';
                line.style.strokeWidth = '3.5';
                line.style.strokeDasharray = '8 4';
            });
            connections.querySelectorAll('.rpg-line--active').forEach(function(line) {
                line.style.opacity = '0.65';
                line.style.strokeWidth = '2.5';
            });
            connections.querySelectorAll('.rpg-line').forEach(function(line) {
                if (!line.classList.contains('rpg-line--completed') && !line.classList.contains('rpg-line--active')) {
                    line.style.opacity = '0.1';
                    line.style.strokeWidth = '1.5';
                }
            });
        }, 200);
    }

    // ======== PANEL ========
    function renderPanel(level, status, pct) {
        currentLevel = level;
        panelPlaceholder.style.display = 'none';
        panelContent.style.display = 'block';

        panelIcon.textContent = level.emoji;
        panelIcon.style.background = 'linear-gradient(135deg, var(--role-color), var(--role-glow))';
        panelLevel.textContent = 'Level ' + level.level;
        panelName.textContent = level.name;
        panelDesc.textContent = level.desc;
        panelXp.textContent = level.xp + ' XP';
        panelMateri.textContent = level.materi + ' Materi';

        const statusMap = { completed: '&#10004; Selesai', 'in-progress': '&#9878; Active', available: '&#9654; Tersedia', locked: '&#128274; Terkunci' };
        panelStatus.innerHTML = statusMap[status] || 'Tersedia';

        // Ring
        const offset = circumference - (pct / 100) * circumference;
        panelRingCircle.style.strokeDashoffset = offset;
        panelRingCircle.style.stroke = 'var(--role-color)';
        panelRingText.textContent = pct + '%';
        setTimeout(function() {
            panelRingCircle.style.transition = 'stroke-dashoffset 0.8s ease';
        }, 50);

        // Boss
        panelBoss.innerHTML = '<div class="rpg-panel-boss-item"><span class="rpg-panel-boss-icon">&#128126;</span> ' + level.boss + '</div>';

        // Quests
        panelQuests.innerHTML = '';
        level.quests.forEach(function(q, i) {
            const qDiv = document.createElement('div');
            qDiv.className = 'rpg-panel-quest';
            let qStatus = 'pending';
            if (status === 'completed') qStatus = 'done';
            else if (status === 'in-progress' && i === 0) qStatus = 'active';
            qDiv.innerHTML =
                '<span class="rpg-panel-quest-check rpg-panel-quest-check--' + qStatus + '">' +
                    (qStatus === 'done' ? '&#10004;' : qStatus === 'active' ? '&#8226;' : '&#9632;') +
                '</span>' +
                '<span>' + q + '</span>';
            panelQuests.appendChild(qDiv);
        });

        // Button
        if (status === 'locked') {
            panelBtn.disabled = true;
            panelBtn.innerHTML = '&#128274; Terkunci';
            panelBtn.className = 'rpg-panel-btn rpg-panel-btn--disabled';
        } else {
            panelBtn.disabled = false;
            panelBtn.innerHTML = status === 'completed' ? '&#128260; Review Ulang' : (status === 'in-progress' ? '&#9878; Lanjutkan' : '&#9654; Mulai Quest');
            panelBtn.className = 'rpg-panel-btn';
        }

        // Pulse active node
        nodesContainer.querySelectorAll('.rpg-node').forEach(function(n) {
            n.classList.toggle('rpg-node--selected', Number(n.dataset.level) === Number(level.level));
        });
    }

    panelBtn.addEventListener('click', function() {
        if (currentLevel && !this.disabled) {
            showToast('&#127758;', 'Quest Dimulai!', 'Memulai: ' + currentLevel.name);
        }
    });

    // ======== TOAST ========
    function showToast(icon, title, desc) {
        document.getElementById('rpg-toast-icon').innerHTML = icon;
        document.getElementById('rpg-toast-title').textContent = title;
        document.getElementById('rpg-toast-desc').textContent = desc;
        toast.classList.add('rpg-toast--show');
        setTimeout(function() {
            toast.classList.remove('rpg-toast--show');
        }, 4000);
    }

    // ======== INTERSECTION ANIMATION ========
    if (window.IntersectionObserver) {
        const obs = new IntersectionObserver(function(entries) {
            entries.forEach(function(e) {
                if (e.isIntersecting) {
                    e.target.style.opacity = '1';
                    e.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });
        document.querySelectorAll('.rpg-role-card').forEach(function(c) { obs.observe(c); });
    }

    // ======== PARTICLE EFFECT ========
    const hero = document.querySelector('.rpg-hero');
    if (hero) {
        for (let i = 0; i < 12; i++) {
            const p = document.createElement('div');
            p.className = 'rpg-particle';
            p.style.cssText = 'top:' + (Math.random()*100) + '%;left:' + (Math.random()*100) + '%;width:' + (2+Math.random()*4) + 'px;height:' + (2+Math.random()*4) + 'px;animation-delay:' + (Math.random()*3) + 's;animation-duration:' + (3+Math.random()*4) + 's';
            hero.querySelector('.rpg-hero-bg').appendChild(p);
        }
    }
})();
</script>
</body>
</html>
