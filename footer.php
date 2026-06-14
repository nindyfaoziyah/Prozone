<!-- Professional Footer for Prozone -->
<footer class="site-footer">
	<div class="footer-wrapper">
		<div class="footer-content">
			<!-- Branding Column -->
			<div class="footer-col footer-brand-col">
				<div class="footer-logo-box">
					<img src="assets/img/Prozone%20Purple.png" alt="Prozone" class="footer-logo">
				</div>
				<p class="footer-tagline">Platform pembelajaran coding interaktif dengan sistem gamifikasi RPG. Belajar, berkompetisi, dan raih prestasi bersama komunitas developer Indonesia.</p>
				<div class="footer-socials">
					<a href="https://twitter.com" class="social-icon" title="Twitter">𝕏</a>
					<a href="https://facebook.com" class="social-icon" title="Facebook">f</a>
					<a href="https://instagram.com" class="social-icon" title="Instagram">📷</a>
					<a href="https://linkedin.com" class="social-icon" title="LinkedIn">in</a>
					<a href="https://github.com" class="social-icon" title="GitHub">⚙</a>
				</div>
			</div>

			<!-- Quick Links Column -->
			<div class="footer-col">
				<h3 class="footer-col-title">Quick Links</h3>
				<ul class="footer-links">
					<li><a href="courses.php">Courses</a></li>
					<li><a href="playground.php">Code Playground</a></li>
					<li><a href="multiplayer.php">Multiplayer Battle</a></li>
					<li><a href="leaderboard.php">Leaderboard</a></li>
					<li><a href="characters.php">Achievements</a></li>
				</ul>
			</div>

			<!-- Company Column -->
			<div class="footer-col">
				<h3 class="footer-col-title">Company</h3>
				<ul class="footer-links">
					<li><a href="about.php">About Prozone</a></li>
					<li><a href="pengaturan.php">Account Settings</a></li>
					<li><a href="#privacy">Privacy Policy</a></li>
					<li><a href="#terms">Terms of Service</a></li>
					<li><a href="#contact">Contact Support</a></li>
				</ul>
			</div>

			<!-- Contact Column -->
			<div class="footer-col">
				<h3 class="footer-col-title">Contact Info</h3>
				<div class="contact-item">
					<span class="contact-label">📍 Office:</span>
					<p>Jakarta, Indonesia</p>
				</div>
				<div class="contact-item">
					<span class="contact-label">📞 Phone:</span>
					<p>+62 (21) XXXX-XXXX</p>
				</div>
				<div class="contact-item">
					<span class="contact-label">✉️ Email:</span>
					<p><a href="mailto:hello@prozone.id">hello@prozone.id</a></p>
				</div>
			</div>

			<!-- Newsletter Column -->
			<div class="footer-col footer-newsletter">
				<h3 class="footer-col-title">Stay Updated</h3>
				<p class="newsletter-desc">Dapatkan notifikasi pembelajaran dan challenge terbaru.</p>
				<form class="newsletter-form" onsubmit="return false;">
					<input type="email" placeholder="Email Anda" class="newsletter-input" required>
					<button type="submit" class="newsletter-btn">Subscribe</button>
				</form>
			</div>
		</div>

		<!-- Footer Bottom -->
		<div class="footer-bottom">
			<div class="footer-bottom-content">
				<p class="copyright">&copy; <?php echo date('Y'); ?> <strong>Prozone</strong>. All rights reserved. | Built with ❤️ for developers in Indonesia</p>
				<div class="footer-bottom-links">
					<a href="#privacy">Privacy</a>
					<span class="divider">•</span>
					<a href="#terms">Terms</a>
					<span class="divider">•</span>
					<a href="#cookies">Cookies</a>
				</div>
			</div>
		</div>
	</div>
</footer>

<style>
	.site-footer{background:#1a1a2e;color:#f0f0f0;padding:48px 20px 32px;margin-top:48px;border-top:1px solid #3B82F6;position:relative}
	.footer-wrapper{max-width:1400px;margin:0 auto}
	.footer-content{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:32px;margin-bottom:40px}
	
	.footer-col{display:flex;flex-direction:column}
	.footer-col-title{font-size:1rem;font-weight:700;color:#fff;margin:0 0 16px 0;letter-spacing:0.5px}
	
	.footer-brand-col .footer-logo-box{margin-bottom:16px}
	.footer-brand-col .footer-logo{height:48px;width:auto;display:block}
	.footer-tagline{font-size:0.9rem;color:#b0b0b0;line-height:1.6;margin:12px 0 0 0;padding-right:12px}
	
	.footer-socials{display:flex;gap:12px;margin-top:20px}
	.social-icon{width:36px;height:36px;border:1.5px solid #3B82F6;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#3B82F6;text-decoration:none;font-weight:600;font-size:0.85rem;transition:all 0.3s ease;background:transparent}
	.social-icon:hover{background:#3B82F6;color:#fff;transform:translateY(-2px)}
	
	.footer-links{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:10px}
	.footer-links a{color:#b0b0b0;text-decoration:none;font-size:0.95rem;transition:color 0.3s ease}
	.footer-links a:hover{color:#3B82F6}
	
	.contact-item{margin-bottom:14px}
	.contact-label{color:#3B82F6;font-weight:600;font-size:0.9rem;display:block;margin-bottom:4px}
	.contact-item p{margin:0;color:#b0b0b0;font-size:0.95rem}
	.contact-item a{color:#3B82F6;text-decoration:none}
	.contact-item a:hover{text-decoration:underline}
	
	.newsletter-desc{font-size:0.9rem;color:#b0b0b0;margin:0 0 12px 0}
	.newsletter-form{display:flex;gap:8px}
	.newsletter-input{flex:1;padding:10px 14px;background:#252540;border:1.5px solid #3B82F6;border-radius:6px;color:#f0f0f0;font-size:0.9rem;outline:none;transition:all 0.3s}
	.newsletter-input::placeholder{color:#808080}
	.newsletter-input:focus{background:#2a2a45;box-shadow:0 0 12px rgba(59,130,246,0.2)}
	.newsletter-btn{padding:10px 18px;background:#3B82F6;color:#fff;border:none;border-radius:6px;font-weight:600;cursor:pointer;font-size:0.9rem;transition:all 0.3s}
	.newsletter-btn:hover{background:#2563EB;transform:translateY(-2px);box-shadow:0 6px 16px rgba(59,130,246,0.3)}
	
	.footer-bottom{border-top:1px solid #333;padding-top:24px}
	.footer-bottom-content{text-align:center}
	.copyright{margin:0;color:#808080;font-size:0.9rem}
	.copyright strong{color:#fff}
	.footer-bottom-links{display:flex;justify-content:center;gap:12px;margin-top:12px;font-size:0.9rem}
	.footer-bottom-links a{color:#808080;text-decoration:none;transition:color 0.3s}
	.footer-bottom-links a:hover{color:#3B82F6}
	.divider{color:#555}
	
	/* Place footer in the dashboard main column */
	body.dashboard-layout .site-footer{
		grid-column:2/-1;grid-row:3;width:auto;margin-left:0;margin-top:24px;padding:40px 20px 28px;border-radius:12px;border:1px solid #333
	}
	
	@media(max-width:1024px){
		body.dashboard-layout .site-footer{grid-column:1/-1;width:100%;margin-left:0}
	}
	
	@media(max-width:768px){
		.footer-content{grid-template-columns:1fr;gap:24px;margin-bottom:32px}
		.footer-socials{gap:10px}
		.social-icon{width:32px;height:32px;font-size:0.75rem}
		.newsletter-form{flex-direction:column}
		.newsletter-input,.newsletter-btn{width:100%}
	}
</style>
