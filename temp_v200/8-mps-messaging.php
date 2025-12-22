<?php
/**
 * MPS MESSAGING SYSTEM
 * 
 * Implements internal messaging between Owners and Sitters.
 * - CPT: 'mps_message' (private)
 * - Meta: sender_id, recipient_id, read_status
 */

if (!defined('ABSPATH')) exit;

// 1. REGISTER CPT
add_action('init', function() {
    register_post_type('mps_message', [
        'public' => false,
        'show_ui' => true, // Visible in admin for debugging
        'label' => 'Messages',
        'supports' => ['title', 'editor', 'author', 'custom-fields'],
        'has_archive' => false
    ]);
});

// HELPER: Send Email Notification
function antigravity_v200_notify_message($msg_id, $sender_id, $recipient_id, $content) {
    $recipient = get_userdata($recipient_id);
    $sender = get_userdata($sender_id);
    
    if ($recipient && $sender) {
        $to = $recipient->user_email;
        $subject = 'New message from ' . $sender->display_name . ' on My Pet Sitters';
        $link = home_url('/messages/?convo=' . $sender_id); // Link directly to conversation
        
        $message = "Hi " . $recipient->display_name . ",\n\n";
        $message .= "You have received a new message from " . $sender->display_name . ":\n\n";
        $message .= "\"" . wp_trim_words(strip_tags($content), 20) . "...\"\n\n";
        $message .= "Click here to view and reply:\n" . $link . "\n\n";
        $message .= "Thanks,\nMy Pet Sitters Team";
        
        wp_mail($to, $subject, $message);
    }
}

// 2. SEND MESSAGE FUNCTION
function antigravity_v200_send_message($sender_id, $recipient_id, $content, $subject = '') {
    if (!$sender_id || !$recipient_id || empty($content)) return new WP_Error('missing_data', 'Missing required fields');
    
    // Conversation ID (always smaller_larger)
    $ids = [$sender_id, $recipient_id];
    sort($ids);
    $conv_id = implode('_', $ids);
    
    $post_data = [
        'post_type' => 'mps_message',
        'post_status' => 'publish',
        'post_author' => $sender_id,
        'post_content' => wp_kses_post($content),
        'post_title' => $subject ? sanitize_text_field($subject) : 'Message ' . date('Y-m-d H:i')
    ];
    
    $msg_id = wp_insert_post($post_data);
    
    if (is_wp_error($msg_id)) return $msg_id;
    
    update_post_meta($msg_id, 'mps_recipient_id', $recipient_id);
    update_post_meta($msg_id, 'mps_read_status', 0); // 0 = unread
    update_post_meta($msg_id, 'mps_conversation_id', $conv_id);
    
    // Send Email Notification
    antigravity_v200_notify_message($msg_id, $sender_id, $recipient_id, $content);
    
    return $msg_id;
}

// 3. FORM HANDLER
add_action('admin_post_mps_send_msg', 'antigravity_v200_handle_msg_send');
function antigravity_v200_handle_msg_send() {
    if (!is_user_logged_in()) wp_safe_redirect(home_url('/login/'));
    
    if (!wp_verify_nonce($_POST['_wpnonce'], 'mps_send_msg')) {
        wp_die('Security check failed');
    }
    
    $sender_id = get_current_user_id();
    $recipient_id = absint($_POST['recipient_id']);
    $content = trim($_POST['message_content']);
    
    if ($recipient_id && $content) {
        antigravity_v200_send_message($sender_id, $recipient_id, $content);
        // Redirect back to conversation
        wp_redirect(home_url('/messages/?convo=' . $recipient_id));
        exit;
    }
    
    // Error case
    wp_redirect(home_url('/messages/?error=1'));
    exit;
}

// 4. SHORTCODE: [mps_inbox]
add_shortcode('mps_inbox', 'antigravity_v200_inbox_shortcode');
function antigravity_v200_inbox_shortcode($atts) {
    if (!is_user_logged_in()) return '<p>Please <a href="/login/">log in</a> to view messages.</p>';
    
    $current_user_id = get_current_user_id();
    $active_convo_user = isset($_GET['convo']) ? absint($_GET['convo']) : 0;
    
    ob_start();
    ?>
    <div class="mps-messaging-container" style="display:grid;grid-template-columns:300px 1fr;gap:20px;min-height:500px;border:1px solid #ddd;border-radius:12px;overflow:hidden;background:#fff;">
        
        <!-- SIDEBAR: CONVERSATION LIST -->
        <div class="mps-msg-sidebar" style="background:#f8f9fa;border-right:1px solid #ddd;overflow-y:auto;">
            <div style="padding:16px;border-bottom:1px solid #ddd;font-weight:bold;">Messages</div>
            <?php
            // Get all messages where user is sender OR recipient
            // We need a custom SQL query for performance to group by conversation
            global $wpdb;
            $sql = "
                SELECT 
                    pm.meta_value as conv_id, 
                    MAX(p.post_date) as last_msg_date
                FROM {$wpdb->posts} p
                JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'mps_conversation_id'
                WHERE 
                    (p.post_author = %d OR p.ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'mps_recipient_id' AND meta_value = %d))
                    AND p.post_type = 'mps_message'
                    AND p.post_status = 'publish'
                GROUP BY pm.meta_value
                ORDER BY last_msg_date DESC
            ";
            
            $results = $wpdb->get_results($wpdb->prepare($sql, $current_user_id, $current_user_id));
            
            if (!$results) {
                echo '<div style="padding:16px;color:#666;">No messages yet.</div>';
            } else {
                echo '<ul style="list-style:none;margin:0;padding:0;">';
                foreach ($results as $row) {
                    // Determine "Other User" ID
                    $ids = explode('_', $row->conv_id);
                    $other_id = ($ids[0] == $current_user_id) ? $ids[1] : $ids[0];
                    $other_user = get_userdata($other_id);
                    if (!$other_user) continue;
                    
                    $is_active = ($other_id == $active_convo_user);
                    $bg = $is_active ? '#e3f2fd' : 'transparent';
                    
                    echo '<li style="border-bottom:1px solid #eee;">';
                    echo '<a href="?convo=' . $other_id . '" style="display:block;padding:12px 16px;text-decoration:none;color:inherit;background:' . $bg . ';">';
                    echo '<strong>' . esc_html($other_user->display_name) . '</strong><br>';
                    echo '<span style="font-size:12px;color:#888;">' . date('M j', strtotime($row->last_msg_date)) . '</span>';
                    echo '</a></li>';
                }
                echo '</ul>';
            }
            ?>
        </div>
        
        <!-- MAIN AREA: CHAT -->
        <div class="mps-msg-main" style="display:flex;flex-direction:column;height:500px;">
            <?php if ($active_convo_user): 
                $other_user = get_userdata($active_convo_user);
            ?>
                <!-- HEADER -->
                <div style="padding:16px;border-bottom:1px solid #ddd;background:#fff;display:flex;justify-content:space-between;align-items:center;">
                    <?php
                    // Determine Profile Link
                    $profile_url = '';
                    $target_is_sitter = in_array('sitter', (array)$other_user->roles) || in_array('pro', (array)$other_user->roles);
                    
                    if ($target_is_sitter) {
                        $sitter_post = antigravity_v200_get_sitter_post($active_convo_user);
                        if ($sitter_post) $profile_url = get_permalink($sitter_post->ID);
                    } else {
                        // Is Owner -> Link to our new owner profile view
                        $profile_url = home_url('/?mps_owner_profile=' . $active_convo_user);
                    }
                    ?>
                    <div>
                        <strong>Conversation with <?= esc_html($other_user->display_name) ?></strong>
                        <?php if ($profile_url): ?>
                            <a href="<?= esc_url($profile_url) ?>" target="_blank" style="font-size:0.85em;margin-left:8px;color:#2e7d32;text-decoration:none;">View Profile &rarr;</a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- MESSAGES AREA -->
                <div id="mps-chat-history" style="flex:1;overflow-y:auto;padding:20px;background:#fff;">
                    <?php
                    // Fetch messages between these two
                    $ids = [$current_user_id, $active_convo_user];
                    sort($ids);
                    $conv_id = implode('_', $ids);
                    
                    $msgs = get_posts([
                        'post_type' => 'mps_message',
                        'meta_key' => 'mps_conversation_id',
                        'meta_value' => $conv_id,
                        'posts_per_page' => 50,
                        'orderby' => 'date',
                        'order' => 'ASC'
                    ]);
                    
                    foreach ($msgs as $msg) {
                        $is_me = ($msg->post_author == $current_user_id);
                        $align = $is_me ? 'flex-end' : 'flex-start';
                        $bubble_color = $is_me ? '#007bff' : '#f1f0f0';
                        $text_color = $is_me ? '#fff' : '#333';
                        
                        echo '<div style="display:flex;justify-content:' . $align . ';margin-bottom:10px;">';
                        echo '<div style="max-width:70%;padding:10px 14px;border-radius:18px;background:' . $bubble_color . ';color:' . $text_color . ';font-size:14px;line-height:1.4;">';
                        echo nl2br(esc_html($msg->post_content));
                        echo '</div></div>';
                        
                        // Mark as read if I am the recipient
                        if (!$is_me) {
                            update_post_meta($msg->ID, 'mps_read_status', 1);
                        }
                    }
                    ?>
                </div>
                
                <!-- COMPOSER -->
                <div style="padding:16px;background:#f8f9fa;border-top:1px solid #ddd;">
                    <form method="post" action="<?= esc_url(admin_url('admin-post.php')) ?>">
                        <input type="hidden" name="action" value="mps_send_msg">
                        <input type="hidden" name="recipient_id" value="<?= esc_attr($active_convo_user) ?>">
                        <?php wp_nonce_field('mps_send_msg'); ?>
                        
                        <div style="display:flex;gap:10px;">
                            <input type="text" name="message_content" required placeholder="Type a message..." style="flex:1;padding:12px;border:1px solid #ddd;border-radius:24px;outline:none;" autocomplete="off">
                            <button type="submit" style="padding:0 20px;border-radius:24px;background:#007bff;color:#fff;border:none;cursor:pointer;font-weight:bold;">Send</button>
                        </div>
                    </form>
                </div>
                
                <script>
                    // Scroll to bottom
                    var chat = document.getElementById('mps-chat-history');
                    if(chat) chat.scrollTop = chat.scrollHeight;
                </script>
                
            <?php else: ?>
                <div style="flex:1;display:flex;align-items:center;justify-content:center;color:#888;">
                    <p>Select a conversation to start chatting</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <style>
    @media (max-width: 600px) {
        .mps-messaging-container { grid-template-columns: 1fr !important; }
        .mps-msg-sidebar { display: <?= $active_convo_user ? 'none' : 'block' ?>; }
        .mps-msg-main { display: <?= $active_convo_user ? 'flex' : 'none' ?>; }
    }
    </style>
    <?php
    return ob_get_clean();
}

// 5. SHORTCODE: [mps_contact_button]
add_shortcode('mps_contact_button', 'antigravity_v200_contact_button_shortcode');
function antigravity_v200_contact_button_shortcode($atts) {
    if (!is_user_logged_in()) {
        return '<a href="/login/" class="wp-block-button__link">Log in to Contact</a>';
    }
    
    $atts = shortcode_atts(['sitter_id' => 0], $atts);
    if (!$atts['sitter_id']) return '';
    
    // Get sender/recipient
    $current_user_id = get_current_user_id();
    $sitter_id = antigravity_v200_get_sitter_user_id($atts['sitter_id']); // Helper needed to get user ID from post ID
    
    if (!$sitter_id || $sitter_id == $current_user_id) return ''; // Can't msg self
    
    // Link directly to new conversation
    return '<a href="/messages/?convo=' . $sitter_id . '" class="wp-block-button__link" style="border-radius:50px!important;">Message Sitter</a>';
}

// Helper to get User ID from Sitter Post ID
function antigravity_v200_get_sitter_user_id($post_id) {
    $post = get_post($post_id);
    return $post ? $post->post_author : 0;
}

// HELPER: Get Unread Count
function antigravity_v200_get_unread_count($user_id) {
    global $wpdb;
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(p.ID) FROM {$wpdb->posts} p
         JOIN {$wpdb->postmeta} pm_recipient ON p.ID = pm_recipient.post_id AND pm_recipient.meta_key = 'mps_recipient_id'
         JOIN {$wpdb->postmeta} pm_read_status ON p.ID = pm_read_status.post_id AND pm_read_status.meta_key = 'mps_read_status'
         WHERE pm_recipient.meta_value = %d AND pm_read_status.meta_value = 0
         AND p.post_type = 'mps_message' AND p.post_status = 'publish'",
        $user_id
    ));
    return (int) $count;
}

// 6. EMAIL SENDER CONFIGURATION
add_filter('wp_mail_from', 'antigravity_v200_mail_from_email');
function antigravity_v200_mail_from_email($original_email_address) {
    return 'enquiries@mypetsitters.com.au';
}

add_filter('wp_mail_from_name', function($original_email_from) {
    return 'My Pet Sitters';
});


