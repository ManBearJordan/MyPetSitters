<?php
/**
 * MPS PET PROFILES
 * * Manages Owner's Pets.
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

// 2. RENDER PET FORM (Add/Edit)
function antigravity_v200_render_pets_form($pet_id = 0, $owner_id = 0) {
    // Get existing data if editing
    $val = [];
    if ($pet_id) {
        $post = get_post($pet_id);
        if ($post && $post->post_author == $owner_id) {
            $val['name'] = $post->post_title;
            $val['type'] = get_post_meta($pet_id, 'mps_pet_type', true);
            $val['breed'] = get_post_meta($pet_id, 'mps_pet_breed', true);
            $val['age'] = get_post_meta($pet_id, 'mps_pet_age', true);
            $val['sex'] = get_post_meta($pet_id, 'mps_pet_sex', true);
            $val['weight'] = get_post_meta($pet_id, 'mps_pet_weight', true);
            $val['desexed'] = get_post_meta($pet_id, 'mps_pet_desexed', true);
            $val['vaccinated'] = get_post_meta($pet_id, 'mps_pet_vaccinated', true);
            $val['good_children'] = get_post_meta($pet_id, 'mps_pet_good_children', true);
            $val['good_dogs'] = get_post_meta($pet_id, 'mps_pet_good_dogs', true);
            $val['good_cats'] = get_post_meta($pet_id, 'mps_pet_good_cats', true);
            $val['meds'] = get_post_meta($pet_id, 'mps_pet_meds', true);
            $val['behavior'] = get_post_meta($pet_id, 'mps_pet_behavior', true);
            $val['photo1'] = get_the_post_thumbnail_url($pet_id, 'thumbnail');
            $val['photo2_id'] = get_post_meta($pet_id, 'mps_pet_photo_2', true);
            $val['photo2'] = $val['photo2_id'] ? wp_get_attachment_image_url($val['photo2_id'], 'thumbnail') : '';
        }
    }

    ob_start();
    ?>
    <div class="mps-pet-form-container" style="background:#f9f9f9; padding:20px; border-radius:8px; margin-top:20px;">
        <h3 style="margin-top:0;"><?= $pet_id ? 'Edit Pet' : 'Add a New Pet' ?></h3>
        
        <form action="<?= admin_url('admin-post.php') ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="antigravity_v200_save_pet">
            <?php wp_nonce_field('antigravity_v200_save_pet'); ?>
            <input type="hidden" name="pet_id" value="<?= esc_attr($pet_id) ?>">

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:15px;">
                <div>
                    <label>Pet Name *</label>
                    <input type="text" name="pet_name" value="<?= esc_attr($val['name'] ?? '') ?>" required style="width:100%">
                </div>
                <div>
                    <label>Type (Dog, Cat, etc) *</label>
                    <input type="text" name="pet_type" value="<?= esc_attr($val['type'] ?? '') ?>" required style="width:100%">
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:15px;">
                <div><label>Breed</label><input type="text" name="pet_breed" value="<?= esc_attr($val['breed'] ?? '') ?>" style="width:100%"></div>
                <div><label>Age</label><input type="text" name="pet_age" value="<?= esc_attr($val['age'] ?? '') ?>" style="width:100%"></div>
                <div><label>Sex</label><input type="text" name="pet_sex" value="<?= esc_attr($val['sex'] ?? '') ?>" style="width:100%"></div>
                <div><label>Weight (kg)</label><input type="text" name="pet_weight" value="<?= esc_attr($val['weight'] ?? '') ?>" style="width:100%"></div>
            </div>

            <div style="margin-bottom:15px; display:flex; flex-wrap:wrap; gap:15px;">
                <label><input type="checkbox" name="pet_desexed" <?= !empty($val['desexed']) ? 'checked' : '' ?>> Desexed</label>
                <label><input type="checkbox" name="pet_vaccinated" <?= !empty($val['vaccinated']) ? 'checked' : '' ?>> Vaccinated</label>
                <label><input type="checkbox" name="pet_good_children" <?= !empty($val['good_children']) ? 'checked' : '' ?>> Good with Children</label>
                <label><input type="checkbox" name="pet_good_dogs" <?= !empty($val['good_dogs']) ? 'checked' : '' ?>> Good with Dogs</label>
                <label><input type="checkbox" name="pet_good_cats" <?= !empty($val['good_cats']) ? 'checked' : '' ?>> Good with Cats</label>
            </div>

            <div style="margin-bottom:15px;">
                <label>Medical / Dietary Needs</label>
                <textarea name="pet_meds" rows="3" style="width:100%"><?= esc_textarea($val['meds'] ?? '') ?></textarea>
            </div>
            <div style="margin-bottom:15px;">
                <label>Behavioral Notes</label>
                <textarea name="pet_behavior" rows="3" style="width:100%"><?= esc_textarea($val['behavior'] ?? '') ?></textarea>
            </div>

            <div style="margin-bottom:20px; border-top:1px solid #ddd; padding-top:15px;">
                <label><strong>Photos</strong></label><br>
                <div style="display:flex; gap:20px; margin-top:10px;">
                    <div>
                        <label>Main Photo</label><br>
                        <?php if (!empty($val['photo1'])) echo '<img src="'.$val['photo1'].'" style="height:80px; display:block; margin-bottom:5px;">'; ?>
                        <input type="file" name="pet_photo_1" accept="image/*">
                    </div>
                    <div>
                        <label>Second Photo</label><br>
                        <?php if (!empty($val['photo2'])) echo '<img src="'.$val['photo2'].'" style="height:80px; display:block; margin-bottom:5px;">'; ?>
                        <input type="file" name="pet_photo_2" accept="image/*">
                    </div>
                </div>
            </div>

            <button type="submit" class="button button-primary" style="padding:10px 20px;">Save Pet</button>
            <?php if ($pet_id): ?>
                <a href="<?= wp_nonce_url(admin_url('admin-post.php?action=mps_delete_pet&pet_id='.$pet_id), 'mps_delete_pet') ?>" 
                   onclick="return confirm('Delete this pet?')" 
                   style="color:red; margin-left:15px; text-decoration:none;">Delete</a>
            <?php endif; ?>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

// 3. SAVE PET FUNCTION
function antigravity_v200_save_pet($user_id, $data, $files = []) {
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

// 4. HANDLE PET SAVE
add_action('admin_post_antigravity_v200_save_pet', 'antigravity_v200_handle_save_pet');
function antigravity_v200_handle_save_pet() {
    if (!is_user_logged_in()) wp_die('Unauthorized');
    check_admin_referer('antigravity_v200_save_pet');
    
    $user_id = get_current_user_id();
    antigravity_v200_save_pet($user_id, $_POST, $_FILES);
    
    wp_redirect(home_url('/account/?tab=pets&saved=1'));
    exit;
}

// 5. ACTION: DELETE PET
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

// 6. HELPER: GET USER PETS
function antigravity_v200_get_user_pets($user_id) {
    return get_posts([
        'post_type' => 'mps_pet',
        'author' => $user_id,
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ]);
}