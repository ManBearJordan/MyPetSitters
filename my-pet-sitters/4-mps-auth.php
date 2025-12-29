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




// ===========================================================================
// VIRTUAL AUTH PAGES (Join, Login, etc.)
// ===========================================================================


// FIX V231: Handle Registration on INIT (before headers)
add_action('init', 'antigravity_v200_handle_registration_post');

function antigravity_v200_handle_registration_post() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['mps_reg_nonce'])) {
        // Ensure virtual pages still load
        add_rewrite_rule('^join/?$', 'index.php?mps_auth_page=join', 'top');
        add_rewrite_rule('^login/?$', 'index.php?mps_auth_page=login', 'top');
        return;
    }

    // Honey Pot
    if (!empty($_POST['website'])) return; 

    if (!wp_verify_nonce($_POST['mps_reg_nonce'], 'mps_register')) {
        wp_die('Security check failed. Please go back and try again.');
    }

    $active_tab = $_POST['mps_tab'] ?? 'owner';
    $name  = sanitize_text_field($_POST['name'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $pass2 = $_POST['password_confirm'] ?? '';
    
    $error_code = '';

    // Validation
    if (!$name) $error_code = 'missing_name';
    elseif (!is_email($email)) $error_code = 'invalid_email';
    elseif (email_exists($email)) $error_code = 'email_exists';
    elseif (strlen($pass) < 8) $error_code = 'password_short';
    elseif ($pass !== $pass2) $error_code = 'password_mismatch';

    // If Error, Redirect Back
    if ($error_code) {
        $referer = remove_query_arg(['registered', 'mps_error'], wp_get_referer());
        wp_safe_redirect(add_query_arg(['mps_error' => $error_code, 'mps_tab' => $active_tab], $referer));
        exit;
    }

    // Create User
    $user_id = wp_create_user($email, $pass, $email);
    
    if (is_wp_error($user_id)) {
        $referer = remove_query_arg(['registered', 'mps_error'], wp_get_referer());
        wp_safe_redirect(add_query_arg(['mps_error' => 'generic', 'mps_tab' => $active_tab], $referer));
        exit;
    }

    // Success Update
    wp_update_user([
        'ID'           => $user_id,
        'display_name' => $name,
        'nickname'     => $name,
        'first_name'   => explode(' ', $name)[0],
    ]);

    $is_sitter = ($active_tab === 'sitter');
    $user = new WP_User($user_id);
    $user->set_role($is_sitter ? 'pro' : 'subscriber');

    // Init Login & Roles
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id, true);
    
    // Notify Admin
    $admin_email = defined('MPS_ADMIN_EMAIL') ? MPS_ADMIN_EMAIL : get_option('admin_email');
    $role_label = $is_sitter ? 'Sitter' : 'Owner';
    wp_mail($admin_email, "New $role_label Registration: $name", 
        "Name: $name\nEmail: $email\nRole: $role_label");

    // Redirect Success
    $redirect_path = $is_sitter ? '/edit-profile/' : '/account/';
    wp_safe_redirect(home_url($redirect_path) . '?registered=1');
    exit;
}

add_filter('query_vars', function($vars) {
    $vars[] = 'mps_auth_page';
    return $vars;
});

add_action('template_redirect', function() {
    $page = get_query_var('mps_auth_page');
    if (!$page) return;
    
    // FIX V221: Prevent caching of virtual pages (fixes Stale Nonce / Security Check Failed)
    nocache_headers();
    
    // Prevent "Hello World" / 404
    global $wp_query;
    $wp_query->is_home = false;
    $wp_query->is_404 = false;
    $wp_query->is_page = true;
    
    // Create Dummy Post
    $dummy = new stdClass();
    $dummy->ID = -99;
    $dummy->post_author = 1;
    $dummy->post_date = current_time('mysql');
    $dummy->post_date_gmt = current_time('mysql', 1);
    $dummy->post_status = 'publish';
    $dummy->comment_status = 'closed';
    $dummy->ping_status = 'closed';
    $dummy->post_type = 'page';
    $dummy->filter = 'raw';
    
    if ($page === 'join') {
        $dummy->post_title = 'Join My Pet Sitters';
        $dummy->post_content = '[mps_sitter_submit]'; // Use our new wrapper
        $dummy->post_name = 'join';
    } elseif ($page === 'login') {
        $dummy->post_title = 'Log In';
        $dummy->post_content = '[mps_login]';
        $dummy->post_name = 'login';
    } else {
        return; 
    } 

    
    // SIMPLIFIED FIX: Bypass theme complications for now to PROVE it works.
    get_header(); // Validate if header causes it? 
    echo '<div id="primary" class="content-area"><main id="main" class="site-main" role="main">';
    echo do_shortcode($dummy->post_content);
    echo '</main></div>';
    get_footer();
    exit;
});

// Login Shortcode
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

// Register BOTH names (V120 Fix)
add_shortcode('mps_register', 'antigravity_v200_render_register');
add_shortcode('mps_registration', 'antigravity_v200_render_register');


// Converted to named function (V120 Fix)
function antigravity_v200_render_register($atts) {
    $a = shortcode_atts([
        'role'        => '',       
        'redirect'    => '/account/',
        'default_tab' => 'owner',  
    ], $atts, 'mps_register');
    
    if ($a['default_tab'] === 'sitter') {
        $a['role'] = '';
    }
    
    if (is_user_logged_in()) {
        $dest = esc_url(home_url($a['redirect']));
        return '<div class="mps-notice mps-notice-info" style="padding:16px;background:#e8f4f8;border-radius:8px;margin-bottom:16px;">
            <p>You\'re already logged in. <a href="' . $dest . '">Go to your account</a>.</p>
        </div>';
    }
    
    // V231: Handle Errors via Query Args (since POST is handled on init)
    $errors = [];
    if (isset($_GET['mps_error'])) {
        switch ($_GET['mps_error']) {
            case 'missing_name': $errors[] = 'Please enter your full name.'; break;
            case 'invalid_email': $errors[] = 'Please enter a valid email address.'; break;
            case 'email_exists': $errors[] = 'Account exists. <a href="/login/">Log in instead</a>'; break;
            case 'password_short': $errors[] = 'Password must be at least 8 characters.'; break;
            case 'password_mismatch': $errors[] = 'Passwords do not match.'; break;
            case 'generic': $errors[] = 'Could not create account. Please try again.'; break;
        }
    }

    $active_tab = $_GET['mps_tab'] ?? $a['default_tab'];
    $show_owners = ($a['role'] === '' || $a['role'] === 'subscriber');
    $show_sitters = ($a['role'] === '' || $a['role'] === 'pro');
    $show_tabs = $show_owners && $show_sitters;
    
    ob_start(); // Start Output Buffering
    
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



// Converted to named function (V120 Fix)



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


/**
 * [mps_sitter_submit] Shortcode
 * Restores the "List Your Services" flow
 */
add_shortcode('mps_sitter_submit', function($atts) {
    // 1. Logged In? Redirect to Edit Profile
    if (is_user_logged_in()) {
        // Optional: Check if they are already a sitter?
        // For now, just send them to the profile editor.
        // If they don't have a profile, edit-profile should handle creation.
        echo '<script>window.location.href="/edit-profile/";</script>';
        return '<p>Redirecting to your profile...</p>';
    }

    // 2. Guest? Show Sitter Registration Form
    // We reuse the existing register function but force the "Sitter" role/tab
    ob_start();
    ?>
    <section class="mps-wrap" style="max-width:500px;margin:0 auto;padding:40px 0;">
        <div style="text-align:center;margin-bottom:30px;">
            <h1 style="font-size:2rem;margin-bottom:12px;">Create your Sitter Profile</h1>
            <p style="color:#666;">Join the community, set your rates, and start earning.</p>
        </div>
        
        <?php
        // Force 'pro' role (Sitter) logic
        echo antigravity_v200_render_register([
            'role' => '', // Show BOTH tabs per user request
            'default_tab' => 'sitter', // But default to Sitter
            'redirect' => '/edit-profile/'
        ]); 
        ?>
    </section>
    <?php
    return ob_get_clean();
});