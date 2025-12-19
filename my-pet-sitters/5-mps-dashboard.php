<?php
/**
 * MPS DASHBOARD & TABS
 * 
 * Central Dashboard for Owners & Sitters.
 * Replaces old simple dashboard with Tabbed Interface.
 */

if (!defined('ABSPATH')) exit;

add_shortcode('mps_dashboard', 'antigravity_v200_render_dashboard');
function antigravity_v200_render_dashboard() {
    if (!is_user_logged_in()) {
        return '<script>window.location.href="/login/";</script>';
    }

    $user = wp_get_current_user();
    
    // ADMIN VIEWER MODE
    $is_viewing = false;
    if (isset($_GET['view_user_id']) && current_user_can('manage_options')) {
        $target_id = intval($_GET['view_user_id']);
        $target_user = get_userdata($target_id);
        if ($target_user) {
            $user = $target_user;
            $is_viewing = true;
        }
    }
    $is_sitter = in_array('pro', (array)$user->roles) || in_array('sitter', (array)$user->roles) || in_array('administrator', (array)$user->roles);
    $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'overview';
    
    // DEFINE TABS
    $tabs = [
        'overview' => ['label' => 'Overview', 'icon' => '🏠'],
        'messages' => ['label' => 'Messages', 'icon' => '✉️'],
        'bookings' => ['label' => 'Bookings', 'icon' => '📅'],
    ];
    
    if ($is_sitter) {
        $tabs['calendar'] = ['label' => 'Availability', 'icon' => '⛔'];
        $tabs['profile']  = ['label' => 'My Profile', 'icon' => '👤'];
    } else {
        $tabs['pets']     = ['label' => 'My Pets', 'icon' => '🐾'];
        $tabs['favorites'] = ['label' => 'Favorites', 'icon' => '❤️'];
        $tabs['profile']  = ['label' => 'My Details', 'icon' => '⚙️'];
    }
    
    ob_start();
    ?>
    <div class="mps-dashboard-wrapper">
        <?php if ($is_viewing): ?>
            <div style="background:#fff3cd;color:#856404;padding:12px;border:1px solid #ffeeba;border-radius:6px;margin-bottom:20px;text-align:center;">
                <strong>👁️ Admin Mode:</strong> You are viewing the dashboard as <u><?= esc_html($user->display_name) ?></u>. 
                <a href="<?= admin_url('user-edit.php?user_id='.$user->ID) ?>">Edit User</a> | <a href="<?= remove_query_arg('view_user_id') ?>">Exit View</a>
            </div>
        <?php endif; ?>
        <!-- HEADER -->
        <div class="mps-dash-head" style="margin-bottom:24px;border-bottom:1px solid #eee;padding-bottom:16px;">
            <h2 style="margin:0;">Welcome, <?= esc_html($user->display_name) ?></h2>
            <p style="margin:0;color:#666;">Member since <?= date('M Y', strtotime($user->user_registered)) ?></p>
        </div>
        
        <!-- TABS NAV -->
        <div class="mps-tabs-nav" style="display:flex;gap:10px;margin-bottom:24px;flex-wrap:wrap;border-bottom:1px solid #ddd;">
            <?php foreach ($tabs as $key => $tab): 
                $active = ($key === $active_tab) ? 'border-bottom:2px solid #2e7d32;color:#2e7d32;font-weight:bold;' : 'color:#555;';
                $link = add_query_arg(['tab' => $key]);
            ?>
                <a href="<?= esc_url($link) ?>" style="padding:10px 16px;text-decoration:none;<?= $active ?>">
                    <?= $tab['icon'] ?> <?= $tab['label'] ?>
                </a>
            <?php endforeach; ?>
            <a href="<?= wp_logout_url('/') ?>" style="padding:10px 16px;text-decoration:none;color:#cc0000;margin-left:auto;">Logout</a>
        </div>
        
        <!-- CONTENT AREA -->
        <div class="mps-tab-content">
            
            <?php 
            // --- TAB: OVERVIEW ---
            if ($active_tab === 'overview'): 
            ?>
                <div style="padding:20px;background:#f9f9f9;border-radius:8px;">
                    <h3>Quick Status</h3>
                    <p>You have <strong><?= antigravity_v200_count_unread_messages($user->ID) ?></strong> unread messages.</p>
                    <p>You have <strong><?= antigravity_v200_count_pending_bookings($user->ID, $is_sitter) ?></strong> pending bookings.</p>
                </div>
            
            <?php 
            // --- TAB: MESSAGES ---
            elseif ($active_tab === 'messages'):
                echo do_shortcode('[mps_inbox]');
            
            // --- TAB: BOOKINGS ---
            elseif ($active_tab === 'bookings'):
                antigravity_v200_render_bookings_tab($user->ID, $is_sitter);
            
            // --- TAB: CALENDAR (Sitter) ---
            elseif ($active_tab === 'calendar' && $is_sitter):
                echo do_shortcode('[mps_availability_editor]');
                
            // --- TAB: PETS (Owner) ---
            elseif ($active_tab === 'pets' && !$is_sitter):
                antigravity_v200_render_pets_tab($user->ID);
                
            // --- TAB: FAVORITES (Owner) ---
            elseif ($active_tab === 'favorites' && !$is_sitter):
                $favs = function_exists('antigravity_v200_get_user_favorites') ? antigravity_v200_get_user_favorites($user->ID) : [];
                if ($favs) {
                    echo '<h3>My Favorite Sitters</h3>';
                    echo '<div class="mps-dash-grid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(250px, 1fr));gap:20px;">';
                    foreach ($favs as $sitter) {
                        $thumb = get_the_post_thumbnail_url($sitter->ID, 'medium');
                        echo '<div style="border:1px solid #ddd;border-radius:8px;padding:16px;">';
                        if ($thumb) echo '<img src="'.esc_url($thumb).'" style="width:100%;height:150px;object-fit:cover;border-radius:4px;margin-bottom:10px;">';
                        echo '<strong>'.esc_html($sitter->post_title).'</strong>';
                        echo '<br><a href="'.get_permalink($sitter->ID).'" class="button" style="margin-top:10px;display:inline-block;">View Profile</a>';
                        echo '</div>';
                    }
                    echo '</div>';
                } else {
                    echo '<p>You haven\'t saved any sitters yet. Go <a href="/list-your-services/">browse sitters</a>.</p>';
                }
                
            // --- TAB: PROFILE ---
            elseif ($active_tab === 'profile'):
                if ($is_sitter) {
                    $sc = $is_viewing ? '[mps_edit_profile user_id="'.$user->ID.'"]' : '[mps_edit_profile]';
                    echo do_shortcode($sc);
                } else {
                     // OWNER PROFILE (Extended)
                     $meta_phone = get_user_meta($user->ID, 'mps_phone', true);
                     $meta_address = get_user_meta($user->ID, 'mps_address', true);
                     $meta_suburb = get_user_meta($user->ID, 'mps_suburb', true);
                     
                     if (isset($_GET['details_saved'])) {
                         echo '<div style="background:#d4edda;color:#155724;padding:10px;border-radius:6px;margin-bottom:15px;">Details saved successfully!</div>';
                     }
                     
                     echo '<h3>My Details</h3>';
                     echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'">';
                     echo '<input type="hidden" name="action" value="mps_update_owner_details">';
                     wp_nonce_field('mps_update_owner_details');
                     
                     echo '<p><label><strong>Name</strong><br><input type="text" name="display_name" value="'.esc_attr($user->display_name).'" style="width:100%;padding:8px;"></label></p>';
                     
                     echo '<div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">';
                     echo '<p><label><strong>Email</strong><br><input type="email" name="user_email" value="'.esc_attr($user->user_email).'" style="width:100%;padding:8px;"></label></p>';
                     echo '<p><label><strong>Phone</strong> (Private until booked)<br><input type="text" name="mps_phone" value="'.esc_attr($meta_phone).'" style="width:100%;padding:8px;"></label></p>';
                     echo '</div>';
                     
                     echo '<div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">';
                     echo '<p><label><strong>Street Address</strong> (Private until booked)<br><input type="text" name="mps_address" value="'.esc_attr($meta_address).'" style="width:100%;padding:8px;"></label></p>';
                     echo '<p><label><strong>Suburb</strong> (Visible to sitters)<br><input type="text" name="mps_suburb" value="'.esc_attr($meta_suburb).'" style="width:100%;padding:8px;"></label></p>';
                     echo '</div>';
                     
                     echo '<p><button class="button" style="background:#2e7d32;color:#fff;">Save Details</button></p>';
                     echo '</form>';
                     echo '<hr style="margin:40px 0;">';
                     echo '<h3>Security</h3>';
                }
                
                // CHANGE PASSWORD
                echo do_shortcode('[mps_change_password]');
                
                // DANGER ZONE (Delete Account)
                ?>
                <div style="margin-top:40px;padding-top:20px;border-top:1px solid #ffcccc;">
                    <h4 style="color:#cc0000;margin-top:0;">Danger Zone</h4>
                    <p style="font-size:13px;color:#666;">Once you delete your account, there is no going back. All your data including messages, pets, and profile will be permanently removed.</p>
                    <form method="post" action="<?= esc_url(admin_url('admin-post.php')) ?>" onsubmit="return confirm('WARNING: Are you sure you want to delete your account? This cannot be undone.');">
                        <input type="hidden" name="action" value="mps_delete_account">
                        <?php wp_nonce_field('mps_delete_account'); ?>
                        <button type="submit" class="button" style="background:#cc0000;color:#fff;border-color:#cc0000;">Delete My Account</button>
                    </form>
                </div>
                <?php
                
            endif; 
            ?>
            
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// --- HELPER RENDERS ---

function antigravity_v200_render_pets_tab($user_id) {
    if (isset($_GET['action']) && ($_GET['action'] === 'add' || $_GET['action'] === 'edit')) {
        // ADD/EDIT FORM
        require_once plugin_dir_path(__FILE__) . '12-mps-pets.php';
        
        $pet_data = [];
        $is_edit = ($_GET['action'] === 'edit');
        if ($is_edit && isset($_GET['pet_id'])) {
            $pet_id = absint($_GET['pet_id']);
            $pet = get_post($pet_id);
            if ($pet && $pet->post_author == $user_id) {
                $pet_data = get_post_meta($pet_id);
                $pet_data['post_title'] = $pet->post_title; // Add title to data array for convenience
                $pet_data['ID'] = $pet_id;
            } else {
                echo '<p class="error">Pet not found or unauthorized.</p>'; 
                return;
            }
        }
        
        // Helper to get value
        $val = function($key) use ($pet_data) {
             // For checkbox/radio meta usually stored as array
             // For text, might be single
             if (isset($pet_data[$key])) {
                 return is_array($pet_data[$key]) ? $pet_data[$key][0] : $pet_data[$key];
             }
             return '';
        };
        
        $title = $is_edit ? 'Edit ' . esc_html($pet_data['post_title']) : 'Add New Pet';
        $btn_text = $is_edit ? 'Update Pet' : 'Save Pet';
        ?>
        <h3><?= $title ?></h3>
        <form method="post" action="<?= esc_url(admin_url('admin-post.php')) ?>" enctype="multipart/form-data" style="max-width:600px;">
            <input type="hidden" name="action" value="antigravity_v200_save_pet">
            <?php if ($is_edit) echo '<input type="hidden" name="pet_id" value="'.$pet_data['ID'].'">'; ?>
            <?php wp_nonce_field('antigravity_v200_save_pet'); ?>
            
            <p>
                <label>Name * <input type="text" name="pet_name" value="<?= $is_edit ? esc_attr($pet_data['post_title']) : '' ?>" required style="width:100%;padding:8px;"></label>
            </p>
            <p>
                <label>Type (Dog/Cat/etc) * <input type="text" name="pet_type" value="<?= esc_attr($val('mps_pet_type')) ?>" required style="width:100%;padding:8px;"></label>
            </p>
            <div style="display:flex;gap:15px;margin-bottom:15px;">
                <div style="flex:1;">
                    <label>Breed <input type="text" name="pet_breed" value="<?= esc_attr($val('mps_pet_breed')) ?>" style="width:100%;padding:8px;"></label>
                </div>
                <div style="flex:1;">
                    <label>Age (Years) <input type="number" name="pet_age" step="0.5" value="<?= esc_attr($val('mps_pet_age')) ?>" style="width:100%;padding:8px;"></label>
                </div>
                <div style="flex:1;">
                    <label>Sex
                    <select name="pet_sex" style="width:100%;padding:8px;">
                        <option value="Male" <?php selected($val('mps_pet_sex'), 'Male'); ?>>Male</option>
                        <option value="Female" <?php selected($val('mps_pet_sex'), 'Female'); ?>>Female</option>
                    </select></label>
                </div>
                <div style="flex:1;">
                    <label>Weight (kg) <input type="number" name="pet_weight" step="0.1" value="<?= esc_attr($val('mps_pet_weight')) ?>" style="width:100%;padding:8px;"></label>
                </div>
            </div>
            
            <p><strong>Health & Behavior</strong></p>
            <div style="display:flex;gap:20px;flex-wrap:wrap;margin-bottom:20px;">
                <label><input type="checkbox" name="pet_desexed" value="1" <?php checked($val('mps_pet_desexed'), 1); ?>> Desexed?</label>
                <label><input type="checkbox" name="pet_vaccinated" value="1" <?php checked($val('mps_pet_vaccinated'), 1); ?>> Vaccinated?</label>
                <label><input type="checkbox" name="pet_good_children" value="1" <?php checked($val('mps_pet_good_children'), 1); ?>> Good with Children</label>
                <label><input type="checkbox" name="pet_good_dogs" value="1" <?php checked($val('mps_pet_good_dogs'), 1); ?>> Good with Dogs</label>
                <label><input type="checkbox" name="pet_good_cats" value="1" <?php checked($val('mps_pet_good_cats'), 1); ?>> Good with Cats</label>
            </div>

            <p>
                <label>Meds/Medical Notes <textarea name="pet_meds" rows="3" style="width:100%;padding:8px;"><?= esc_textarea($val('mps_pet_meds')) ?></textarea></label>
            </p>
            <p>
                <label>Behavior/General Notes <textarea name="pet_behavior" rows="3" style="width:100%;padding:8px;"><?= esc_textarea($val('mps_pet_behavior')) ?></textarea></label>
            </p>
            
            <?php if ($is_edit): ?>
                <div style="display:flex;gap:20px;margin-bottom:15px;">
                    <?php if (has_post_thumbnail($pet_id)): ?>
                        <div><img src="<?= get_the_post_thumbnail_url($pet_id, 'thumbnail') ?>" style="height:80px;border-radius:6px;"> <br>Current Photo 1</div>
                    <?php endif; ?>
                    <?php if ($val('mps_pet_photo_2')):  $src2 = wp_get_attachment_image_src($val('mps_pet_photo_2'), 'thumbnail'); ?>
                        <?php if($src2): ?><div><img src="<?= $src2[0] ?>" style="height:80px;border-radius:6px;"> <br>Current Photo 2</div><?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <p>
                <label>Update Photo 1 (Main) <input type="file" name="pet_photo_1" accept="image/*"></label>
            </p>
            <p>
                <label>Update Photo 2 <input type="file" name="pet_photo_2" accept="image/*"></label>
            </p>
            
            <p><button class="button button-primary" style="background:#2e7d32;color:#fff;border:none;padding:10px 20px;border-radius:4px;cursor:pointer;"><?= $btn_text ?></button> <a href="?tab=pets" class="button" style="padding:10px 20px;">Cancel</a></p>
        </form>
        <?php
    } else {
        // LIST
        $pets = antigravity_v200_get_user_pets($user_id);
        echo '<a href="?tab=pets&action=add" class="button" style="background:#2e7d32;color:#fff;margin-bottom:20px;padding:10px 20px;text-decoration:none;display:inline-block;border-radius:4px;">+ Add New Pet</a>';
        
        if ($pets) {
            echo '<div style="display:grid;gap:20px;grid-template-columns:repeat(auto-fill, minmax(280px, 1fr));">';
            foreach ($pets as $pet) {
                $img = get_the_post_thumbnail_url($pet->ID, 'medium');
                $meta = get_post_meta($pet->ID);
                echo '<div style="border:1px solid #ddd;border-radius:8px;padding:16px;background:#fff;position:relative;">';
                // Clickable Area for Edit
                echo '<a href="?tab=pets&action=edit&pet_id='.$pet->ID.'" style="text-decoration:none;color:inherit;">';
                
                if ($img) echo '<img src="' . esc_url(get_the_post_thumbnail_url($pet->ID, 'large')) . '" style="width:100%;height:auto;max-height:300px;object-fit:contain;background:#f8f9fa;border-radius:4px;margin-bottom:10px;">';
                
                // Name & Basic Info
                $type = $meta['mps_pet_type'][0] ?? '';
                $breed = $meta['mps_pet_breed'][0] ?? '';
                $age = $meta['mps_pet_age'][0] ?? '?';
                $sex = $meta['mps_pet_sex'][0] ?? '';
                $weight = $meta['mps_pet_weight'][0] ?? '';
                
                echo '<strong style="font-size:1.2em;">' . esc_html($pet->post_title) . '</strong>';
                echo '<div style="color:#666;font-size:0.9em;margin-bottom:8px;">' . esc_html("$type • $breed • $age yrs • $sex • {$weight}kg") . '</div>';
                
                // Badges
                echo '<div style="display:flex;gap:8px;flex-wrap:wrap;font-size:0.8em;">';
                if (!empty($meta['mps_pet_desexed'][0])) echo '<span style="background:#e8f5e9;color:#2e7d32;padding:2px 6px;border-radius:4px;">✂️ Desexed</span>';
                if (!empty($meta['mps_pet_vaccinated'][0])) echo '<span style="background:#e3f2fd;color:#1565c0;padding:2px 6px;border-radius:4px;">💉 Vaccinated</span>';
                if (!empty($meta['mps_pet_good_children'][0])) echo '<span style="background:#fff3e0;color:#e65100;padding:2px 6px;border-radius:4px;">👶 Kids OK</span>';
                if (!empty($meta['mps_pet_good_dogs'][0])) echo '<span style="background:#fff3e0;color:#e65100;padding:2px 6px;border-radius:4px;">🐕 Dogs OK</span>';
                if (!empty($meta['mps_pet_good_cats'][0])) echo '<span style="background:#fff3e0;color:#e65100;padding:2px 6px;border-radius:4px;">🐈 Cats OK</span>';
                echo '</div>';
                
                echo '<div style="margin-top:12px;color:#2d8a39;font-weight:500;font-size:0.9em;">Click to View Details & Edit &rarr;</div>';
                echo '</a>';
                
                // Delete Button (Outside the big link)
                echo '<div style="margin-top:10px;text-align:right;">';
                echo '<form method="post" action="' . admin_url('admin-post.php') . '" onsubmit="return confirm(\'Delete?\');" style="display:inline;">';
                echo '<input type="hidden" name="action" value="mps_delete_pet">';
                echo '<input type="hidden" name="pet_id" value="' . $pet->ID . '">';
                wp_nonce_field('mps_delete_pet');
                echo '<button style="color:#cc0000;background:none;border:none;cursor:pointer;padding:0;font-size:0.9em;text-decoration:underline;">Delete Pet</button>';
                echo '</form>';
                echo '</div>';
                
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<p>No pets added yet. Click above to add one.</p>';
        }
    }
}

function antigravity_v200_render_bookings_tab($user_id, $is_sitter) {
    // Determine meta query based on role
    $meta_key = $is_sitter ? 'mps_sitter_id' : 'mps_owner_id';
    
    $bookings = get_posts([
        'post_type' => 'mps_booking',
        'meta_key' => $meta_key,
        'meta_value' => $user_id,
        'post_status' => 'any',
        'posts_per_page' => 20
    ]);
    
    if (!$bookings) {
        echo '<p>No bookings found.</p>';
        return;
    }
    
    echo '<table style="width:100%;border-collapse:collapse;">';
    echo '<tr style="background:#eee;text-align:left;"><th style="padding:10px;">ID</th><th style="padding:10px;">Dates</th><th style="padding:10px;">Status</th><th style="padding:10px;">Action</th></tr>';
    
    foreach ($bookings as $b) {
        $status = get_post_status_object($b->post_status)->label;
        $start = get_post_meta($b->ID, 'mps_start_date', true);
        $end = get_post_meta($b->ID, 'mps_end_date', true);
        
        $action_html = '';
        if ($is_sitter && $b->post_status === 'mps-pending') {
            $nonce = wp_create_nonce('mps_booking_action');
            $url = admin_url('admin-post.php?action=mps_update_booking&booking_id='.$b->ID.'&_wpnonce='.$nonce);
            $action_html = '<a href="'.$url.'&status=accept" style="color:green;margin-right:10px;">Accept</a> ';
            $action_html .= '<a href="'.$url.'&status=decline" style="color:red;">Decline</a>';
        }
        
        echo '<tr style="border-bottom:1px solid #eee;">';
        echo '<td style="padding:10px;">#' . $b->ID . '</td>';
        echo '<td style="padding:10px;">' . $start . ' to ' . $end . '</td>';
        echo '<td style="padding:10px;">';
        echo '<span style="display:inline-block;padding:4px 8px;border-radius:4px;font-size:0.9em;background:' . ($status==='Accepted'?'#d4edda':($status==='Pending'?'#fff3cd':'#f8d7da')) . ';">' . $status . '</span>';
        
        // CONTACT INFO REVEAL
        if ($is_sitter && $b->post_status === 'mps-accepted') {
            $owner_id = get_post_meta($b->ID, 'mps_owner_id', true);
            $owner = get_userdata($owner_id);
            $phone = get_user_meta($owner_id, 'mps_phone', true);
            $addr = get_user_meta($owner_id, 'mps_address', true);
            
            echo '<div style="margin-top:8px;font-size:0.85em;background:#f9f9f9;padding:8px;border:1px solid #eee;border-radius:4px;">';
            echo '<strong>Owner Details:</strong><br>';
            echo esc_html($owner->display_name) . '<br>';
            if ($phone) echo '<a href="tel:'.esc_attr($phone).'">'.esc_html($phone).'</a><br>';
            if ($addr) echo esc_html($addr) . '<br>';
            echo '<a href="mailto:'.esc_attr($owner->user_email).'">'.esc_html($owner->user_email).'</a>';
            echo '<br><a href="' . home_url('/?mps_owner_profile=' . $owner_id) . '" target="_blank" style="text-decoration:underline;color:#2e7d32;display:inline-block;margin-top:4px;">View Owner Profile & Pets</a>';
            echo '</div>';
        }
        echo '</td>';
        echo '<td style="padding:10px;">' . $action_html . '</td>';
        echo '</tr>';
    }
    echo '</table>';
}

// Counts for Overview
function antigravity_v200_count_unread_messages($user_id) {
    global $wpdb;
    return $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = 'mps_recipient_id' AND meta_value = %d AND post_id IN (SELECT ID FROM {$wpdb->posts} WHERE post_type = 'mps_message') AND post_id IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'mps_read_status' AND meta_value = '0')",
        $user_id
    ));
}

function antigravity_v200_count_pending_bookings($user_id, $is_sitter) {
    $meta_key = $is_sitter ? 'mps_sitter_id' : 'mps_owner_id';
    $args = [
        'post_type' => 'mps_booking',
        'meta_key' => $meta_key,
        'meta_value' => $user_id,
        'post_status' => 'mps-pending',
        'fields' => 'ids'
    ];
    return count(get_posts($args));
}


