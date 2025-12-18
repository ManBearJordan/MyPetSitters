<?php
/**
 * MPS ADMIN DASHBOARD
 * 
 * Backend tools for Platform Management.
 * - Overview Stats
 * - Bookings List
 * - Messages Log
 */

if (!defined('ABSPATH')) exit;

// 1. ADD MENU ITEM
add_action('admin_menu', 'antigravity_v200_add_admin_menu');
function antigravity_v200_add_admin_menu() {
    add_menu_page(
        'My Pet Sitters', 'Pet Sitters', 'manage_options', 'mps-dashboard', 'antigravity_v200_render_admin_home', 'dashicons-pets', 6
    );
    add_submenu_page(
        'mps-dashboard', 'Bookings', 'Bookings', 'manage_options', 'mps-admin-bookings', 'antigravity_v200_render_admin_bookings'
    );
    add_submenu_page(
        'mps-dashboard', 'Messages', 'Messages', 'manage_options', 'mps-admin-messages', 'antigravity_v200_render_admin_messages'
    );
    add_submenu_page(
        'mps-dashboard', 'Add New User', 'Add User', 'manage_options', 'mps-admin-add-user', 'antigravity_v200_render_admin_add_user'
    );
    add_submenu_page(
        'mps-dashboard', 'Communication', 'Communication', 'manage_options', 'mps-admin-comm', 'antigravity_v200_render_admin_communication'
    );
}

// Handle Admin Actions
// V110 Restoration: Removed Self-Healing hooks.
/*
add_action('admin_init', function() { ... });
*/

// SEPARATE ACTION: Create User Logic (Moved outside admin_init closure if needed, but keeping inside for now as original design intended logic to be here, but wait... original file had MULTIPLE if blocks inside one admin_init closure.
// My previous edit might have unintentionally closed the closure early.
// To be safe, I will hook a SECOND admin_init for Create User, or merge properly.)

add_action('admin_init', 'antigravity_v200_handle_admin_actions');
function antigravity_v200_handle_admin_actions() {
    // CREATE USER BLOCK

    // CREATE USER
    if (isset($_POST['mps_admin_action']) && $_POST['mps_admin_action'] == 'create_user') {
        check_admin_referer('mps_admin_create_user');
        
        $email = sanitize_email($_POST['user_email']);
        $pass = $_POST['user_pass'];
        $role = $_POST['user_role'];
        $fname = sanitize_text_field($_POST['first_name']);
        $lname = sanitize_text_field($_POST['last_name']);
        
        if (email_exists($email)) {
            wp_die('Email already exists');
        }
        
        $user_id = wp_create_user($email, $pass, $email);
        if (is_wp_error($user_id)) wp_die($user_id->get_error_message());
        
        $user = new WP_User($user_id);
        $user->set_role($role);
        
        update_user_meta($user_id, 'first_name', $fname);
        update_user_meta($user_id, 'last_name', $lname);
        
        // If Sitter -> Create Profile
        if ($role == 'sitter') {
            $sitter_post = [
                'post_title' => "$fname $lname",
                'post_type' => 'sitter',
                'post_status' => 'publish',
                'post_author' => $user_id
            ];
            $pid = wp_insert_post($sitter_post);
            update_user_meta($user_id, 'mps_sitter_post_id', $pid);
        }
        
        wp_redirect(admin_url('admin.php?page=mps-admin-add-user&created=1'));
        exit;
    }
    
    // SEND MESSAGE
    if (isset($_POST['mps_admin_action']) && $_POST['mps_admin_action'] == 'send_message') {
        check_admin_referer('mps_admin_send_message');
        
        $recipient_email = sanitize_text_field($_POST['recipient_email']); // ID or Email
        $subject = sanitize_text_field($_POST['subject']);
        $message_content = sanitize_textarea_field($_POST['message']);
        
        $recipient = get_user_by('email', $recipient_email);
        // Fallback: try by login
        if (!$recipient) $recipient = get_user_by('login', $recipient_email);
        
        if (!$recipient) wp_die("User '$recipient_email' not found.");
        
        $sender_id = get_current_user_id();
        
        // Create Message Post
        $msg_id = wp_insert_post([
            'post_type' => 'mps_message',
            'post_title' => $subject, // Used as subject
            'post_content' => $message_content,
            'post_status' => 'publish',
            'post_author' => $sender_id
        ]);
        
        update_post_meta($msg_id, 'mps_recipient_id', $recipient->ID);
        update_post_meta($msg_id, 'mps_read_status', 0);
        
        // Trigger Email Notification (Reuse existing function if possible, or manual)
        if (function_exists('antigravity_v200_notify_message')) {
            antigravity_v200_notify_message($msg_id, $sender_id, $recipient->ID, $message_content);
        } else {
            // Manual fallback
            $link = home_url('/messages/');
            $body = "You have a new message from Administrator:\n\n";
            $body .= "\"$message_content\"\n\n";
            $body .= "View here: $link";
            wp_mail($recipient->user_email, "New Message: $subject", $body);
        }
        
        wp_redirect(admin_url('admin.php?page=mps-admin-comm&sent=1'));
        exit;
    }
});

// 1. HOME / OVERVIEW
function antigravity_v200_render_admin_home() {
    // Stats
    $sitter_count = count(get_posts(['post_type'=>'sitter','numberposts'=>-1]));
    $booking_count = count(get_posts(['post_type'=>'mps_booking','numberposts'=>-1]));
    $msg_count = count(get_posts(['post_type'=>'mps_message','numberposts'=>-1]));
    $pending = count(get_posts(['post_type'=>'mps_booking','post_status'=>'mps-pending','numberposts'=>-1]));
    
    ?>
    <div class="wrap">
        <h1>Pet Sitters Platform Overview</h1>
        <div style="display:flex;gap:20px;margin-top:20px;">
            <div style="background:#fff;padding:20px;border-left:4px solid #2e7d32;box-shadow:0 1px 3px rgba(0,0,0,0.1);flex:1;">
                <h3 style="margin:0;">Sitters</h3>
                <p style="font-size:32px;font-weight:bold;margin:10px 0;"><?= $sitter_count ?></p>
            </div>
            <div style="background:#fff;padding:20px;border-left:4px solid #1976d2;box-shadow:0 1px 3px rgba(0,0,0,0.1);flex:1;">
                <h3 style="margin:0;">Total Bookings</h3>
                <p style="font-size:32px;font-weight:bold;margin:10px 0;"><?= $booking_count ?></p>
            </div>
            <div style="background:#fff;padding:20px;border-left:4px solid #f57c00;box-shadow:0 1px 3px rgba(0,0,0,0.1);flex:1;">
                <h3 style="margin:0;">Pending Requests</h3>
                <p style="font-size:32px;font-weight:bold;margin:10px 0;"><?= $pending ?></p>
            </div>
            <div style="background:#fff;padding:20px;border-left:4px solid #7b1fa2;box-shadow:0 1px 3px rgba(0,0,0,0.1);flex:1;">
                <h3 style="margin:0;">Messages</h3>
                <p style="font-size:32px;font-weight:bold;margin:10px 0;"><?= $msg_count ?></p>
            </div>
        </div>
        
        <h2 style="margin-top:40px;">Recent Activity</h2>
        <!-- Simple recent list -->
        <?php
        $recent = new WP_Query([
            'post_type' => ['mps_booking', 'mps_message'],
            'posts_per_page' => 5,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        if ($recent->have_posts()) {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th>Type</th><th>Date</th><th>Subject/Status</th><th>Action</th></tr></thead><tbody>';
            while ($recent->have_posts()) {
                $recent->the_post();
                $type = (get_post_type() == 'mps_booking') ? 'Booking' : 'Message';
                // Status for booking, Subject for message
                $info = (get_post_type() == 'mps_booking') ? get_post_status_object(get_post_status())->label : get_the_title();
                $edit_link = get_edit_post_link();
                
                echo "<tr>
                    <td>$type</td>
                    <td>".get_the_date()." ".get_the_time()."</td>
                    <td>$info</td>
                    <td><a href='$edit_link'>View</a></td>
                </tr>";
            }
            echo '</tbody></table>';
        }
        ?>
    </div>
    <?php
}

// 2. BOOKINGS TABLE
function antigravity_v200_render_admin_bookings() {
    ?>
    <div class="wrap">
        <h1>Bookings Management</h1>
        <?php
        $bookings = get_posts(['post_type'=>'mps_booking','posts_per_page'=>50]);
        
        echo '<table class="wp-list-table widefat fixed striped" style="margin-top:10px;">';
        echo '<thead><tr><th>ID</th><th>Owner</th><th>Sitter</th><th>Dates</th><th>Pets</th><th>Status</th><th>Actions</th></tr></thead>';
        echo '<tbody>';
        
        foreach ($bookings as $b) {
            $owner_id = get_post_meta($b->ID, 'mps_owner_id', true);
            $sitter_id = get_post_meta($b->ID, 'mps_sitter_id', true);
            $start = get_post_meta($b->ID, 'mps_start_date', true);
            $end = get_post_meta($b->ID, 'mps_end_date', true);
            $pets = get_post_meta($b->ID, 'mps_pets', true);
            $status = get_post_status_object($b->post_status)->label;
            
            $owner = get_userdata($owner_id);
            $sitter = get_userdata($sitter_id);
            
            $o_name = $owner ? $owner->display_name : "User $owner_id";
            $s_name = $sitter ? $sitter->display_name : "User $sitter_id";
            
            echo "<tr>
                <td>#{$b->ID}</td>
                <td><a href='".get_edit_user_link($owner_id)."'>$o_name</a></td>
                <td><a href='".get_edit_user_link($sitter_id)."'>$s_name</a></td>
                <td>$start to $end</td>
                <td>".esc_html($pets)."</td>
                <td><span class='mps-status-badge'>$status</span></td>
                <td>
                    <a href='".get_edit_post_link($b->ID)."' class='button button-small'>Edit</a>
                </td>
            </tr>";
        }
        echo '</tbody></table>';
        ?>
    </div>
    <?php
}

// 3. MESSAGES TABLE
function antigravity_v200_render_admin_messages() {
    ?>
    <div class="wrap">
        <h1>Messages Log</h1>
        <p>Monitor conversation threads for safety/moderation.</p>
        <?php
        $msgs = get_posts(['post_type'=>'mps_message','posts_per_page'=>50]);
        
        echo '<table class="wp-list-table widefat fixed striped" style="margin-top:10px;">';
        echo '<thead><tr><th>Date</th><th>From</th><th>To</th><th>Content Preview</th><th>Actions</th></tr></thead>';
        echo '<tbody>';
        
        foreach ($msgs as $m) {
            $sender = get_userdata($m->post_author);
            $recipient_id = get_post_meta($m->ID, 'mps_recipient_id', true);
            $recipient = get_userdata($recipient_id);
            
            $from = $sender ? $sender->display_name : 'Unknown';
            $to = $recipient ? $recipient->display_name : 'Unknown';
            $preview = wp_trim_words($m->post_content, 10);
            
            echo "<tr>
                <td>".get_the_date('', $m->ID)." ".get_the_time('', $m->ID)."</td>
                <td>$from</td>
                <td>$to</td>
                <td>".esc_html($preview)."</td>
                <td>
                    <a href='".get_edit_post_link($m->ID)."' class='button button-small'>View Full</a>
                </td>
            </tr>";
        }
        echo '</tbody></table>';
        ?>
    </div>
    <?php
}

// 4. ADD USER
function antigravity_v200_render_admin_add_user() {
    if (isset($_GET['created'])) echo '<div class="updated"><p>User created successfully!</p></div>';
    ?>
    <div class="wrap">
        <h1>Add New User</h1>
        <form method="post" action="">
            <input type="hidden" name="mps_admin_action" value="create_user">
            <?php wp_nonce_field('mps_admin_create_user'); ?>
            
             <table class="form-table">
                <tr>
                    <th><label>Role</label></th>
                    <td>
                        <select name="user_role">
                            <option value="owner">Owner</option>
                            <option value="sitter">Sitter (Creates Profile)</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label>Email (Username)</label></th>
                    <td><input type="email" name="user_email" required class="regular-text"></td>
                </tr>
                <tr>
                    <th><label>First Name</label></th>
                    <td><input type="text" name="first_name" required class="regular-text"></td>
                </tr>
                <tr>
                    <th><label>Last Name</label></th>
                    <td><input type="text" name="last_name" required class="regular-text"></td>
                </tr>
                <tr>
                    <th><label>Password</label></th>
                    <td><input type="password" name="user_pass" required class="regular-text"></td>
                </tr>
            </table>
            <?php submit_button('Create User'); ?>
        </form>
    </div>
    <?php
}

// 5. COMMUNICATION
function antigravity_v200_render_admin_communication() {
    if (isset($_GET['sent'])) echo '<div class="updated"><p>Message sent successfully!</p></div>';
    ?>
    <div class="wrap">
        <h1>Send Message from Admin</h1>
        <form method="post" action="">
            <input type="hidden" name="mps_admin_action" value="send_message">
            <?php wp_nonce_field('mps_admin_send_message'); ?>
            
             <table class="form-table">
                <tr>
                    <th><label>To (Email Address)</label></th>
                    <td><input type="text" name="recipient_email" required class="regular-text" placeholder="user@example.com"></td>
                </tr>
                <tr>
                    <th><label>Subject</label></th>
                    <td><input type="text" name="subject" required class="regular-text"></td>
                </tr>
                <tr>
                    <th><label>Message</label></th>
                    <td><textarea name="message" rows="8" required class="large-text"></textarea></td>
                </tr>
            </table>
            <?php submit_button('Send Message'); ?>
        </form>
    </div>
    <?php
}


