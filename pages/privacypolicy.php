<?php
$page_title = "Privacy Policy";
include '../includes/header.php';
?>

<style>
    :root {
        --primary-red: #ca1313;
        --text-dark: #1a1a1a;
        --text-light: #666;
    }

    .policy-header {
        background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('../assets/images/omaa.jpg') center/cover no-repeat;
        padding: 5rem 2rem;
        color: #fff;
        text-align: center;
        margin-bottom: 3rem;
    }

    .policy-content {
        max-width: 900px;
        margin: 0 auto 5rem;
        padding: 0 20px;
        line-height: 1.8;
        color: var(--text-light);
    }

    .policy-content h2 {
        color: var(--text-dark);
        margin-top: 2.5rem;
        margin-bottom: 1rem;
        border-left: 4px solid var(--primary-red);
        padding-left: 15px;
    }

    .policy-content p {
        margin-bottom: 1.5rem;
    }

    .policy-card {
        background: #fff;
        padding: 2.5rem;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        border: 1px solid #f0f0f0;
    }

    .last-updated {
        font-style: italic;
        color: #999;
        margin-bottom: 2rem;
    }

    ul.policy-list {
        padding-left: 1.5rem;
        margin-bottom: 1.5rem;
    }

    ul.policy-list li {
        margin-bottom: 0.5rem;
    }
</style>

<section>
    <div class="policy-header">
        <h1 style="font-size: 3rem; font-weight: 800;">Privacy Policy</h1>
        <p>How we protect your data at Oriental Muayboran Academy</p>
    </div>

    <div class="policy-content">
        <div class="policy-card">
            <p class="last-updated">Last Updated: <?php echo date('F d, Y'); ?></p>

            <p>At <strong>Oriental Muayboran Academy (OMA)</strong>, we respect your privacy and are committed to protecting the personal data you share with us. This policy outlines how we collect, use, and safeguard your information when you visit our website or enroll in our programs.</p>

            <h2>1. Information We Collect</h2>
            <p>We collect information that you provide directly to us, including:</p>
            <ul class="policy-list">
                <li><strong>Personal Identity:</strong> Name, age, and gender (for Khan grading and membership records).</li>
                <li><strong>Contact Information:</strong> Email address, phone number, and physical address.</li>
                <li><strong>Media:</strong> Photos or videos taken during training sessions or events (with your consent) for use on our "Khan Members" page or social media.</li>
                <li><strong>Technical Data:</strong> IP address and browser type for website performance monitoring.</li>
            </ul>

            <h2>2. How We Use Your Information</h2>
            <p>Your data is used to provide a better experience, specifically for:</p>
            <ul class="policy-list">
                <li>Maintaining accurate records for the <strong>Khan Grading System</strong>.</li>
                <li>Responding to inquiries via our Contact Form.</li>
                <li>Providing updates on class schedules, seminars, and academy events.</li>
                <li>Displaying member achievements on our website.</li>
            </ul>

            <h2>3. Khan Grading Visibility</h2>
            <p>As part of our traditional heritage, we celebrate the progress of our students. By participating in the Khan system, your name, current Khan level, and instructor may be displayed publicly on our "Khan Members" page. You may request to have your profile made private at any time.</p>

            <h2>4. Data Protection</h2>
            <p>We implement industry-standard security measures to protect your personal information from unauthorized access, alteration, or disclosure. Access to your sensitive data is restricted to authorized administrative staff and Ajarns only.</p>

            <h2>5. Third-Party Services</h2>
            <p>We do not sell or trade your personal information. We may use trusted third-party services (such as Google Maps for our location or hosting providers) that adhere to their own strict privacy standards.</p>

            <h2>6. Your Rights</h2>
            <p>You have the right to:</p>
            <ul class="policy-list">
                <li>Request access to the personal data we hold about you.</li>
                <li>Request corrections to any inaccurate information.</li>
                <li>Request the deletion of your membership profile from our public website.</li>
            </ul>

            <h2>7. Contact Us</h2>
            <p>If you have any questions regarding this Privacy Policy or how your data is handled, please reach out to us:</p>
            <p>
                <strong>Email:</strong> orientalmuayboranacademy@gmail.com<br>
                <strong>Location:</strong> 240 Rosal St., Pingkian 3, Pasong Tamo, Quezon City
            </p>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>