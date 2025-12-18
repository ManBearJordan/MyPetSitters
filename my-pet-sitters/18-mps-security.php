<?php
/**
 * MPS SECURITY & PASSWORDS
 * 
 * Frontend Password Management:
 * 1. Change Password (Logged in)
 * 2. Lost Password (Public)
 * 3. Reset Password (Public, via email link)
 */

if (!defined('ABSPATH')) exit;

// ---------------------------------------------------------
// 1. CHANGE PASSWORD (Logged In)
// ---------------------------------------------------------
add_shortcode('mps_change_password', function() {
    if (!is_user_logged_in()) return '';
    
    $msg = '';
    if (isset($_GET['pass_updated'])) {
        $msg = '<div style="background:#d4edda;color:#155724;padding:10px;border-radius:4px;margin-bottom:15px;">Password updated successfully.</div>';
    }
    
    ob_start();
    ?>
    <div class="mps-security-box">
        <h4>Change Password</h4>
        <?= $msg ?>
        <form method="post" action="<?= esc_url(admin_url('admin-post.php')) ?>">
            <input type="hidden" name="action" value="mps_process_change_password">
            <?php wp_nonce_field('mps_change_pass'); ?>
            
            <p>
                <label>Current Password <br>
                <input type="password" name="current_pass" required style="width:100%;padding:8px;"></label>
            </p>
            <p>
                <label>New Password <br>
                <input type="password" name="new_pass" required style="width:100%;padding:8px;"></label>
            </p>
            <p>
                <label>Confirm New Password <br>
                <input type="password" name="confirm_pass" required style="width:100%;padding:8px;"></label>
            </p>
            <button type="submit" class="button">Update Password</button>
        </form>
    </div>
    <?php
    return ob_get_clean();
});

add_action('admin_post_mps_process_change_password', function() {
    if (!is_user_logged_in()) wp_die('Unauthorized');
    check_admin_referer('mps_change_pass');
    
    $user = wp_get_current_user();
    $current = $_POST['current_pass'];
    $new = $_POST['new_pass'];
    $confirm = $_POST['confirm_pass'];
    
    // Check current
    if (!wp_check_password($current, $user->user_pass, $user->ID)) {
        wp_die('Error: Current password is incorrect.');
    }
    
    if ($new !== $confirm) {
        wp_die('Error: New passwords do not match.');
    }
    
    wp_set_password($new, $user->ID);
    
    // Auth cookie is cleared by wp_set_password, so re-login
    wp_set_auth_cookie($user->ID);
    
    wp_redirect(home_url('/account/?tab=profile&pass_updated=1'));
    exit;
});


// ---------------------------------------------------------
// 2. LOST PASSWORD (Public)
// ---------------------------------------------------------
add_shortcode('mps_lost_password', function() {
    if (is_user_logged_in()) return '<p>You are already logged in.</p>';
    
    $msg = '';
    if (isset($_GET['reset_sent'])) {
        $msg = '<div style="background:#d4edda;color:#155724;padding:10px;margin-bottom:15px;">Check your email for the reset link!</div>';
    }
    
    ob_start();
    ?>
    <div class="mps-auth-container" style="max-width:400px;margin:40px auto;padding:30px;background:#fff;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1);">
        <h2 style="text-align:center;margin-bottom:20px;">Lost Password</h2>
        <?= $msg ?>
        <p style="text-align:center;color:#666;">Enter your email address and we'll send you a link to reset your password.</p>
        
        <form method="post" action="<?= esc_url(admin_url('admin-post.php')) ?>">
            <input type="hidden" name="action" value="mps_process_lost_password">
            <?php wp_nonce_field('mps_lost_pass'); ?>
            
            <p>
                <label>Email Address<br>
                <input type="email" name="user_login" required style="width:100%;padding:10px;"></label>
            </p>
            <button type="submit" class="wp-block-button__link" style="width:100%;text-align:center;justify-content:center;">Get Reset Link</button>
        </form>
        <p style="text-align:center;margin-top:20px;"><a href="/login/">Back to Login</a></p>
    </div>
    <?php
    return ob_get_clean();
});

add_action('admin_post_mps_process_lost_password', function() {
    check_admin_referer('mps_lost_pass');
    
    $login = sanitize_text_field($_POST['user_login']);
    $user = get_user_by('email', $login);
    
    if (!$user) {
        // Don't reveal user doesn't exist, just redirect (security)
        wp_redirect(home_url('/lost-password/?reset_sent=1'));
        exit;
    }
    
    // Generate Key
    $key = get_password_reset_key($user);
    if (is_wp_error($key)) wp_die('Error generating key');
    
    // Build Link to custom page
    $reset_link = home_url("/reset-password/?key=$key&login=" . rawurlencode($user->user_login));
    
    // Send Email
    $message = "Someone requested a password reset for your account.\n\n";
    $message .= "If this was a mistake, just ignore this email and nothing will happen.\n\n";
    $message .= "To reset your password, visit the following address:\n";
    $message .= $reset_link . "\n";
    
    // Use standard WP mail (our HTML wrapper will catch it!)
    wp_mail($user->user_email, 'Password Reset Request', $message);
    
    wp_redirect(home_url('/lost-password/?reset_sent=1'));
    exit;
});


// ---------------------------------------------------------
// 3. RESET PASSWORD FORM (Public)
// ---------------------------------------------------------
add_shortcode('mps_reset_password', function() {
    if (!isset($_GET['key']) || !isset($_GET['login'])) {
        return '<p>Invalid password reset link.</p>';
    }
    
    $key = $_GET['key'];
    $login = $_GET['login'];
    
    // Verify Key
    $user = check_password_reset_key($key, $login);
    if (is_wp_error($user)) {
        return '<div style="color:red;padding:20px;text-align:center;">This password reset link has expired or is invalid. <a href="/lost-password/">Try again</a>.</div>';
    }
    
    ob_start();
    ?>
    <div class="mps-auth-container" style="max-width:400px;margin:40px auto;padding:30px;background:#fff;border-radius:8px;">
        <h2 style="text-align:center;">Set New Password</h2>
        <form method="post" action="<?= esc_url(admin_url('admin-post.php')) ?>">
            <input type="hidden" name="action" value="mps_process_reset_password">
            <input type="hidden" name="user_login" value="<?= esc_attr($login) ?>">
            <input type="hidden" name="key" value="<?= esc_attr($key) ?>">
            <?php wp_nonce_field('mps_do_reset'); ?>
            
            <p>
                <label>New Password<br>
                <input type="password" name="pass1" required style="width:100%;padding:10px;"></label>
            </p>
            <p>
                <label>Confirm Password<br>
                <input type="password" name="pass2" required style="width:100%;padding:10px;"></label>
            </p>
            <button type="submit" class="wp-block-button__link" style="width:100%;justify-content:center;">Save Password</button>
        </form>
    </div>
    <?php
    return ob_get_clean();
});

add_action('admin_post_mps_process_reset_password', function() {
    check_admin_referer('mps_do_reset');
    
    $login = $_POST['user_login'];
    $key = $_POST['key'];
    $pass1 = $_POST['pass1'];
    $pass2 = $_POST['pass2'];
    
    if ($pass1 !== $pass2) wp_die('Passwords do not match.');
    
    $user = check_password_reset_key($key, $login);
    if (is_wp_error($user)) wp_die('Expired link.');
    
    reset_password($user, $pass1);
    
    wp_redirect(home_url('/login/?password_reset=1'));
    exit;
});
