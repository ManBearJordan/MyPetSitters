<?php
/**
 * MPS PET PROFILES
 * 
 * Manages Owner's Pets.
 * - CPT: 'mps_pet'
 * - Meta: Weight, Meds, Behavior, Photos
 */

if (!defined('ABSPATH')) exit;

// 1. REGISTER CPT
add_action('init', function() {
    register_post_type('mps_pet', [
        'public' => false,
        'show_ui' => true,
        'label' => 'Pets',
        'supports' => ['title', 'author', 'thumbnail'], // Thumbnail = Main photo
        'has_archive' => false
    ]);
});

// 2. SAVE PET FUNCTION
function mps_save_pet($user_id, $data, $files = []) {
    $pet_id = isset($data['pet_id']) ? absint($data['pet_id']) : 0;
    
    $post_data = [
        'post_type' => 'mps_pet',
        'post_status' => 'publish',
        'post_title' => sanitize_text_field($data['pet_name']),
        'post_author' => $user_id
    ];
    
    if ($pet_id > 0) {
        $post_data['ID'] = $pet_id;
        $id = wp_update_post($post_data);
    } else {
        $id = wp_insert_post($post_data);
    }
    
    if (is_wp_error($id)) return $id;
    
    // Meta
    // Extended Meta
    update_post_meta($id, 'mps_pet_type', sanitize_text_field($data['pet_type']));
    update_post_meta($id, 'mps_pet_breed', sanitize_text_field($data['pet_breed']));
    update_post_meta($id, 'mps_pet_age', sanitize_text_field($data['pet_age']));
    update_post_meta($id, 'mps_pet_sex', sanitize_text_field($data['pet_sex']));
    update_post_meta($id, 'mps_pet_weight', sanitize_text_field($data['pet_weight']));
    
    // Checkboxes (Save as 1 or 0)
    update_post_meta($id, 'mps_pet_desexed', isset($data['pet_desexed']) ? 1 : 0);
    update_post_meta($id, 'mps_pet_vaccinated', isset($data['pet_vaccinated']) ? 1 : 0);
    update_post_meta($id, 'mps_pet_good_children', isset($data['pet_good_children']) ? 1 : 0);
    update_post_meta($id, 'mps_pet_good_dogs', isset($data['pet_good_dogs']) ? 1 : 0);
    update_post_meta($id, 'mps_pet_good_cats', isset($data['pet_good_cats']) ? 1 : 0);

    update_post_meta($id, 'mps_pet_meds', sanitize_textarea_field($data['pet_meds']));
    update_post_meta($id, 'mps_pet_behavior', sanitize_textarea_field($data['pet_behavior']));
    
    // Handle Photo Uploads (Max 2)
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    
    if (!empty($files['pet_photo_1']['name'])) {
        $attach_id = media_handle_upload('pet_photo_1', $id);
        if (!is_wp_error($attach_id)) set_post_thumbnail($id, $attach_id); // Main photo
    }
    
    if (!empty($files['pet_photo_2']['name'])) {
        $attach_id = media_handle_upload('pet_photo_2', $id);
        if (!is_wp_error($attach_id)) update_post_meta($id, 'mps_pet_photo_2', $attach_id);
    }
    
    return $id;
}

// 3. FORM HANDLER
add_action('admin_post_mps_save_pet', function() {
    if (!is_user_logged_in()) wp_die('Unauthorized');
    check_admin_referer('mps_save_pet');
    
    $user_id = get_current_user_id();
    mps_save_pet($user_id, $_POST, $_FILES);
    
    wp_redirect(home_url('/account/?tab=pets&saved=1'));
    exit;
});

// 4. ACTION: DELETE PET
add_action('admin_post_mps_delete_pet', function() {
    if (!is_user_logged_in()) wp_die('Unauthorized');
    check_admin_referer('mps_delete_pet');
    
    $pet_id = absint($_POST['pet_id']);
    $post = get_post($pet_id);
    
    if ($post && $post->post_author == get_current_user_id()) {
        wp_delete_post($pet_id, true);
    }
    
    wp_redirect(home_url('/account/?tab=pets&deleted=1'));
    exit;
});

// 5. HELPER: GET USER PETS
function mps_get_user_pets($user_id) {
    return get_posts([
        'post_type' => 'mps_pet',
        'author' => $user_id,
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ]);
}
