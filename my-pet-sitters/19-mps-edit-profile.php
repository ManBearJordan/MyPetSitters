<?php
/**
 * ================================================================
 * MPS EDIT PROFILE - Sitter Profile Management
 * ================================================================
 * 
 * Requires: MPS Core
 * 
 * This snippet provides:
 * - [mps_edit_profile] - Form for sitters to edit their profile
 */

if (!defined('ABSPATH')) exit;

// [mps_edit_profile] SHORTCODE
// [mps_edit_profile] SHORTCODE
add_shortcode('mps_edit_profile', function() {
    if (!is_user_logged_in()) {
        return '<p>Please <a href="/login/">log in</a> to edit your profile.</p>';
    }
    
    $current_user = wp_get_current_user();
    $sitter_post = mps_get_user_sitter_post($current_user->ID);
    
    // Load values
    $values = [
        'name' => $current_user->display_name,
        'email' => $current_user->user_email,
        'phone' => '', 'city' => '', 'suburb' => '',
        'bio' => '', 'services' => [], 'prices' => [],
        'skills' => [], 'accepted_pets' => []
    ];
    
    if ($sitter_post) {
        $meta = mps_get_sitter_meta($sitter_post->ID);
        $values['phone'] = $meta['phone'];
        $values['city'] = $meta['city'];
        $values['suburb'] = $meta['suburb'];
        $values['bio'] = $sitter_post->post_content;
        $values['services'] = $meta['services'];
        $values['skills'] = get_post_meta($sitter_post->ID, 'mps_skills', true) ?: [];
        $values['accepted_pets'] = get_post_meta($sitter_post->ID, 'mps_accepted_pets', true) ?: [];
        
        $services_map = mps_services_map();
        foreach (array_keys($services_map) as $svc) {
            $price_key = 'mps_price_' . sanitize_title($svc);
            $values['prices'][sanitize_title($svc)] = get_post_meta($sitter_post->ID, $price_key, true);
        }
    }
    
    $services_map = mps_services_map();
    $cities = mps_cities_list();
    
    $skill_options = [
        'Fenced Backyard', 'Allowed on Furniture', 'Oral Medication', 
        'Injected Medication', 'Puppy Care (< 1 year)', 'Senior Care', 
        'Has Car', '24/7 Supervision'
    ];
    
    $pet_options = [
        'Dogs (Small < 10kg)', 'Dogs (Medium 10-20kg)', 'Dogs (Large 20-40kg)', 
        'Dogs (Giant 40kg+)', 'Cats', 'Small Pets (Rabbits/Guinea Pigs)'
    ];
    
    ob_start();
    
    // Messages
    if (isset($_GET['profile_saved'])) {
        echo '<div style="background:#d4edda;border:1px solid #c3e6cb;color:#155724;padding:12px;border-radius:8px;margin-bottom:16px;"><strong>Profile saved successfully!</strong></div>';
    }
    if (isset($_GET['error_msg'])) {
        echo '<div style="background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;padding:12px;border-radius:8px;margin-bottom:16px;">' . esc_html($_GET['error_msg']) . '</div>';
    }
    ?>
    
    <h3>Edit Profile</h3>
    <form method="post" action="<?= esc_url(admin_url('admin-post.php')) ?>" enctype="multipart/form-data" style="max-width:800px;">
        <input type="hidden" name="action" value="mps_save_profile">
        <input type="hidden" name="redirect_to" value="<?= esc_url($_SERVER['REQUEST_URI']) ?>">
        <?php wp_nonce_field('mps_save_profile', 'mps_edit_nonce'); ?>
        
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
            <div>
                <label><strong>Your Name *</strong></label><br>
                <input type="text" name="name" value="<?= esc_attr($values['name']) ?>" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
            </div>
            <div>
                <label><strong>Email *</strong></label><br>
                <input type="email" name="email" value="<?= esc_attr($values['email']) ?>" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
            </div>
        </div>
        
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
            <div>
                <label><strong>City *</strong></label><br>
                <select name="city" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
                    <option value="">-- Select City --</option>
                    <?php foreach ($cities as $c): ?>
                        <option value="<?= esc_attr($c) ?>" <?php selected($values['city'], $c); ?>><?= esc_html($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label><strong>Suburb</strong></label><br>
                <input type="text" name="suburb" value="<?= esc_attr($values['suburb']) ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
            </div>
        </div>
        
        <div style="margin-bottom:16px;">
            <label><strong>Phone</strong></label><br>
            <input type="tel" name="phone" value="<?= esc_attr($values['phone']) ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
        </div>
        
        <div style="margin-bottom:16px;">
            <label><strong>Services & Pricing *</strong></label><br>
            <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-top:6px;">
                <?php foreach ($services_map as $svc => $slug): 
                    $is_checked = in_array($svc, $values['services']);
                    $price = $values['prices'][sanitize_title($svc)] ?? '';
                ?>
                <div style="display:flex;align-items:center;gap:12px;padding:8px;margin-bottom:4px;">
                    <label style="flex:1;display:flex;align-items:center;gap:8px;">
                        <input type="checkbox" name="services[]" value="<?= esc_attr($svc) ?>" <?php checked($is_checked); ?>>
                        <strong><?= esc_html($svc) ?></strong>
                    </label>
                    <span>$</span>
                    <input type="number" name="price_<?= sanitize_title($svc) ?>" value="<?= esc_attr($price) ?>" placeholder="0" min="0" step="1" style="width:80px;padding:6px;">
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- NEW SECTIONS -->
        <div style="margin-bottom:16px;">
            <label><strong>What pets do you accept?</strong></label><br>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;background:#f9f9f9;padding:16px;border-radius:8px;margin-top:6px;">
                <?php foreach ($pet_options as $pet): ?>
                    <label style="display:flex;align-items:center;gap:8px;">
                        <input type="checkbox" name="accepted_pets[]" value="<?= esc_attr($pet) ?>" <?php checked(in_array($pet, $values['accepted_pets'])); ?>>
                        <?= esc_html($pet) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div style="margin-bottom:16px;">
            <label><strong>Skills & Attributes</strong></label><br>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;background:#f9f9f9;padding:16px;border-radius:8px;margin-top:6px;">
                <?php foreach ($skill_options as $skill): ?>
                    <label style="display:flex;align-items:center;gap:8px;">
                        <input type="checkbox" name="skills[]" value="<?= esc_attr($skill) ?>" <?php checked(in_array($skill, $values['skills'])); ?>>
                        <?= esc_html($skill) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div style="margin-bottom:16px;">
            <label><strong>About You (Bio) *</strong></label><br>
            <textarea name="bio" rows="6" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;"><?= esc_textarea($values['bio']) ?></textarea>
        </div>
        
        <div style="margin-bottom:16px;">
            <label><strong>Profile Photo</strong></label><br>
            <?php if ($sitter_post && has_post_thumbnail($sitter_post->ID)): ?>
                <img src="<?= get_the_post_thumbnail_url($sitter_post->ID, 'thumbnail') ?>" alt="Current photo" style="display:block;margin:8px 0;border-radius:8px;max-width:150px;">
            <?php endif; ?>
            <input type="file" name="photo" accept="image/*" style="display:block;margin-top:8px;">
        </div>
        
        <div>
            <button type="submit" class="wp-block-button__link" style="background:#2e7d32;color:#fff;padding:12px 32px;border:none;cursor:pointer;border-radius:50px!important;">Save Profile</button>
        </div>
    </form>
    <?php
    return ob_get_clean();
});

// FORM HANDLER
add_action('admin_post_mps_save_profile', function() {
    if (!isset($_POST['mps_edit_nonce']) || !wp_verify_nonce($_POST['mps_edit_nonce'], 'mps_save_profile')) {
        wp_die('Security check failed');
    }
    
    $current_user = wp_get_current_user();
    $redirect_url = !empty($_POST['redirect_to']) ? esc_url_raw($_POST['redirect_to']) : home_url('/account/');
    
    // Validate
    $errors = [];
    $name = sanitize_text_field($_POST['name'] ?? '');
    $bio = wp_kses_post($_POST['bio'] ?? '');
    $services = isset($_POST['services']) ? array_map('sanitize_text_field', (array)$_POST['services']) : [];
    
    // New Fields
    $skills = isset($_POST['skills']) ? array_map('sanitize_text_field', (array)$_POST['skills']) : [];
    $accepted_pets = isset($_POST['accepted_pets']) ? array_map('sanitize_text_field', (array)$_POST['accepted_pets']) : [];
    
    if (!$name || !$bio || empty($services)) {
        wp_redirect(add_query_arg('error_msg', 'Missing required fields (Name, Bio, Services)', $redirect_url));
        exit;
    }
    
    // Save User
    wp_update_user(['ID' => $current_user->ID, 'display_name' => $name]);
    
    // Save Sitter Post
    $sitter_post = mps_get_user_sitter_post($current_user->ID);
    $post_data = [
        'post_type' => 'sitter',
        'post_status' => 'publish',
        'post_title' => $name . ' - ' . sanitize_text_field($_POST['city']),
        'post_content' => $bio,
        'post_author' => $current_user->ID,
    ];
    
    if ($sitter_post) {
        $post_data['ID'] = $sitter_post->ID;
        wp_update_post($post_data);
        $post_id = $sitter_post->ID;
    } else {
        $post_id = wp_insert_post($post_data);
    }
    
    if (!is_wp_error($post_id)) {
        // Meta
        update_post_meta($post_id, 'mps_email', sanitize_email($_POST['email']));
        update_post_meta($post_id, 'mps_phone', sanitize_text_field($_POST['phone']));
        update_post_meta($post_id, 'mps_city', sanitize_text_field($_POST['city']));
        update_post_meta($post_id, 'mps_suburb', sanitize_text_field($_POST['suburb']));
        update_post_meta($post_id, 'mps_services', implode(', ', mps_normalise_services_labels($services)));
        
        // Save New Meta
        update_post_meta($post_id, 'mps_skills', $skills);
        update_post_meta($post_id, 'mps_accepted_pets', $accepted_pets);
        
        // Prices
        $services_map = mps_services_map();
        foreach (array_keys($services_map) as $svc) {
            $key = 'price_' . sanitize_title($svc);
            if (isset($_POST[$key])) {
                update_post_meta($post_id, 'mps_price_' . sanitize_title($svc), sanitize_text_field($_POST[$key]));
            }
        }
        
        // Terms (Safety Logic from V38)
        $city = sanitize_text_field($_POST['city']);
        if ($city) {
            $term_check = term_exists($city, 'mps_city');
            $term_id = 0;
            if ($term_check && isset($term_check['term_id'])) {
                $term_id = $term_check['term_id']; 
            } elseif (is_numeric($term_check) && $term_check > 0) {
                $term_id = $term_check;
            } else {
                $new = wp_insert_term($city, 'mps_city');
                if (!is_wp_error($new)) $term_id = $new['term_id'];
            }
            if ($term_id) wp_set_object_terms($post_id, (int)$term_id, 'mps_city');
        }
        
        // Service Terms
        $svc_ids = [];
        foreach ($services as $svc_label) {
            $term_check = term_exists($svc_label, 'mps_service');
             $term_id = 0;
            if ($term_check && isset($term_check['term_id'])) {
                $term_id = $term_check['term_id']; 
            } elseif (is_numeric($term_check) && $term_check > 0) {
                $term_id = $term_check;
            } else {
                $new = wp_insert_term($svc_label, 'mps_service');
                if (!is_wp_error($new)) $term_id = $new['term_id'];
            }
            if ($term_id) $svc_ids[] = (int)$term_id;
        }
        if ($svc_ids) wp_set_object_terms($post_id, $svc_ids, 'mps_service');

        // Image
        if (!empty($_FILES['photo']['name'])) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            $attach_id = media_handle_upload('photo', $post_id);
            if (!is_wp_error($attach_id)) set_post_thumbnail($post_id, $attach_id);
        }
        
        wp_redirect(add_query_arg('profile_saved', '1', $redirect_url));
    } else {
        wp_redirect(add_query_arg('error_msg', 'Save failed', $redirect_url));
    }
    exit;
});

function mps_get_user_sitter_post($user_id) {
    $posts = get_posts(['post_type'=>'sitter', 'author'=>$user_id, 'numberposts'=>1, 'post_status'=>'any']);
    return $posts ? $posts[0] : null;
}
