<?php
$page_title = "Membership Inquiry";
require_once '../config/database.php';

$error = '';
$success = '';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

// Handle membership inquiry submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_inquiry'])) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $age = (int)$_POST['age'];
    $experience_level = sanitize($_POST['experience_level']);
    $training_goals = sanitize($_POST['training_goals']);
    $preferred_schedule = sanitize($_POST['preferred_schedule']);
    $message = sanitize($_POST['message']);
    
    // Validation
    if (empty($name) || empty($email) || empty($phone)) {
        $error = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        $conn = getDbConnection();
        
        // Save inquiry to contact_messages table
        $subject = 'Membership Inquiry';
        $full_message = "=== MEMBERSHIP INQUIRY ===\n\n";
        $full_message .= "Name: {$name}\n";
        $full_message .= "Email: {$email}\n";
        $full_message .= "Phone: {$phone}\n";
        $full_message .= "Address: {$address}\n";
        $full_message .= "Age: {$age}\n";
        $full_message .= "Experience Level: {$experience_level}\n";
        $full_message .= "Training Goals: {$training_goals}\n";
        $full_message .= "Preferred Schedule: {$preferred_schedule}\n\n";
        $full_message .= "Additional Message:\n{$message}\n";
        
        $status = 'new';
        
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssssss", $name, $email, $phone, $subject, $full_message, $status);
        
        if ($stmt->execute()) {
            $success = 'Thank you for your interest! Your membership inquiry has been received. Our team will contact you within 24-48 hours to discuss the next steps.';
            $_POST = array();
        } else {
            $error = 'Failed to submit inquiry. Please try again or contact us directly.';
        }
        
        $stmt->close();
        $conn->close();
    }
}

include '../includes/header.php';
?>

<section class="section">
    <div class="container">
        <div style="max-width: 700px; margin: 0 auto;">
            <div class="section-header text-center">
                <p class="section-subtitle">Join OMA</p>
                <h1 class="section-title">Membership Inquiry</h1>
                <p class="section-description">
                    Start your journey in Muayboran. Fill out this form and we'll contact you to discuss membership options and schedule a visit.
                </p>
            </div>
            
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 1.25rem; margin-bottom: 2rem; border-radius: 4px;">
                <h3 style="margin: 0 0 0.5rem 0; color: #856404; font-size: 1.1rem;">📋 Next Steps After Submission:</h3>
                <ul style="margin: 0; padding-left: 1.5rem; color: #856404;">
                    <li>Our team will review your inquiry within 24-48 hours</li>
                    <li>You'll receive an email/call to schedule a visit to our training facility</li>
                    <li>During the visit, we'll discuss membership options, training programs, and answer your questions</li>
                    <li>Alternatively, we can arrange an online discussion if you prefer</li>
                    <li>After consultation, we'll create your member account and assign you a Khan level</li>
                </ul>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error" style="background: #ffebee; color: var(--color-primary); padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" style="background: #e8f5e9; color: #2e7d32; padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem;">
                    <strong>✅ <?php echo $success; ?></strong>
                    <br><br>
                    <p style="margin: 1rem 0 0 0;">
                        <strong>Contact Information:</strong><br>
                        📍 240 Rosal St., Pingkian 3, Pasong Tamo, Quezon City<br>
                        📞 +63 960 566 7175<br>
                        📧 orientalmuayboranacademy@gmail.com
                    </p>
                </div>
            <?php endif; ?>
            
            <?php if (!$success): ?>
            <form method="POST" action="" data-validate>
                <h3 style="margin: 0 0 1rem 0; color: var(--color-primary);">Personal Information</h3>
                
                <div class="form-group">
                    <label for="name" class="form-label">Full Name *</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        class="form-input" 
                        placeholder="Enter your full name"
                        required
                        value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                    >
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email Address *</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        placeholder="your.email@example.com"
                        required
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    >
                </div>
                
                <div class="form-group">
                    <label for="phone" class="form-label">Phone Number *</label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone" 
                        class="form-input" 
                        placeholder="+63 XXX XXX XXXX"
                        required
                        value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                    >
                </div>
                
                <div class="form-group">
                    <label for="address" class="form-label">Address</label>
                    <input 
                        type="text" 
                        id="address" 
                        name="address" 
                        class="form-input" 
                        placeholder="City, Province"
                        value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>"
                    >
                </div>
                
                <div class="form-group">
                    <label for="age" class="form-label">Age</label>
                    <input 
                        type="number" 
                        id="age" 
                        name="age" 
                        class="form-input" 
                        placeholder="Your age"
                        min="10"
                        max="100"
                        value="<?php echo isset($_POST['age']) ? htmlspecialchars($_POST['age']) : ''; ?>"
                    >
                </div>
                
                <hr style="margin: 2rem 0; border: none; border-top: 1px solid #ddd;">
                
                <h3 style="margin: 0 0 1rem 0; color: var(--color-primary);">Training Background</h3>
                
                <div class="form-group">
                    <label for="experience_level" class="form-label">Martial Arts Experience Level</label>
                    <select 
                        id="experience_level" 
                        name="experience_level" 
                        class="form-input"
                    >
                        <option value="No Experience">No Experience (Complete Beginner)</option>
                        <option value="Beginner">Beginner (Less than 1 year)</option>
                        <option value="Intermediate">Intermediate (1-3 years)</option>
                        <option value="Advanced">Advanced (3+ years)</option>
                        <option value="Instructor">Instructor Level</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="training_goals" class="form-label">Training Goals</label>
                    <textarea 
                        id="training_goals" 
                        name="training_goals" 
                        class="form-input" 
                        rows="3"
                        placeholder="What do you hope to achieve through Muayboran training? (e.g., fitness, self-defense, cultural knowledge, competition)"
                    ><?php echo isset($_POST['training_goals']) ? htmlspecialchars($_POST['training_goals']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="preferred_schedule" class="form-label">Preferred Training Schedule</label>
                    <select 
                        id="preferred_schedule" 
                        name="preferred_schedule" 
                        class="form-input"
                    >
                        <option value="Weekday Mornings">Weekday Mornings</option>
                        <option value="Weekday Evenings">Weekday Evenings</option>
                        <option value="Weekends">Weekends</option>
                        <option value="Flexible">Flexible</option>
                        <option value="To be discussed">To be discussed</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="message" class="form-label">Additional Questions or Comments</label>
                    <textarea 
                        id="message" 
                        name="message" 
                        class="form-input" 
                        rows="4"
                        placeholder="Any questions or information you'd like to share?"
                    ><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                </div>
                
                <div style="margin-bottom: 1.5rem; background: #e3f2fd; padding: 1rem; border-radius: 4px;">
                    <label style="display: flex; align-items: start; gap: 0.5rem; font-size: 0.9rem;">
                        <input type="checkbox" required style="width: auto; margin-top: 0.25rem;">
                        <span>
                            I understand that this is an inquiry form and not an automatic registration. 
                            I agree to be contacted by OMA staff to discuss membership options and schedule a facility visit or online consultation.
                        </span>
                    </label>
                </div>
                
                <button type="submit" name="submit_inquiry" class="btn btn-primary" style="width: 100%;">
                    Submit Membership Inquiry
                </button>
                
                <p style="text-align: center; margin-top: 1.5rem; color: var(--color-text-light);">
                    Already have an account? 
                    <a href="login.php" style="color: var(--color-primary); font-weight: 500;">Sign in here</a>
                </p>
            </form>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>