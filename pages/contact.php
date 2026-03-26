<?php
$page_title = "Contact Us";
$extra_head = '<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">';
require_once '../config/database.php';

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $name    = trim($_POST['name']);
    $email   = trim($_POST['email']);
    $phone   = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $conn = getDbConnection();
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);
        if ($stmt->execute()) {
            $success = 'Thank you for your message! We will get back to you soon.';
            $_POST   = [];
        } else {
            $error = 'Failed to send message. Please try again.';
        }
        $stmt->close();
        $conn->close();
    }
}

include '../includes/header.php';
?>

<style>
/* ============================================================
   DESIGN TOKENS
   ============================================================ */
:root {
    --gold:         #D4AF37;
    --gold-light:   #F0D060;
    --gold-dark:    #A07C10;
    --red:          #ca1313;
    --black:        #0a0a0a;
    --dark:         #111;
    --mid:          #1a1a1a;
    --white:        #fff;
    --muted:        rgba(255,255,255,0.65);
    --border-gold:  rgba(212,175,55,0.15);
    --font-display: 'Cinzel', serif;
    --font-body:    'Cormorant Garamond', serif;
    --font-ui:      'Rajdhani', sans-serif;
    --ease:         0.25s cubic-bezier(0.4,0,0.2,1);
}

body { background: var(--dark); color: var(--white); }

.container {
    width: 90%;
    max-width: 1200px;
    margin: 0 auto;
}

/* ============================================================
   HERO
   ============================================================ */
.contact-hero {
    position: relative;
    min-height: 60vh;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: var(--black);
}
.contact-hero-bg {
    position: absolute; inset: 0;
}
.contact-hero-bg img {
    width: 100%; height: 100%;
    object-fit: cover;
    opacity: 0.32;
    transform: scale(1.04);
    transition: transform 14s ease;
}
.contact-hero:hover .contact-hero-bg img { transform: scale(1.0); }
.contact-hero-overlay {
    position: absolute; inset: 0;
    background:
        linear-gradient(to right,  rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.3) 50%, rgba(0,0,0,0.9) 100%),
        linear-gradient(to bottom, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0.05) 50%, rgba(0,0,0,0.85) 100%);
}

.hero-corner {
    position: absolute;
    width: 72px; height: 72px;
    z-index: 3; opacity: 0.5;
}
.hero-corner--tl { top: 24px; left: 24px;    border-top: 2px solid var(--gold); border-left:  2px solid var(--gold); }
.hero-corner--tr { top: 24px; right: 24px;   border-top: 2px solid var(--gold); border-right: 2px solid var(--gold); }
.hero-corner--bl { bottom: 24px; left: 24px;  border-bottom: 2px solid var(--gold); border-left:  2px solid var(--gold); }
.hero-corner--br { bottom: 24px; right: 24px; border-bottom: 2px solid var(--gold); border-right: 2px solid var(--gold); }

.contact-hero-content {
    position: relative; z-index: 2;
    text-align: center;
    padding: 80px 24px;
    opacity: 0;
    animation: heroFade 1s ease 0.1s forwards;
}
@keyframes heroFade { to { opacity: 1; } }

.hero-eyebrow {
    display: inline-block;
    font-family: var(--font-ui);
    font-size: 0.72rem; font-weight: 700;
    letter-spacing: 6px; text-transform: uppercase;
    color: var(--gold);
    background: rgba(212,175,55,0.08);
    border: 1px solid rgba(212,175,55,0.3);
    padding: 6px 18px; border-radius: 2px;
    margin-bottom: 22px;
}
.contact-hero-content h1 {
    font-family: var(--font-display);
    font-size: 3.8rem; font-weight: 900;
    color: var(--white); text-transform: uppercase;
    letter-spacing: 3px; line-height: 1.05;
    margin: 0 0 24px;
    text-shadow: 0 4px 30px rgba(0,0,0,0.9);
}
.contact-hero-content h1 span { color: var(--gold); }

.hero-divider {
    display: flex; align-items: center; justify-content: center;
    gap: 14px; margin: 0 auto 20px; max-width: 440px;
}
.hero-divider-line      { flex: 1; height: 1px; }
.hero-divider-line.l    { background: linear-gradient(to left,  var(--gold), transparent); }
.hero-divider-line.r    { background: linear-gradient(to right, var(--gold), transparent); }
.hero-divider-diamond   { width: 7px; height: 7px; background: var(--gold); transform: rotate(45deg); flex-shrink: 0; }

.contact-hero-content p {
    font-family: var(--font-body);
    font-size: 1.2rem; color: var(--muted);
    max-width: 500px; margin: 0 auto;
    line-height: 1.75; font-weight: 300; font-style: italic;
}

.hero-scroll {
    position: absolute; bottom: 28px; left: 50%;
    transform: translateX(-50%); z-index: 3;
    display: flex; flex-direction: column;
    align-items: center; gap: 6px; opacity: 0.4;
    animation: scrollBob 2.5s ease-in-out infinite;
}
.hero-scroll span { font-family: var(--font-ui); font-size: 0.6rem; letter-spacing: 3px; color: var(--gold); text-transform: uppercase; }
.hero-scroll-line { width: 1px; height: 32px; background: linear-gradient(to bottom, var(--gold), transparent); }
@keyframes scrollBob {
    0%,100% { transform: translateX(-50%) translateY(0); }
    50%      { transform: translateX(-50%) translateY(8px); }
}

/* ============================================================
   CONTACT SECTION
   ============================================================ */
.contact-section {
    background: var(--dark);
    padding: 90px 0 100px;
}

.contact-grid {
    display: grid;
    grid-template-columns: 1fr 1.6fr;
    gap: 28px;
    margin-bottom: 28px;
}

/* ---- Info Card ---- */
.info-card {
    background: rgba(255,255,255,0.02);
    border: 1px solid var(--border-gold);
    border-radius: 4px;
    padding: 3rem 2.4rem;
    position: relative;
    overflow: hidden;
}
.info-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    background: linear-gradient(to right, var(--red), var(--gold));
}

.info-card-title {
    font-family: var(--font-display);
    font-size: 1.3rem; font-weight: 700;
    color: var(--white); letter-spacing: 2px;
    text-transform: uppercase;
    margin: 0 0 2.4rem;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 2rem;
}
.contact-item:last-of-type { margin-bottom: 0; }

.icon-box {
    width: 42px; height: 42px;
    flex-shrink: 0;
    background: rgba(212,175,55,0.08);
    border: 1px solid rgba(212,175,55,0.2);
    border-radius: 2px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
    transition: background var(--ease), border-color var(--ease);
}
.contact-item:hover .icon-box { background: rgba(212,175,55,0.14); border-color: rgba(212,175,55,0.4); }

.contact-item-label {
    font-family: var(--font-ui);
    font-size: 0.62rem; font-weight: 700;
    letter-spacing: 3px; text-transform: uppercase;
    color: var(--gold);
    display: block; margin-bottom: 5px;
}
.contact-item-value {
    font-family: var(--font-body);
    font-size: 1.05rem; color: var(--muted);
    line-height: 1.5; word-break: break-word;
    margin: 0;
}

/* Social row */
.social-divider {
    width: 100%;
    height: 1px;
    background: linear-gradient(to right, transparent, var(--border-gold), transparent);
    margin: 2rem 0;
}
.social-label {
    font-family: var(--font-ui);
    font-size: 0.62rem; font-weight: 700;
    letter-spacing: 3px; text-transform: uppercase;
    color: rgba(255,255,255,0.3);
    margin-bottom: 14px;
    display: block;
}
.fb-link {
    display: inline-flex; align-items: center; gap: 12px;
    text-decoration: none;
    font-family: var(--font-ui);
    font-size: 0.8rem; font-weight: 600;
    letter-spacing: 2px; text-transform: uppercase;
    color: var(--muted);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 2px;
    padding: 10px 16px;
    transition: border-color var(--ease), color var(--ease), background var(--ease);
}
.fb-link:hover { border-color: rgba(59,89,152,0.6); color: var(--white); background: rgba(59,89,152,0.1); }
.fb-icon {
    width: 28px; height: 28px;
    background: #3b5998;
    border-radius: 2px;
    display: flex; align-items: center; justify-content: center;
    font-family: var(--font-display);
    font-size: 0.9rem; font-weight: 900;
    color: var(--white);
    flex-shrink: 0;
}

/* ---- Form Card ---- */
.form-card {
    background: rgba(255,255,255,0.02);
    border: 1px solid var(--border-gold);
    border-radius: 4px;
    padding: 3rem 2.4rem;
    position: relative;
}
.form-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    background: linear-gradient(to right, var(--gold), var(--red));
}

.form-card-title {
    font-family: var(--font-display);
    font-size: 1.3rem; font-weight: 700;
    color: var(--white); letter-spacing: 2px;
    text-transform: uppercase;
    margin: 0 0 2rem;
}

/* Alert messages */
.alert {
    padding: 14px 18px;
    border-radius: 2px;
    margin-bottom: 1.5rem;
    font-family: var(--font-ui);
    font-size: 0.82rem; letter-spacing: 1px;
    font-weight: 600;
    display: flex; align-items: center; gap: 10px;
}
.alert-error   { background: rgba(202,19,19,0.1); border: 1px solid rgba(202,19,19,0.3); color: #f87171; }
.alert-success { background: rgba(34,197,94,0.08); border: 1px solid rgba(34,197,94,0.25); color: #4ade80; }

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.form-group { margin-bottom: 1.4rem; }

.form-label {
    display: block;
    font-family: var(--font-ui);
    font-size: 0.68rem; font-weight: 700;
    letter-spacing: 3px; text-transform: uppercase;
    color: rgba(255,255,255,0.5);
    margin-bottom: 8px;
}
.form-label .required { color: var(--gold); margin-left: 2px; }

.form-input,
.form-textarea {
    width: 100%;
    padding: 12px 16px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(212,175,55,0.18);
    border-radius: 2px;
    font-family: var(--font-body);
    font-size: 1.05rem;
    color: var(--white);
    outline: none;
    box-sizing: border-box;
    transition: border-color var(--ease), background var(--ease), box-shadow var(--ease);
}
.form-input::placeholder,
.form-textarea::placeholder { color: rgba(255,255,255,0.22); }
.form-input:focus,
.form-textarea:focus {
    border-color: rgba(212,175,55,0.55);
    background: rgba(212,175,55,0.04);
    box-shadow: 0 0 0 3px rgba(212,175,55,0.08);
}
.form-textarea { resize: vertical; min-height: 130px; }

.submit-btn {
    width: 100%;
    font-family: var(--font-ui);
    font-weight: 700; font-size: 0.82rem;
    letter-spacing: 3px; text-transform: uppercase;
    background: var(--gold); color: #000;
    padding: 16px;
    border: none; border-radius: 2px;
    cursor: pointer;
    transition: background var(--ease), box-shadow var(--ease), transform 0.2s;
    box-shadow: 0 4px 20px rgba(212,175,55,0.25);
    margin-top: 8px;
}
.submit-btn:hover {
    background: var(--gold-light);
    box-shadow: 0 6px 28px rgba(212,175,55,0.45);
    transform: translateY(-2px);
}

/* ---- Map Card ---- */
.map-card {
    border: 1px solid var(--border-gold);
    border-radius: 4px;
    overflow: hidden;
    position: relative;
}
.map-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    background: linear-gradient(to right, var(--gold), var(--red));
    z-index: 1;
}
.map-card iframe {
    width: 100%;
    height: 420px;
    display: block;
    border: 0;
    filter: grayscale(0.3) brightness(0.85) contrast(1.1);
    transition: filter var(--ease);
}
.map-card:hover iframe { filter: grayscale(0) brightness(0.95) contrast(1.05); }

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 900px) {
    .contact-hero-content h1 { font-size: 2.6rem; }
    .contact-grid { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .contact-hero-content h1 { font-size: 1.9rem; }
    .hero-corner { width: 48px; height: 48px; }
    .form-row { grid-template-columns: 1fr; }
    .info-card, .form-card { padding: 2rem 1.5rem; }
    .map-card iframe { height: 300px; }
}
</style>

<!-- ============================================================
     HERO
     ============================================================ -->
<section class="contact-hero">
    <div class="contact-hero-bg">
        <img src="../assets/images/omaa.jpg" alt="Contact OMA">
    </div>
    <div class="contact-hero-overlay"></div>

    <div class="hero-corner hero-corner--tl"></div>
    <div class="hero-corner hero-corner--tr"></div>
    <div class="hero-corner hero-corner--bl"></div>
    <div class="hero-corner hero-corner--br"></div>

    <div class="contact-hero-content">
        <span class="hero-eyebrow">Get In Touch</span>
        <h1>Let's <span>Connect</span></h1>
        <div class="hero-divider">
            <div class="hero-divider-line l"></div>
            <div class="hero-divider-diamond"></div>
            <div class="hero-divider-diamond"></div>
            <div class="hero-divider-line r"></div>
        </div>
        <p>Have questions about training, schedules, or our Muayboran heritage? Drop us a message below.</p>
    </div>

    <div class="hero-scroll">
        <span>Scroll</span>
        <div class="hero-scroll-line"></div>
    </div>
</section>

<!-- ============================================================
     CONTACT SECTION
     ============================================================ -->
<section class="contact-section">
    <div class="container">

        <div class="contact-grid">

            <!-- Info Card -->
            <div class="info-card">
                <h2 class="info-card-title">Contact Info</h2>

                <div class="contact-item">
                    <div class="icon-box">📍</div>
                    <div>
                        <span class="contact-item-label">Location</span>
                        <p class="contact-item-value">240 Rosal St., Pingkian 3,<br>Pasong Tamo, Quezon City</p>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="icon-box">📧</div>
                    <div>
                        <span class="contact-item-label">Email Us</span>
                        <p class="contact-item-value">orientalmuayboranacademy@gmail.com</p>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="icon-box">📱</div>
                    <div>
                        <span class="contact-item-label">Call Us</span>
                        <p class="contact-item-value">+63 960 566 7175</p>
                    </div>
                </div>

                <div class="social-divider"></div>

                <span class="social-label">Follow Us</span>
                <a href="https://web.facebook.com/OrientalMuayboranAcademy"
                   target="_blank" class="fb-link">
                    <div class="fb-icon">f</div>
                    Facebook Page
                </a>
            </div>

            <!-- Form Card -->
            <div class="form-card">
                <h2 class="form-card-title">Send a Message</h2>

                <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Full Name <span class="required">*</span></label>
                            <input type="text" name="name" class="form-input"
                                   placeholder="Juan Dela Cruz" required
                                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Address <span class="required">*</span></label>
                            <input type="email" name="email" class="form-input"
                                   placeholder="juan@example.com" required
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone <span style="color:rgba(255,255,255,0.2);font-size:0.6rem;letter-spacing:1px;">(Optional)</span></label>
                        <input type="tel" name="phone" class="form-input"
                               placeholder="+63 9XX XXX XXXX"
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Subject <span class="required">*</span></label>
                        <input type="text" name="subject" class="form-input"
                               placeholder="How can we help?" required
                               value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Message <span class="required">*</span></label>
                        <textarea name="message" class="form-textarea"
                                  rows="5" placeholder="Your message here..." required
                        ><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                    </div>

                    <button type="submit" name="send_message" class="submit-btn">
                        <i class="fas fa-paper-plane" style="margin-right:8px;font-size:0.75rem;"></i>
                        Send Message
                    </button>
                </form>
            </div>

        </div><!-- /.contact-grid -->

        <!-- Map -->
        <div class="map-card">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d964.8578717303418!2d121.0590624!3d14.688174199999997!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397b163299740e3%3A0x8364f859c7f14155!2sOriental%20Muay%20Boran%20Academy%20(updated)!5e0!3m2!1sen!2sph!4v1768915162638!5m2!1sen!2sph"
                allowfullscreen="" loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                title="Oriental Muayboran Academy Location">
            </iframe>
        </div>

    </div>
</section>

<?php include '../includes/footer.php'; ?>