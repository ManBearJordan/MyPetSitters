<?php
/**
 * MPS PAGES - City/Service Page Shortcodes & SEO
 * 
 * Requires: MPS Core
 * 
 * This snippet provides:
 * - [mps_page] shortcode for city/service landing pages
 * - [mps_about] shortcode for about page
 * - Automatic SEO titles and descriptions (Rank Math integration)
 * - FAQ generation with Schema markup
 * - Term link filtering (taxonomy URLs -> Page URLs)
 */

if (!defined('ABSPATH')) exit;

// ===========================================================================
// SECTION 0: REWRITE RULES & QUERY VARS (V75)
// ===========================================================================

add_action('init', function() {
    // 1. Regional Service Pages: /regions/{state}/{region}/{service}/
    // e.g. /regions/nsw/new-england/dog-walking/
    add_rewrite_rule(
        '^regions/([^/]+)/([^/]+)/([^/]+)/?$',
        'index.php?mps_state=$matches[1]&mps_region_slug=$matches[2]&mps_service_slug=$matches[3]',
        'top'
    );
    
    // 2. Rural Hub Pages: /regions/{state}/rural-pet-sitters/
    // e.g. /regions/nsw/rural-pet-sitters/
    add_rewrite_rule(
        '^regions/([^/]+)/rural-pet-sitters/?$',
        'index.php?mps_state=$matches[1]&mps_rural_hub=1',
        'top'
    );
});

add_filter('query_vars', function($vars) {
    $vars[] = 'mps_state';
    $vars[] = 'mps_region_slug';
    $vars[] = 'mps_service_slug';
    $vars[] = 'mps_rural_hub';
    return $vars;
});

add_action('template_redirect', function() {
    global $wp_query;
    
    // Handle Regional Service Pages
    if (get_query_var('mps_state') && get_query_var('mps_region_slug') && get_query_var('mps_service_slug')) {
        $state   = get_query_var('mps_state');
        $region  = get_query_var('mps_region_slug');
        $service = get_query_var('mps_service_slug');
        
        include(plugin_dir_path(__FILE__) . 'tmpl-regional.php');
        exit;
    }
    
    // Handle Rural Hub Pages
    if (get_query_var('mps_state') && get_query_var('mps_rural_hub')) {
        // PREVENT "HELLO WORLD":
        // The custom URL defaults to "Latest Posts" (is_home=true). We must override this.
        $wp_query->is_home = false;
        $wp_query->is_archive = false;
        $wp_query->is_page = true;
        $wp_query->is_singular = true;
        $wp_query->is_404 = false;
        
        // Create dummy post to satisfy theme loops
        $dummy = new stdClass();
        $dummy->ID = -999;
        $dummy->post_author = 1;
        $dummy->post_date = current_time('mysql');
        $dummy->post_date_gmt = current_time('mysql', 1);
        $dummy->post_title = 'Rural Sitters';
        $dummy->post_content = ''; // Empty content so theme outputs nothing extra
        $dummy->post_status = 'publish';
        $dummy->comment_status = 'closed';
        $dummy->ping_status = 'closed';
        $dummy->post_type = 'page';
        $dummy->filter = 'raw';
        
        global $post; 
        $post = new WP_Post($dummy);
    // setup_postdata($post); // DISABLED: Safety (see auth fix)
        
        $wp_query->post = $post;
        $wp_query->posts = [$post];
        
        include(plugin_dir_path(__FILE__) . 'tmpl-rural.php');
        exit;
    }
    
    // Handle Sitter Search (V84)
    // Handle Sitter Search (V84)
    $pt = get_query_var('post_type');
    if (is_search() && ( $pt === 'sitter' || (is_array($pt) && in_array('sitter', $pt)) )) {
        include(plugin_dir_path(__FILE__) . 'tmpl-search.php');
        exit;
    }
});

add_shortcode('mps_page', 'antigravity_v200_render_page');
function antigravity_v200_render_page($atts) {
    $a = shortcode_atts(['city' => '', 'service' => ''], $atts, 'mps_page');
    $city    = trim($a['city']);
    $service = trim($a['service']);
    
    if (!$city && !$service) {
        return '<p>Shortcode needs at least a city or a service.</p>';
    }
    
    $cities = antigravity_v200_cities_list();
    $services = antigravity_v200_services_map();
    $blurbs = [
        'Dog Walking'     => 'Local dog walkers offering reliable 30-60 min walks with photo updates.',
        'Overnight Stays' => 'In-home boarding or a sitter at your place - message before booking.',
        'Daycare'         => 'Safe daytime care with play and rest in verified sitter homes.',
        'Home Visits'     => 'Pop-in visits for feeding, meds and welfare checks while you are out.',
    ];
    
    $esc  = function($s) { return esc_html($s); };
    $slug = function($s) { return sanitize_title($s); };
    
    ob_start();
    echo '<section class="mps-wrap" style="max-width:1100px;margin:0 auto;padding:24px 16px;">';
    
    // CITY + SERVICE PAGE
    if ($city && $service) {
        $citySlug = $slug($city);
        $svcSlug = $services[$service] ?? $slug($service);
        ?>
        <p class="eyebrow" style="text-transform:uppercase;font-weight:600;margin:0 0 8px;color:var(--mps-teal, #0d7377);">Trusted Local Network</p>
        <h1 style="margin:.25rem 0 8px;"><?= $esc($service) ?> in <?= $esc($city) ?></h1>
        <p class="lead">Find trusted <?= strtolower($esc($service)) ?> in <?= $esc($city) ?>. Compare profiles, read genuine reviews, and meet your sitter before you book.</p>
        
        <p class="mps-breadcrumb" style="margin:0 0 16px;">
            <a href="/cities/<?= $citySlug ?>/" style="color:var(--mps-teal, #0d7377);text-decoration:none;">&larr; Back to <?= $esc($city) ?></a> &middot;
            <a href="/services/<?= $svcSlug ?>/" style="color:var(--mps-teal, #0d7377);text-decoration:none;">About <?= $esc($service) ?></a>
        </p>
        
        <div class="wp-block-buttons" style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin:12px 0 24px;">
            <div class="wp-block-button"><a class="wp-block-button__link" href="#mps-sitter-grid" style="background:var(--mps-teal, #0d7377);border-radius:50px !important;">View Sitters</a></div>
            <div class="wp-block-button is-style-outline"><a class="wp-block-button__link" href="/list-your-services/" style="border-radius:50px !important;color:var(--mps-teal, #0d7377);border-color:var(--mps-teal, #0d7377);">List Your Services</a></div>
        </div>
        
        <?php
        echo antigravity_v200_render_sitter_grid($city, $service);
        ?>
        
        <h2>About <?= $esc($service) ?> in <?= $esc($city) ?></h2>
        <p><?= $esc($blurbs[$service] ?? '') ?></p>
        
        <h3>Other services in <?= $esc($city) ?></h3>
        <div style="display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin:16px 0 32px;">
            <?php foreach ($services as $name => $sSlug): if ($name === $service) continue; ?>
                <a href="/cities/<?= $citySlug ?>/<?= $sSlug ?>/" style="padding:10px 20px;background:#eef6f6;border-radius:50px;text-decoration:none;color:var(--mps-teal, #0d7377);font-weight:600;transition:all 0.2s;"><?= $esc($name) ?></a>
            <?php endforeach; ?>
        </div>
        
        <h3>Top cities for <?= $esc($service) ?></h3>
        <p class="mps-inline">
            <?php foreach ($cities as $c): ?>
                <a href="/cities/<?= $slug($c) ?>/<?= $svcSlug ?>/" style="padding:10px 20px;background:#e8f0ea;border-radius:50px;text-decoration:none;color:#4a7c59;font-weight:600;transition:all 0.2s;"><?= $esc($c) ?></a>
            <?php endforeach; ?>
        </p>
        
        <?= antigravity_v200_render_faqs($city, $service); ?>
        
    <?php
    // CITY ONLY PAGE
    } elseif ($city) {
        $citySlug = $slug($city);
        ?>
        <p class="eyebrow" style="text-transform:uppercase;font-weight:600;margin:0 0 8px;color:var(--mps-teal, #0d7377);">Trusted Local Network</p>
        <h1>Pet Sitters in <?= $esc($city) ?></h1>
        <p>Find daycare, dog walking, overnight stays and home visits across <?= $esc($city) ?>. Browse profiles and read reviews from other pet owners.</p>
        
        <h2>Browse services in <?= $esc($city) ?></h2>
        <div style="display:flex;flex-wrap:wrap;gap:12px;justify-content:center;margin:16px 0 32px;">
            <?php foreach ($services as $name => $sSlug): ?>
                <a href="/cities/<?= $citySlug ?>/<?= $sSlug ?>/" style="padding:12px 24px;background:#eef6f6;border-radius:50px;text-decoration:none;color:var(--mps-teal, #0d7377);font-weight:600;transition:all 0.2s;"><?= $esc($name) ?></a>
            <?php endforeach; ?>
        </div>
        
        <?php echo antigravity_v200_render_sitter_grid($city, ''); ?>
        
        <h3>Explore other cities</h3>
        <div style="display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin:16px 0 32px;">
            <?php foreach ($cities as $c): if ($c === $city) continue; ?>
                <a href="/cities/<?= $slug($c) ?>/" style="padding:10px 20px;background:#e8f0ea;border-radius:50px;text-decoration:none;color:#4a7c59;font-weight:600;transition:all 0.2s;"><?= $esc($c) ?></a>
            <?php endforeach; ?>
        </div>
        
        <?= antigravity_v200_render_faqs($city, ''); ?>
        
    <?php
    // SERVICE ONLY PAGE
    } elseif ($service) {
        $svcSlug = $services[$service] ?? $slug($service);
        ?>
        <p class="eyebrow" style="text-transform:uppercase;font-weight:600;margin:0 0 8px;color:var(--mps-teal, #0d7377);">Trusted Local Network</p>
        <h1><?= $esc($service) ?> in Australia</h1>
        <p><?= $esc($blurbs[$service] ?? '') ?></p>
        
        <h2>Top cities</h2>
        <div style="display:flex;flex-wrap:wrap;gap:12px;justify-content:center;margin:16px 0 32px;">
            <?php foreach ($cities as $c): ?>
                <a href="/cities/<?= $slug($c) ?>/<?= $svcSlug ?>/" style="padding:12px 24px;background:#e8f0ea;border-radius:50px;text-decoration:none;color:#4a7c59;font-weight:600;transition:all 0.2s;"><?= $esc($c) ?></a>
            <?php endforeach; ?>
        </div>
        
        <h3>Browse other services</h3>
        <div style="display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin:16px 0 32px;">
            <?php foreach ($services as $name => $sSlug): if ($name === $service) continue; ?>
                <a href="/services/<?= $sSlug ?>/" style="padding:10px 20px;background:#eef6f6;border-radius:50px;text-decoration:none;color:var(--mps-teal, #0d7377);font-weight:600;transition:all 0.2s;"><?= $esc($name) ?></a>
            <?php endforeach; ?>
        </div>
        
        <?= antigravity_v200_render_faqs('', $service); ?>
        
    <?php } ?>
    
    </section>
    <?php
    return ob_get_clean();
}

// ===========================================================================
// SECTION 1B: [MPS_CITIES] SHORTCODE - Cities Index Page
// ===========================================================================

// Converted to named function (V120 Fix)
function antigravity_v200_render_cities($atts) {
    $cities = antigravity_v200_cities_list();
    $services = antigravity_v200_services_map();
    
    ob_start();
    ?>
    <section class="mps-wrap" style="max-width:1100px;margin:0 auto;padding:24px 16px;text-align:center;">
        <p class="eyebrow" style="text-transform:uppercase;font-weight:600;margin:0 0 8px;color:var(--mps-teal, #0d7377);">Trusted Local Network</p>
        <h1>Find Pet Sitters by City</h1>
        <style>
        .mps-city-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease !important;
            border: 1px solid #d1d9e6 !important;
            border-top: 4px solid #d1d9e6 !important;
        }
        .mps-city-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.15) !important;
            border-color: var(--mps-teal, #0d7377) !important; 
            border-top-color: var(--mps-teal, #0d7377) !important;
        }
        .mps-city-card:hover h3 {
            color: var(--mps-teal, #0d7377) !important;
        }
    </style>
    <p class="lead">Browse trusted pet sitters across Australia's major cities. Compare profiles, read reviews, and book with confidence.</p>
        
    <div class="mps-city-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:24px;margin:32px 0;">
            <?php foreach ($cities as $city): 
                $slug = sanitize_title($city);
            ?>
            <a href="/cities/<?= $slug ?>/" class="mps-city-card" style="display:block;padding:24px;background:#fff;border:1px solid #eaeaea;border-radius:12px;text-decoration:none;transition:all 0.2s;">
                <h3 style="margin:0 0 8px;color:#333;"><?= esc_html($city) ?></h3>
                <p style="margin:0;font-size:0.9em;color:#666;">Pet sitters in <?= esc_html($city) ?></p>
            </a>
            <?php endforeach; ?>
        </div>
        
        <h2>Browse by Service</h2>
        <p>Looking for a specific service? Browse our service categories:</p>
        <div class="mps-services-list" style="display:flex;flex-wrap:wrap;gap:12px;justify-content:center;margin:16px 0 32px;">
            <?php foreach ($services as $name => $slug): ?>
            <a href="/services/<?= $slug ?>/" style="padding:10px 20px;background:#eef6f6;border-radius:50px;text-decoration:none;color:var(--mps-teal, #0d7377);font-weight:600;"><?= esc_html($name) ?></a>
            <?php endforeach; ?>
        </div>
        
        <div style="background:#eef6f6;padding:32px;border-radius:20px;text-align:center;margin-top:32px;">
            <h3 style="margin:0 0 12px;color:var(--mps-teal, #0d7377);">Are You a Pet Sitter?</h3>
            <p style="margin:0 0 16px;">List your services for free and connect with pet owners in your area.</p>
            <a href="/list-your-services/" class="wp-block-button__link" style="display:inline-block;background:var(--mps-teal, #0d7377);border-radius:50px;">List Your Services</a>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
// Register Shortcode (V120 Fix)
add_shortcode('mps_cities', 'antigravity_v200_render_cities');

// ===========================================================================
// SECTION 1C: [MPS_SERVICES] SHORTCODE - Services Index Page
// ===========================================================================

// Converted to named function (V120 Fix)
// Converted to named function (V120 Fix)
function antigravity_v200_render_services($atts) {
    $cities = antigravity_v200_cities_list();
    $services = antigravity_v200_services_map();
    $blurbs = [
        'Dog Walking'     => 'Local dog walkers offering reliable 30-60 min walks with photo updates.',
        'Overnight Stays' => 'In-home boarding or a sitter at your place - message before booking.',
        'Daycare'         => 'Safe daytime care with play and rest in trusted sitter homes.',
        'Home Visits'     => 'Pop-in visits for feeding, meds and welfare checks while you are out.',
    ];
    
    ob_start();
    ?>
    <section class="mps-wrap" style="max-width:1100px;margin:0 auto;padding:24px 16px;text-align:center;">
        <p class="eyebrow" style="text-transform:uppercase;font-weight:600;margin:0 0 8px;color:var(--mps-teal, #0d7377);">Trusted Local Network</p>
        <h1>Pet Care Services</h1>
        <p class="lead">Find the perfect care for your pet. Browse our trusted services below.</p>
        
        <style>
        .mps-service-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease !important;
            border: 1px solid #d1d9e6 !important;
            border-top: 4px solid #d1d9e6 !important;
        }
        .mps-service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.15) !important;
            border-color: var(--mps-teal, #0d7377) !important; 
            border-top-color: var(--mps-teal, #0d7377) !important;
        }
        .mps-service-card:hover h3 {
            color: var(--mps-teal, #0d7377) !important;
        }
    </style>
    <div class="mps-service-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:24px;margin:32px 0;">
            <?php foreach ($services as $name => $slug): ?>
            <a href="/services/<?= $slug ?>/" class="mps-service-card" style="display:block;padding:24px;background:#fff;border:1px solid #eaeaea;border-radius:12px;text-decoration:none;transition:all 0.2s;">
                <h3 style="margin:0 0 12px;color:#333;"><?= esc_html($name) ?></h3>
                <p style="margin:0;font-size:0.9em;color:#666;line-height:1.5;"><?= esc_html($blurbs[$name] ?? '') ?></p>
            </a>
            <?php endforeach; ?>
        </div>
        
        <h2>Browse by City</h2>
        <p>Find pet care services in your city:</p>
        <div class="mps-cities-list" style="display:flex;flex-wrap:wrap;gap:12px;justify-content:center;margin:16px 0 32px;">
            <?php foreach ($cities as $city): 
                $slug = sanitize_title($city);
            ?>
            <a href="/cities/<?= $slug ?>/" style="padding:10px 20px;background:#e8f0ea;border-radius:50px;text-decoration:none;color:#4a7c59;font-weight:600;"><?= esc_html($city) ?></a>
            <?php endforeach; ?>
        </div>
        
        <div style="background:#eef6f6;padding:32px;border-radius:20px;text-align:center;margin-top:32px;">
            <h3 style="margin:0 0 12px;color:var(--mps-teal, #0d7377);">Offer Pet Care Services?</h3>
            <p style="margin:0 0 16px;">Join our community of trusted pet sitters. It's free to list.</p>
            <a href="/list-your-services/" class="wp-block-button__link" style="display:inline-block;background:var(--mps-teal, #0d7377);border-radius:50px;">List Your Services</a>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
// Register BOTH names (V120 Fix) - Fixes "Sitter List" pages
// Register Shortcode (V121 Fix)
add_shortcode('mps_services', 'antigravity_v200_render_services');

// ===========================================================================
// SECTION 2: SITTER GRID RENDERER
// ===========================================================================

function antigravity_v200_render_sitter_grid($city, $service = '', $limit = 12) {
    if (!post_type_exists('sitter')) {
        return '<div style="background:var(--mps-primary-light, #e8f0ea);padding:24px;border-radius:12px;text-align:center;margin:24px 0;">
            <p style="margin:0 0 8px;font-size:1.1em;"><strong>Coming Soon!</strong></p>
            <p style="margin:0 0 16px;opacity:.8;">We\'re growing! Listings coming soon.</p>
            <a href="/list-your-services/" class="wp-block-button__link" style="display:inline-block;">Be the first to list your services</a>
        </div>';
    }
    
    $args = [
        'post_type'      => 'sitter',
        'post_status'    => 'publish',
        'posts_per_page' => $limit,
    ];
    
    // Build tax query
    $tax_query = [];
    if ($city && taxonomy_exists('mps_city')) {
        $tax_query[] = [
            'taxonomy' => 'mps_city',
            'field'    => 'name',
            'terms'    => $city,
        ];
    }
    if ($service && taxonomy_exists('mps_service')) {
        $tax_query[] = [
            'taxonomy' => 'mps_service',
            'field'    => 'name',
            'terms'    => $service,
        ];
    }
    if (count($tax_query) > 1) {
        $tax_query['relation'] = 'AND';
    }
    if ($tax_query) {
        $args['tax_query'] = $tax_query;
    }
    
    $q = new WP_Query($args);
    
    if (!$q->have_posts()) {
        $context = $service ?: 'pet sitters';
        $location = $city ? " in {$city}" : '';
        $msg = "We're growing! No {$context} listed yet{$location}.";
        return '<div style="background:var(--mps-primary-light, #e8f0ea);padding:24px;border-radius:12px;text-align:center;margin:24px 0;">
            <p style="margin:0 0 8px;font-size:1.1em;"><strong>Coming Soon!</strong></p>
            <p style="margin:0 0 16px;opacity:.8;">' . esc_html($msg) . '</p>
            <a href="/list-your-services/" class="wp-block-button__link" style="display:inline-block;">Be the first to list your services</a>
        </div>';
    }
    
    ob_start();
    $heading = $service 
        ? esc_html($service) . ' Sitters' . ($city ? ' in ' . esc_html($city) : '')
        : 'Local Sitters' . ($city ? ' in ' . esc_html($city) : '');
    // CSS for Modern Cards on these pages 
    echo '<style>
    .mps-sitter-grid-modern { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px; }
    .mps-sitter-card-modern { background: #fff; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: transform 0.3s, box-shadow 0.3s; border:1px solid #eee; }
    .mps-sitter-card-modern:hover { transform: translateY(-6px); box-shadow: 0 12px 40px rgba(0,0,0,0.12); border-color:var(--mps-teal, #0d7377); }
    .mps-sitter-photo { width: 100%; height: 200px; object-fit: cover; background: linear-gradient(135deg, #e0f4f4, #f5f3f0); }
    .mps-sitter-info { padding: 20px; }
    .mps-sitter-name { font-size: 1.25rem; font-weight: 600; color: #1a2b3c; margin: 0 0 4px; }
    .mps-sitter-location { color: #5a6b7c; font-size: 14px; margin: 0 0 12px; display: flex; align-items: center; gap: 4px; }
    .mps-sitter-badges { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; }
    .mps-badge { background: #e0f4f4; color: #0d7377; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 500; }
    .mps-badge.coral { background: #ffe5e5; color: #ff6b6b; }
    .mps-sitter-price { display: flex; justify-content: space-between; align-items: center; margin-top: 12px; padding-top: 12px; border-top: 1px solid #eee; }
    .mps-sitter-price .price { font-size: 1.1rem; font-weight: 600; color: #0d7377; }
    .mps-sitter-price .label { font-size: 12px; color: #5a6b7c; }
    .mps-view-btn { background: #0d7377 !important; color: #fff !important; padding: 10px 20px !important; border-radius: 25px !important; font-size: 14px !important; font-weight: 500 !important; text-decoration: none !important; transition: background 0.2s !important; }
    .mps-view-btn:hover { background: #095456 !important; }
    </style>';

    echo '<div class="mps-results" id="mps-sitter-grid"><h2>' . $heading . '</h2>';
    echo '<div class="mps-sitter-grid-modern">';
    
    while ($q->have_posts()) {
        $q->the_post();
        $ID = get_the_ID();
        
        $city_meta = get_post_meta($ID, 'mps_city', true);
        $suburb_meta = get_post_meta($ID, 'mps_suburb', true);
        $services_list = get_post_meta($ID, 'mps_services', true);
        $services_arr = array_map('trim', explode(',', $services_list));
        $thumb = antigravity_v200_get_sitter_thumbnail($ID);
        
        // Logic to find the LOWEST price across all services
        $price = 0;
        $min_price = 99999;
        
        // Scan all potential services for this sitter
        $all_services = antigravity_v200_services_map(); 
        
        // V77 Price Fix: Iterate all services and check for ANY valid price
        // We do not rely solely on 'mps_services' array because it might be out of sync or have whitespace.
        // If a price exists > 0, we count it.
        
        foreach ($all_services as $svc_label => $svc_slug) {
            $p_key = 'mps_price_' . $svc_slug;
            $p_val = get_post_meta($ID, $p_key, true);
            
            if ($p_val && is_numeric($p_val) && $p_val > 0) {
                 if ($p_val < $min_price) {
                     $min_price = $p_val;
                 }
            }
        }
        
        if ($min_price < 99999) {
            $price = $min_price;
        } else {
            $price = ''; // No valid prices found
        }
        $name = get_the_title();

        ?>
        <div class="mps-sitter-card-modern">
            <?php if ($thumb): ?>
                <img src="<?= esc_url($thumb) ?>" alt="<?= esc_attr($name) ?>" class="mps-sitter-photo">
            <?php else: ?>
                <div class="mps-sitter-photo" style="display:flex;align-items:center;justify-content:center;color:#999;">No photo</div>
            <?php endif; ?>
            
            <div class="mps-sitter-info">
                <h3 class="mps-sitter-name"><a href="<?= get_permalink() ?>" style="text-decoration:none;color:inherit;"><?= esc_html($name) ?></a></h3>
                <?php
                // V104: PREFER REGION DISPLAY
                $region_term = wp_get_post_terms($ID, 'mps_region', ['fields' => 'names']);
                $region_name = (!empty($region_term) && !is_wp_error($region_term)) ? $region_term[0] : '';
                
                $location_display = $city_meta;
                if ($suburb_meta) {
                    // If Region exists, show Suburb, Region (e.g. Tamworth, New England)
                    if ($region_name) {
                        $location_display = "$suburb_meta, $region_name";
                    } else {
                        $location_display = "$suburb_meta, $city_meta";
                    }
                } elseif ($region_name) {
                    $location_display = $region_name;
                }
                ?>
                <p class="mps-sitter-location">
                    📍 <?= esc_html($location_display) ?>
                </p>
                
                <div class="mps-sitter-badges">
                    <?php 
                    $count = 0;
                    foreach ($services_arr as $svc): 
                        if ($count >= 2) break;
                        $svc = trim($svc);
                        if (!$svc) continue;
                    ?>
                        <span class="mps-badge"><?= esc_html($svc) ?></span>
                    <?php 
                        $count++;
                    endforeach; 
                    if (count($services_arr) > 2): ?>
                        <span class="mps-badge coral">+<?= count($services_arr) - 2 ?> more</span>
                    <?php endif; ?>
                </div>
                
                <div class="mps-sitter-price">
                    <div>
                        <?php if ($price): ?>
                            <span class="price">From $<?= esc_html($price) ?></span>
                        <?php else: ?>
                            <span class="label">Contact for pricing</span>
                        <?php endif; ?>
                    </div>
                    <a href="<?= get_permalink($ID) ?>" class="mps-view-btn">View</a>
                </div>
            </div>
        </div>
        <?php
    }
    wp_reset_postdata();
    
    echo '</div></div>';
    return ob_get_clean();
}



// ===========================================================================
// SECTION 3: FAQ RENDERER
// ===========================================================================

function antigravity_v200_render_faqs($city = '', $service = '', $mode = 'preview') {
    $all_faqs = [
        'general' => [
            ['q' => "How do I ensure a sitter is trustworthy?", 'a' => 'We encourage all owners to read reviews, check verified badges, and always organize a configured Meet & Greet before booking.'],
            ['q' => "What happens if I need to cancel?", 'a' => 'Each sitter sets their own cancellation policy. Check their profile for details before booking.'],
            ['q' => "Is there insurance included?", 'a' => 'We are working on comprehensive insurance. Currently, check with your specific sitter regarding their own coverage.'],
            ['q' => "How do I pay?", 'a' => 'My Pet Sitters is a directory service. All payments are arranged directly between you and the sitter.'],
            ['q' => "Can I meet the sitter first?", 'a' => 'Yes! We highly recommend a Meet & Greet to ensure your pet is comfortable with the sitter.'],
        ],
        'city_specific' => [],
        'service_specific' => []
    ];

    if ($service && $city) {
        $all_faqs['specific'] = [
            ['q' => "How much does {$service} cost in {$city}?", 'a' => 'Rates vary by sitter. Compare profiles to find one that fits your budget.'],
            ['q' => "What areas of {$city} do sitters cover?", 'a' => 'Most sitters list their suburb and travel range in their profile.'],
            ['q' => "How do I book {$service} in {$city}?", 'a' => 'Pick your service, enter your suburb, compare trusted local profiles, then book.'],
        ];
    } elseif ($city) {
        $all_faqs['specific'] = [
            ['q' => "How do I find a pet sitter in {$city}?", 'a' => 'Browse sitters by service type above, then compare profiles and reviews.'],
            ['q' => "What services are available in {$city}?", 'a' => 'Dog walking, overnight stays, daycare, and home visits.'],
        ];
    } elseif ($service) {
        $all_faqs['specific'] = [
            ['q' => "What is {$service}?", 'a' => 'Professional pet care service provided by trusted local sitters.'],
            ['q' => "How much does {$service} cost?", 'a' => 'Rates vary by city and sitter. Compare profiles for pricing.'],
            ['q' => "Is {$service} available in my area?", 'a' => 'We cover major Australian cities. Check your city page.'],
        ];
    }
    
    // Merge FAQs: Specific first, then General
    $faqs = array_merge($all_faqs['specific'] ?? [], $all_faqs['general']);
    
    if ($mode === 'preview') {
        $faqs = array_slice($faqs, 0, 3); // Show only top 3
    }
    
    if (!$faqs) return '';
    
    ob_start();
    ?>
    <div class="mps-faqs" style="margin:32px 0;">
        <h2><?= ($mode === 'full') ? 'Frequently Asked Questions' : 'FAQs' ?></h2>
        <?php foreach ($faqs as $faq): ?>
            <details style="border:1px solid #eaeaea;border-radius:12px;margin-bottom:12px;padding:16px 20px;background:#fff;transition:all 0.2s;">
                <summary style="cursor:pointer;font-weight:600;color:var(--mps-teal, #0d7377);outline:none;"><?= esc_html($faq['q']) ?></summary>
                <p style="margin:12px 0 0;color:#555;line-height:1.6;"><?= esc_html($faq['a']) ?></p>
            </details>
        <?php endforeach; ?>
        
        <?php if ($mode === 'preview'): ?>
            <div style="text-align:center;margin-top:20px;">
                <a href="/faqs/" style="color:var(--mps-teal, #0d7377);font-weight:600;text-decoration:none;">View all FAQs &rarr;</a>
            </div>
        <?php endif; ?>
    </div>
    
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            <?php foreach ($faqs as $i => $faq): ?>
            {
                "@type": "Question",
                "name": <?= json_encode($faq['q']) ?>,
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": <?= json_encode($faq['a']) ?>
                }
            }<?= $i < count($faqs) - 1 ? ',' : '' ?>
            <?php endforeach; ?>
        ]
    }
    </script>
    <?php
    return ob_get_clean();
}

/**
 * [mps_faq_full] Shortcode
 */
add_shortcode('mps_faq_full', function($atts) {
    return '<section class="mps-wrap" style="max-width:800px;margin:0 auto;padding:40px 20px;">' . 
           antigravity_v200_render_faqs('', '', 'full') . 
           '</section>';
});

// ===========================================================================
// SECTION 4: [MPS_ABOUT] SHORTCODE
// ===========================================================================

add_shortcode('mps_about', function($atts) {
    ob_start();
    ?>
    <style>
        /* Hide default page title for this page */
        .page .entry-header, .page .entry-title { display: none !important; }
        
        .mps-about-hero {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 60px;
            color: #fff;
            text-align: center;
            background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('/wp-content/uploads/2025/12/about-joy.jpg');
            background-size: cover;
            background-position: center;
            padding: 100px 20px;
        }
        .mps-about-hero h1 {
            font-size: 3.5rem;
            color: #fff;
            margin-bottom: 24px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        .mps-about-hero .lead {
            font-size: 1.4rem;
            max-width: 700px;
            margin: 0 auto;
            color: #f0f0f0;
            line-height: 1.6;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .mps-values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin: 60px 0;
        }
        .mps-value-card {
            background: #fff;
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: transform 0.3s;
            border: 1px solid #eee;
        }
        .mps-value-card:hover { transform: translateY(-5px); }
        .mps-value-icon {
            font-size: 40px;
            margin-bottom: 24px;
            display: inline-block;
            background: var(--mps-cream, #faf8f5);
            width: 80px;
            height: 80px;
            line-height: 80px;
            border-radius: 50%;
            color: var(--mps-teal, #0d7377);
        }
        .mps-value-card h3 { margin-bottom: 12px; color: var(--mps-text-dark, #1a2b3c); }
        .mps-value-card p { color: #666; line-height: 1.6; }
    </style>

    <section class="mps-about" style="max-width:1000px;margin:0 auto;padding:40px 20px;">
        
        <!-- 3. The Story Hero (Replaces plain header + stats) -->
        <div class="mps-about-hero">
            <h1>We Connect Pet Lovers</h1>
            <p class="lead">My Pet Sitters was built on a simple promise: to help every pet parent find a trusted, local animal lover who will treat their best friend like family.</p>
        </div>
        
        <!-- 1. Our Values Grid -->
        <h2 style="text-align:center;font-size:2.2rem;margin-bottom:40px;">Why We're Different</h2>
        <div class="mps-values-grid">
            <div class="mps-value-card">
                <div class="mps-value-icon">🛡️</div>
                <h3>Safety First</h3>
                <p>Every sitter is a verified animal lover. Meet & Greets are always encouraged so you can get to know who you're engaging with and don't have to compromise on safety for your fur baby.</p>
            </div>
            <div class="mps-value-card">
                <div class="mps-value-icon">📍</div>
                <h3>Truly Local</h3>
                <p>We connect you with neighbours, not anonymous contractors. Build a relationship with someone nearby.</p>
            </div>
            <div class="mps-value-card">
                <div class="mps-value-icon">🤝</div>
                <h3>Community Built</h3>
                <p>We take 0% commission from our sitters, helping them build their own small business with fair rates.</p>
            </div>
        </div>
        
        <!-- Bottom CTA -->
        <div style="text-align:center;margin-top:60px;background:var(--mps-cream, #faf8f5);padding:60px;border-radius:20px;">
            <h2 style="margin-bottom:16px;">Ready to join the pack?</h2>
            <p style="margin-bottom:32px;font-size:1.1rem;color:#666;">Create your profile today and start connecting.</p>
            <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;">
                <a href="/index.php#mps-sitter-grid" class="wp-block-button__link" style="background:var(--mps-teal, #0d7377) !important;border-radius:50px;padding:14px 32px;text-align:center;min-width:200px;">Find a Sitter</a>
                <a href="/list-your-services/" class="wp-block-button__link" style="background:#fff !important;color:var(--mps-teal, #0d7377) !important;border:1px solid var(--mps-teal, #0d7377);border-radius:50px;padding:14px 32px;text-align:center;min-width:200px;">List Your Services</a>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
});

// ===========================================================================
// SECTION 5: SEO INTEGRATION (Rank Math)
// ===========================================================================

add_filter('rank_math/frontend/title', function($title) {
    if (!is_page()) return $title;
    
    $content = get_post_field('post_content', get_the_ID());
    if (preg_match('/\[mps_page\s+city="([^"]+)"\s+service="([^"]+)"\]/', $content, $m)) {
        return "{$m[2]} in {$m[1]} | My Pet Sitters";
    }
    if (preg_match('/\[mps_page\s+city="([^"]+)"\]/', $content, $m)) {
        return "Pet Sitters in {$m[1]} | My Pet Sitters";
    }
    if (preg_match('/\[mps_page\s+service="([^"]+)"\]/', $content, $m)) {
        return "{$m[1]} Australia | My Pet Sitters";
    }
    return $title;
});

add_filter('rank_math/frontend/description', function($desc) {
    if (!is_page()) return $desc;
    
    $content = get_post_field('post_content', get_the_ID());
    if (preg_match('/\[mps_page\s+city="([^"]+)"\s+service="([^"]+)"\]/', $content, $m)) {
        return "Find {$m[2]} in {$m[1]}. Compare verified pet sitters, read reviews and book securely.";
    }
    if (preg_match('/\[mps_page\s+city="([^"]+)"\]/', $content, $m)) {
        return "Find pet sitters in {$m[1]} for daycare, dog walking, overnight stays and home visits.";
    }
    return $desc;
});

// ===========================================================================
// SECTION 6: TAXONOMY TERM LINK REWRITING
// ===========================================================================

add_filter('term_link', function($url, $term, $taxonomy) {
    if ($taxonomy === 'mps_city') {
        return home_url('/cities/' . $term->slug . '/');
    }
    if ($taxonomy === 'mps_service') {
        return home_url('/services/' . $term->slug . '/');
    }
    return $url;
}, 10, 3);

// Force pages to take precedence over taxonomy archives
add_action('template_redirect', function() {
    if (is_tax('mps_city') || is_tax('mps_service')) {
        $term = get_queried_object();
        if ($term && isset($term->slug)) {
            $tax = is_tax('mps_city') ? 'cities' : 'services';
            wp_safe_redirect(home_url("/{$tax}/{$term->slug}/"), 301);
            exit;
        }
    }
});

// ===========================================================================
// SECTION 7: REGIONS INDEX SHORTCODE [mps_regions]
// ===========================================================================

add_shortcode('mps_regions', function($atts) {
    // Group Locations by State
    $locations = antigravity_v200_get_valid_locations();
    $states_map = antigravity_v200_states_list();
    $regions_by_state = antigravity_v200_get_valid_regions(); // Fetch the region data
    $groups = [];
    
    foreach ($states_map as $code => $name) {
        $groups[$code] = [
            'name' => $name,
            'regions' => $regions_by_state[$code] ?? [] // Populate regions
        ];
    }
    
    ob_start();
    ?>
    <section class="mps-wrap" style="max-width:1100px;margin:0 auto;padding:40px 20px;">
        <p class="eyebrow" style="text-transform:uppercase;font-weight:600;margin:0 0 8px;color:var(--mps-teal, #0d7377);">Australia Wide</p>
        <h1 style="margin-bottom:32px;">Browse Pet Sitters by Region</h1>
        
        <div class="mps-state-grid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(300px, 1fr));gap:32px;">
            <?php foreach ($groups as $code => $data): ?>
            <div class="mps-state-card" style="background:#fff;border:1px solid #eee;border-radius:12px;padding:24px;">
                <h2 style="font-size:1.5rem;margin:0 0 16px;border-bottom:2px solid #e8f0ea;padding-bottom:12px;"><?= esc_html($data['name']) ?></h2>
                
                <h4 style="margin:0 0 12px;color:#666;font-size:0.95rem;text-transform:uppercase;letter-spacing:1px;">Rural & Farm</h4>
                <ul style="list-style:none;margin:0 0 24px;padding:0;">
                    <li>
                        <a href="/regions/<?= strtolower($code) ?>/rural-pet-sitters/" style="display:flex;align-items:center;background:#f8faf9;padding:12px;border-radius:8px;text-decoration:none;color:#2e7d32;font-weight:600;border:1px solid #e5ebe7;">
                            <span style="margin-right:8px;">🚜</span> Rural <?= esc_html($code) ?> Hub
                        </a>
                    </li>
                </ul>

                <?php if (!empty($data['regions'])): ?>
                <h4 style="margin:0 0 12px;color:#666;font-size:0.95rem;text-transform:uppercase;letter-spacing:1px;">Regions</h4>
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:24px;">
                    <?php foreach ($data['regions'] as $region): 
                        $r_slug = sanitize_title($region);
                    ?>
                    <a href="/regions/<?= $r_slug ?>/" style="display:inline-block;padding:8px 16px;background:#eef6f6;color:#0d7377;text-decoration:none;border-radius:20px;font-size:0.9rem;font-weight:600;transition:all 0.2s;">
                        <?= esc_html($region) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <?php
                // V85: Dynamic State Examples
                $examples = [
                    'NSW' => 'Dubbo, Tamworth, or Orange',
                    'VIC' => 'Geelong, Ballarat, or Bendigo',
                    'QLD' => 'Toowoomba, Mackay, or Cairns',
                    'WA'  => 'Bunbury, Albany, or Kalgoorlie',
                    'SA'  => 'Mount Gambier, Whyalla, or Gawler',
                    'TAS' => 'Launceston, Devonport, or Burnie',
                    'ACT' => 'Canberra, Belconnen, or Tuggeranong',
                    'NT'  => 'Alice Springs, Katherine, or Palmerston'
                ];
                $city_text = $examples[$code] ?? 'your local town';
                ?>
                <div style="font-size:0.9rem;color:#666;">
                    <p><em>Check the main search bar to find sitters in specific towns like <?= esc_html($city_text) ?>.</em></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="margin-top:60px;text-align:center;background:#eef6f6;padding:40px;border-radius:20px;">
            <h3>Don't see your location?</h3>
            <p>We are expanding rapidly across Australia. <a href="/list-your-services/">List your services</a> to create a new regional hub.</p>
        </div>
    </section>
    <?php
    return ob_get_clean();
});

