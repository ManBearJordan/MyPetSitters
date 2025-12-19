<?php
/**
 * MPS HOMEPAGE - Care.com Style Homepage
 * 
 * Requires: MPS Core
 * 
 * Provides:
 * - [mps_homepage] shortcode with hero, search, sitter cards
 * - Modern, warm color scheme
 * - Photo-forward sitter cards with badges
 */

if (!defined('ABSPATH')) exit;

// ===========================================================================
// HOMEPAGE STYLES
// ===========================================================================

add_action('wp_head', function() {
    if (!is_front_page() && !has_shortcode(get_post()->post_content ?? '', 'mps_homepage')) return;
    ?>
    <style>
    /* Hide default theme title on homepage */
    .home .entry-header, 
    .home .entry-title,
    .page-id-92 .entry-header {
        display: none !important;
    }

    /* Care.com Inspired Color Scheme */
    :root {
        --mps-teal: #0d7377;
        --mps-teal-dark: #095456;
        --mps-teal-light: #e0f4f4;
        --mps-coral: #ff6b6b;
        --mps-coral-light: #ffe5e5;
        --mps-cream: #faf8f5;
        --mps-warm-gray: #f5f3f0;
        --mps-text-dark: #1a2b3c;
        --mps-text-muted: #5a6b7c;
        --mps-green: #4CAF50;
    }

    /* Hero Section */
    /* Hero Section */
    /* Hero Section */
    .mps-hero {
        /* Layered: Image (if exists) ON TOP of Gradient? Or Gradient over image? Usually Gradient overlay. */
        /* Let's try just the gradient as the primary, but cleaner */
        background: linear-gradient(135deg, #4da1a9 0%, #0d7377 100%) !important; 
        color: #fff;
        padding: 80px 20px 100px; /* Reduced from 180px to 80px */
        text-align: center;
        border-radius: 0 0 40px 40px;
        margin: -20px -20px 40px;
        position: relative;
        overflow: visible;
    }
    
    /* Pets Hero Image */
    .mps-hero-image {
        position: absolute;
        bottom: 0px; 
        left: 50%;
        transform: translateX(-50%);
        width: 100%;
        max-width: 1200px;
        height: auto;
        max-height: 380px;
        object-fit: contain;
        object-position: bottom center;
        z-index: 0;
        opacity: 1;
    }
    
    .mps-hero .sub-headline {
        color: #a8e6cf;
        font-size: 1rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
        margin-bottom: 8px;
        display: block;
    }

    .mps-hero-content {
        position: relative;
        z-index: 10;
        max-width: 800px;
        margin: 0 auto;
    }
    .mps-hero h1 {
        font-size: clamp(2rem, 5vw, 3.2rem);
        font-weight: 700;
        margin: 0 0 24px;
        line-height: 1.1;
        color: #fff;
    }
    .mps-hero p.lead {
        font-size: 1.25rem;
        opacity: 0.95;
        margin: 0 0 32px;
    }

    /* Search Box */
    .mps-search-box {
        background: #fff;
        border-radius: 60px;
        padding: 4px;
        display: flex;
        gap: 6px;
        max-width: 650px;
        margin: 0 auto 40px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.15);
        position: relative;
        z-index: 20;
    }
    .mps-search-box select,
    .mps-search-box input {
        flex: 1;
        border: none;
        padding: 0 16px;
        height: 36px;
        line-height: normal;
        font-size: 15px;
        background: transparent;
        outline: none;
        color: #333 !important;
        opacity: 1;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
    }
    .mps-search-box select {
        /* max-width removed to allow flex-grow to fill container */
        cursor: pointer;
        border-right: 1px solid #eee;
        background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23333333%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E");
        background-repeat: no-repeat;
        background-position: right 16px center;
        background-size: 10px;
        padding-right: 36px; 
    }
    .mps-search-box select option {
        color: #333;
        background: #fff;
        padding: 10px;
    }
    #mps-hero-search-btn {
        background: #4A773C !important; /* Darker green to match screenshot */
        color: #fff !important;
        border: none !important;
        border-radius: 50px !important; /* Rounded to match container */
        padding: 0 24px !important;
        height: 36px !important;
        font-weight: 600 !important;
        cursor: pointer !important;
        transition: transform 0.2s, box-shadow 0.2s !important;
        font-size: 15px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        line-height: 1 !important;
    }
    #mps-hero-search-btn:hover {
        transform: scale(1.02);
        box-shadow: 0 4px 12px rgba(74, 119, 60, 0.4);
    }

    /* Trust Badges */
    .mps-trust-row {
        display: flex;
        justify-content: center;
        gap: 32px;
        margin-top: 0;
        position: relative;
        z-index: 10;
        flex-wrap: wrap;
        background: rgba(0,0,0,0.2);
        display: inline-flex;
        padding: 10px 20px;
        border-radius: 30px;
        backdrop-filter: blur(4px);
    }
    .mps-trust-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: #fff;
        font-weight: 500;
    }
    .mps-trust-item svg {
        width: 18px;
        height: 18px;
    }

    /* Service Cards Section */
    .mps-services-section {
        text-align: center;
        padding: 60px 20px;
        background: var(--mps-cream);
        margin: 0 -20px;
        border-radius: 40px;
    }
    .mps-services-section h2 {
        font-size: 2rem;
        color: var(--mps-text-dark);
        margin: 0 0 8px;
    }
    .mps-services-section > p {
        color: var(--mps-text-muted);
        margin: 0 0 40px;
        font-size: 1.1rem;
    }
    .mps-service-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 24px;
        max-width: 1000px;
        margin: 0 auto;
    }
    .mps-service-card {
        background: #fff;
        border-radius: 24px;
        padding: 40px 24px;
        text-align: center;
        transition: transform 0.3s, box-shadow 0.3s, border-color 0.3s;
        cursor: pointer;
        text-decoration: none !important;
        color: inherit;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        border: 1px solid rgba(0,0,0,0.04);
    }
    .mps-service-card:hover {
        transform: translateY(-12px);
        box-shadow: 0 30px 60px rgba(13, 115, 119, 0.25);
        border-color: var(--mps-teal);
        background-color: #f8fdfe;
    }
    .mps-service-card .icon-img {
        height: 64px;
        width: auto;
        display: block;
        margin-bottom: 8px;
    }
    .mps-service-card .icon {
        font-size: 48px;
        margin-bottom: 16px;
    }
    .mps-service-card h3 {
        margin: 0 0 8px;
        color: var(--mps-text-dark);
        font-size: 1.25rem;
    }
    .mps-service-card p {
        margin: 0;
        color: var(--mps-text-muted);
        font-size: 14px;
    }

    /* Featured Sitters Section */
    .mps-featured-section {
        padding: 60px 20px;
        max-width: 1200px;
        margin: 0 auto;
    }
    .mps-featured-section h2 {
        text-align: center;
        font-size: 2rem;
        color: var(--mps-text-dark);
        margin: 0 0 8px;
    }
    .mps-featured-section > p {
        text-align: center;
        color: var(--mps-text-muted);
        margin: 0 0 40px;
    }

    /* Modern Sitter Cards */
    .mps-sitter-grid-modern {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 24px;
    }
    .mps-sitter-card-modern {
        background: #fff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        transition: transform 0.3s, box-shadow 0.3s;
    }
    .mps-sitter-card-modern:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 40px rgba(0,0,0,0.12);
    }
    .mps-sitter-photo {
        width: 100%;
        height: 200px;
        object-fit: cover;
        background: linear-gradient(135deg, var(--mps-teal-light), var(--mps-warm-gray));
    }
    .mps-sitter-info {
        padding: 20px;
    }
    .mps-sitter-name {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--mps-text-dark);
        margin: 0 0 4px;
    }
    .mps-sitter-location {
        color: var(--mps-text-muted);
        font-size: 14px;
        margin: 0 0 12px;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .mps-sitter-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 12px;
    }
    .mps-badge {
        background: var(--mps-teal-light);
        color: var(--mps-teal);
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }
    .mps-badge.coral {
        background: var(--mps-coral-light);
        color: var(--mps-coral);
    }
    .mps-sitter-price {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #eee;
    }
    .mps-sitter-price .price {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--mps-teal);
    }
    .mps-sitter-price .label {
        font-size: 12px;
        color: var(--mps-text-muted);
    }
    .mps-view-btn {
        background: var(--mps-teal) !important;
        color: #fff !important;
        padding: 10px 20px !important;
        border-radius: 25px !important;
        font-size: 14px !important;
        font-weight: 500 !important;
        text-decoration: none !important;
        transition: background 0.2s !important;
    }
    .mps-view-btn:hover {
        background: var(--mps-teal-dark) !important;
    }

    /* Cities Section */
    /* Cities Section */
    .mps-cities-section {
        background: linear-gradient(135deg, var(--mps-teal) 0%, #14919b 50%, #0d7377 100%);
        padding: 80px 20px;
        margin: 0 -20px;
        text-align: center;
        border-radius: 40px;
        color: #fff;
    }
    .mps-cities-section h2 {
        font-size: 2rem;
        color: #fff;
        margin: 0 0 40px;
    }
    .mps-cities-row {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 16px;
        max-width: 900px;
        margin: 0 auto;
    }
    .mps-city-pill {
        background: #fff;
        padding: 14px 28px;
        border-radius: 50px;
        text-decoration: none !important;
        color: var(--mps-teal);
        font-weight: 600;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        display: inline-block;
        font-size: 1.05rem;
    }
    .mps-city-pill:hover {
        transform: translateY(-4px) scale(1.05);
        box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        background: #fff;
        color: var(--mps-teal-dark);
    }

    /* Why Choose Section */
    /* Why Choose Section & Features */
    .mps-section-columns {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 60px;
        padding: 60px 20px;
        max-width: 1200px;
        margin: 0 auto;
        position: relative;
    }
    
    /* Remove shadow/border from image, add blob background */
    .mps-img-container {
        box-shadow: none; 
        border-radius: 0;
        background: transparent;
        position: relative;
        z-index: 1;
    }
    /* Decorative blob behind image */
    /* Watermark behind section */
    .mps-section-columns::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 600px;
        height: 600px;
        background: url('/wp-content/uploads/mps-logo.png') no-repeat center center;
        background-size: contain;
        opacity: 0.04;
        z-index: 0; /* Changed from -1 to 0 to sit above white bg */
        pointer-events: none;
    }
    
    /* Remove old blob */
    .mps-hero {
        background: url('/wp-content/uploads/2025/12/hero-bg-2.png') no-repeat center center; /* Fallback */
        background-size: cover; /* scaling */
        background-position: center bottom;
        background-color: #e6f5ea; /* Fallback color */
        padding: 80px 20px 180px; /* Increased bottom padding for dog spacing */
        margin: 0 -20px 40px; /* Reset top margin to 0 */
        text-align: center;
        position: relative;
        overflow: visible; /* Allow images to pop out */
    }
    .mps-img-container::before { content: none; }

    .mps-col {
        flex: 1;
        min-width: 300px;
        position: relative; /* Ensure stacking context */
        z-index: 1; /* Content above watermark */
    }
    .mps-img-container img {
        width: 100%;
        height: auto;
        display: block;
        margin-bottom: 8px;
    }
    .mps-img-map {
        height: 450px;
        object-fit: contain;
        object-position: center; 
    }

    .mps-content-col h2 {
        font-size: 2.8rem;
        margin-bottom: 24px;
        color: var(--mps-text-dark);
        position: relative;
        text-align: left;
    }
    
    /* Custom Paw Separator */
    .mps-paw-divider {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 16px;
        margin: 60px auto 30px;
        width: 100%;
        max-width: 300px;
    }
    .mps-paw-divider::before,
    .mps-paw-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--mps-teal);
        opacity: 0.3;
        display: block;
    }
    .mps-paw-divider img {
        height: 28px;
        width: auto;
        opacity: 0.8;
    }

    .mps-content-col p {
        font-size: 1.15rem;
        line-height: 1.7;
        color: var(--mps-text-muted);
        margin-bottom: 24px;
    }
    .mps-link-arrow {
        color: var(--mps-teal);
        font-weight: 700;
        text-decoration: none;
        font-size: 1.1rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: gap 0.2s;
    }
    .mps-link-arrow:hover { 
        text-decoration: none; 
        gap: 12px;
    }
    
    .mps-features-grid {
        display: flex;
        gap: 32px;
        margin-top: 50px;
    }
    .mps-feature h3 {
        font-size: 1.1rem;
        margin-bottom: 12px;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
        font-weight: 700;
        line-height: 1.2;
    }
    .mps-feature h3 span {
        color: var(--mps-teal);
        font-weight: 800;
        font-size: 1.5rem;
        background: var(--mps-teal-light);
        padding: 5px 12px;
        border-radius: 12px;
        display: inline-block;
    }
    .mps-feature p { font-size: 0.95rem; margin: 0; line-height: 1.5; color: #666; }

    /* How It Works Section */
    .mps-section-alt {
        background: #fff;
        border-radius: 0;
    }
    .mps-section-divider {
        width: 100%;
        max-width: 1200px;
        height: 1px;
        background: #eee;
        margin: 0 auto;
        display: block;
    }
    
    .mps-steps-list {
        display: flex;
        flex-direction: column;
        gap: 32px;
    }
    .mps-step {
        display: flex;
        gap: 16px;
        align-items: flex-start;
    }
    .mps-step-icon {
        color: var(--mps-green);
        font-size: 24px;
        flex-shrink: 0;
        margin-top: 4px;
    }
    .mps-step-content h3 {
        margin: 0 0 8px;
        font-size: 1.3rem;
    }
    .mps-step-content p {
        margin: 0;
        color: var(--mps-text-muted);
    }
    .mps-img-steps {
        height: 500px;
        object-fit: contain;
        object-position: center;
    }

    /* List Your Services - Refined Overlay */
    .mps-list-services-section {
        max-width: 1000px;
        margin: 60px auto 80px;
        position: relative;
        background: transparent;
        box-shadow: none;
        border: none;
        padding: 0;
        border-radius: 0;
        text-align: center;
    }
    .mps-list-bg-img {
        width: 100%;
        height: auto;
        display: block;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    }
    .mps-list-overlay-content {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 40px 140px; /* Padding to keep text inside the 'white block' between pets */
    }
    .mps-list-overlay-content h2 {
        font-size: 2.5rem;
        margin-bottom: 16px;
        color: var(--mps-text-dark);
    }
    .mps-list-overlay-content p {
        font-size: 1.15rem;
        color: #555;
        margin-bottom: 24px;
        max-width: 500px;
    }
    .mps-benefits-ul {
        list-style: none;
        padding: 0;
        margin: 0 0 32px;
        text-align: left;
        display: inline-block;
    }
    .mps-benefits-ul li {
        margin-bottom: 10px;    
        font-size: 1.1rem;
        position: relative;
        padding-left: 28px;
    }
    .mps-benefits-ul li::before {
        content: '🐾';
        position: absolute;
        left: 0;
        top: 2px;
        font-size: 18px;
    }
    .mps-btn-green {
        background: var(--mps-teal) !important;
        color: #fff !important;
        padding: 18px 48px !important;
        border-radius: 50px !important;
        font-weight: 700 !important;
        font-size: 1.2rem !important;
        text-decoration: none !important;
        display: inline-block;
        transition: transform 0.2s, background 0.2s, box-shadow 0.2s !important;
        box-shadow: 0 4px 12px rgba(13, 115, 119, 0.3);
    }
    .mps-btn-green:hover {
        transform: scale(1.05);
        background: var(--mps-teal-dark) !important;
        box-shadow: 0 8px 20px rgba(13, 115, 119, 0.4);
    }

    /* Join Pack Banner */
    .mps-join-banner {
        background-color: #fff;
        padding: 0;
        margin: 40px -20px -50px; /* Negative bottom margin to sit flush */
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .mps-join-banner-img {
        width: 100%;
        max-height: 400px;
        object-fit: cover;
        object-position: bottom center;
        display: block;
    }
    .mps-join-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(255,255,255,0.9);
        padding: 40px 60px;
        border-radius: 30px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        text-align: center;
        max-width: 600px;
        width: 90%;
    }
    .mps-join-overlay h2 {
        font-size: 2.5rem;
        color: var(--mps-text-dark);
        margin: 0 0 16px;
    }
    .mps-join-overlay p {
        font-size: 1.2rem;
        color: var(--mps-text-muted);
        margin: 0 0 32px;
    }

    @media (max-width: 768px) {
        /* FORCE CENTERED LAYOUT - NO OFFSETS */
        .mps-home-wrapper {
            margin: 0 auto;
            width: 100%;
            overflow-x: hidden;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center; /* Center children */
        }
        
        .mps-hero,
        .mps-services-section,
        .mps-cities-section,
        .mps-join-banner,
        .mps-list-services-section,
        .mps-section-columns {
            width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            margin-bottom: 20px !important; /* Force positive bottom margin */
            max-width: 100% !important;
            border-radius: 20px !important; /* Consistent rounded look */
            box-sizing: border-box !important;
        }

        /* Specific fix for Join Banner to ensure footer space */
        .mps-join-banner {
            margin-bottom: 0 !important;
            padding-bottom: 60px !important;
        }

        .mps-hero { padding: 60px 20px 180px; }
        .mps-search-box { flex-direction: column; border-radius: 20px; width: 100%; }
        
        /* Fix list width */
        .mps-list-overlay-content { width: auto; max-width: 100%; padding: 30px 20px; }
        
        /* Ensure images don't overflow */
        img { max-width: 100%; height: auto; }
        
        /* Fix Paw Divider centering */
        .mps-paw-divider { width: 80%; margin: 40px auto; }

        /* FIX JOIN BANNER OVERLAP - Unstack content */
        .mps-join-overlay {
            position: relative !important;
            top: auto !important;
            left: auto !important;
            transform: none !important;
            width: auto !important;
            margin: -60px 20px 0 !important; /* Pull up slightly over image bottom, but safely */
            max-width: none !important;
            padding: 30px !important;
        }

        /* FIX LIST SERVICES - Hide Image & Clean Card */
        .mps-list-bg-img {
            display: none !important;
        }
        .mps-list-overlay-content {
            position: relative !important;
            top: auto !important;
            left: auto !important;
            transform: none !important;
            width: auto !important;
            margin: 0 !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important; /* Restore shadow */
            background: #fff !important;
            padding: 40px 30px !important;
            border-radius: 20px !important;
        }
        .mps-join-banner-img {
            height: 200px; /* Reduced height for decorative top part */
            object-fit: cover;
        }
    }
        /* Scroll Reveal Animation */
    .reveal {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.8s ease-out;
    }
    .reveal.active {
        opacity: 1;
        transform: translateY(0);
    }
</style>
    <?php
});

// ===========================================================================
// [MPS_HOMEPAGE] SHORTCODE
// ===========================================================================

// Converted to named function// Converted to named function (V120 Fix)
function antigravity_v200_render_homepage($atts) {
    // Get Services
    $services_map = function_exists('antigravity_v200_services_map') ? antigravity_v200_services_map() : [];
    
    // Get Featured Sitters
    $sitters = get_posts([
        'post_type' => 'sitter',
        'post_status' => 'publish',
        'posts_per_page' => 6,
        'orderby' => 'rand'
    ]);
    
    // -----------------------------------------------------------
    // DYNAMIC REGION LOGIC
    // -----------------------------------------------------------
    
    // 1. Define the "Always Show" list (The Main 5)
    // Ensure these match exactly what is in your database/optgroups
    $pinned_locations = [
        'Sydney', 
        'Melbourne', 'Greater Melbourne', 
        'Brisbane', 'Brisbane & Surrounds', 
        'Perth', 'Perth & Peel', 
        'Adelaide', 'Adelaide & Surrounds'
    ];

    // 2. Define Structure
    $regions_by_state = [
        'NSW' => ['Sydney', 'Hunter Region', 'Central Coast', 'Greater Western Sydney', 'Illawarra'],
        'QLD' => ['Brisbane & Surrounds', 'Gold Coast', 'Sunshine Coast'],
        'VIC' => ['Greater Melbourne', 'Mornington Peninsula'],
        'WA'  => ['Perth & Peel'],
        'SA'  => ['Adelaide & Surrounds']
    ];

    ob_start();
    ?>
    <div class="mps-home-wrapper">
    
    <section class="mps-hero">
        <div class="mps-hero-content">
            <span class="sub-headline">Australia-wide directory of pet sitters & dog walkers</span>
            <h1>Find Trusted Pet Sitters & Dog Walkers Near You</h1>
            
            <form class="mps-search-box" action="/" method="get" onsubmit="return mpsSearch(this)">
                
                <select name="region" id="mps-region-select" onchange="mpsRegionChange()" required>
                    <option value="">Select Region...</option>
                    <?php foreach ($regions_by_state as $state => $regions): ?>
                        <optgroup label="<?= esc_attr($state) ?>">
                            <?php foreach ($regions as $region_name): 
                                // LOGIC: Should we show this region?
                                $show = false;

                                // Rule A: Is it pinned?
                                if (in_array($region_name, $pinned_locations)) {
                                    $show = true;
                                } 
                                // Rule B: Does it have active sitters?
                                else {
                                    $term = get_term_by('name', $region_name, 'mps_region');
                                    if ($term && !is_wp_error($term) && $term->count > 0) {
                                        $show = true;
                                    }
                                }

                                if ($show):
                            ?>
                                <option value="<?= esc_attr($region_name) ?>"><?= esc_html($region_name) ?></option>
                            <?php endif; endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
                
                <div id="mps-suburb-wrapper" style="display:none; flex:1; border-left:1px solid #eee;">
                    <select name="suburb" id="mps-suburb-select" style="width:100%;">
                        <option value="">Select Suburb (Optional)</option>
                    </select>
                </div>

                <select name="service" id="mps-service-select">
                    <option value="">All Services</option>
                    <?php foreach ($services_map as $name => $slug): ?>
                        <option value="<?= esc_attr($slug) ?>"><?= esc_html($name) ?></option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit" id="mps-hero-search-btn">Search</button>
            </form>
            
            <div class="mps-trust-row">
                <div class="mps-trust-item">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L3 7v10l9 5 9-5V7l-9-5zm0 2.18l7 3.89v7.86l-7 3.89-7-3.89V8.07l7-3.89z"/><path d="M12 7a3 3 0 100 6 3 3 0 000-6z"/></svg>
                    Verified Sitters
                </div>
                <div class="mps-trust-item">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 6c1.4 0 2.8 1.1 2.8 2.5V11h1.7v5.5c0 .8-.7 1.5-1.5 1.5h-6c-.8 0-1.5-.7-1.5-1.5V11h1.7V9.5C9.2 8.1 10.6 7 12 7z"/></svg>
                    Secure Booking
                </div>
            </div>
        </div>
        
        <img src="/wp-content/uploads/2025/12/pets-hero.png" alt="Happy pets" class="mps-hero-image">
    </section>
    
    <section class="mps-services-section">
        <h2>What do you need?</h2>
        <p>Choose from our range of trusted pet care services</p>
        <div class="mps-service-grid">
            <a href="/services/dog-walking/" class="mps-service-card reveal">
                <img src="/wp-content/uploads/2025/12/icon-dog.png" alt="Dog Walking" class="icon-img">
                <h3>Dog Walking</h3>
                <p>Daily walks with photo updates</p>
            </a>
            <a href="/services/overnight-stays/" class="mps-service-card reveal">
                <img src="/wp-content/uploads/2025/12/icon-house.png" alt="Overnight Stays" class="icon-img">
                <h3>Overnight Stays</h3>
                <p>Pet care in your home or theirs</p>
            </a>
            <a href="/services/daycare/" class="mps-service-card reveal">
                <img src="/wp-content/uploads/2025/12/icon-sun.png" alt="Daycare" class="icon-img">
                <h3>Daycare</h3>
                <p>Safe daytime care with play and rest</p>
            </a>
            <a href="/services/home-visits/" class="mps-service-card reveal">
                <img src="/wp-content/uploads/2025/12/icon-key.png" alt="Home Visits" class="icon-img">
                <h3>Home Visits</h3>
                <p>Pop-in checks for feeding and meds</p>
            </a>
        </div>
    </section>

    <?php if ($sitters && !is_wp_error($sitters)): ?>
    <section class="mps-featured-section">
        <h2>Featured Pet Sitters</h2>
        <div class="mps-sitter-grid-modern">
            <?php foreach ($sitters as $sitter): 
                $city = get_post_meta($sitter->ID, 'mps_city', true);
                $thumb = get_the_post_thumbnail_url($sitter->ID, 'medium');
                $name = explode(' - ', $sitter->post_title)[0];
            ?>
            <div class="mps-sitter-card-modern">
                <?php if ($thumb): ?>
                    <img src="<?= esc_url($thumb) ?>" alt="<?= esc_attr($name) ?>" class="mps-sitter-photo">
                <?php else: ?>
                    <div class="mps-sitter-photo" style="background:#eee;"></div>
                <?php endif; ?>
                <div class="mps-sitter-info">
                    <h3 class="mps-sitter-name"><?= esc_html($name) ?></h3>
                    <p class="mps-sitter-location">📍 <?= esc_html($city) ?></p>
                    <a href="<?= get_permalink($sitter->ID) ?>" class="mps-view-btn">View Profile</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
    
    </div><script>
    function mpsRegionChange() {
        var regionSelect = document.getElementById('mps-region-select');
        var suburbWrapper = document.getElementById('mps-suburb-wrapper');
        var suburbSelect = document.getElementById('mps-suburb-select');
        var region = regionSelect.value;

        if (!region) {
            suburbWrapper.style.display = 'none';
            return;
        }

        // Show Wrapper and Loading state
        suburbWrapper.style.display = 'block';
        suburbSelect.innerHTML = '<option>Loading...</option>';

        // Call AJAX
        var formData = new FormData();
        formData.append('action', 'antigravity_get_suburbs');
        formData.append('region', region);

        fetch('/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            suburbSelect.innerHTML = '<option value="">Select Suburb (Optional)</option>';
            if(data.success && data.data && data.data.length > 0) {
                data.data.forEach(function(sub) {
                    var opt = document.createElement('option');
                    opt.value = sub;
                    opt.text = sub;
                    suburbSelect.appendChild(opt);
                });
            } else {
                var opt = document.createElement('option');
                opt.text = "No specific suburbs found";
                suburbSelect.appendChild(opt);
            }
        })
        .catch(err => {
            console.error(err);
            suburbSelect.innerHTML = '<option value="">Error loading</option>';
        });
    }

    function mpsSearch(form) {
        var region = document.getElementById('mps-region-select').value;
        var suburb = document.getElementById('mps-suburb-select').value;
        var service = document.getElementById('mps-service-select').value;

        if (!region) {
            alert('Please select a region');
            return false;
        }

        // If suburb selected, search for suburb. Otherwise search for region.
        var locationQuery = suburb ? suburb : region;
        
        var searchUrl = '/?s=' + encodeURIComponent(locationQuery) + '&post_type=sitter';
        
        window.location.href = searchUrl;
        return false;
    }
    </script>
    
    <?php
    return ob_get_clean();
}
// Register BOTH names (V120 Fix)
add_shortcode('mps_homepage', 'antigravity_v200_render_homepage');
add_shortcode('mps_landing_page', 'antigravity_v200_render_homepage');
