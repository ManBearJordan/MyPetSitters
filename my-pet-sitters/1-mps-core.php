if (!defined('ABSPATH')) exit;

if ( ! class_exists( 'MPS_Core' ) ) {

    class MPS_Core {

        public function __construct() {
            $this->init_hooks();
        }

        private function init_hooks() {
            
            // AUTO-FLUSH REWRITE RULES (V220 Emergency Fix)
            add_action('admin_init', function() {
                if (!get_option('mps_v220_emergency_flush')) {
                    flush_rewrite_rules();
                    update_option('mps_v220_emergency_flush', true);
                    error_log('MPS V220: Emergency Cache Flush Triggered.');
                }
            });

            // SECTION 1B: FRONTEND STYLES (MPS Color Scheme)
            // HIDE ADMIN BAR FOR NON-ADMINS (Sitters & Owners)
            add_filter('show_admin_bar', function($show) {
                if (!current_user_can('administrator')) {
                    return false;
                }
                return $show;
            });

            // FORCE MOBILE MENU TO USE PRIMARY MENU
            add_filter('wp_nav_menu_args', function($args) {
                if ( isset($args['theme_location']) && ( $args['theme_location'] == 'mobile_menu' || $args['theme_location'] == 'handheld' ) ) {
                    $locations = get_nav_menu_locations();
                    if ( isset($locations['primary']) ) {
                        $args['menu'] = $locations['primary'];
                    }
                }
                return $args;
            });

            add_filter('wp_page_menu_args', function($args) {
                $locations = get_nav_menu_locations();
                if ( isset($locations['primary']) ) {
                    $args['menu'] = $locations['primary'];
                }
                return $args;
            });

            add_filter('wp_nav_menu_objects', function($items, $args) {
                if (is_user_logged_in()) {
                    foreach ($items as $key => $item) {
                        // 1. Change "Login" to "Account"
                        if (stripos($item->title, 'Login') !== false) {
                            $item->title = 'Account'; 
                            $item->url = home_url('/account/'); 
                            $item->classes[] = 'mps-logged-in-account'; 
                        }
                        // 2. Hide "Join" / "Become a Sitter"
                        if (stripos($item->title, 'Join') !== false || stripos($item->title, 'Become a Sitter') !== false) {
                            unset($items[$key]);
                        }
                    }
                }
                return $items;
            }, 10, 2);
        }
    }
}

// ======================================
// GLOBAL HELPERS & CONSTANTS (Must remain global)
// ======================================

// Include Master Locations List (V76)
$mps_loc_file = plugin_dir_path(__FILE__) . 'includes/mps-locations-au.php';
if (file_exists($mps_loc_file)) {
    require_once $mps_loc_file;
} else {
    if (!function_exists('antigravity_v200_get_valid_locations')) {
        function antigravity_v200_get_valid_locations() { return ['Sydney', 'Melbourne', 'Brisbane', 'Perth', 'Adelaide']; }
    }
}

if (!function_exists('antigravity_v200_is_valid_suburb')) {
    function antigravity_v200_is_valid_suburb($s) { return $s; } 
}

if (!function_exists('antigravity_v200_get_valid_regions')) {
    function antigravity_v200_get_valid_regions() { return []; }
}

// SECTION 1: CONSTANTS & CONFIGURATION
if (!defined('MPS_VERSION'))      define('MPS_VERSION', '2.0.0');
if (!defined('MPS_ADMIN_EMAIL'))  define('MPS_ADMIN_EMAIL', 'enquiries@mypetsitters.com.au');

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

        font-size: 14px !important;

        background: var(--mps-text-light) !important;

        box-shadow: none !important;

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

// Duplicate function definition removed (See line 1821)



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

        'capability_type'     => 'post',

        'map_meta_cap'        => true,

    ]);

}, 0);



// SECTION 4: REGISTER TAXONOMIES (No public URLs - Pages handle routing)

// ==============================================



add_action('init', function() {

    // City / Location Taxonomy

    register_taxonomy('mps_city', 'sitter', [

        'labels' => [

            'name' => 'Locations',

            'singular_name' => 'Location',

            'add_new_item' => 'Add Location',

        ],

        'public' => true,

        'hierarchical' => false,

        'show_ui' => true,

        'show_admin_column' => true,

        'rewrite' => ['slug' => 'location'],

    ]);

    

    // Service Taxonomy

    // Unnecessary if we use meta query based search, but good for structured data

    // Keeping simple for now, using POST META for services to allow multi-select checkboxes

});



// SECTION 5: ROLES AND CAPS

// ==============================================



// Add Sitter Role on Activation (Handled in loader activation hook mostly, but safe here)

// (Removed to avoid constant DB writes - rely on installer/activation hook)



// SECTION 6: AJAX HANDLER FOR SUBURBS

// ==============================================



// ===========================================================================

// AJAX HANDLER FOR SUBURBS (DYNAMIC DB QUERY)

// ===========================================================================

add_action('wp_ajax_antigravity_get_suburbs', 'antigravity_get_suburbs_handler');

add_action('wp_ajax_nopriv_antigravity_get_suburbs', 'antigravity_get_suburbs_handler');



function antigravity_get_suburbs_handler() {

    try {

        $region_name = isset($_POST['region']) ? sanitize_text_field($_POST['region']) : '';

        

        if (empty($region_name)) {

            wp_send_json_error(['message' => 'No region provided']);

        }



        // 1. Find all Sitter Posts in this Region

        // We query for posts that have the selected 'mps_region' term

        $args = [

            'post_type' => 'sitter',

            'post_status' => 'publish',

            'posts_per_page' => -1, // Get all to ensure we find all suburbs

            'tax_query' => [

                [

                    'taxonomy' => 'mps_region',

                    'field'    => 'name',

                    'terms'    => $region_name,

                ]

            ]

        ];



        $query = new WP_Query($args);

        $found_suburbs = [];



        // 2. Extract Suburbs (mps_city) from those Sitters

        if ($query->have_posts()) {

            foreach ($query->posts as $post) {

                // Try to get the suburb from the 'mps_city' taxonomy first (preferred)

                $terms = get_the_terms($post->ID, 'mps_city');

                if ($terms && !is_wp_error($terms)) {

                    foreach ($terms as $term) {

                        $found_suburbs[] = $term->name;

                    }

                }

                

                // Fallback: Check custom field 'mps_suburb' if taxonomy is empty

                $meta_suburb = get_post_meta($post->ID, 'mps_suburb', true);

                if ($meta_suburb) {

                    $found_suburbs[] = trim($meta_suburb);

                }

            }

        }



        // 3. Clean up list

        $found_suburbs = array_unique($found_suburbs);

        sort($found_suburbs);



        // 4. Return Data

        // If no specific suburbs found (e.g., region has sitters but no suburb data),

        // return empty so frontend handles it gracefully.

        wp_send_json_success($found_suburbs);

        

    } catch (Exception $e) {

        // Log error and return strict failure

        error_log('MPS AJAX Error: ' . $e->getMessage());

        wp_send_json_error(['message' => 'Internal Server Error']);

    }

}



/**

 * Get list of supported cities

 * RESTORED V208

 */

if (!function_exists('antigravity_v200_cities_list')) {

function antigravity_v200_cities_list() {

    return ['Brisbane', 'Sydney', 'Melbourne', 'Perth', 'Adelaide'];

}

}





/**

 * Get list of Australian States

 * RESTORED V210 (Fixes Regions Page Crash)

 */

if (!function_exists('antigravity_v200_states_list')) {

function antigravity_v200_states_list() {

    return [

        'NSW' => 'New South Wales',

        'VIC' => 'Victoria',

        'QLD' => 'Queensland',

        'WA'  => 'Western Australia',

        'SA'  => 'South Australia',

        'TAS' => 'Tasmania',

        'ACT' => 'Australian Capital Territory',

        'NT'  => 'Northern Territory'

    ];

}

}



/**

 * Get Suburbs mapped by Region

 * RESTORED V211 (Fixes Edit Profile Tab)

 */

if (!function_exists('antigravity_v200_get_suburbs_by_region')) {

function antigravity_v200_get_suburbs_by_region() {

    return [

        // NSW

        'Hunter Region' => ['Newcastle', 'Maitland', 'Cessnock', 'Singleton', 'Muswellbrook', 'Port Stephens', 'Lake Macquarie', 'Kurri Kurri'],

        'Greater Western Sydney' => ['Parramatta', 'Penrith', 'Blacktown', 'Campbelltown', 'Liverpool', 'Fairfield', 'Richmond', 'Windsor', 'Camden'],

        'Central Coast' => ['Gosford', 'Wyong', 'Terrigal', 'The Entrance', 'Woy Woy', 'Avoca Beach', 'Erina'],

        'Mid North Coast' => ['Port Macquarie', 'Coffs Harbour', 'Taree', 'Forster', 'Tuncurry', 'Kempsey', 'Wauchope'],

        'Northern Rivers' => ['Lismore', 'Ballina', 'Byron Bay', 'Tweed Heads', 'Casino', 'Kyogle', 'Grafton', 'Yamba'],

        'New England / North West' => ['Tamworth', 'Armidale', 'Moree', 'Gunnedah', 'Narrabri', 'Inverell', 'Glen Innes'],

        'Central West' => ['Orange', 'Bathurst', 'Dubbo', 'Mudgee', 'Parkes', 'Forbes', 'Cowra', 'Lithgow'],

        'Southern Highlands' => ['Bowral', 'Mittagong', 'Moss Vale', 'Goulburn'],

        'South Coast' => ['Wollongong', 'Nowra', 'Bomaderry', 'Ulladulla', 'Batemans Bay', 'Moruya', 'Bega', 'Merimbula'],

        'Riverina' => ['Wagga Wagga', 'Griffith', 'Albury', 'Leeton', 'Cootamundra', 'Narrandera', 'Deniliquin'],

        'Illawarra' => ['Wollongong', 'Shellharbour', 'Kiama', 'Dapto', 'Albion Park'],

        'Snowy Mountains' => ['Cooma', 'Jindabyne', 'Thredbo', 'Perisher Valley'],

        

        // QLD

        'Brisbane & Surrounds' => ['Brisbane', 'Ipswich', 'Logan City', 'Redcliffe', 'Cleveland', 'Strathpine', 'Caboolture'],

        'South East Queensland' => ['Gold Coast', 'Sunshine Coast', 'Toowoomba', 'Gatton', 'Beaudesert'],

        'Gold Coast' => ['Surfers Paradise', 'Southport', 'Burleigh Heads', 'Coolangatta', 'Robina', 'Nerang', 'Coomera'],

        'Sunshine Coast' => ['Maroochydore', 'Caloundra', 'Noosa Heads', 'Nambour', 'Maleny', 'Gympie'],

        'North Queensland' => ['Townsville', 'Ayr', 'Charters Towers', 'Ingham', 'Bowen'],

        'Far North Queensland' => ['Cairns', 'Port Douglas', 'Atherton', 'Mareeba', 'Innisfail'],

        'Central Queensland' => ['Rockhampton', 'Gladstone', 'Yeppoon', 'Emerald', 'Biloela'],

        'Wide Bay-Burnett' => ['Bundaberg', 'Hervey Bay', 'Maryborough', 'Kingaroy'],

        'Darling Downs' => ['Toowoomba', 'Warwick', 'Dalby', 'Stanthorpe', 'Goondiwindi'],

        

        // VIC

        'Greater Melbourne' => ['Melbourne', 'Geelong', 'Frankston', 'Dandenong', 'Pakenham', 'Sunbury', 'Werribee', 'Melton'],

        'Gippsland' => ['Traralgon', 'Moe', 'Morwell', 'Sale', 'Bairnsdale', 'Warragul', 'Leongatha', 'Wonthaggi'],

        'Barwon South West' => ['Warrnambool', 'Colac', 'Portland', 'Hamilton', 'Torquay', 'Lorne'],

        'Hume' => ['Shepparton', 'Wangaratta', 'Wodonga', 'Benalla', 'Seymour', 'Echuca'],

        'Loddon Mallee' => ['Bendigo', 'Mildura', 'Swan Hill', 'Castlemaine', 'Maryborough', 'Kyneton'],

        'Central Victoria' => ['Ballarat', 'Ararat', 'Stawell', 'Daylesford'],

        

        // WA, SA, TAS placeholders for brevity

        'Perth & Peel' => ['Perth', 'Fremantle', 'Joondalup', 'Rockingham', 'Mandurah', 'Armadale'],

        'South West' => ['Bunbury', 'Busselton', 'Margaret River', 'Collie', 'Manjimup'],

        'Adelaide & Surrounds' => ['Adelaide', 'Gawler', 'Mount Barker', 'Salisbury', 'Glenelg'],

        'Hobart & Surrounds' => ['Hobart', 'Glenorchy', 'Kingston', 'Sorell', 'New Norfolk'],

        'Canberra' => ['Canberra', 'Queanbeyan', 'Gungahlin', 'Tuggeranong', 'Belconnen', 'Woden'],

        'Darwin & Surrounds' => ['Darwin', 'Palmerston', 'Casuarina']

    ];

}

}

