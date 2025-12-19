<?php
/**
 * MPS BOOKING SYSTEM
 * 
 * Implements Booking Requests between Owners and Sitters.
 * - CPT: 'mps_booking' (private)
 * - Meta: sitter_id, owner_id, start_date, end_date, pets, message
 * - Status: pending, accepted, declined, cancelled
 */

if (!defined('ABSPATH')) exit;

// 1. REGISTER CPT and STATUSES
add_action('init', function() {
    register_post_type('mps_booking', [
        'public' => false,
        'show_ui' => true,
        'label' => 'Bookings',
        'supports' => ['title', 'author', 'custom-fields'],
        'has_archive' => false
    ]);
});

// Register Custom Statuses
add_action('init', function() {
    register_post_status('mps-pending', [
        'label' => 'Pending',
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
    ]);
    register_post_status('mps-accepted', [
        'label' => 'Accepted',
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
    ]);
    register_post_status('mps-declined', [
        'label' => 'Declined',
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
    ]);
});


// 2. CREATE BOOKING FUNCTION
function antigravity_v200_create_booking($owner_id, $sitter_id, $data) {
    if (!$owner_id || !$sitter_id) return new WP_Error('missing_user', 'User ID missing');
    
    $defaults = ['start_date' => '', 'end_date' => '', 'message' => '', 'pets' => ''];
    $data = wp_parse_args($data, $defaults);
    
    $title = sprintf("Booking: %s -> %s (%s)", 
        antigravity_v200_get_name($owner_id), 
        antigravity_v200_get_name($sitter_id),
        $data['start_date']
    );
    
    $post_id = wp_insert_post([
        'post_type' => 'mps_booking',
        'post_status' => 'mps-pending',
        'post_title' => $title,
        'post_author' => $owner_id
    ]);
    
    if (is_wp_error($post_id)) return $post_id;
    
    // Save Meta
    update_post_meta($post_id, 'mps_sitter_id', $sitter_id);
    update_post_meta($post_id, 'mps_owner_id', $owner_id);
    update_post_meta($post_id, 'mps_start_date', $data['start_date']);
    update_post_meta($post_id, 'mps_end_date', $data['end_date']);
    update_post_meta($post_id, 'mps_pets', sanitize_text_field($data['pets']));
    update_post_meta($post_id, 'mps_message', wp_kses_post($data['message']));
    
    // Notify Sitter
    antigravity_v200_notify_booking_update($post_id, 'created');
    
    return $post_id;
}

// [mps_book_sitter] SHORTCODE
add_shortcode('mps_book_sitter', 'antigravity_v200_render_booking_form_shortcode');
function antigravity_v200_render_booking_form_shortcode($atts) {
    // Placeholder for shortcode content, actual form rendering logic would go here.
    // For now, it's just a placeholder as the instruction only provided the function signature.
    return "Booking form shortcode placeholder.";
}

// 3. FORM HANDLER
add_action('admin_post_mps_request_booking', 'antigravity_v200_handle_booking_submission');
function antigravity_v200_handle_booking_submission() {
    if (!is_user_logged_in()) wp_safe_redirect(home_url('/login/'));
    
    if (!wp_verify_nonce($_POST['_wpnonce'], 'mps_book')) {
        wp_die('Security check failed');
    }
    
    $sitter_id = absint($_POST['sitter_id']); // This is the USER ID of the sitter
    $owner_id = get_current_user_id();
    
    $booking_id = antigravity_v200_create_booking($owner_id, $sitter_id, [
        'start_date' => sanitize_text_field($_POST['start_date']),
        'end_date' => sanitize_text_field($_POST['end_date']),
        'pets' => sanitize_text_field($_POST['pets']),
        'message' => sanitize_textarea_field($_POST['message']),
    ]);
    
    if (!is_wp_error($booking_id)) {
        wp_redirect(home_url('/account/?booking_sent=1'));
    } else {
        wp_redirect(home_url('/sitter/' . $sitter_id . '/?error=booking')); // Fallback redirect
    }
    exit;
}

// 4. ACTION HANDLER (Accept/Decline)
add_action('admin_post_mps_update_booking', 'antigravity_v200_handle_booking_update');
function antigravity_v200_handle_booking_update() {
    if (!is_user_logged_in()) wp_die('Unauthorized');
    
    if (!wp_verify_nonce($_GET['_wpnonce'], 'mps_booking_action')) wp_die('Security fail');
    
    $booking_id = absint($_GET['booking_id']);
    $action = $_GET['status']; // 'accept' or 'decline'
    $user_id = get_current_user_id();
    
    // Verify ownership (Must be the Sitter)
    $sitter_id = get_post_meta($booking_id, 'mps_sitter_id', true);
    if ($sitter_id != $user_id) wp_die('Access Denied');
    
    $new_status = ($action === 'accept') ? 'mps-accepted' : 'mps-declined';
    
    wp_update_post([
        'ID' => $booking_id,
        'post_status' => $new_status
    ]);
    
    // Notify Owner
    antigravity_v200_notify_booking_update($booking_id, $action);
    
    wp_redirect(home_url('/account/?booking_updated=1'));
    exit;
}


// 5. HELPER: NOTIFICATIONS
function antigravity_v200_notify_booking_update($booking_id, $type) {
    $sitter_id = get_post_meta($booking_id, 'mps_sitter_id', true);
    $owner_id = get_post_meta($booking_id, 'mps_owner_id', true);
    
    $sitter = get_userdata($sitter_id);
    $owner = get_userdata($owner_id);
    
    // Links
    $dashboard_link = home_url('/account/');
    
    if ($type === 'created') {
        // Email Sitter
        $subject = 'New Booking Request from ' . $owner->display_name;
        $msg = "Hi " . $sitter->display_name . ",\n\n";
        $msg .= "You have a new booking request for " . get_post_meta($booking_id, 'mps_start_date', true) . ".\n";
        $msg .= "Please log in to your dashboard to Accept or Decline.\n\n";
        $msg .= $dashboard_link;
        wp_mail($sitter->user_email, $subject, $msg);
        
    } elseif ($type === 'accept') {
        // Email Owner
        $subject = 'Booking Accepted! - My Pet Sitters';
        $msg = "Hi " . $owner->display_name . ",\n\n";
        $msg .= "Great news! " . $sitter->display_name . " has ACCEPTED your booking request.\n";
        $msg .= "You can view details in your dashboard:\n" . $dashboard_link;
        wp_mail($owner->user_email, $subject, $msg);
        
    } elseif ($type === 'decline') {
        // Email Owner
        $subject = 'Booking Update - My Pet Sitters';
        $msg = "Hi " . $owner->display_name . ",\n\n";
        $msg .= "Currently " . $sitter->display_name . " is unavailable and has declined your request.\n";
        $msg .= "Please verify your dashboard or search for other sitters.\n";
        wp_mail($owner->user_email, $subject, $msg);
    }
}

// Helper
function antigravity_v200_get_name($user_id) {
    $u = get_userdata($user_id);
    return $u ? $u->display_name : 'User ' . $user_id;
}


