/**
 * PROZONE MULTIPLAYER BATTLE ARENA
 * Arena Javascript Logic (Timer, Fake enemy typing, Popups, Live Chat)
 */

document.addEventListener('DOMContentLoaded', () => {

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
    const messages = [
        {name: "Zhafir", msg: "Bro soal nomor 2 susah banget 😭"},
        {name: "Alya_Dev", msg: "Pakai regex aja buat filter angkanya"},
        {name: "Budi_Coder", msg: "Gila Dika ngetiknya cepet amat!"},
        {name: "CodeNinja", msg: "GLHF everyone!! Mabar coding 🔥"}
    ];
    let msgIndex = 0;

    function injectChatMessage() {
        if (msgIndex < messages.length) {
            const m = messages[msgIndex];
            const div = document.createElement('div');
            div.className = 'chat-msg';
            
            // Random avatar
            const avatarNum = Math.floor(Math.random() * 4) + 1;
            
            div.innerHTML = `
                <img src="assets/img/characters/code-warrior.png" class="chat-avatar" alt="User">
                <div class="chat-bubble">
                    <strong>${m.name}</strong>
                    ${m.msg}
                </div>
            `;
            chatFeed.appendChild(div);
            chatFeed.scrollTop = chatFeed.scrollHeight;
            
            msgIndex++;
            setTimeout(injectChatMessage, Math.floor(Math.random() * 5000) + 3000);
        }
    }
    setTimeout(injectChatMessage, 3000);

    // 4. Submit button action
    document.getElementById('btn-submit-code').addEventListener('click', function() {
        // Mock compilation
        const btn = this;
        const originalText = btn.innerHTML;
        btn.innerHTML = `<span style="display:inline-flex;align-items:center;gap:8px;">
            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="width:20px;animation:spin 1s linear infinite;">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg> Menjalankan...
        </span>`;
        btn.style.opacity = 0.8;
        btn.style.pointerEvents = 'none';

        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.style.opacity = 1;
            btn.style.pointerEvents = 'auto';

            triggerXpPopup('you-lb-item', '+1000 XP (All passed)');
            updateScore('you-lb-item', 2550);

        }, 1500);
    });

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
});
