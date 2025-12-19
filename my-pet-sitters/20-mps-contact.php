<?php
/**
 * MPS CONTACT - Simple Contact Form
 * 
 * Requires: MPS Core
 * 
 * This snippet provides:
 * - [mps_contact] shortcode
 * - styled form matching the theme
 * - wp_mail sending logic
 */

if (!defined('ABSPATH')) exit;

add_shortcode('mps_contact', function($atts) {
    $a = shortcode_atts([], $atts, 'mps_contact');
    
    // Handle Form Submission
    $response = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mps_contact_nonce'])) {
        if (!wp_verify_nonce($_POST['mps_contact_nonce'], 'mps_contact_action')) {
            $response = '<div class="mps-notice mps-notice-error">Security check failed. Please try again.</div>';
        } else {
            $name    = sanitize_text_field($_POST['mps_name'] ?? '');
            $email   = sanitize_email($_POST['mps_email'] ?? '');
            $subject = sanitize_text_field($_POST['mps_subject'] ?? '');
            $message = sanitize_textarea_field($_POST['mps_message'] ?? '');
            
            if (!$name || !$email || !$message) {
                $response = '<div class="mps-notice mps-notice-error">Please fill in all required fields.</div>';
            } else {
                $to = get_option('admin_email');
                $headers = ["Content-Type: text/html; charset=UTF-8", "Reply-To: $name <$email>"];
                $body = "<h2>New Contact Message</h2>
                         <p><strong>Name:</strong> $name</p>
                         <p><strong>Email:</strong> $email</p>
                         <p><strong>Subject:</strong> $subject</p>
                         <p><strong>Message:</strong><br>" . nl2br($message) . "</p>";
                
                $sent = wp_mail($to, "Contact: $subject", $body, $headers);
                
                if ($sent) {
                    $response = '<div class="mps-notice mps-notice-success">Thanks for your message! We will get back to you shortly.</div>';
                    // Clear fields
                    $name = $email = $subject = $message = '';
                } else {
                    $response = '<div class="mps-notice mps-notice-error">Sorry, there was an error sending your message. Please try again later.</div>';
                }
            }
        }
    }
    
    ob_start();
    ?>
    <div class="mps-contact-form-wrapper" style="max-width:600px;margin:0 auto;background:#fff;padding:40px;border-radius:20px;box-shadow:0 10px 40px rgba(0,0,0,0.08);">
        <?php if ($response) echo $response; ?>
        
        <form method="post" class="mps-contact-form">
            <?php wp_nonce_field('mps_contact_action', 'mps_contact_nonce'); ?>
            
            <div class="mps-form-group">
                <label for="mps_name">Your Name</label>
                <input type="text" name="mps_name" id="mps_name" value="<?= esc_attr($name ?? '') ?>" required placeholder="Full Name">
            </div>
            
            <div class="mps-form-group">
                <label for="mps_email">Email Address</label>
                <input type="email" name="mps_email" id="mps_email" value="<?= esc_attr($email ?? '') ?>" required placeholder="name@example.com">
            </div>
            
            <div class="mps-form-group">
                <label for="mps_subject">Subject</label>
                <select name="mps_subject" id="mps_subject">
                    <option value="General Enquiry">General Enquiry</option>
                    <option value="Support">Support</option>
                    <option value="Feedback">Feedback</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            
            <div class="mps-form-group">
                <label for="mps_message">Message</label>
                <textarea name="mps_message" id="mps_message" rows="5" required placeholder="How can we help?"><?= esc_textarea($message ?? '') ?></textarea>
            </div>
            
            <button type="submit" class="mps-btn-primary" style="width:100%;">Send Message</button>
        </form>
    </div>
    
    <style>
        .mps-form-group { margin-bottom: 20px; }
        .mps-form-group label { display: block; font-weight: 600; margin-bottom: 8px; color: var(--mps-text, #2c3e50); }
        .mps-form-group input, 
        .mps-form-group select, 
        .mps-form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e0e6e3;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.2s;
            box-sizing: border-box;
        }
        .mps-form-group input:focus, 
        .mps-form-group select:focus, 
        .mps-form-group textarea:focus {
            outline: none;
            border-color: var(--mps-teal, #0d7377);
            box-shadow: 0 0 0 3px rgba(13, 115, 119, 0.1);
        }
        .mps-btn-primary {
            background: var(--mps-teal, #0d7377);
            color: #fff;
            padding: 14px 24px;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .mps-btn-primary:hover {
            background: #0a5f63;
            transform: translateY(-1px);
        }
        .mps-notice { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .mps-notice-error { background: #ffebee; color: #c62828; }
        .mps-notice-success { background: #e8f5e9; color: #2e7d32; }
    </style>
    <?php
    return ob_get_clean();
});


