<?php
/**
 * MPS ACCOUNT MANAGEMENT
 * 
 * Handles Account Deletion and data cleanup.
 */

if (!defined('ABSPATH')) exit;

add_action('admin_post_mps_delete_account', function() {
    if (!is_user_logged_in()) wp_die('Unauthorized');
    
    check_admin_referer('mps_delete_account');
    
    $user_id = get_current_user_id();
    $current_user = wp_get_current_user();
    
    // Safety check: Don't allow admins to delete themselves via this frontend form to prevent accidental lockout
    if (in_array('administrator', (array)$current_user->roles)) {
        wp_die('Administrators cannot delete their account from the frontend. Please use the WP Admin dashboard.');
    }
    
    // 1. CLEANUP CUSTOM DATA
    // Delete Sitter Profile
    $sitter_post = antigravity_v200_get_sitter_post($user_id);
    if ($sitter_post) {
        wp_delete_post($sitter_post->ID, true);
    }
    
    // Delete Pets
    $pets = get_posts(['post_type'=>'mps_pet', 'author'=>$user_id, 'posts_per_page'=>-1]);
    foreach ($pets as $p) wp_delete_post($p->ID, true);
    
    // Delete/Cancel Bookings? 
    // If Owner: Cancel pending bookings made by them?
    // If Sitter: Cancel pending bookings assigned to them?
    // For simplicity, we will let wp_delete_user handle post reassignment or deletion if we passed a reassign ID, 
    // but here we want to ERASE data.
    
    // Delete Bookings where this user is the author (Owner)
    $bookings = get_posts(['post_type'=>'mps_booking', 'author'=>$user_id, 'posts_per_page'=>-1]);
    foreach ($bookings as $b) wp_delete_post($b->ID, true);
    
    // Note: Bookings received by a Sitter are authored by the Owner, so they wont be deleted automatically by WP.
    // We should mark them as cancelled or delete them.
    $received_bookings = get_posts([
        'post_type' => 'mps_booking',
        'meta_key' => 'mps_sitter_id',
        'meta_value' => $user_id,
        'posts_per_page' => -1
    ]);
    foreach ($received_bookings as $b) wp_delete_post($b->ID, true);
    
    // 2. DELETE USER
    require_once(ABSPATH . 'wp-admin/includes/user.php');
    $deleted = wp_delete_user($user_id);
    
    if ($deleted) {
        wp_redirect(home_url('/join/?account_deleted=1'));
        exit;
    } else {
        wp_die('Could not delete account. Please contact support.');
    }
});

// OWNER PROFILE PUBLIC VIEW (Restricted to Sitters usually, or public safe info)
// OWNER/SITTER PROFILE VIEW HANDLER
add_action('template_redirect', function() {
    $target_user_id = 0;
    
    // 1. Check for Standard WP Author Archive (The "View" link)
    if (is_author()) {
        $target_user_id = get_queried_object_id();
        
        // If Sitter: Redirect to their Public Listing
        $sitter_post = antigravity_v200_get_sitter_post($target_user_id);
        if ($sitter_post && $sitter_post->post_status === 'publish') {
            wp_redirect(get_permalink($sitter_post->ID));
            exit;
        }
        
        // If Owner (or sitter with no post): Continue to render Owner Profile logic
    }
    
    // 2. Check for Custom Parameter (Legacy/Manual)
    if (isset($_GET['mps_owner_profile'])) {
        $target_user_id = intval($_GET['mps_owner_profile']);
    }

    if ($target_user_id) {
        $owner = get_userdata($target_user_id);
        if (!$owner) wp_die('User not found.');
        
        // Security: Only Admins or the user themselves (or sitters with a booking?)
        // For now, let's keep it visible if they have the link (View link is public-ish in WP)
        
        // RENDER PROFILE
        
        // RENDER PROFILE
        get_header();
        ?>
        <div class="wrap" style="max-width:800px;margin:40px auto;padding:20px;">
            <h1>Owner Profile: <?= esc_html($owner->display_name) ?></h1>
            <p style="color:#666;">Member since <?= date('M Y', strtotime($owner->user_registered)) ?></p>
            
            <?php 
            $suburb = get_user_meta($owner_id, 'mps_suburb', true); 
            if ($suburb) echo '<p><strong>Location:</strong> ' . esc_html($suburb) . '</p>';
            ?>
            
            <hr>
            <h3>My Pets</h3>
            <?php
            // Reuse the pet render logic or query directly
            $pets = get_posts([
                'post_type' => 'mps_pet',
                'meta_key' => 'mps_owner_id',
                'meta_value' => $owner_id,
                'post_status' => 'publish',
                'posts_per_page' => -1
            ]);
            
            if ($pets) {
                echo '<div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(250px, 1fr));gap:20px;">';
                foreach ($pets as $pet) {
                    $img = get_the_post_thumbnail_url($pet->ID, 'medium');
                    $meta = get_post_meta($pet->ID);
                    
                    echo '<div style="border:1px solid #ddd;border-radius:8px;padding:16px;background:#fff;">';
                    if ($img) echo '<img src="' . esc_url($img) . '" style="width:100%;height:200px;object-fit:cover;border-radius:4px;margin-bottom:10px;">';
                    echo '<strong style="display:block;font-size:1.2em;">' . esc_html($pet->post_title) . '</strong>';
                    
                    // Details
                    $info = [];
                    if (!empty($meta['mps_pet_type'][0])) $info[] = $meta['mps_pet_type'][0];
                    if (!empty($meta['mps_pet_breed'][0])) $info[] = $meta['mps_pet_breed'][0];
                    if (!empty($meta['mps_pet_age'][0])) $info[] = $meta['mps_pet_age'][0] . ' yrs';
                    
                    if ($info) echo '<p style="color:#666;margin:5px 0;">' . implode(' • ', array_map('esc_html', $info)) . '</p>';
                    
                    // Badges
                    echo '<div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:8px;">';
                     if (!empty($meta['mps_pet_good_children'][0])) echo '<span style="background:#e8f5e9;color:#2e7d32;padding:2px 6px;border-radius:4px;font-size:0.85em;">👶 Kids OK</span>';
                     if (!empty($meta['mps_pet_good_dogs'][0])) echo '<span style="background:#e8f5e9;color:#2e7d32;padding:2px 6px;border-radius:4px;font-size:0.85em;">🐕 Dogs OK</span>';
                    echo '</div>';
                    
                    // Bio
                    if (!empty($meta['mps_pet_behavior'][0])) {
                        echo '<p style="font-size:0.95em;margin-top:10px;line-height:1.4;">' . wp_trim_words($meta['mps_pet_behavior'][0], 15) . '</p>';
                    }
                    echo '</div>';
                }
                echo '</div>';
            } else {
                echo '<p>No pets listed.</p>';
            }
            ?>
            
        </div>
        <?php
        get_footer();
        exit;
    }
});

add_action('admin_post_mps_update_owner_details', function() {
    if (!is_user_logged_in()) wp_die('Unauthorized');
    check_admin_referer('mps_update_owner_details');
    
    $user_id = get_current_user_id();
    
    // 1. Basic User Data
    $update_data = ['ID' => $user_id];
    if (isset($_POST['display_name'])) $update_data['display_name'] = sanitize_text_field($_POST['display_name']);
    if (isset($_POST['user_email'])) $update_data['user_email'] = sanitize_email($_POST['user_email']);
    
    wp_update_user($update_data);
    
    // 2. Meta Data
    if (isset($_POST['mps_phone'])) update_user_meta($user_id, 'mps_phone', sanitize_text_field($_POST['mps_phone']));
    if (isset($_POST['mps_address'])) update_user_meta($user_id, 'mps_address', sanitize_text_field($_POST['mps_address']));
    if (isset($_POST['mps_suburb'])) update_user_meta($user_id, 'mps_suburb', sanitize_text_field($_POST['mps_suburb']));
    
    wp_safe_redirect(add_query_arg(['tab'=>'profile', 'details_saved'=>'1'], wp_get_referer()));
    exit;
});


