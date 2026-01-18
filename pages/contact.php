<?php
$page_title = "Contact Us";
require_once '../config/database.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone'] ?? ''); // Added null coalescing for safety
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        $conn = getDbConnection();
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);

        if ($stmt->execute()) {
            $success = 'Thank you for your message! We will get back to you soon.';
            $_POST = array();
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
    :root {
        --primary-red: #ca1313;
        --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        --input-focus: rgba(202, 19, 19, 0.1);
    }

    /* Core Layout */
    .contact-container {
        max-width: 1100px;
        margin: -60px auto 60px;
        padding: 0 20px;
        position: relative;
        z-index: 10;
    }

    .contact-grid {
        display: grid;
        grid-template-columns: 1fr 1.5fr;
        gap: 2rem;
    }

    .info-card, .form-card {
        background: #fff;
        padding: 40px;
        border-radius: 16px;
        box-shadow: var(--card-shadow);
    }

    .info-card {
        background-color: #1a1a1a;
        color: #fff;
    }

    /* Form Styles */
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .contact-item {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .icon-box {
        width: 45px;
        height: 45px;
        flex-shrink: 0; /* Prevents icon from squishing */
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .form-group { margin-bottom: 1.5rem; }
    
    .form-label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #333;
        font-size: 0.9rem;
    }

    .form-input, .form-textarea {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #f0f0f0;
        border-radius: 8px;
        transition: all 0.3s ease;
        background: #fcfcfc;
        box-sizing: border-box; /* Ensures padding doesn't break width */
    }

    .form-input:focus, .form-textarea:focus {
        border-color: var(--primary-red);
        background: #fff;
        outline: none;
        box-shadow: 0 0 0 4px var(--input-focus);
    }

    .submit-btn {
        background: var(--primary-red);
        color: white;
        padding: 14px;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        cursor: pointer;
        transition: all 0.2s ease;
        width: 100%;
    }

    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(202, 19, 19, 0.3);
    }

    /* Responsive Breakpoints */
    @media (max-width: 992px) {
        .contact-grid { 
            grid-template-columns: 1fr; 
        }
        .contact-container { 
            margin-top: 2rem; 
            margin-bottom: 3rem;
        }
        .section-header {
            min-height: 300px !important;
            padding: 4rem 1rem !important;
        }
    }

    @media (max-width: 600px) {
        .form-row {
            grid-template-columns: 1fr; /* Stack Name and Email on mobile */
        }
        .info-card, .form-card {
            padding: 25px; /* Reduce padding for more screen space */
        }
        .section-header h1 {
            font-size: 2.2rem !important;
        }
    }
</style>

<section>
    <div class="section-header text-center" style="
        min-height: 400px;
        background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('../assets/images/omaa.jpg') center/cover no-repeat;
        padding: 6rem 2rem;
        color: #fff;
    ">
        <h1 style="font-weight: 800; margin-bottom: 1rem;">Let's Connect</h1>
        <p style="max-width: 600px; margin: 0 auto; opacity: 0.9;">
            Have questions about training, schedules, or our Muayboran heritage? Drop us a message below.
        </p>
    </div>

    <div class="contact-container">
        <div class="contact-grid">
            
            <div class="info-card">
                <h2 style="margin-bottom: 2rem; font-size: 1.8rem;">Contact Info</h2>
                
                <div class="contact-item">
                    <div class="icon-box">📍</div>
                    <div>
                        <h4 style="margin:0; color: #aaa; font-size: 0.8rem; text-transform: uppercase;">Location</h4>
                        <p style="margin:0;">240 Rosal St., Pingkian 3, Pasong Tamo, QC</p>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="icon-box">📧</div>
                    <div>
                        <h4 style="margin:0; color: #aaa; font-size: 0.8rem; text-transform: uppercase;">Email Us</h4>
                        <p style="margin:0; word-break: break-all;">orientalmuayboranacademy@gmail.com</p>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="icon-box">📱</div>
                    <div>
                        <h4 style="margin:0; color: #aaa; font-size: 0.8rem; text-transform: uppercase;">Call Us</h4>
                        <p style="margin:0;">+63 960 566 7175</p>
                    </div>
                </div>

                <div style="margin-top: 3rem;">
                    <h4 style="color: #aaa; font-size: 0.8rem; text-transform: uppercase; margin-bottom: 1rem;">Social Media</h4>
                    <a href="https://web.facebook.com/OrientalMuayboranAcademy" style="text-decoration: none; color: white; display: inline-flex; align-items: center; gap: 8px;">
                        <span style="background: #3b5998; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">f</span>
                        Facebook Page
                    </a>
                </div>
            </div>

            <div class="form-card">
                <?php if ($error): ?>
                    <div style="background: #fff5f5; border-left: 4px solid #ff4d4d; color: #cc0000; padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem;">
                        <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div style="background: #f0fff4; border-left: 4px solid #38a169; color: #276749; padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem;">
                        <strong>Success!</strong> <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-input" placeholder="Juan Dela Cruz" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-input" placeholder="juan@example.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-input" placeholder="How can we help?" required value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-textarea" rows="5" placeholder="Your message here..." required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                    </div>

                    <button type="submit" name="send_message" class="submit-btn">
                        Send Message
                    </button>
                </form>
            </div>

        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>