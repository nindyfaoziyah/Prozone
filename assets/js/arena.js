/**
 * PROZONE MULTIPLAYER BATTLE ARENA
 * Arena Javascript Logic (Timer, Fake enemy typing, Popups, Live Chat)
 */

function initArenaPage() {

    // 1. Timer Logic
    const timerEl = document.getElementById('arena-timer');
    let timeLeft = 15 * 60; // 15 minutes
    
    function updateTimer() {
        const m = Math.floor(timeLeft / 60).toString().padStart(2, '0');
        const s = (timeLeft % 60).toString().padStart(2, '0');
        timerEl.textContent = `${m}:${s}`;
        
        if (timeLeft <= 60) {
            timerEl.style.color = '#E11D48'; // Flash red
        }
        if (timeLeft > 0) {
            timeLeft--;
            setTimeout(updateTimer, 1000);
        }
    }
    updateTimer();

    // 2. Enemy Typing Simulation
    const enemyCodeBase = `function calculateMaxProfit(prices) {
  let minPrice = Infinity;
  let maxProfit = 0;

  for (let i = 0; i < prices.length; i++) {
    if (prices[i] < minPrice) {
      minPrice = prices[i];
    } else if (prices[i] - minPrice > maxProfit) {
      maxProfit = prices[i] - minPrice;
    }
  }

  return maxProfit;
}

// Enemy is testing edge cases...`;
    
    const enemyEditorNode = document.getElementById('enemy-code');
    let enemyTypedIndex = 0;

    function applyEnemyTyping() {
        if (enemyTypedIndex < enemyCodeBase.length) {
            // Pick a chunk of code (variable speed to simulate human)
            const chunkSize = Math.floor(Math.random() * 5) + 1;
            const textToAppend = enemyCodeBase.substring(enemyTypedIndex, enemyTypedIndex + chunkSize);
            enemyTypedIndex += chunkSize;
            
            // Format syntax safely as simple mock
            let formatted = enemyCodeBase.substring(0, enemyTypedIndex)
                .replace(/(function|return|let|for|if|else)/g, '<span class="syntax-keyword">$1</span>')
                .replace(/(calculateMaxProfit|Infinity)/g, '<span class="syntax-func">$1</span>')
                .replace(/(\/\/.+)/g, '<span class="syntax-comment">$1</span>');

            enemyEditorNode.innerHTML = formatted + '<span class="syntax-cursor"></span>';
            
            // Scroll to bottom
            enemyEditorNode.scrollTop = enemyEditorNode.scrollHeight;

            const nextStroke = Math.floor(Math.random() * 150) + 50;
            setTimeout(applyEnemyTyping, nextStroke);
        } else {
            // Trigger an XP boost for enemy after finish
            triggerXpPopup('enemy-lb-item', '+500 XP');
            updateScore('enemy-lb-item', 1450);
        }
    }
    setTimeout(applyEnemyTyping, 2000);

    // 3. Live Chat Simulation
    const chatFeed = document.getElementById('chat-feed');
    const chatInput = document.getElementById('chat-input');
    const chatSendBtn = document.getElementById('chat-send-btn');
    const userName = window.battleUserName || 'You';
    const userAvatar = window.battleUserAvatar || 'assets/img/characters/code-warrior.png';
    const enemyAvatar = 'assets/img/characters/code-warrior.png';

    const messages = [
        {name: "Zhafir", msg: "Bro soal nomor 2 susah banget 😭"},
        {name: "Alya_Dev", msg: "Pakai regex aja buat filter angkanya"},
        {name: "Budi_Coder", msg: "Gila Dika ngetiknya cepet amat!"},
        {name: "CodeNinja", msg: "GLHF everyone!! Mabar coding 🔥"}
    ];
    let msgIndex = 0;

    function escapeHtml(text) {
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function appendChatMessage({name, msg, avatar, isUser = false}) {
        const div = document.createElement('div');
        div.className = 'chat-msg' + (isUser ? ' user' : '');
        div.innerHTML = `
            <img src="${avatar}" class="chat-avatar" alt="${escapeHtml(name)}">
            <div class="chat-body">
                <div class="chat-author">${escapeHtml(name)}</div>
                <div class="chat-text">${escapeHtml(msg)}</div>
            </div>
        `;
        chatFeed.appendChild(div);
        chatFeed.scrollTop = chatFeed.scrollHeight;
    }

    function handleChatSend() {
        const message = chatInput.value.trim();
        if (!message) return;
        appendChatMessage({name: userName, msg: message, avatar: userAvatar, isUser: true});
        chatInput.value = '';
        chatInput.focus();
    }

    if (chatSendBtn) {
        chatSendBtn.addEventListener('click', handleChatSend);
    }
    if (chatInput) {
        chatInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                handleChatSend();
            }
        });
    }

    function injectChatMessage() {
        if (msgIndex < messages.length) {
            const m = messages[msgIndex];
            appendChatMessage({name: m.name, msg: m.msg, avatar: enemyAvatar});
            msgIndex++;
            setTimeout(injectChatMessage, Math.floor(Math.random() * 5000) + 3000);
        }
    }
    setTimeout(injectChatMessage, 3000);

    // 4. Submit button action - Improved with battle analysis
    document.getElementById('btn-submit-code').addEventListener('click', submitBattleCode);

    async function submitBattleCode() {
        const btn = document.getElementById('btn-submit-code');
        const originalText = btn.innerHTML;
        
        // Show loading state
        btn.innerHTML = `<span style="display:inline-flex;align-items:center;gap:8px;">
            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="width:20px;animation:spin 1s linear infinite;">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg> Menjalankan...
        </span>`;
        btn.style.opacity = 0.8;
        btn.style.pointerEvents = 'none';

        try {
            const userCode = document.querySelector('.editor-pane.you textarea')?.value || '';
            const battleRun = await executeBattleTest(userCode);
            if (battleRun.error) {
                throw new Error(battleRun.error);
            }

            const quality = estimateCodeQuality(userCode, battleRun.passed, battleRun.total);
            const complexity = estimateTimeComplexity(userCode);
            const score = Math.round((battleRun.passed / Math.max(1, battleRun.total)) * 100 * 0.7 + quality * 0.3);

            const battleMetrics = {
                test_cases: battleRun.passed,
                total_test_cases: battleRun.total,
                execution_time: battleRun.duration,
                code_quality: quality,
                time_complexity: complexity,
                language: 'javascript',
                score: score
            };

            // Update the test result summary before showing modal
            // Call analyze-battle API
            const response = await fetch('api/analyze-battle.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(battleMetrics)
            });

            if (!response.ok) {
                throw new Error('API Error: ' + response.status);
            }

            const data = await response.json();
            
            if (data.success) {
                // Update modal with analysis data
                updateBattleResultModal(data.analysis);
                
                // Show modal
                showBattleResultModal();

                // Trigger XP popup (optional)
                triggerXpPopup('you-lb-item', '+' + battleMetrics.score + ' XP');
            } else {
                console.error('Analysis failed:', data.message);
                alert('Gagal menganalisis battle. Coba lagi.');
            }

        } catch (error) {
            console.error('Submit error:', error);
            alert('Terjadi kesalahan: ' + error.message);
        } finally {
            // Reset button
            btn.innerHTML = originalText;
            btn.style.opacity = 1;
            btn.style.pointerEvents = 'auto';
        }
    }

    async function executeBattleTest(userCode) {
        const testCases = [
            {input: [7,1,5,3,6,4], expected: 7},
            {input: [7,6,4,3,1], expected: 0},
            {input: [1,2,3,4,5], expected: 4},
            {input: [2,4,1], expected: 2},
            {input: [3,3,5,0,0,3,1,4], expected: 4}
        ];

        const harness = buildBattleTestHarness(userCode, testCases);
        const runResponse = await runUserCode(harness, 'javascript');
        if (!runResponse.success) {
            return { error: runResponse.output || 'Gagal menjalankan kode.' };
        }

        const parsed = parseBattleOutput(runResponse.output);
        if (parsed.error) {
            return { error: parsed.error };
        }

        return parsed.data;
    }

    function buildBattleTestHarness(userCode, testCases) {
        // Build the test harness code
        let harness = userCode + '\n\n(function() {\n';
        harness += '  const __tests = ' + JSON.stringify(testCases) + ';\n';
        harness += '  const __start = Date.now();\n';
        harness += '  if (typeof calculateMaxProfit !== "function") {\n';
        harness += '    throw new Error("Fungsi calculateMaxProfit() tidak ditemukan.");\n';
        harness += '  }\n';
        harness += '  const __results = __tests.map(test => ({\n';
        harness += '    input: test.input,\n';
        harness += '    expected: test.expected,\n';
        harness += '    actual: calculateMaxProfit(test.input),\n';
        harness += '    passed: calculateMaxProfit(test.input) === test.expected\n';
        harness += '  }));\n';
        harness += '  const __duration = Date.now() - __start;\n';
        harness += '  console.log(JSON.stringify({ passed: __results.filter(t => t.passed).length, total: __results.length, duration: __duration, results: __results }));\n';
        harness += '})();';
        return harness;
    }

    async function runUserCode(code, language) {
        const response = await fetch('api/run-code.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ language, code })
        });
        if (!response.ok) {
            throw new Error('Layanan menjalankan kode gagal.');
        }
        return response.json();
    }

    function parseBattleOutput(output) {
        const normalized = output.toString().trim();
        const jsonMatch = normalized.match(/\{[\s\S]*\}/);
        if (!jsonMatch) {
            return { error: 'Output dari runner tidak dalam format JSON.\nOutput lengkap: ' + normalized.substring(0, 300) };
        }

        try {
            const data = JSON.parse(jsonMatch[0]);
            if (data.error) {
                return { error: data.error };
            }
            return { data };
        } catch (err) {
            return { error: 'Gagal memproses output JSON dari runner. ' + err.message };
        }
    }

    function estimateCodeQuality(code, passed, total) {
        let quality = 55;
        const normalized = code.trim();
        if (passed === total && total > 0) quality += 20;
        if (/const|let/.test(normalized)) quality += 8;
        if (!/var\b/.test(normalized)) quality += 4;
        if (/function\s+calculateMaxProfit/.test(normalized) || /=>/.test(normalized)) quality += 6;
        if (normalized.length < 280) quality += 7;
        quality += Math.min(10, Math.floor((passed / Math.max(1, total)) * 10));
        return Math.min(100, quality);
    }

    function estimateTimeComplexity(code) {
        if (/for\s*\(.*for\s*\(/.test(code) || /while\s*\(/.test(code) && /for\s*\(/.test(code)) {
            return 'O(n²)';
        }
        if (/for\s*\(|while\s*\(/.test(code)) {
            return 'O(n)';
        }
        return 'O(1)';
    }

    const btnSubmit = document.getElementById('btn-submit-code');

    if (btnSubmit) {
        btnSubmit.addEventListener('click', submitBattleCode);
        btnSubmit.onclick = submitBattleCode;
    }

    window.submitBattleCode = submitBattleCode;
    window.__arenaInitRan = true;

    // ============================================
    // MODAL FUNCTIONS
    // ============================================

    function showBattleResultModal() {
        const modal = document.getElementById('battle-result-modal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('show');
        }
    }

    window.closeBattleResultModal = function() {
        const modal = document.getElementById('battle-result-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('show');
        }
    };

    function updateBattleResultModal(analysis) {
        // Score
        document.getElementById('br-score-val').textContent = analysis.score;
        
        // Metrics
        document.getElementById('br-test-cases').innerHTML = 
            analysis.test_cases + ' <span class="br-badge">+' + analysis.test_cases_percentage + '%</span>';
        
        document.getElementById('br-execution-time').innerHTML = 
            analysis.execution_time + ' <span class="br-badge green">+24pts</span>';
        
        document.getElementById('br-code-quality').innerHTML = 
            '<span class="br-badge green">' + analysis.code_quality + '/100</span>';
        
        document.getElementById('br-complexity').innerHTML = 
            analysis.time_complexity + ' <span class="br-badge green">+10pts</span>';
        
        // Role info
        document.getElementById('br-role-icon').textContent = analysis.icon;
        document.getElementById('br-role-name').textContent = analysis.role;
        document.getElementById('br-role-description').textContent = analysis.description;
        document.getElementById('br-feedback').innerHTML = 
            '<strong>Terus berkembang, Student!</strong> ' + analysis.feedback;
        
        // Skills
        const skills = analysis.skills || [];
        for (let i = 1; i <= 5; i++) {
            const skillEl = document.getElementById('br-skill-' + i);
            if (skillEl) {
                if (i <= skills.length) {
                    skillEl.textContent = skills[i - 1];
                    skillEl.style.display = 'inline-flex';
                } else {
                    skillEl.style.display = 'none';
                }
            }
        }
    }

    window.viewRoleComparison = function() {
        alert('Fitur role comparison akan segera tersedia!');
    };

    // ============================================
    // ROLE COMPARISON MODAL
    // ============================================

    window.showRoleComparisonModal = function() {
        // Always rebuild the grid to ensure fresh data
        window.buildRoleComparisonGrid();
        
        // Close main modal and show comparison modal
        closeBattleResultModal();
        
        const modal = document.getElementById('role-comparison-modal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('show');
        }
    };

    window.closeRoleComparisonModal = function() {
        const modal = document.getElementById('role-comparison-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('show');
        }
    };

    window.buildRoleComparisonGrid = function() {
        const roles = [
            {
                name: 'Frontend Developer',
                icon: '🎨',
                description: 'Ahli dalam UI/UX dan interaktivitas web',
                skills: ['HTML/CSS', 'JavaScript', 'React', 'UI/UX']
            },
            {
                name: 'Backend Developer',
                icon: '⚙️',
                description: 'Spesialis dalam logika dan database',
                skills: ['Python', 'Node.js', 'SQL', 'APIs']
            },
            {
                name: 'Full Stack Developer',
                icon: '🌐',
                description: 'Menguasai frontend dan backend sepenuhnya',
                skills: ['React', 'Node.js', 'SQL', 'DevOps']
            },
            {
                name: 'DevOps Engineer',
                icon: '🚀',
                description: 'Expert dalam infrastructure dan deployment',
                skills: ['Docker', 'CI/CD', 'Cloud', 'Linux']
            }
        ];

        const grid = document.getElementById('role-comparison-grid');
        grid.innerHTML = roles.map(role => `
            <div class="role-comparison-card" onclick="selectRole('${role.name}')">
                <div class="rcc-icon">${role.icon}</div>
                <div class="rcc-name">${role.name}</div>
                <div class="rcc-desc">${role.description}</div>
                <div class="rcc-skills">
                    ${role.skills.map(skill => `<span class="rcc-skill">${skill}</span>`).join('')}
                </div>
            </div>
        `).join('');
    };

    window.selectRole = function(roleName) {
        // Simple selection effect
        document.querySelectorAll('.role-comparison-card').forEach(card => {
            if (card.textContent.includes(roleName)) {
                card.classList.add('active');
            } else {
                card.classList.remove('active');
            }
        });
    };

    // Helpers
    function triggerXpPopup(elementId, text) {
        const el = document.getElementById(elementId);
        if(!el) return;
        const pop = document.createElement('div');
        pop.className = 'xp-popup';
        pop.textContent = text;
        el.appendChild(pop);
        setTimeout(() => pop.remove(), 1500);
    }

    function updateScore(elementId, newScore) {
        const el = document.getElementById(elementId);
        if(el) {
            const scoreNode = el.querySelector('.lb-score');
            if(scoreNode) {
                // simple counter animation
                let current = parseInt(scoreNode.textContent.replace(/[^0-9]/g, ''));
                const inc = Math.max(1, Math.floor((newScore - current) / 10));
                
                const timer = setInterval(() => {
                    current += inc;
                    if(current >= newScore) {
                        current = newScore;
                        clearInterval(timer);
                    }
                    scoreNode.textContent = current.toLocaleString() + ' XP';
                }, 50);
            }
        }
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initArenaPage);
} else {
    initArenaPage();
}

