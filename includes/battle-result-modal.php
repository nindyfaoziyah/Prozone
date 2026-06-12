<!-- Battle Result Analysis Modal -->
<div id="battle-result-modal" class="battle-result-modal hidden">
    <div class="battle-result-backdrop" onclick="closeBattleResultModal()"></div>
    
    <div class="battle-result-dialog">
        <!-- Header -->
        <div class="battle-result-header">
            <div class="br-title-row">
                <h2 class="br-title">
                    <span class="br-icon">⚔️</span>
                    Analisis AI: Battle Result
                </h2>
                <button class="br-close-btn" onclick="closeBattleResultModal()">✕</button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="battle-result-content">
            
            <!-- Score Circle & Metrics -->
            <div class="br-score-section">
                <div class="br-score-circle">
                    <div class="br-score-value" id="br-score-val">59</div>
                    <div class="br-score-max">/100</div>
                </div>
                
                <div class="br-metrics">
                    <div class="br-metric-item">
                        <div class="br-metric-label">✓ Test Cases</div>
                        <div class="br-metric-value" id="br-test-cases">0/5 <span class="br-badge">+Opts</span></div>
                    </div>
                    <div class="br-metric-item">
                        <div class="br-metric-label">⏱ Kecepatan</div>
                        <div class="br-metric-value" id="br-execution-time">14m 19s <span class="br-badge green">+24pts</span></div>
                    </div>
                    <div class="br-metric-item">
                        <div class="br-metric-label">📝 Code Quality</div>
                        <div class="br-metric-value" id="br-code-quality"><span class="br-badge green">+25pts</span></div>
                    </div>
                    <div class="br-metric-item">
                        <div class="br-metric-label">⚡ Efisiensi</div>
                        <div class="br-metric-value" id="br-complexity">O(n) <span class="br-badge green">+10pts</span></div>
                    </div>
                </div>
            </div>

            <!-- Role Recommendation -->
            <div class="br-role-section">
                <div class="br-role-badge">
                    <span class="br-role-icon" id="br-role-icon">🎨</span>
                    <span class="br-role-text">ROLE YANG COCOK UNTUKMU</span>
                </div>

                <div class="br-role-card">
                    <div class="br-role-header">
                        <span class="br-role-name" id="br-role-name">Frontend Developer</span>
                    </div>
                    
                    <p class="br-role-description" id="br-role-description">
                        Kreativitas dan perhatian detail adalah kekuatanmu. Kamu akan berhasil membangun UI yang indah dan responsif.
                    </p>

                    <div class="br-skills">
                        <div class="br-skill-tag" id="br-skill-1">HTML/CSS</div>
                        <div class="br-skill-tag" id="br-skill-2">JavaScript</div>
                        <div class="br-skill-tag" id="br-skill-3">React</div>
                        <div class="br-skill-tag" id="br-skill-4">UI/UX</div>
                        <div class="br-skill-tag" id="br-skill-5">Figma</div>
                    </div>
                </div>

                <div class="br-feedback-box">
                    <p class="br-feedback" id="br-feedback">
                        <strong>Terus berkembang, Student!</strong> Skor 59/100 menunjukkan potensi besar di frontend — terus problem solving-mu!
                    </p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="br-actions">
                <button class="br-btn br-btn-primary" onclick="window.location.href='leaderboard.php'">Leaderboard</button>
                <button class="br-btn br-btn-secondary" onclick="showRoleComparisonModal()">Lihat Semua Role</button>
            </div>
        </div>
    </div>
</div>

<!-- Role Comparison Modal (All Roles) -->
<div id="role-comparison-modal" class="battle-result-modal hidden">
    <div class="battle-result-backdrop" onclick="closeRoleComparisonModal()"></div>
    
    <div class="battle-result-dialog">
        <!-- Header -->
        <div class="battle-result-header">
            <div class="br-title-row">
                <h2 class="br-title">
                    <span class="br-icon">🎯</span>
                    Perbandingan Semua Role
                </h2>
                <button class="br-close-btn" onclick="closeRoleComparisonModal()">✕</button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="battle-result-content">
                <p class="br-comparison-intro">
            </p>

            <div id="role-comparison-grid" class="role-comparison-grid">
                <!-- Will be populated by JavaScript -->
            </div>

            <!-- Action Buttons -->
            <div class="br-actions">
                <button class="br-btn br-btn-secondary" onclick="closeRoleComparisonModal()">Kembali</button>
            </div>
        </div>
    </div>
</div>



