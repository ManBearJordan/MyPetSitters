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
add_shortcode('mps_edit_profile', 'antigravity_v200_edit_profile_shortcode');
function antigravity_v200_edit_profile_shortcode($atts) {
    // NUCLEAR ERROR HANDLING (V214)
    try {
    if (!is_user_logged_in()) {
        return '<p>Please <a href="/login/">log in</a> to edit your profile.</p>';
    }
    
    $current_user = wp_get_current_user();
    
    // ADMIN OVERRIDE
    $a = shortcode_atts(['user_id' => 0], $atts);
    if (!empty($a['user_id']) && current_user_can('manage_options')) {
        $target = get_userdata($a['user_id']);
        if ($target) {
            $current_user = $target;
        }
    }
    
    $sitter_post = antigravity_v200_get_sitter_post($current_user->ID);
    
    // Load values
    $values = [
        'name' => $current_user->display_name,
        'email' => $current_user->user_email,
        'phone' => '', 'city' => '', 'suburb' => '',
        'state' => '', 'region' => '', 'location_type' => 'Urban', 'radius' => '',
        'bio' => '', 'services' => [], 'prices' => [],
        'skills' => [], 'accepted_pets' => [],
        'show_phone' => 0, 'show_email' => 0
    ];
    
    if ($sitter_post) {
        $meta = antigravity_v200_get_sitter_meta($sitter_post->ID);
        $values['phone'] = $meta['phone'];
        $values['city'] = $meta['city'];
        $values['suburb'] = $meta['suburb'];
        $values['state'] = $meta['state'];
        $values['location_type'] = $meta['location_type'] ?: 'Urban';
        $values['radius'] = $meta['radius'];
        
        // Get Region Term
        $regions = wp_get_post_terms($sitter_post->ID, 'mps_region', ['fields' => 'names']);
        if (is_wp_error($regions)) $regions = []; // Safety fix V215
        $values['region'] = !empty($regions) ? $regions[0] : '';

        $values['bio'] = $sitter_post->post_content;
        $values['services'] = $meta['services'];
        $values['skills'] = get_post_meta($sitter_post->ID, 'mps_skills', true) ?: [];
        $values['accepted_pets'] = get_post_meta($sitter_post->ID, 'mps_accepted_pets', true) ?: [];
        $values['show_phone'] = get_post_meta($sitter_post->ID, 'mps_show_phone', true);
        $values['show_email'] = get_post_meta($sitter_post->ID, 'mps_show_email', true);
        
        $services_map = antigravity_v200_services_map();
        foreach (array_keys($services_map) as $svc) {
            $price_key = 'mps_price_' . sanitize_title($svc);
            $values['prices'][sanitize_title($svc)] = get_post_meta($sitter_post->ID, $price_key, true);
        }
    }
    // Get Data
    $allowed_cities = antigravity_v200_get_valid_locations(); // V76 Master List
    $services_map = antigravity_v200_services_map();
    $states = antigravity_v200_states_list();
    
    // V102: INLINE REGION DATA (Guaranteed Load)
    $structured_regions = [
        'NSW' => [
            'Hunter Region', 'Greater Western Sydney', 'Central Coast', 'Mid North Coast', 'Northern Rivers',
            'New England / North West', 'New England', 'North West', 'Central West', 'Southern Highlands', 'South Coast',
            'Riverina', 'Illawarra', 'Wollongong', 'Snowy Mountains', 'Alpine Country', 'Murray', 'Far West'
        ],
        'QLD' => [
            'South East Queensland', 'Brisbane & Surrounds', 'Gold Coast', 'Sunshine Coast', 'Darling Downs',
            'Wide Bay-Burnett', 'Central Queensland', 'Far North Queensland', 'North Queensland',
            'Gulf Country', 'Central West Queensland', 'South West Queensland'
        ],
        'VIC' => [
            'Greater Melbourne', 'Gippsland', 'Barwon South West', 'Hume', 'Loddon Mallee',
            'Central Victoria', 'Yarra Valley & Dandenong Ranges', 'Mornington Peninsula'
        ],
        'SA' => [
            'Adelaide & Surrounds', 'Barossa Valley', 'Clare Valley', 'Eyre Peninsula',
            'Fleurieu Peninsula', 'Kangaroo Island', 'Murraylands', 'Riverland', 'Far North', 'Limestone Coast'
        ],
        'WA' => [
            'Perth & Peel', 'South West', 'Great Southern', 'Wheatbelt', 'Mid West',
            'Gascoyne', 'Pilbara', 'Kimberley'
        ],
        'TAS' => [
            'Hobart & Surrounds', 'Launceston', 'North West Coast', 'Midlands', 'Central Highlands', 'East Coast'
        ],
        'NT' => [
            'Darwin & Surrounds', 'Katherine Region', 'Top End', 'Central Australia', 'Red Centre'
        ],
        'ACT' => [
            'Canberra'
        ]
    ];
    
    // Flatten for validation/fallback
    $all_regions_flat = [];
    foreach ($structured_regions as $state_regions) {
        $all_regions_flat = array_merge($all_regions_flat, $state_regions);
    }
    
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
    
    <!-- V103: Dynamic Region Scrip (Dropdown) -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Safe JSON parse
        let regionsData = {};
        let suburbsData = {};
        try {
            regionsData = <?= json_encode($structured_regions) ?>;
            suburbsData = <?= json_encode(array_merge(
                (function_exists('antigravity_v200_get_suburbs_by_region') ? antigravity_v200_get_suburbs_by_region() : []), 
                // Fallback for unmapped regions?
                [] 
            )) ?>;
        } catch (e) { console.error(e); }

        const stateSelect = document.querySelector('select[name="state"]');
        const regionSelect = document.querySelector('select[name="region"]');
        // V219: Repointed to 'suburb' input since 'city' is now a dropdown
        const suburbInput = document.querySelector('input[name="suburb"]');
        
        if (!stateSelect || !regionSelect) return;
        
        // Create datalist if it doesn't exist (it was removed in PHP)
        let suburbDatalist = document.getElementById('mps-suburb-list');
        if (!suburbDatalist && suburbInput) {
            suburbDatalist = document.createElement('datalist');
            suburbDatalist.id = 'mps-suburb-list';
            document.body.appendChild(suburbDatalist);
            suburbInput.setAttribute('list', 'mps-suburb-list');
        }

        // Store initial values
        const initialRegion = "<?= esc_js($values['region']) ?>";
        
        function updateRegions() {
            const state = stateSelect.value;
            regionSelect.innerHTML = '<option value="">-- Select Region (Optional) --</option>';
            
            let options = [];
            if (state && regionsData[state]) {
                options = regionsData[state];
                regionSelect.disabled = false;
            } else {
                regionSelect.disabled = true;
                return; 
            }
            
            options.sort().forEach(r => {
                const opt = document.createElement('option');
                opt.value = r;
                opt.textContent = r;
                if (r === initialRegion) opt.selected = true;
                regionSelect.appendChild(opt);
            });
            
            // Trigger suburb update when region changes
            updateSuburbs();
        }

        function updateSuburbs() {
            if (!suburbInput || !suburbDatalist) return;

            const region = regionSelect.value;
            
            // Clear current options
            suburbDatalist.innerHTML = '';
            
            let suburbs = [];
            if (region && suburbsData[region]) {
                suburbs = suburbsData[region];
            } else {
                return;
            }
            
            suburbs.sort().forEach(s => {
                const opt = document.createElement('option');
                opt.value = s;
                suburbDatalist.appendChild(opt);
            });
            
            // Helpful placeholder
            if (suburbs.length > 0) {
                 suburbInput.placeholder = "Select suburb in " + region + "...";
            } else {
                 suburbInput.placeholder = "Type your suburb...";
            }
        }
        
        stateSelect.addEventListener('change', function() {
            updateRegions();
            regionSelect.value = ""; 
            updateSuburbs(); 
        });
        
        regionSelect.addEventListener('change', function() {
            updateSuburbs();
        });
        
        // Initial Load
        if (stateSelect.value) {
            updateRegions();
            if (initialRegion) {
                regionSelect.value = initialRegion;
                updateSuburbs(); 
            }
        } else {
            regionSelect.disabled = true;
        }
    });
    </script>

    <style>
        .mps-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }
        @media (max-width: 600px) {
            .mps-form-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }
        }
    </style>

    <form method="post" action="<?= esc_url(admin_url('admin-post.php')) ?>" enctype="multipart/form-data" style="max-width:800px;">
        <input type="hidden" name="action" value="mps_save_profile">
        <input type="hidden" name="redirect_to" value="<?= esc_url($_SERVER['REQUEST_URI']) ?>">
        <?php 
        wp_nonce_field('mps_save_profile', 'mps_edit_nonce'); 
        
        // Pass target ID if viewing as admin
        if ($current_user->ID !== get_current_user_id()) {
            echo '<input type="hidden" name="target_user_id" value="' . esc_attr($current_user->ID) . '">';
        }
        ?>
        
        <div class="mps-form-grid">
            <div>
                <label><strong>Your Name *</strong></label><br>
                <input type="text" name="name" value="<?= esc_attr($values['name']) ?>" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
            </div>
            <div>
                <label><strong>Email *</strong></label><br>
                <input type="email" name="email" value="<?= esc_attr($values['email']) ?>" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
                <label style="display:inline-flex;align-items:center;gap:6px;margin-top:8px;font-size:0.9em;color:#555;">
                    <input type="checkbox" name="show_email" value="1" <?php checked($values['show_email']); ?>>
                    Show email on public profile
                </label>
            </div>
        </div>
        
        <!-- V75: REGIONAL LOCATIONS -->
        <div class="mps-form-grid">
            <div>
                <label><strong>State</strong></label><br>
                <select name="state" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
                    <option value="">-- Select State --</option>
                    <?php foreach ($states as $code => $label): ?>
                        <option value="<?= esc_attr($code) ?>" <?php selected($values['state'], $code); ?>><?= esc_html($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label><strong>Region</strong> (Optional)</label><br>
                <!-- V103: Select Dropdown instead of Text Input -->
                <select name="region" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
                    <option value="">-- Select State First --</option>
                </select>
            </div>
        </div>
        
        <div class="mps-form-grid">
            <div>
                <label><strong>Location Type *</strong></label><br>
                <select name="location_type" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
                    <option value="Urban" <?php selected($values['location_type'], 'Urban'); ?>>Urban (City/Suburbs)</option>
                    <option value="Regional" <?php selected($values['location_type'], 'Regional'); ?>>Regional (Large Towns)</option>
                    <option value="Rural" <?php selected($values['location_type'], 'Rural'); ?>>Rural (Farm/Acreage)</option>
                </select>
            </div>
            <div>
                <label><strong>Service Radius (km)</strong></label><br>
                <input type="number" name="radius" value="<?= esc_attr($values['radius']) ?>" placeholder="e.g. 25" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
            <div>
                <label><strong>Closest Major City *</strong></label><br>
                <select name="city" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
                    <option value="">-- Select Closest City --</option>
                    <?php 
                    $city_options = function_exists('antigravity_v200_cities_list') ? antigravity_v200_cities_list() : ['Brisbane', 'Sydney', 'Melbourne', 'Perth', 'Adelaide', 'Canberra', 'Hobart', 'Darwin'];
                    foreach ($city_options as $c) {
                        $selected = ($values['city'] === $c) ? 'selected' : '';
                        echo '<option value="' . esc_attr($c) . '" ' . $selected . '>' . esc_html($c) . '</option>';
                    }
                    ?>
                    <option value="Not Listed" <?php selected($values['city'], 'Not Listed'); ?>>Not Listed / Other</option>
                </select>
                <p style="font-size:0.85em;color:#666;margin:4px 0 0;">(Choose "Not Listed" if you are in a regional area not near a major city)</p>
            </div>
            <div>
                <label><strong>Suburb</strong></label><br>
                <input type="text" name="suburb" value="<?= esc_attr($values['suburb']) ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
            </div>
        </div>
        
        <div style="margin-bottom:16px;">
            <label><strong>Phone</strong></label><br>
            <input type="tel" name="phone" value="<?= esc_attr($values['phone']) ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
            <label style="display:inline-flex;align-items:center;gap:6px;margin-top:8px;font-size:0.9em;color:#555;">
                <input type="checkbox" name="show_phone" value="1" <?php checked($values['show_phone']); ?>>
                Show phone number on public profile
            </label>
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
    
    } catch (Throwable $e) {
        ob_end_clean();
        return '<div style="background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;padding:20px;border-radius:8px;">' .
               '<h3>⚠️ Profile Error</h3>' .
               '<p>We encountered an issue loading your profile form:</p>' .
               '<pre style="background:#fff;padding:10px;border:1px solid #ccc;overflow:auto;">' . esc_html($e->getMessage()) . ' in ' . basename($e->getFile()) . ':' . $e->getLine() . '</pre>' .
               '<p>Please contact support with this message.</p>' .
               '</div>';
    }
}

// HANDLE FORM SUBMISSION
add_action('admin_post_mps_save_profile', 'antigravity_v200_handle_profile_save');
add_action('admin_post_nopriv_mps_save_profile', 'antigravity_v200_handle_profile_save');

function antigravity_v200_handle_profile_save() {
    if (!isset($_POST['mps_edit_nonce']) || !wp_verify_nonce($_POST['mps_edit_nonce'], 'mps_save_profile')) {
        wp_die('Security check failed');
    }
    
    $current_user = wp_get_current_user();
    
    // ADMIN OVERRIDE
    if (isset($_POST['target_user_id']) && current_user_can('manage_options')) {
        $target_id = intval($_POST['target_user_id']);
        $target = get_userdata($target_id);
        if ($target) {
             $current_user = $target;
        }
    }

    $redirect_url = !empty($_POST['redirect_to']) ? esc_url_raw($_POST['redirect_to']) : home_url('/account/');
    
    // Validate
    $errors = [];
    $name = sanitize_text_field($_POST['name'] ?? '');
    $bio = wp_kses_post($_POST['bio'] ?? '');
    $services = isset($_POST['services']) ? array_map('sanitize_text_field', (array)$_POST['services']) : [];
    
    // V76 Strict Validation
    $city_input = sanitize_text_field($_POST['city']);
    $valid_locations = antigravity_v200_get_valid_locations();
    
    // Case-insensitive check
    $found_city = false;
    foreach ($valid_locations as $valid) {
        if (strcasecmp($valid, $city_input) === 0) {
            $city_input = $valid; // Normalize to master casing
            $found_city = true;
            break;
        }
    }
    
    if (!$found_city) {
        // V109: Allow NEW suburbs not in the list.
        // User requested "Comprehensive" coverage. Best way is to allow all inputs.
        // We attempt to Title Case it for consistency.
        $city_input = ucwords(strtolower($city_input));
    }

    // V103: INLINED VALIDATION DATA
    $region_input = sanitize_text_field($_POST['region']);
    if ($region_input) {
        $structured_regions = [
            'NSW' => [
                'Hunter Region', 'Greater Western Sydney', 'Central Coast', 'Mid North Coast', 'Northern Rivers',
                'New England / North West', 'New England', 'North West', 'Central West', 'Southern Highlands', 'South Coast',
                'Riverina', 'Illawarra', 'Wollongong', 'Snowy Mountains', 'Alpine Country', 'Murray', 'Far West'
            ],
            'QLD' => [
                'South East Queensland', 'Brisbane & Surrounds', 'Gold Coast', 'Sunshine Coast', 'Darling Downs',
                'Wide Bay-Burnett', 'Central Queensland', 'Far North Queensland', 'North Queensland',
                'Gulf Country', 'Central West Queensland', 'South West Queensland'
            ],
            'VIC' => [
                'Greater Melbourne', 'Gippsland', 'Barwon South West', 'Hume', 'Loddon Mallee',
                'Central Victoria', 'Yarra Valley & Dandenong Ranges', 'Mornington Peninsula'
            ],
            'SA' => [
                'Adelaide & Surrounds', 'Barossa Valley', 'Clare Valley', 'Eyre Peninsula',
                'Fleurieu Peninsula', 'Kangaroo Island', 'Murraylands', 'Riverland', 'Far North', 'Limestone Coast'
            ],
            'WA' => [
                'Perth & Peel', 'South West', 'Great Southern', 'Wheatbelt', 'Mid West',
                'Gascoyne', 'Pilbara', 'Kimberley'
            ],
            'TAS' => [
                'Hobart & Surrounds', 'Launceston', 'North West Coast', 'Midlands', 'Central Highlands', 'East Coast'
            ],
            'NT' => [
                'Darwin & Surrounds', 'Katherine Region', 'Top End', 'Central Australia', 'Red Centre'
            ],
            'ACT' => [
                'Canberra'
            ]
        ];
        
        $found_region = false;
        foreach ($structured_regions as $group) {
            foreach ($group as $valid) {
                 if (strcasecmp((string)$valid, (string)$region_input) === 0) {
                     $region_input = $valid;
                     $found_region = true;
                     break 2;
                 }
            }
        }
        
        if (!$found_region) {
            wp_redirect(add_query_arg('error_msg', 'Invalid Region. Please select from the suggestion list or leave blank.', $redirect_url));
            exit;
        }
    }

    // New Fields
    $skills = isset($_POST['skills']) ? array_map('sanitize_text_field', (array)$_POST['skills']) : [];
    $accepted_pets = isset($_POST['accepted_pets']) ? array_map('sanitize_text_field', (array)$_POST['accepted_pets']) : [];
    $show_phone = isset($_POST['show_phone']) ? 1 : 0;
    $show_email = isset($_POST['show_email']) ? 1 : 0;
    
    if (!$name || !$bio || empty($services)) {
        wp_redirect(add_query_arg('error_msg', 'Missing required fields (Name, Bio, Services)', $redirect_url));
        exit;
    }
    
    // Save User
    wp_update_user(['ID' => $current_user->ID, 'display_name' => $name]);
    
    // Ensure User has 'sitter' role
    if (!in_array('sitter', (array)$current_user->roles)) {
        $current_user->add_role('sitter');
    }
        // Create/Update Sitter Post
        $sitter_post = antigravity_v200_get_sitter_post($current_user->ID);
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
        
        // Notify Admin of New Listing
        if (!is_wp_error($post_id)) {
            $admin_email = defined('MPS_ADMIN_EMAIL') ? MPS_ADMIN_EMAIL : get_option('admin_email');
            $edit_link = admin_url('post.php?post=' . $post_id . '&action=edit');
            wp_mail($admin_email, "New Sitter Profile Published: $name", 
                "A new sitter profile has been published.\n\nName: $name\nCity: " . sanitize_text_field($_POST['city']) . "\n\nEdit/Review: $edit_link");
        }
    }
    
    if (!is_wp_error($post_id)) {
        // Meta
        update_post_meta($post_id, 'mps_email', sanitize_email($_POST['email']));
        update_post_meta($post_id, 'mps_phone', sanitize_text_field($_POST['phone']));
        update_post_meta($post_id, 'mps_city', sanitize_text_field($_POST['city']));

        update_post_meta($post_id, 'mps_suburb', sanitize_text_field($_POST['suburb']));
        
        // V75 Updates
        update_post_meta($post_id, 'mps_state', sanitize_text_field($_POST['state']));
        update_post_meta($post_id, 'mps_location_type', sanitize_text_field($_POST['location_type']));
        update_post_meta($post_id, 'mps_radius', sanitize_text_field($_POST['radius']));
        
        update_post_meta($post_id, 'mps_services', implode(', ', antigravity_v200_normalise_services_labels($services)));
        
        // Save New Meta
        update_post_meta($post_id, 'mps_skills', $skills);
        update_post_meta($post_id, 'mps_accepted_pets', $accepted_pets);
        update_post_meta($post_id, 'mps_show_phone', $show_phone);
        update_post_meta($post_id, 'mps_show_email', $show_email);
        
        // Prices
        $services_map = antigravity_v200_services_map();
        foreach (array_keys($services_map) as $svc) {
            $key = 'price_' . sanitize_title($svc);
            if (isset($_POST[$key])) {
                update_post_meta($post_id, 'mps_price_' . sanitize_title($svc), sanitize_text_field($_POST[$key]));
            }
        }
        
        // Terms (Safety Logic from V38)
        // V110 RESTORATION: Auto-create terms (No quarantine)
        $city = sanitize_text_field($_POST['city']);
        
        if ($city) {
            wp_set_object_terms($post_id, $city, 'mps_city');
        }

        
        // Region Terms (V75)
        $region = sanitize_text_field($_POST['region']);
        if ($region) {
             $term_check = term_exists($region, 'mps_region');
             $term_id = 0;
             if ($term_check && isset($term_check['term_id'])) {
                 $term_id = $term_check['term_id']; 
             } elseif (is_numeric($term_check) && $term_check > 0) {
                 $term_id = $term_check;
             } else {
                 $new = wp_insert_term($region, 'mps_region');
                 if (!is_wp_error($new)) $term_id = $new['term_id'];
             }
             if ($term_id) wp_set_object_terms($post_id, (int)$term_id, 'mps_region');
        } else {
             wp_set_object_terms($post_id, [], 'mps_region'); // Clear if empty
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
            $attach_id = antigravity_v200_image_upload_handler('photo');
            if (!is_wp_error($attach_id)) set_post_thumbnail($post_id, $attach_id);
        }
        
        wp_redirect(add_query_arg('profile_saved', '1', $redirect_url));
    } else {
        wp_redirect(add_query_arg('error_msg', 'Save failed', $redirect_url));
    }
    exit;
}



// Helper: Handle Image Upload
if (!function_exists('antigravity_v200_image_upload_handler')) {
function antigravity_v200_image_upload_handler($file_key) {
    // This function would contain the logic for handling image uploads,
    // similar to what was previously in the main handler.
    // For this specific change, we're just replacing the call.
    // The actual implementation of this helper is not provided in the instruction.
    // Assuming it would handle 'photo' from $_FILES.
    return media_handle_upload($file_key, 0); // Post ID 0 for now, would be passed or determined within the function
}
}
