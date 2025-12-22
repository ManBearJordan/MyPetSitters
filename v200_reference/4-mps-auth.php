<?php


if (!defined('ABSPATH')) exit;


function antigravity_v200_can_attempt_login($email) {
    $key = 'mps_login_' . md5(strtolower($email));
    $attempts = (int) get_transient($key);
    return $attempts < 5; // Max 5 attempts per 15 minutes
}

/**
 * Record a login attempt (success resets counter, failure increments)
 */
function antigravity_v200_record_login_attempt($email, $success = false) {
    $key = 'mps_login_' . md5(strtolower($email));
    if ($success) {
        delete_transient($key);
    } else {
        $attempts = (int) get_transient($key);
        set_transient($key, $attempts + 1, 15 * MINUTE_IN_SECONDS);
    }
}



add_shortcode('mps_login', function($atts) {
    $a = shortcode_atts([
        'redirect' => '/account/',
    ], $atts, 'mps_login');
    
    // Already logged in
    if (is_user_logged_in()) {
        $dest = esc_url(home_url($a['redirect']));
        return '<div class="mps-notice mps-notice-info" style="padding:16px;background:#e8f4f8;border-radius:8px;margin-bottom:16px;">
            <p>You\'re already logged in. <a href="' . $dest . '">Go to your account</a>.</p>
        </div>';
    }
    
    $errors = [];
    $email_value = '';
    
    // Process form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mps_login_nonce'])) {
        if (!wp_verify_nonce($_POST['mps_login_nonce'], 'mps_login')) {
            $errors[] = 'Security check failed. Please reload the page and try again.';
        } else {
            $email_raw = trim($_POST['email'] ?? '');
            $pass_raw = $_POST['password'] ?? '';
            $remember = !empty($_POST['remember']);
            
            $email_value = $email_raw;
            
            if ($email_raw === '' || $pass_raw === '') {
                $errors[] = 'Please enter both your email and password.';
            } elseif (!antigravity_v200_can_attempt_login($email_raw)) {
                $errors[] = 'Too many login attempts. Please try again in 15 minutes.';
            } else {
                // Try to find user by email
                $user = null;
                if (strpos($email_raw, '@') !== false) {
                    $user = get_user_by('email', $email_raw);
                }
                if (!$user) {
                    $user = get_user_by('login', $email_raw);
                }
                
                $creds = [
                    'user_login'    => $user ? $user->user_login : $email_raw,
                    'user_password' => $pass_raw,
                    'remember'      => $remember,
                ];
                
                $result = wp_signon($creds, is_ssl());
                
                if (is_wp_error($result)) {
                    antigravity_v200_record_login_attempt($email_raw, false);
                    $errors[] = 'Invalid email or password. Please try again.';
                } else {
                    antigravity_v200_record_login_attempt($email_raw, true);
                    
                    // Link any unlinked sitter profiles to this user
                    antigravity_v200_link_sitter_to_user($result);
                    
                    wp_safe_redirect(home_url($a['redirect']));
                    exit;
                }
            }
        }
    }
    
    ob_start();
    
    // Error display
    if ($errors) {
        echo '<div class="mps-notice mps-notice-error" style="border:1px solid #f3d7d7;background:#fff2f2;padding:12px;border-radius:8px;margin-bottom:16px;">';
        echo '<strong>There was a problem:</strong><ul style="margin:.5em 0 0 1.25em">';
        foreach ($errors as $e) echo '<li>' . esc_html($e) . '</li>';
        echo '</ul></div>';
    }
    ?>
    <form method="post" class="mps-login-form" style="max-width:420px;">
        <?php wp_nonce_field('mps_login', 'mps_login_nonce'); ?>
        
        <p>
            <label for="mps_email"><strong>Email or Username</strong></label><br>
            <input type="text" id="mps_email" name="email" value="<?= esc_attr($email_value) ?>" required 
                   style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;font-size:16px;">
        </p>
        
        <p>
            <label for="mps_password"><strong>Password</strong></label><br>
            <input type="password" id="mps_password" name="password" required 
                   style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;font-size:16px;">
        </p>
        
        <p style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
            <label style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;">
                <input type="checkbox" name="remember" value="1"> Remember me
            </label>
            <a href="/lost-password/">Forgot password?</a>
        </p>
        
        <p>
            <button type="submit" class="wp-block-button__link" style="width:100%;padding:12px;font-size:16px;cursor:pointer;">
                Log In
            </button>
        </p>
        
        <p style="text-align:center;opacity:.8;">
            Don't have an account? <a href="/join/">Sign up as a sitter</a>
        </p>
    </form>
    <?php
    return ob_get_clean();
});



// Converted to named function (V120 Fix)
function antigravity_v200_render_register($atts) {
    $a = shortcode_atts([
        'role'     => '',  // Empty = show both tabs, 'pro' = sitters only, 'subscriber' = owners only
        'redirect' => '/account/',
    ], $atts, 'mps_register');
    
    // Already logged in
    if (is_user_logged_in()) {
        $dest = esc_url(home_url($a['redirect']));
        return '<div class="mps-notice mps-notice-info" style="padding:16px;background:#e8f4f8;border-radius:8px;">
            <p>You\'re already logged in. <a href="' . $dest . '">Go to your account</a>.</p>
        </div>';
    }
    
    $errors = [];
    $active_tab = $_POST['mps_tab'] ?? 'owner';
    $values = ['name' => '', 'email' => ''];
    
    // Process form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mps_reg_nonce'])) {
        // Honeypot check
        if (!empty($_POST['website'])) {
            return ''; // Silent fail for bots
        }
        
        if (!wp_verify_nonce($_POST['mps_reg_nonce'], 'mps_register')) {
            $errors[] = 'Security check failed. Please reload and try again.';
        } else {
            $values['name']  = sanitize_text_field($_POST['name'] ?? '');
            $values['email'] = sanitize_email($_POST['email'] ?? '');
            $pass  = $_POST['password'] ?? '';
            $pass2 = $_POST['password_confirm'] ?? '';
            $is_sitter = ($active_tab === 'sitter');
            
            // Validation
            if (!$values['name']) {
                $errors[] = 'Please enter your full name.';
            }
            if (!is_email($values['email'])) {
                $errors[] = 'Please enter a valid email address.';
            }
            if (email_exists($values['email'])) {
                $errors[] = 'An account with this email already exists. <a href="/login/">Log in instead</a>';
            }
            if (strlen($pass) < 8) {
                $errors[] = 'Password must be at least 8 characters.';
            }
            if ($pass !== $pass2) {
                $errors[] = 'Passwords don\'t match.';
            }
            
            // Create user
            if (!$errors) {
                $user_id = wp_create_user($values['email'], $pass, $values['email']);
                
                if (is_wp_error($user_id)) {
                    $errors[] = 'Could not create account. Please try again.';
                } else {
                    // Update user details
                    wp_update_user([
                        'ID'           => $user_id,
                        'display_name' => $values['name'],
                        'nickname'     => $values['name'],
                        'first_name'   => explode(' ', $values['name'])[0],
                    ]);
                    
                    // Set role (Slug is 'pro', Display Name is 'Sitter')
                    $user = new WP_User($user_id);
                    $user->set_role($is_sitter ? 'pro' : 'subscriber');
                    
                    // Log in the user
                    wp_set_current_user($user_id);
                    wp_set_auth_cookie($user_id, true);
                    
                    // Redirect sitters to profile creation, owners to account
                    $redirect_to = $is_sitter ? '/edit-profile/' : '/account/';

                    // Notify Admin
                    $admin_email = defined('MPS_ADMIN_EMAIL') ? MPS_ADMIN_EMAIL : 'enquiries@mypetsitters.com.au';
                    $role_label = $is_sitter ? 'Sitter' : 'Owner';
                    wp_mail($admin_email, "New $role_label Registration: " . $values['name'], 
                        "A new user has registered:\n\nName: {$values['name']}\nEmail: {$values['email']}\nRole: $role_label\n\nManage users: " . admin_url('users.php'));

                    wp_safe_redirect(home_url($redirect_to) . '?registered=1');
                    exit;
                }
            }
        }
    }
    
    $show_owners = ($a['role'] === '' || $a['role'] === 'subscriber');
    $show_sitters = ($a['role'] === '' || $a['role'] === 'pro');
    $show_tabs = $show_owners && $show_sitters;
    
    ob_start();
    
    // Error display
    if ($errors) {
        echo '<div class="mps-notice mps-notice-error" style="border:1px solid #f3d7d7;background:#fff2f2;padding:12px;border-radius:8px;margin-bottom:16px;">';
        echo '<strong>Please fix the following:</strong><ul style="margin:.5em 0 0 1.25em">';
        foreach ($errors as $e) echo '<li>' . wp_kses_post($e) . '</li>';
        echo '</ul></div>';
    }
    
    if ($show_tabs) {
    ?>
    <style>
    .mps-reg-tabs { display:flex; gap:0; margin-bottom:0; }
    .mps-reg-tabs button { flex:1; padding:14px 20px; border:1px solid #ddd; background:#f8f8f8; cursor:pointer; font-size:16px; font-weight:600; border-bottom:none; border-radius:8px 8px 0 0; }
    .mps-reg-tabs button.active { background:#fff; border-bottom:1px solid #fff; margin-bottom:-1px; }
    .mps-reg-panel { display:none; border:1px solid #ddd; padding:24px; border-radius:0 0 8px 8px; background:#fff; }
    .mps-reg-panel.active { display:block; }
    </style>
    <div class="mps-reg-tabs">
        <button type="button" class="<?= $active_tab === 'owner' ? 'active' : '' ?>" onclick="mpsShowTab('owner')">Owners</button>
        <button type="button" class="<?= $active_tab === 'sitter' ? 'active' : '' ?>" onclick="mpsShowTab('sitter')">Sitters</button>
    </div>
    <script>
    function mpsShowTab(t) {
        document.querySelectorAll('.mps-reg-tabs button').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.mps-reg-panel').forEach(p => p.classList.remove('active'));
        document.querySelector('.mps-reg-tabs button:' + (t==='owner'?'first':'last') + '-child').classList.add('active');
        document.getElementById('mps-panel-'+t).classList.add('active');
    }
    </script>
    <?php } ?>
    
    <?php if ($show_owners): ?>
    <div id="mps-panel-owner" class="mps-reg-panel <?= (!$show_tabs || $active_tab === 'owner') ? 'active' : '' ?>">
        <h2 style="margin-top:0;">Owners</h2>
        <form method="post" class="mps-register-form">
            <?php wp_nonce_field('mps_register', 'mps_reg_nonce'); ?>
            <input type="hidden" name="mps_tab" value="owner">
            <div style="display:none;"><label>Website <input type="text" name="website" value=""></label></div>
            
            <p>
                <label><strong>Full name</strong></label><br>
                <input type="text" name="name" value="<?= esc_attr($values['name']) ?>" required 
                       style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;font-size:16px;">
            </p>
            <p>
                <label><strong>Email</strong></label><br>
                <input type="email" name="email" value="<?= esc_attr($values['email']) ?>" required 
                       style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;font-size:16px;">
            </p>
            <p>
                <label><strong>Password</strong></label><br>
                <input type="password" name="password" required minlength="8"
                       style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;font-size:16px;">
            </p>
            <p>
                <label><strong>Confirm password</strong></label><br>
                <input type="password" name="password_confirm" required 
                       style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;font-size:16px;">
            </p>
            <p>
                <button type="submit" class="wp-block-button__link" style="width:100%;padding:14px;font-size:16px;cursor:pointer;">
                    Create account
                </button>
            </p>
            <p style="font-size:.85rem;opacity:.8;text-align:center;">
                By registering you agree to our <a href="/terms/">Terms</a> and <a href="/privacy-policy/">Privacy Policy</a>.
            </p>
        </form>
    </div>
    <?php endif; ?>
    
    <?php if ($show_sitters): ?>
    <div id="mps-panel-sitter" class="mps-reg-panel <?= (!$show_tabs && $a['role'] === 'pro') || $active_tab === 'sitter' ? 'active' : '' ?>">
        <h2 style="margin-top:0;">Sitters</h2>
        <form method="post" class="mps-register-form">
            <?php wp_nonce_field('mps_register', 'mps_reg_nonce'); ?>
            <input type="hidden" name="mps_tab" value="sitter">
            <div style="display:none;"><label>Website <input type="text" name="website" value=""></label></div>
            
            <p>
                <label><strong>Full name</strong></label><br>
                <input type="text" name="name" value="<?= esc_attr($values['name']) ?>" required 
                       style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;font-size:16px;">
            </p>
            <p>
                <label><strong>Email</strong></label><br>
                <input type="email" name="email" value="<?= esc_attr($values['email']) ?>" required 
                       style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;font-size:16px;">
            </p>
            <p>
                <label><strong>Password</strong></label><br>
                <input type="password" name="password" required minlength="8"
                       style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;font-size:16px;">
            </p>
            <p>
                <label><strong>Confirm password</strong></label><br>
                <input type="password" name="password_confirm" required 
                       style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;font-size:16px;">
            </p>
            <p>
                <button type="submit" class="wp-block-button__link" style="width:100%;padding:14px;font-size:16px;cursor:pointer;">
                    Create account
                </button>
            </p>
            <p style="font-size:.85rem;opacity:.8;text-align:center;">
                By registering you agree to our <a href="/terms/">Terms</a> and <a href="/privacy-policy/">Privacy Policy</a>.
            </p>
        </form>
    </div>
    <?php endif; ?>
    
    <p style="text-align:center;opacity:.8;margin-top:16px;font-size: 0.95rem;">
        Already have an account? <a href="/login/" style="font-weight: 600; color: var(--mps-teal, #0d7377);">Log in</a>
    </p>
    <?php
    return ob_get_clean();
}
// Register BOTH names (V120 Fix)
add_shortcode('mps_register', 'antigravity_v200_render_register');
add_shortcode('mps_registration', 'antigravity_v200_render_register');


add_shortcode('mps_logout', 'antigravity_v200_render_logout');
function antigravity_v200_render_logout($atts) {
    $a = shortcode_atts([
        'redirect' => '/login/',
        'text'     => 'Log out',
    ], $atts, 'mps_logout');
    
    if (!is_user_logged_in()) {
        return '';
    }
    
    $url = wp_logout_url(home_url($a['redirect']));
    return '<a href="' . esc_url($url) . '" class="mps-logout-link">' . esc_html($a['text']) . '</a>';
}



/**
 * Link sitter profiles to user by matching email
 */
function antigravity_v200_link_sitter_to_user($user) {
    $user_obj = is_numeric($user) ? get_userdata($user) : $user;
    if (!$user_obj || !$user_obj->user_email) return;
    
    $email = $user_obj->user_email;
    
    $listings = get_posts([
        'post_type'   => 'sitter',
        'post_status' => ['publish', 'pending', 'draft'],
        'meta_key'    => 'mps_email',
        'meta_value'  => $email,
        'numberposts' => -1,
        'fields'      => 'ids',
    ]);
    
    foreach ($listings as $id) {
        $current_author = get_post_field('post_author', $id);
        if ($current_author != $user_obj->ID) {
            wp_update_post([
                'ID'          => $id,
                'post_author' => $user_obj->ID,
            ]);
        }
    }
}

// Hook on registration
add_action('user_register', 'antigravity_v200_link_sitter_to_user', 10, 1);

// Hook on login (catches users registered before this code existed)
add_action('wp_login', function($user_login, $user) {
    antigravity_v200_link_sitter_to_user($user);
}, 10, 2);