<?php
/**
 * MPS ADMIN - Admin Meta Boxes, Validation, Cron Jobs
 * Requires: MPS Core
 */

if (!defined('ABSPATH')) exit;

// ===========================================================================
// ADMIN META BOX FOR SITTER DETAILS
// ===========================================================================

add_action('add_meta_boxes', function() {
    add_meta_box('mps_sitter_meta', 'Sitter Details', 'mps_sitter_meta_box', 'sitter', 'normal', 'high');
});

function mps_sitter_meta_box($post) {
    wp_nonce_field('mps_sitter_meta', 'mps_sitter_meta_nonce');
    $meta = mps_get_sitter_meta($post->ID);
    $services_map = mps_services_map();
    ?>
    <style>
        .mps-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .mps-service-row { display: flex; align-items: center; gap: 12px; padding: 8px; background: #f9f9f9; margin-bottom: 4px; border-radius: 4px; }
        .mps-service-row label { flex: 1; display: flex; align-items: center; gap: 8px; }
        .mps-service-row input[type="number"] { width: 80px; }
    </style>
    <div class="mps-grid">
        <p><label>City<br>
            <select name="mps_city" style="width:100%">
                <option value="">Select...</option>
                <?php foreach (mps_cities_list() as $c): ?>
                    <option <?php selected($meta['city'], $c); ?>><?= esc_html($c) ?></option>
                <?php endforeach; ?>
            </select>
        </label></p>
        <p><label>Suburb<br><input type="text" name="mps_suburb" value="<?= esc_attr($meta['suburb']) ?>" style="width:100%"></label></p>
        <p><label>Email<br><input type="email" name="mps_email" value="<?= esc_attr($meta['email']) ?>" style="width:100%"></label></p>
        <p><label>Phone<br><input type="text" name="mps_phone" value="<?= esc_attr($meta['phone']) ?>" style="width:100%"></label></p>
    </div>
    
    <h4 style="margin: 16px 0 8px;">Services & Pricing</h4>
    <p style="color: #666; margin-bottom: 12px;">Check services offered and set starting prices ($/session).</p>
    
    <?php foreach ($services_map as $svc => $slug): 
        $is_checked = in_array($svc, $meta['services']);
        $price_key = 'mps_price_' . sanitize_title($svc);
        $price = get_post_meta($post->ID, $price_key, true);
    ?>
    <div class="mps-service-row">
        <label>
            <input type="checkbox" name="mps_services[]" value="<?= esc_attr($svc) ?>" <?php checked($is_checked); ?>>
            <strong><?= esc_html($svc) ?></strong>
        </label>
        <span>$</span>
        <input type="number" name="price_<?= sanitize_title($svc) ?>" value="<?= esc_attr($price) ?>" placeholder="0" min="0" step="1">
    </div>
    <?php endforeach; ?>
    <?php
}

// ===========================================================================
// SAVE META + SYNC TAXONOMIES
// ===========================================================================

add_action('save_post_sitter', function($post_id) {
    if (!isset($_POST['mps_sitter_meta_nonce']) || !wp_verify_nonce($_POST['mps_sitter_meta_nonce'], 'mps_sitter_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    
    $city = sanitize_text_field($_POST['mps_city'] ?? '');
    $suburb = sanitize_text_field($_POST['mps_suburb'] ?? '');
    $email = sanitize_email($_POST['mps_email'] ?? '');
    $phone = sanitize_text_field($_POST['mps_phone'] ?? '');
    $services = isset($_POST['mps_services']) ? mps_normalise_services_labels($_POST['mps_services']) : [];
    
    update_post_meta($post_id, 'mps_city', $city);
    update_post_meta($post_id, 'mps_suburb', $suburb);
    update_post_meta($post_id, 'mps_email', $email);
    update_post_meta($post_id, 'mps_phone', $phone);
    update_post_meta($post_id, 'mps_services', implode(', ', $services));
    
    // Save per-service prices
    $services_map = mps_services_map();
    foreach (array_keys($services_map) as $svc) {
        $key = 'price_' . sanitize_title($svc);
        $price = sanitize_text_field($_POST[$key] ?? '');
        update_post_meta($post_id, 'mps_price_' . sanitize_title($svc), $price);
    }
    
    // Sync city taxonomy
    if ($city && taxonomy_exists('mps_city')) {
        $term = term_exists($city, 'mps_city');
        if (!$term) $term = wp_insert_term($city, 'mps_city', ['slug' => mps_slugify($city)]);
        if (!is_wp_error($term)) wp_set_object_terms($post_id, (int)($term['term_id'] ?? $term), 'mps_city', false);
    } else {
        wp_set_object_terms($post_id, [], 'mps_city', false);
    }
    
    // Sync service taxonomy
    if (taxonomy_exists('mps_service')) {
        $ids = [];
        foreach ($services as $label) {
            $slug = mps_services_map()[$label] ?? mps_slugify($label);
            $t = term_exists($label, 'mps_service');
            if (!$t) $t = wp_insert_term($label, 'mps_service', ['slug' => $slug]);
            if (!is_wp_error($t)) $ids[] = (int)($t['term_id'] ?? $t);
        }
        wp_set_object_terms($post_id, $ids, 'mps_service', false);
    }
}, 10);

// ===========================================================================
// ADMIN COLUMNS
// ===========================================================================

add_filter('manage_sitter_posts_columns', function($cols) {
    $cols['mps_city'] = 'City';
    $cols['mps_services'] = 'Services';
    return $cols;
});

add_action('manage_sitter_posts_custom_column', function($col, $post_id) {
    if ($col === 'mps_city') echo esc_html(get_post_meta($post_id, 'mps_city', true));
    if ($col === 'mps_services') echo esc_html(get_post_meta($post_id, 'mps_services', true));
}, 10, 2);

// ===========================================================================
// GUARD PUBLISH (require city + service)
// ===========================================================================

add_action('transition_post_status', function($new, $old, $post) {
    if ($post->post_type !== 'sitter') return;
    if ($old === 'publish' && $new === 'publish') return;
    if ($new !== 'publish') return;
    
    $city = trim(get_post_meta($post->ID, 'mps_city', true));
    $svc = array_filter(explode(',', get_post_meta($post->ID, 'mps_services', true)));
    
    if (!$city || !$svc) {
        remove_action('transition_post_status', __FUNCTION__, 10);
        wp_update_post(['ID' => $post->ID, 'post_status' => 'pending']);
        add_action('transition_post_status', __FUNCTION__, 10, 3);
        
        if (defined('MPS_ADMIN_EMAIL')) {
            wp_mail(MPS_ADMIN_EMAIL, 'Sitter incomplete: moved to pending',
                "Sitter #{$post->ID} missing " . (!$city ? 'city' : 'services') . "\n" . admin_url("post.php?post={$post->ID}&action=edit"));
        }
    }
}, 10, 3);

// ===========================================================================
// NIGHTLY RESYNC CRON
// ===========================================================================

add_action('init', function() {
    if (!wp_next_scheduled('mps_nightly_resync')) {
        wp_schedule_event(time() + 60, 'daily', 'mps_nightly_resync');
    }
});

add_action('mps_nightly_resync', function() {
    $q = new WP_Query(['post_type' => 'sitter', 'post_status' => ['publish','pending','draft'], 'posts_per_page' => 500, 'fields' => 'ids']);
    
    foreach ($q->posts as $id) {
        $city = get_post_meta($id, 'mps_city', true);
        if ($city && taxonomy_exists('mps_city')) {
            $t = term_exists($city, 'mps_city');
            if (!$t) $t = wp_insert_term($city, 'mps_city', ['slug' => mps_slugify($city)]);
            if (!is_wp_error($t)) wp_set_object_terms($id, (int)($t['term_id'] ?? $t), 'mps_city', false);
        }
        
        $terms = wp_get_object_terms($id, 'mps_service', ['fields' => 'all']);
        if (!is_wp_error($terms) && $terms) {
            $labels = array_map(fn($t) => mps_service_slug_to_label($t->slug), $terms);
            $csv = implode(', ', array_unique($labels));
            if ($csv !== get_post_meta($id, 'mps_services', true)) {
                update_post_meta($id, 'mps_services', $csv);
            }
        }
    }
});

add_action('switch_theme', function() {
    $ts = wp_next_scheduled('mps_nightly_resync');
    if ($ts) wp_unschedule_event($ts, 'mps_nightly_resync');
});

// ===========================================================================
// SITTER TITLE FALLBACK
// ===========================================================================

add_action('save_post_sitter', function($post_id, $post, $update) {
    if ($update) return;
    $title = get_the_title($post_id);
    if (!$title || $title === 'Auto Draft') {
        $name = get_post_meta($post_id, 'mps_name', true);
        $city = get_post_meta($post_id, 'mps_city', true);
        $fallback = implode(' - ', array_filter([$name, $city])) ?: 'New Sitter';
        wp_update_post(['ID' => $post_id, 'post_title' => $fallback, 'post_name' => sanitize_title($fallback)]);
    }
}, 20, 3);
