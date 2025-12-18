<?php
/**
 * MPS CORE - Constants, Helpers, CPT & Taxonomies
 * 
 * Priority: CRITICAL - Must be active for all other MPS snippets to work
 * 
 * This snippet provides:
 * - Configuration constants
 * - Helper functions used throughout
 * - Sitter custom post type registration
 * - City and Service taxonomy registration
 * - Pro (sitter) role creation
 */

if (!defined('ABSPATH')) exit;

// Include Master Locations List (V76)
$mps_loc_file = plugin_dir_path(__FILE__) . 'includes/mps-locations-au.php';
if (file_exists($mps_loc_file)) {
    require_once $mps_loc_file;
} else {
    // Fallback preventing fatal error if file missing
    if (!function_exists('antigravity_v200_get_valid_locations')) {
        function antigravity_v200_get_valid_locations() { return ['Sydney', 'Melbourne', 'Brisbane', 'Perth', 'Adelaide']; }
    }
}

// V111: Include Master Suburbs Validation List (Quarantine System) - REVERTED TO V110
/*
$mps_master_file = plugin_dir_path(__FILE__) . 'includes/mps-all-suburbs-master.php';
if (file_exists($mps_master_file)) {
    include_once $mps_master_file;
} else {
    // Fallback stub
    if (!function_exists('antigravity_v200_is_valid_suburb')) {
        function antigravity_v200_is_valid_suburb($s) { return $s; } 
    }
}
*/
if (!function_exists('antigravity_v200_is_valid_suburb')) {
    function antigravity_v200_is_valid_suburb($s) { return $s; } 
}

// POLYFILL: Ensure Regions function exists (if user has old includes file)
if (!function_exists('antigravity_v200_get_valid_regions')) {
    function antigravity_v200_get_valid_regions() { return []; }
}

// SECTION 1: CONSTANTS & CONFIGURATION
// ======================================

if (!defined('MPS_VERSION'))      define('MPS_VERSION', '2.0.0');
if (!defined('MPS_ADMIN_EMAIL'))  define('MPS_ADMIN_EMAIL', 'enquiries@mypetsitters.com.au');

// SECTION 1B: FRONTEND STYLES (MPS Color Scheme)
// ==============================================

// HIDE ADMIN BAR FOR NON-ADMINS (Sitters & Owners)
add_filter('show_admin_bar', function($show) {
    if (!current_user_can('administrator')) {
        return false;
    }
    return $show;
});

// FORCE MOBILE MENU TO USE PRIMARY MENU
add_filter('wp_nav_menu_args', function($args) {
    // Only target the mobile menu location (Astra uses 'mobile_menu' or falls back)
    // If the theme location is 'slide_in_menu', 'mobile_menu', or text domain matches 'astra'
    if ( isset($args['theme_location']) && ( $args['theme_location'] == 'mobile_menu' || $args['theme_location'] == 'handheld' ) ) {
        // Try to get the primary menu object
        $locations = get_nav_menu_locations();
        if ( isset($locations['primary']) ) {
            $args['menu'] = $locations['primary'];
        }
    }
    // Fallback: If no theme location is set (often happens with fallback page menus), 
    // try to force it if we are sure it's the mobile toggle.
    return $args;
});

add_filter('wp_page_menu_args', function($args) {
    // If we are falling back to page menu, try to switch to primary nav menu instead
    $locations = get_nav_menu_locations();
    if ( isset($locations['primary']) ) {
        $args['menu'] = $locations['primary'];
    }
    return $args;
});

add_action('wp_head', function() {
    ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
    :root {
        --mps-font: 'Outfit', sans-serif;

        --mps-primary: #4a7c59;
        --mps-primary-dark: #3d6a4a;
        --mps-primary-light: #e8f0ea;
        --mps-accent: #8fb996;
        --mps-text: #2c3e50;
        --mps-text-light: #6c757d;
        --mps-bg: #f8faf9;
        --mps-white: #ffffff;
        --mps-border: #e0e6e3;
        --mps-success: #28a745;
        --mps-warning: #ffc107;
        --mps-shadow: 0 2px 8px rgba(74, 124, 89, 0.12);
        --mps-shadow-lg: 0 8px 24px rgba(74, 124, 89, 0.15);
        --mps-radius: 12px;
        --mps-radius-sm: 8px;
        --mps-teal: #0d7377;
    }

    /* Apply Outfit font to most elements, but CAREFULLY exclude Site Title/Branding area */
    
    /* Body text */
    body {
        font-family: var(--mps-font) !important;
    }

    /* Headings & UI elements - Force Outfit UNLESS it's inside the Site Branding area */
    /* This ensures Astra Customizer settings for Site Title work on Mobile & Desktop */
    :not(.site-branding):not(.ast-site-identity) > h1,
    :not(.site-branding):not(.ast-site-identity) > h2,
    :not(.site-branding):not(.ast-site-identity) > h3,
    :not(.site-branding):not(.ast-site-identity) > h4,
    :not(.site-branding):not(.ast-site-identity) > h5,
    :not(.site-branding):not(.ast-site-identity) > h6,
    input, select, textarea, button {
        font-family: var(--mps-font) !important;
    }
    
    /* Explicitly un-force the title just in case */
    .site-title, .site-title a, .ast-site-identity * {
        font-family: inherit !important;
    }

    /* Header Styling - V29 (NO GREEN LINE + HERO FIX) */
    .site-header, 
    .ast-main-header-bar, 
    .main-header-bar, 
    .ast-primary-header-bar {
        /* Pastel color match */
        background: linear-gradient(90deg, rgba(74, 124, 89, 0.35) 0%, rgba(13, 115, 119, 0.35) 100%) !important;
        
        /* ENSURE NO GREEN LINE BELOW */
        border: none !important;
        border-bottom: none !important;
        border-top: none !important;
        box-shadow: none !important;
        
        /* FLUSH TO TOP - No green line above */
        position: relative !important;
        top: 0 !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important; /* REMOVED GAP to fix green line issue */
        border-bottom: 0 none transparent !important;
        
        /* REMOVE ALL PADDING */
        padding: 0 !important;
        
        width: 100% !important;
        z-index: 999 !important;
        min-height: 60px !important;
    }
    
    
    
    /* Site Title - NO FONT (customizable via Additional CSS) */
    .site-title a, 
    .site-branding .site-title a {
        /* NO font-family - user can set via Additional CSS */
        color: #ffffff !important;
        text-shadow: 
            0 3px 6px rgba(0, 0, 0, 0.4),
            0 6px 12px rgba(0, 0, 0, 0.25),
            0 -1px 0 rgba(255, 255, 255, 0.5),
            1px 1px 0 rgba(0, 0, 0, 0.15) !important;
    }
    
    /* Other header links - keep white with simpler shadow */
    .site-header a:not(.site-title a),
    .ast-main-header-bar a:not(.site-title a),
    .main-header-menu .menu-link {
        color: #ffffff !important;
        text-shadow: 0 1px 2px rgba(0,0,0,0.3) !important;
    }

    /* FIX DROPDOWN MENU VISIBILITY */
    .main-header-menu .sub-menu .menu-item .menu-link,
    .ast-header-break-point .main-header-bar .main-header-menu .sub-menu .menu-item .menu-link,
    .ast-desktop .main-header-menu .sub-menu .menu-link,
    ul.sub-menu li a,
    .sub-menu .menu-link {
        color: #333333 !important;
        text-shadow: none !important;
        background-color: #ffffff !important;
    }
    .main-header-menu .sub-menu .menu-item .menu-link:hover,
    ul.sub-menu li a:hover {
        background-color: #f5f5f5 !important;
        color: var(--mps-primary, #4a7c59) !important;
    }

    /* Site Title & Logo Sizing - COMPACT */
    .site-title a {
        font-weight: 700 !important;
        font-size: 1.25rem !important;
        letter-spacing: -0.5px !important;
        color: #ffffff !important;
    }
    .site-branding {
        display: flex !important;
        align-items: center !important;
    }
    .site-logo-img img, .custom-logo-link img {
        max-width: 55px !important; 
        max-height: 55px !important;
        width: auto !important;
    }

    /* SHOW SEARCH BAR */
    .ast-header-search,
    .site-header .search-form,
    .main-header-bar .search-form,
    .ast-search-menu-icon .search-form {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        width: auto !important;
    }
    .search-field {
        background: #ffffff !important;
        border: 1px solid #ddd !important;
        border-radius: 4px !important;
        color: #333 !important;
        padding: 5px 10px !important;
    }
    
    /* REMOVE RANDOM ASTRA BUTTON ("Get Seen Today") */
    .ast-header-button-1, 
    .ast-header-button-1 .ast-builder-button-wrap,
    .ast-builder-button-wrap .ast-custom-button {
        display: none !important;
    }
    
    /* ===========================
       Header (Astra) – Centering + Pills
       =========================== */

    /* Keep header row on a single line */
    .ast-desktop .ast-builder-grid-row { flex-wrap: nowrap !important; }

    /* Base grid: [logo] [center cluster] [CTA] */
    .ast-primary-header-bar .ast-builder-grid-row,
    .ast-transparent-header-active .ast-primary-header-bar .ast-builder-grid-row {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
    }
    
    /* Remove old grid styles that might conflict */
    /* Center-area contents align center by default */
    .ast-primary-header-bar .site-header-section-center,
    .ast-primary-header-bar .site-header-section-middle {
        flex: 1 !important;
        justify-content: center !important;
    }

    /* Hard center the middle section exactly between logo and CTA (desktop only) */
    @media (min-width: 1025px) {
        .ast-primary-header-bar .ast-builder-grid-row { position: relative !important; }
        .ast-primary-header-bar .site-header-section-center {
            position: absolute !important; 
            left: 50% !important; 
            top: 50% !important; 
            transform: translate(-50%, -50%) !important;
            display: flex !important; 
            align-items: center !important; 
            gap: 10px !important; 
            white-space: nowrap !important; 
            z-index: 5 !important;
        }
    }

    /* Auth “pills” appearance */
    .ast-desktop .main-header-menu .menu-item.mps-auth > a,
    .mps-auth-wrap > li > a {
        display: inline-flex !important; 
        align-items: center !important;
        padding: 8px 14px !important; 
        border: 1px solid rgba(255,255,255,0.4) !important; 
        border-radius: 999px !important;
        line-height: 1.1 !important; 
        background: transparent !important; 
        white-space: nowrap !important;
    }
    .ast-desktop .main-header-menu .menu-item.mps-auth > a:hover,
    .mps-auth-wrap > li > a:hover { 
        background: #52b142 !important; 
        border-color: #52b142 !important; 
        color: #fff !important; 
    }

    /* Header Compactness */
    .ast-main-header-bar {
        line-height: 1.2 !important;
        padding-top: 10px !important;
        padding-bottom: 10px !important;
    }    

    /* Buttons - Modern green style */
    .entry-title, .page-title, .ast-single-post .entry-header { display: none !important; }

    .wp-block-button__link,
    .mps-btn,
    button[type="submit"] {
        background: linear-gradient(135deg, var(--mps-primary) 0%, var(--mps-primary-dark) 100%) !important;
        color: var(--mps-white) !important;
        border: none !important;
        border-radius: 50px !important;
        padding: 14px 28px !important;
        font-weight: 600 !important;
        font-size: 15px !important;
        cursor: pointer !important;
        transition: all 0.25s ease !important;
        box-shadow: var(--mps-shadow) !important;
        text-decoration: none !important;
    }
    
    /* Menu CTA button - List Your Services (Astra theme specific) */
    .menu-cta > a,
    .menu-item.menu-cta > a,
    li.menu-cta > a,
    .ast-header-break-point .menu-cta > a,
    .main-header-menu .menu-cta > a,
    .ast-nav-menu .menu-cta > a,
    #ast-desktop-header .menu-cta > a,
    .ast-primary-header-bar .menu-cta > a,
    nav .menu-cta > a,
    header .menu-cta > a {
        background: #4A773C !important;
        color: #fff !important;
        padding: 0 24px !important;
        border-radius: 50px !important;
        font-weight: 600 !important;
        font-size: 14px !important;
        height: 36px !important;
        line-height: 1 !important; /* Reset line-height */
        margin-left: 12px !important;
        transition: all 0.2s ease !important;
        box-shadow: none !important;
        display: inline-flex !important; /* Flex allows centering content */
        align-items: center !important;
        justify-content: center !important;
        align-self: center !important; /* Center vertically in flex container */
        margin-top: auto !important;   /* Prevent full height stretch */
        margin-bottom: auto !important; /* Prevent full height stretch */
    }
    .menu-cta > a:hover,
    li.menu-cta > a:hover,
    header .menu-cta > a:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 12px rgba(74, 119, 60, 0.4) !important;
        background: #3d6a4a !important;
    }
    
    /* Fix search widget - Nuclear Option */
    .ast-search-menu-icon,
    .ast-header-search,
    .site-header .search-form,
    .main-header-bar .search-form,
    .ast-primary-header-bar .search-form,
    .widget_search,
    .search-field,
    input.search-field,
    form.search-form {
        display: none !important;
        visibility: hidden !important;
        width: 0 !important;
        height: 0 !important;
        opacity: 0 !important;
        pointer-events: none !important;
    }
    .search-form {
        display: flex;
        gap: 0;
    }
    .search-form input[type="search"] {
        border-radius: 6px 0 0 6px !important;
        border-right: none !important;
    }
    .search-form button,
    .search-form .search-submit {
        border-radius: 0 6px 6px 0 !important;
        padding: 8px 12px !important;
    }
    .wp-block-button__link:hover,
    .mps-btn:hover,
    button[type="submit"]:hover {
        transform: translateY(-2px) !important;
        box-shadow: var(--mps-shadow-lg) !important;
        background: linear-gradient(135deg, var(--mps-primary-dark) 0%, var(--mps-primary) 100%) !important;
    }
    
    /* Outlined buttons */
    .mps-btn-outline {
        background: transparent !important;
        color: var(--mps-primary) !important;
        border: 2px solid var(--mps-primary) !important;
    }
    .mps-btn-outline:hover {
        background: var(--mps-primary-light) !important;
    }
    
    /* Form inputs */
    .mps-register-form input[type="text"],
    .mps-register-form input[type="email"],
    .mps-register-form input[type="password"],
    .mps-register-form input[type="tel"],
    .mps-register-form select,
    .mps-register-form textarea,
    .mps-login-form input[type="text"],
    .mps-login-form input[type="email"],
    .mps-login-form input[type="password"] {
        border: 2px solid var(--mps-border) !important;
        border-radius: var(--mps-radius-sm) !important;
        padding: 14px 16px !important;
        font-size: 16px !important;
        transition: border-color 0.2s, box-shadow 0.2s !important;
        background: var(--mps-white) !important;
    }
    .mps-register-form input:focus,
    .mps-register-form select:focus,
    .mps-register-form textarea:focus,
    .mps-login-form input:focus {
        border-color: var(--mps-primary) !important;
        box-shadow: 0 0 0 4px var(--mps-primary-light) !important;
        outline: none !important;
    }
    
    /* Registration tabs */
    .mps-reg-tabs {
        display: flex;
        gap: 0;
        margin-bottom: 0;
    }
    .mps-reg-tabs button {
        flex: 1;
        padding: 16px 24px !important;
        border: 2px solid var(--mps-border) !important;
        background: var(--mps-bg) !important;
        cursor: pointer !important;
        font-size: 16px !important;
        font-weight: 600 !important;
        color: var(--mps-text-light) !important;
        border-bottom: none !important;
        border-radius: var(--mps-radius) var(--mps-radius) 0 0 !important;
        transition: all 0.2s !important;
    }
    .mps-reg-tabs button.active {
        background: var(--mps-white) !important;
        border-bottom: 2px solid var(--mps-white) !important;
        margin-bottom: -2px !important;
        color: var(--mps-primary) !important;
        border-color: var(--mps-border) !important;
        border-bottom-color: var(--mps-white) !important;
    }
    .mps-reg-tabs button:hover:not(.active) {
        background: var(--mps-primary-light) !important;
    }
    .mps-reg-panel {
        display: none;
        border: 2px solid var(--mps-border);
        padding: 32px;
        border-radius: 0 0 var(--mps-radius) var(--mps-radius);
        background: var(--mps-white);
        box-shadow: var(--mps-shadow);
    }
    .mps-reg-panel.active {
        display: block;
    }
    .mps-reg-panel h2 {
        color: var(--mps-text);
        font-size: 24px;
        margin-bottom: 24px;
    }
    
    /* Links */
    .mps-register-form a,
    .mps-login-form a,
    .mps-dashboard a {
        color: var(--mps-primary);
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s;
    }
    .mps-register-form a:hover,
    .mps-login-form a:hover,
    .mps-dashboard a:hover {
        color: var(--mps-primary-dark);
        text-decoration: underline;
    }
    
    /* Dashboard cards */
    .mps-dashboard {
        max-width: 800px;
    }
    .mps-dashboard h2 {
        color: var(--mps-text);
    }
    .mps-dashboard > div[style*="background:#f9f9f9"] {
        background: var(--mps-bg) !important;
        border: 1px solid var(--mps-border);
        border-radius: var(--mps-radius);
    }
    .mps-dashboard div[style*="background:#fff"] {
        border: 1px solid var(--mps-border) !important;
        border-radius: var(--mps-radius-sm) !important;
        box-shadow: var(--mps-shadow) !important;
        transition: box-shadow 0.2s, transform 0.2s !important;
    }
    .mps-dashboard div[style*="background:#fff"]:hover {
        box-shadow: var(--mps-shadow-lg) !important;
        transform: translateY(-2px) !important;
    }
    
    /* Success/Notice messages */
    .mps-notice-info,
    div[style*="background:#d4edda"] {
        background: var(--mps-primary-light) !important;
        border: 1px solid var(--mps-accent) !important;
        border-radius: var(--mps-radius-sm) !important;
        color: var(--mps-primary-dark) !important;
    }
    .mps-notice-error,
    div[style*="background:#fff2f2"] {
        border-radius: var(--mps-radius-sm) !important;
    }
    
    /* Fieldsets */
    fieldset {
        border: 2px solid var(--mps-primary-light) !important;
        border-radius: var(--mps-radius-sm) !important;
        background: var(--mps-bg);
    }
    fieldset legend {
        color: var(--mps-primary-dark) !important;
        font-weight: 600 !important;
        padding: 0 8px !important;
    }
    
    /* Checkboxes */
    input[type="checkbox"] {
        accent-color: var(--mps-primary);
        width: 18px;
        height: 18px;
    }
    
    /* Mobile responsive */
    @media (max-width: 921px) {
        /* FORCE STOP HORIZONTAL SCROLL & GREEN BACKGROUND */
        html, body { 
            overflow-x: hidden !important; 
            width: 100% !important;
            position: relative !important;
            background-color: #ffffff !important;
            background: #ffffff !important;
        }
        
        .site-content {
            overflow-x: hidden !important;
        }

        /* HAMBURGER ICON COLOR - NUCLEAR OPTION */
        .ast-mobile-menu-trigger-fill,
        .ast-button-wrap .menu-toggle .ast-mobile-menu-trigger-fill,
        .ast-header-break-point .ast-mobile-menu-trigger-fill, 
        .menu-toggle .ast-mobile-menu-trigger-fill { 
            fill: #333333 !important; 
            color: #333333 !important;
        }
        
        /* HAMBURGER BACKGROUND */
        .ast-header-break-point .main-header-bar .ast-button-wrap .menu-toggle {
            background: transparent !important;
        }
        
        /* MOBILE MENU LINKS COLORS */
        .ast-builder-menu-mobile .main-header-menu .menu-item .menu-link,
        .ast-hfb-header .ast-builder-menu-mobile .main-header-menu .menu-item .menu-link,
        .ast-header-break-point .main-header-menu .menu-item .menu-link,
        .ast-header-break-point .ast-builder-menu-mobile .main-header-menu .menu-item .menu-link {
            color: #333333 !important;
            font-weight: 500 !important;
        }
        
        /* Fix About Page Buttons Stacking */
        .mps-reg-tabs button {
            padding: 12px 16px !important;
            font-size: 14px !important;
        }
        .mps-reg-panel {
            padding: 20px;
        }
    }
    </style>
    <?php
}, 100);

// SECTION 2: HELPER FUNCTIONS
// ==============================================

/**
 * Helper to get sitter thumbnail
 */
if (!function_exists('antigravity_v200_get_sitter_thumbnail')) {
function antigravity_v200_get_sitter_thumbnail($post_id, $size = 'large') {
    if (has_post_thumbnail($post_id)) {
        return get_the_post_thumbnail_url($post_id, 'full');
    }
    return ''; // Or return a default image URL
}
}

/**
 * Helper: Get Lowest Price for Sitter
 */
if (!function_exists('antigravity_v200_get_lowest_price')) {
function antigravity_v200_get_lowest_price($post_id) {
    if (!function_exists('antigravity_v200_services_map')) return 0;
    
    $services_map = antigravity_v200_services_map();
    $min_price = 99999;
    
    foreach ($services_map as $label => $slug) {
        $p = get_post_meta($post_id, 'mps_price_' . $slug, true);
        if ($p && is_numeric($p) && $p > 0 && $p < $min_price) {
            $min_price = $p;
        }
    }
    
    return ($min_price < 99999) ? $min_price : 0;
}
}

/**
 * Helper: Get Sitter Post for User
 */
if (!function_exists('antigravity_v200_get_sitter_post')) {
function antigravity_v200_get_sitter_post($user_id) {
    if (!$user_id) return null;
    $posts = get_posts([
        'post_type' => 'sitter',
        'author' => $user_id,
        'posts_per_page' => 1,
        'post_status' => ['publish', 'draft', 'pending', 'future', 'private']
    ]);
    return !empty($posts) ? $posts[0] : null;
}
}
/**
 * Get the services map (label => slug)
 */
if (!function_exists('antigravity_v200_services_map')) {
function antigravity_v200_services_map() {
    return [
        'Dog Walking'     => 'dog-walking',
        'Overnight Stays' => 'overnight-stays',
        'Daycare'         => 'daycare',
        'Home Visits'     => 'home-visits',
    ];
}
}

/**
 * Get list of supported cities
 * NOW USES MASTER LIST (V84)
 */
if (!function_exists('antigravity_v200_cities_list')) {
function antigravity_v200_cities_list() {
    if (function_exists('antigravity_v200_get_valid_locations')) {
        return antigravity_v200_get_valid_locations();
    }
    return ['Brisbane', 'Sydney', 'Melbourne', 'Perth', 'Adelaide'];
}
}

/**
 * Normalize service labels to canonical form
 */
if (!function_exists('antigravity_v200_normalise_services_labels')) {
function antigravity_v200_normalise_services_labels($labels) {
    $allowed = array_keys(antigravity_v200_services_map());
    $clean = [];
    
    foreach ((array)$labels as $lab) {
        $lab = sanitize_text_field(trim($lab));
        if ($lab === '') continue;
        
        // Normalize common variations
        $lower = strtolower($lab);
        if (in_array($lower, ['dog walk', 'dog walks', 'dogwalking'])) {
            $lab = 'Dog Walking';
        } elseif (in_array($lower, ['home visit', 'homevisit', 'homevisits'])) {
            $lab = 'Home Visits';
        } elseif (in_array($lower, ['overnight', 'overnights', 'overnight stay'])) {
            $lab = 'Overnight Stays';
        }
        
        if (in_array($lab, $allowed, true)) {
            $clean[$lab] = true;
        }
    }
    
    return array_keys($clean);
}
}

/**
 * Create URL-safe slug from text
 */
if (!function_exists('antigravity_v200_slugify')) {
function antigravity_v200_slugify($text) {
    $text = strtolower(remove_accents($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}
}

/**
 * Get service label from slug
 */
if (!function_exists('antigravity_v200_service_slug_to_label')) {
function antigravity_v200_service_slug_to_label($slug) {
    $map = array_flip(antigravity_v200_services_map()); // slug => label
    return $map[$slug] ?? ucwords(str_replace('-', ' ', $slug));
}
}

/**
 * Helper to get sitter meta safely
 */
if (!function_exists('antigravity_v200_get_sitter_meta')) {
function antigravity_v200_get_sitter_meta($post_id) {
    return [
        'city'     => get_post_meta($post_id, 'mps_city', true),
        'suburb'   => get_post_meta($post_id, 'mps_suburb', true),
        'price'    => get_post_meta($post_id, 'mps_price', true),
        'email'    => get_post_meta($post_id, 'mps_email', true),
        'phone'    => get_post_meta($post_id, 'mps_phone', true),
        'services' => antigravity_v200_normalise_services_labels(
            array_map('trim', explode(',', (string)get_post_meta($post_id, 'mps_services', true)))
        ),
        // V75 Regional Fields
        'state'         => get_post_meta($post_id, 'mps_state', true),
        'location_type' => get_post_meta($post_id, 'mps_location_type', true),
        'radius'        => get_post_meta($post_id, 'mps_radius', true),
    ];
}
}

// SECTION 3: REGISTER CUSTOM POST TYPE - SITTER
// ==============================================

add_action('init', function() {
    register_post_type('sitter', [
        'label'               => 'Sitters',
        'labels'              => [
            'name'               => 'Sitters',
            'singular_name'      => 'Sitter',
            'add_new'            => 'Add New Sitter',
            'add_new_item'       => 'Add New Sitter',
            'edit_item'          => 'Edit Sitter',
            'view_item'          => 'View Sitter',
            'search_items'       => 'Search Sitters',
            'not_found'          => 'No sitters found',
        ],
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_rest'        => true,
        'has_archive'         => false,
        'rewrite'             => ['slug' => 'sitter', 'with_front' => false],
        'menu_icon'           => 'dashicons-id',
        'supports'            => ['title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'author'],
        'capability_type'     => 'sitter',
        'map_meta_cap'        => true,
    ]);
}, 0);

// SECTION 4: REGISTER TAXONOMIES (No public URLs - Pages handle routing)
// ==============================================

add_action('init', function() {
    // Cities taxonomy
    register_taxonomy('mps_city', ['sitter'], [
        'label'              => 'Cities',
        'labels'             => [
            'name'          => 'Cities',
            'singular_name' => 'City',
            'add_new_item'  => 'Add New City',
        ],
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_admin_column'  => true,
        'show_in_rest'       => true,
        'hierarchical'       => true,
        'rewrite'            => false,
    ]);

    // Services taxonomy
    register_taxonomy('mps_service', ['sitter'], [
        'label'              => 'Services',
        'labels'             => [
            'name'          => 'Services',
            'singular_name' => 'Service',
            'add_new_item'  => 'Add New Service',
        ],
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_admin_column'  => true,
        'show_in_rest'       => true,
        'hierarchical'       => true,
        'rewrite'            => false,
    ]);

    // Regions taxonomy (V75)
    register_taxonomy('mps_region', ['sitter'], [
        'label'              => 'Regions',
        'labels'             => [
            'name'          => 'Regions',
            'singular_name' => 'Region',
            'add_new_item'  => 'Add New Region',
        ],
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_admin_column'  => true,
        'show_in_rest'       => true,
        'hierarchical'       => true,
        'rewrite'            => false,
    ]);
}, 0);

/**
 * Get list of Australian States
 */
if (!function_exists('antigravity_v200_states_list')) {
function antigravity_v200_states_list() {
    return [
        'NSW' => 'New South Wales',
        'QLD' => 'Queensland',
        'VIC' => 'Victoria',
        'WA'  => 'Western Australia',
        'SA'  => 'South Australia',
        'TAS' => 'Tasmania',
        'ACT' => 'Australian Capital Territory',
        'NT'  => 'Northern Territory'
    ];
}
}

// SECTION 5: CREATE PRO (SITTER) ROLE WITH CAPABILITIES
// ==============================================

add_action('init', function() {
    // Create the 'pro' role if it doesn't exist
    if (!get_role('pro')) {
        add_role('pro', 'Sitter', ['read' => true]);
    }
    
    // Ensure the pro role has correct capabilities
    $pro_role = get_role('pro');
    if ($pro_role) {
        $caps = [
            'read',
            'edit_sitter',
            'edit_sitters',
            'edit_published_sitters',
            'publish_sitters',
            'delete_sitter',
            'upload_files',
        ];
        foreach ($caps as $cap) {
            if (!$pro_role->has_cap($cap)) {
                $pro_role->add_cap($cap);
            }
        }
    }
    
    // Also ensure administrators have full sitter capabilities
    $admin_role = get_role('administrator');
    if ($admin_role) {
        $admin_caps = [
            'edit_sitter', 'read_sitter', 'delete_sitter',
            'edit_sitters', 'edit_others_sitters', 'publish_sitters',
            'read_private_sitters', 'delete_sitters', 'delete_private_sitters',
            'delete_published_sitters', 'delete_others_sitters',
            'edit_private_sitters', 'edit_published_sitters',
        ];
        foreach ($admin_caps as $cap) {
            if (!$admin_role->has_cap($cap)) {
                $admin_role->add_cap($cap);
            }
        }
    }
}, 1);

// SECTION 6: INCLUDE SITTERS IN SEARCH
// ==============================================

add_action('pre_get_posts', function($q) {
    if (is_admin() || !$q->is_main_query()) return;
    
    if ($q->is_search()) {
        $types = (array)$q->get('post_type');
        if (empty($types) || $types === ['post']) {
            $types = ['post', 'page', 'sitter'];
        } elseif (!in_array('sitter', $types)) {
            $types[] = 'sitter';
        }
        $q->set('post_type', array_unique($types));
        
        // V84 Smart Search: Check for Region/City Terms
        // This ensures searching "New England" finds sitters in that Region, even if bio doesn't say it.
        if (taxonomy_exists('mps_city') && taxonomy_exists('mps_region')) {
            $s = trim($q->get('s'));
            $term_city   = get_term_by('name', $s, 'mps_city');
            $term_region = get_term_by('name', $s, 'mps_region');
            
            // Fallback: Try Slug Match (e.g. "New England" -> 'new-england')
            if (!$term_city)   $term_city   = get_term_by('slug', sanitize_title($s), 'mps_city');
            if (!$term_region) $term_region = get_term_by('slug', sanitize_title($s), 'mps_region');
            
            if ($term_city || $term_region) {
                // Determine logic: If term found, should we RESTRICT to that term?
                // Yes, usually user intent is specific.
                
                $tax_query = $q->get('tax_query') ?: [];
                if (!is_array($tax_query)) $tax_query = [];
                
                $relation = count($tax_query) > 0 ? 'AND' : ''; // Respect existing?
                if ($relation) $tax_query['relation'] = 'AND'; // actually we want to combine our OR with existing... complex.
                // Let's keep it simple: Add our finding.
                
                $our_query = ['relation' => 'OR'];
                if ($term_city)   $our_query[] = ['taxonomy'=>'mps_city', 'field'=>'term_id', 'terms'=>$term_city->term_id];
                if ($term_region) $our_query[] = ['taxonomy'=>'mps_region', 'field'=>'term_id', 'terms'=>$term_region->term_id];
                
                $tax_query[] = $our_query;
                $q->set('tax_query', $tax_query);
                
                // OPTIONAL: If we found a taxonomy match, do we clear the text search?
                // If we clear 's', we find ALL sitters in New England.
                // If we keep 's', we find sitters in New England WHO ALSO say "New England" in text.
                // Better UX: Clear 's' for Sitter post type queries to show full inventory.
                // BUT only if we are mostly searching sitters.
                // Let's clear 's' to be helpful. 
                $q->set('s', ''); 
            }
        }
        
        if (!$q->get('posts_per_page')) {
            $q->set('posts_per_page', 12);
        }
    }
});

// SECTION 7: ONE-TIME REWRITE FLUSH (version controlled)
// ==============================================

add_action('init', function() {
    $flush_version = 'mps_flush_v' . MPS_VERSION;
    if (!get_option($flush_version)) {
        flush_rewrite_rules(false);
        update_option($flush_version, 1, true);
    }
}, 99);

// SECTION 8: ADD SEARCH TO NAV MENU
// ==============================================

add_filter('wp_nav_menu_items', function($items, $args) {
    if ($args->theme_location === 'primary') {
        $items .= '<li class="menu-item menu-search">' . get_search_form(false) . '</li>';
    }
    return $items;
}, 10, 2);

// ===========================================================================
// NAVIGATION ROUTING
// ===========================================================================

add_filter('wp_nav_menu_objects', function($items) {
    if (!is_user_logged_in()) {
        return $items;
    }

    foreach ($items as $item) {
        // Target "List Your Services"
        if (in_array('menu-cta', (array) $item->classes) || $item->title === 'List Your Services') {
            $item->url = home_url('/edit-profile/');
        }

        // Change "Login" to "Account"
        if ($item->title === 'Login') {
            $item->title = 'Account';
            $item->url = home_url('/account/');
        }
    }
    return $items;
});

add_action('template_redirect', function() {
    // Redirect logged-out users from /list-your-services/ to /join/
    if (!is_user_logged_in() && is_page('list-your-services')) {
        wp_redirect(home_url('/join/'));
        exit;
    }
});


