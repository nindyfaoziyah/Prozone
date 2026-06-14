<?php
require_once 'config/config.php';
requireLogin();
require_once 'includes/icons.php';

$page_title       = 'AI Mentor';
$page_description = 'Tanya AI tentang coding, algoritma, dan teknologi.';
$page_css         = ['pages/ai-mentor.css'];
$body_class       = getThemeClass();

$user_initial = strtoupper(substr(explode(' ', $_SESSION['nama_lengkap'] ?? 'U')[0], 0, 1));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once 'includes/head.php'; ?>
</head>
<body class="<?php echo trim($body_class . ' dashboard-layout'); ?>">
    <?php require_once 'navbar.php'; ?>

    <div class="page-wrapper dashboard-main-container">
        <div class="dashboard-content">

            <!-- Page Header -->
            <div class="ai-mentor-page-header">
                <div>
                    <h1>🤖 AI Mentor</h1>
                    <p>Tanya apa pun seputar coding — AI Mentor siap membantu!</p>
                </div>
                <div class="header-badge-group">
                    <span class="header-badge purple">🧠 Khusus Coding</span>
                    <span class="header-badge green">⚡ Gratis</span>
                </div>
            </div>

            <!-- Chat Container -->
            <div class="ai-mentor-wrapper">
                <!-- Header -->
                <div class="ai-mentor-header">
                    <div class="ai-mentor-avatar">🤖</div>
                    <div class="ai-mentor-header-info">
                        <h2>AI Mentor</h2>
                        <span class="status">Online · Siap membantu coding kamu</span>
                    </div>
                    <div class="ai-mentor-header-actions">
                        <span class="model-badge">
                            <span class="dot"></span>
                            Llama 3.3 70B
                        </span>
                        <button class="btn-clear-chat" onclick="clearChat()">🗑️ Reset</button>
                    </div>
                </div>

                <!-- Messages -->
                <div class="ai-mentor-messages" id="chatMessages">
                    <div class="welcome-screen" id="welcomeScreen">
                        <div class="welcome-avatar-ring">🧑‍💻</div>
                        <h3>Halo! Ada yang bisa dibantu?</h3>
                        <p>Aku adalah AI Mentor khusus coding. Tanya tentang programming, algoritma, debug, atau teknologi apa pun!</p>
                        <p class="welcome-tagline">💡 Pilih topik di bawah atau tulis pertanyaan langsung</p>
                        <div class="category-chips">
                            <button class="chip" onclick="sendSuggested('Jelaskan perbedaan fungsi dan method dalam OOP')">
                                <span class="chip-icon">🏗️</span> OOP Dasar
                            </button>
                            <button class="chip" onclick="sendSuggested('Bantu saya debug error PHP: Undefined variable')">
                                <span class="chip-icon">🐛</span> Debug PHP
                            </button>
                            <button class="chip" onclick="sendSuggested('Contoh kode CRUD sederhana dengan JavaScript')">
                                <span class="chip-icon">📦</span> CRUD JS
                            </button>
                            <button class="chip" onclick="sendSuggested('Apa itu REST API dan cara kerjanya?')">
                                <span class="chip-icon">🔗</span> REST API
                            </button>
                            <button class="chip" onclick="sendSuggested('Bagaimana cara menggunakan SQL JOIN dengan contoh?')">
                                <span class="chip-icon">🗄️</span> SQL JOIN
                            </button>
                            <button class="chip" onclick="sendSuggested('Jelaskan konsep async/await di JavaScript')">
                                <span class="chip-icon">⏳</span> Async/Await
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Input -->
                <div class="ai-mentor-input-area">
                    <div class="ai-mentor-input-wrapper">
                        <textarea class="ai-mentor-input" id="chatInput" rows="1" placeholder="Tanya tentang coding..." onkeydown="handleKey(event)" oninput="updateCharCount()" maxlength="2000"></textarea>
                        <span class="input-char-count" id="charCount">0</span>
                    </div>
                    <button class="btn-send" id="sendBtn" onclick="sendMessage()">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                    </button>
                </div>

                <!-- Footer -->
                <div class="ai-mentor-footer">
                    <span class="footer-hint">Ditenagai oleh <strong>Groq</strong> · Model <strong>Llama 3.3 70B</strong></span>
                    <span class="footer-hint">Hanya untuk pertanyaan coding</span>
                </div>
            </div>

        </div>
    </div>

    <?php include 'includes/loading.php'; ?>
    <?php include 'includes/toast.php'; ?>
    <script src="assets/js/navbar.js"></script>

    <script>
    const messagesEl = document.getElementById('chatMessages');
    const inputEl = document.getElementById('chatInput');
    const sendBtn = document.getElementById('sendBtn');
    const charCount = document.getElementById('charCount');
    const welcomeEl = document.getElementById('welcomeScreen');
    let chatHistory = [];

    function handleKey(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
        autoResize();
    }

    function autoResize() {
        inputEl.style.height = 'auto';
        inputEl.style.height = Math.min(inputEl.scrollHeight, 120) + 'px';
    }

    function updateCharCount() {
        const len = inputEl.value.length;
        charCount.textContent = len;
        charCount.style.color = len > 1800 ? '#DC2626' : '#CBD5E1';
    }

    function sendSuggested(text) {
        inputEl.value = text;
        updateCharCount();
        sendMessage();
    }

    function clearChat() {
        if (!confirm('Hapus seluruh percakapan?')) return;
        chatHistory = [];
        messagesEl.innerHTML = `
            <div class="welcome-screen" id="welcomeScreen">
                <div class="welcome-avatar-ring">🧑‍💻</div>
                <h3>Halo! Ada yang bisa dibantu?</h3>
                <p>Aku adalah AI Mentor khusus coding. Tanya tentang programming, algoritma, debug, atau teknologi apa pun!</p>
                <p class="welcome-tagline">💡 Pilih topik di bawah atau tulis pertanyaan langsung</p>
                <div class="category-chips">
                    <button class="chip" onclick="sendSuggested('Jelaskan perbedaan fungsi dan method dalam OOP')">
                        <span class="chip-icon">🏗️</span> OOP Dasar
                    </button>
                    <button class="chip" onclick="sendSuggested('Bantu saya debug error PHP: Undefined variable')">
                        <span class="chip-icon">🐛</span> Debug PHP
                    </button>
                    <button class="chip" onclick="sendSuggested('Contoh kode CRUD sederhana dengan JavaScript')">
                        <span class="chip-icon">📦</span> CRUD JS
                    </button>
                    <button class="chip" onclick="sendSuggested('Apa itu REST API dan cara kerjanya?')">
                        <span class="chip-icon">🔗</span> REST API
                    </button>
                    <button class="chip" onclick="sendSuggested('Bagaimana cara menggunakan SQL JOIN dengan contoh?')">
                        <span class="chip-icon">🗄️</span> SQL JOIN
                    </button>
                    <button class="chip" onclick="sendSuggested('Jelaskan konsep async/await di JavaScript')">
                        <span class="chip-icon">⏳</span> Async/Await
                    </button>
                </div>
            </div>
        `;
        inputEl.focus();
    }

    function addMessage(role, content) {
        const existingWelcome = document.getElementById('welcomeScreen');
        if (existingWelcome) existingWelcome.remove();

        const div = document.createElement('div');
        div.className = 'message ' + role;

        const avatarContent = role === 'user'
            ? '<?php echo $user_initial; ?>'
            : '🤖';

        div.innerHTML = `
            <div class="message-avatar">${avatarContent}</div>
            <div class="message-bubble">${formatContent(content)}</div>
        `;

        messagesEl.appendChild(div);
        messagesEl.scrollTop = messagesEl.scrollHeight;

        chatHistory.push({ role, content });
    }

    function formatContent(text) {
        text = text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

        // Extract and protect code blocks
        const codeBlocks = [];
        text = text.replace(/```(\w*)\n?([\s\S]*?)```/g, function(match, lang, code) {
            const idx = codeBlocks.length;
            const trimmed = code.trim();
            const langLabel = lang || 'code';
            codeBlocks.push('<pre><div class="code-header">' +
                '<span class="code-lang">' + langLabel + '</span>' +
                '<button class="copy-btn" onclick="copyCode(this)">📋 Salin</button>' +
                '</div><code>' + trimmed + '</code></pre>');
            return '%%CODEBLOCK' + idx + '%%';
        });

        // Inline code
        text = text.replace(/`([^`]+)`/g, '<code>$1</code>');

        // Bold
        text = text.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');

        // Unordered lists
        text = text.replace(/^\- (.+)$/gm, '<li>$1</li>');
        text = text.replace(/(<li>.*<\/li>\n?)+/g, '<ul>$&</ul>');

        // Ordered lists
        text = text.replace(/^(\d+)\. (.+)$/gm, function(m, num, content) {
            return '<li>' + content + '</li>';
        });
        text = text.replace(/(<li>.*<\/li>)+/g, function(m) {
            return '<ol>' + m + '</ol>';
        });

        // Paragraphs
        text = text.split('\n\n').map(function(p) {
            p = p.trim();
            if (!p) return '';
            if (p.startsWith('<pre>') || p.startsWith('<ul>') || p.startsWith('<ol>')) return p;
            if (/^%%CODEBLOCK\d+%%$/.test(p)) return p;
            return '<p>' + p.replace(/\n/g, '<br>') + '</p>';
        }).join('');

        // Restore code blocks
        text = text.replace(/%%CODEBLOCK(\d+)%%/g, function(m, idx) {
            return codeBlocks[parseInt(idx)] || '';
        });

        return text;
    }

    function copyCode(btn) {
        const code = btn.closest('pre').querySelector('code');
        const text = code.textContent;
        navigator.clipboard.writeText(text).then(function() {
            btn.textContent = '✓ Tersalin';
            btn.classList.add('copied');
            setTimeout(function() {
                btn.textContent = '📋 Salin';
                btn.classList.remove('copied');
            }, 2000);
        });
    }

    function showTyping() {
        const div = document.createElement('div');
        div.className = 'message assistant';
        div.id = 'typingIndicator';
        div.innerHTML = `
            <div class="message-avatar">🤖</div>
            <div class="message-bubble">
                <div class="typing-indicator">
                    <span></span><span></span><span></span>
                </div>
            </div>
        `;
        messagesEl.appendChild(div);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function removeTyping() {
        const el = document.getElementById('typingIndicator');
        if (el) el.remove();
    }

    function sendMessage() {
        const text = inputEl.value.trim();
        if (!text) return;

        inputEl.value = '';
        inputEl.style.height = 'auto';
        updateCharCount();
        sendBtn.disabled = true;

        addMessage('user', text);
        showTyping();

        fetch('api/ai-mentor-chat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: text, history: chatHistory })
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            removeTyping();
            if (data.error) {
                addMessage('error', '❌ ' + data.error);
                if (typeof showToast === 'function') {
                    showToast(data.error, 'error');
                }
            } else {
                addMessage('assistant', data.reply);
            }
        })
        .catch(function() {
            removeTyping();
            addMessage('error', '❌ Gagal terhubung ke server. Coba lagi.');
            if (typeof showToast === 'function') {
                showToast('Gagal terhubung ke server', 'error');
            }
        })
        .finally(function() {
            sendBtn.disabled = false;
            inputEl.focus();
        });
    }

    inputEl.focus();
    </script>

</body>
</html>
