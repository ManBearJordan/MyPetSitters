<?php
/**
 * MPS FAVORITES / SHORTLIST
 * 
 * Allows Owners to save Sitters to a shortlist.
 * - Storage: User Meta 'mps_favorites' (Array of Sitter IDs)
 * - Actions: AJAX Toggle
 */

if (!defined('ABSPATH')) exit;

// 1. AJAX TOGGLE
add_action('wp_ajax_mps_toggle_favorite', function() {
    if (!is_user_logged_in()) wp_die();
    check_ajax_referer('mps_fav_nonce', 'nonce');
    
    $sitter_id = absint($_POST['sitter_id']);
    $user_id = get_current_user_id();
    
    $favs = get_user_meta($user_id, 'mps_favorites', true);
    if (!is_array($favs)) $favs = [];
    
    if (in_array($sitter_id, $favs)) {
        // Remove
        $favs = array_diff($favs, [$sitter_id]);
        $status = 'removed';
    } else {
        // Add
        $favs[] = $sitter_id;
        $status = 'added';
    }
    
    update_user_meta($user_id, 'mps_favorites', array_values($favs));
    
    wp_send_json_success(['status' => $status]);
});

// 2. HELPER: IS FAVORITE?
function mps_is_favorite($user_id, $sitter_id) {
    $favs = get_user_meta($user_id, 'mps_favorites', true);
    return is_array($favs) && in_array($sitter_id, $favs);
}

// 3. HELPER: GET FAVORITES
function mps_get_user_favorites($user_id) {
    $favs = get_user_meta($user_id, 'mps_favorites', true);
    if (!is_array($favs) || empty($favs)) return [];
    
    return get_posts([
        'post_type' => 'sitter',
        'post__in' => $favs,
        'posts_per_page' => -1
    ]);
}

// 4. SHORTCODE: FAVORITE BUTTON
add_shortcode('mps_favorite_btn', function($atts) {
    if (!is_user_logged_in()) return '';
    
    $atts = shortcode_atts(['sitter_id' => 0], $atts);
    $sitter_id = absint($atts['sitter_id']);
    if (!$sitter_id) return '';
    
    $is_fav = mps_is_favorite(get_current_user_id(), $sitter_id);
    $fill = $is_fav ? '#ff4081' : 'none';
    $stroke = $is_fav ? '#ff4081' : '#ccc';
    
    ob_start();
    ?>
    <button class="mps-fav-btn" data-id="<?= $sitter_id ?>" style="background:none;border:none;cursor:pointer;padding:8px;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="<?= $fill ?>" stroke="<?= $stroke ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
        </svg>
    </button>
    
    <script>
    // Simple inline script for autonomy (could be moved to a JS file later)
    if (!window.mpsFavInit) {
        window.mpsFavInit = true;
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.mps-fav-btn');
            if (!btn) return;
            
            e.preventDefault();
            const id = btn.getAttribute('data-id');
            const svg = btn.querySelector('svg');
            
            // Optimistic Toggle
            const isFilled = svg.getAttribute('fill') === '#ff4081';
            if (isFilled) {
                svg.setAttribute('fill', 'none');
                svg.setAttribute('stroke', '#ccc');
            } else {
                svg.setAttribute('fill', '#ff4081');
                svg.setAttribute('stroke', '#ff4081');
            }
            
            const fd = new FormData();
            fd.append('action', 'mps_toggle_favorite');
            fd.append('sitter_id', id);
            fd.append('nonce', '<?= wp_create_nonce('mps_fav_nonce') ?>');
            
            fetch('<?= admin_url('admin-ajax.php') ?>', { method:'POST', body:fd });
        });
    }
    </script>
    <?php
    return ob_get_clean();
});
