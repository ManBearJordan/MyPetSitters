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
// SECTION 1: [MPS_PAGE] SHORTCODE
// ===========================================================================

add_shortcode('mps_page', function($atts) {
    $a = shortcode_atts(['city' => '', 'service' => ''], $atts, 'mps_page');
    $city    = trim($a['city']);
    $service = trim($a['service']);
    
    if (!$city && !$service) {
        return '<p>Shortcode needs at least a city or a service.</p>';
    }
    
    $cities = mps_cities_list();
    $services = mps_services_map();
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
        echo mps_render_sitter_grid($city, $service);
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
        
        <?= mps_render_faqs($city, $service); ?>
        
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
        
        <?php echo mps_render_sitter_grid($city, ''); ?>
        
        <h3>Explore other cities</h3>
        <div style="display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin:16px 0 32px;">
            <?php foreach ($cities as $c): if ($c === $city) continue; ?>
                <a href="/cities/<?= $slug($c) ?>/" style="padding:10px 20px;background:#e8f0ea;border-radius:50px;text-decoration:none;color:#4a7c59;font-weight:600;transition:all 0.2s;"><?= $esc($c) ?></a>
            <?php endforeach; ?>
        </div>
        
        <?= mps_render_faqs($city, ''); ?>
        
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
        
        <?= mps_render_faqs('', $service); ?>
        
    <?php } ?>
    
    </section>
    <?php
    return ob_get_clean();
});

// ===========================================================================
// SECTION 1B: [MPS_CITIES] SHORTCODE - Cities Index Page
// ===========================================================================

add_shortcode('mps_cities', function($atts) {
    $cities = mps_cities_list();
    $services = mps_services_map();
    
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
});

// ===========================================================================
// SECTION 1C: [MPS_SERVICES] SHORTCODE - Services Index Page
// ===========================================================================

add_shortcode('mps_services', function($atts) {
    $cities = mps_cities_list();
    $services = mps_services_map();
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
});

// ===========================================================================
// SECTION 2: SITTER GRID RENDERER
// ===========================================================================

function mps_render_sitter_grid($city, $service = '', $limit = 12) {
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
    echo '<div class="mps-results" id="mps-sitter-grid"><h2>' . $heading . '</h2>';
    echo '<div class="mps-cards" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;">';
    
    while ($q->have_posts()) {
        $q->the_post();
        $meta = mps_get_sitter_meta(get_the_ID());
        $thumb = mps_get_sitter_thumbnail(get_the_ID());
        
        $meta_parts = array_filter([
            $meta['suburb'],
            $city ?: $meta['city'],
            $meta['price'] ? 'From ' . $meta['price'] : '',
        ]);
        ?>
        <article class="mps-card" style="border:1px solid #eaeaea;border-radius:12px;overflow:hidden;background:#fff;">
            <a class="mps-card-img" href="<?= esc_url(get_permalink()) ?>">
                <?php if ($thumb): ?>
                    <img src="<?= esc_url($thumb) ?>" alt="<?= esc_attr(get_the_title()) ?>" style="display:block;width:100%;height:auto">
                <?php else: ?>
                    <div style="background:#f0f0f0;height:140px;display:flex;align-items:center;justify-content:center;color:#999;">No photo</div>
                <?php endif; ?>
            </a>
            <div class="mps-card-body" style="padding:12px;">
                <h4 style="margin:0 0 6px;"><a href="<?= esc_url(get_permalink()) ?>"><?= esc_html(get_the_title()) ?></a></h4>
                <p style="margin:0 0 6px;font-size:0.9em;opacity:.8;">
                    <?= esc_html(implode(' &bull; ', $meta_parts)) ?>
                </p>
                <?php if (!empty($meta['services'])): ?>
                    <p style="margin:0;font-size:0.85em;">
                        <?php foreach (array_slice($meta['services'], 0, 3) as $svc): ?>
                            <span style="background:#e8f0ea;padding:2px 6px;border-radius:4px;margin-right:4px;"><?= esc_html($svc) ?></span>
                        <?php endforeach; ?>
                    </p>
                <?php endif; ?>
                <a href="<?= esc_url(get_permalink()) ?>" class="mps-btn" style="display:block;text-align:center;margin-top:12px;padding:8px;font-size:.9em;">View Profile</a>
            </div>
        </article>
        <?php
    }
    wp_reset_postdata();
    
    echo '</div></div>';
    return ob_get_clean();
}

function mps_get_sitter_thumbnail($post_id) {
    if (has_post_thumbnail($post_id)) {
        return get_the_post_thumbnail_url($post_id, 'medium');
    }
    return '';
}

// ===========================================================================
// SECTION 3: FAQ RENDERER
// ===========================================================================

function mps_render_faqs($city = '', $service = '') {
    $faqs = [];
    
        if ($service && $city) {
        $faqs = [
            ['q' => "How much does {$service} cost in {$city}?", 'a' => 'Rates vary by sitter. Compare profiles to find one that fits your budget.'],
            ['q' => "How do I choose the right sitter?", 'a' => 'Read reviews from other pet owners, check their profile details, and always organize a meet-and-greet first.'],
            ['q' => "What areas of {$city} do sitters cover?", 'a' => 'Most sitters list their suburb and travel range in their profile.'],
            ['q' => "What's included in {$service}?", 'a' => 'Each sitter sets their own services. Message them to confirm details.'],
            ['q' => "How do I book {$service} in {$city}?", 'a' => 'Pick your service, enter your suburb, compare trusted local profiles, then book.'],
        ];
    } elseif ($city) {
        $faqs = [
            ['q' => "How do I find a pet sitter in {$city}?", 'a' => 'Browse sitters by service type above, then compare profiles and reviews.'],
            ['q' => "How do I know a sitter is trustworthy?", 'a' => 'We encourage you to read reviews from other local pet owners and meet the sitter in person before booking.'],
            ['q' => "What services are available in {$city}?", 'a' => 'Dog walking, overnight stays, daycare, and home visits.'],
        ];
    } elseif ($service) {
        $faqs = [
            ['q' => "What is {$service}?", 'a' => 'Professional pet care service provided by trusted local sitters.'],
            ['q' => "How much does {$service} cost?", 'a' => 'Rates vary by city and sitter. Compare profiles for pricing.'],
            ['q' => "Is {$service} available in my area?", 'a' => 'We cover major Australian cities. Check your city page.'],
        ];
    }
    
    if (!$faqs) return '';
    
    ob_start();
    ?>
    <div class="mps-faqs" style="margin:32px 0;">
        <h2>FAQs</h2>
        <?php foreach ($faqs as $faq): ?>
            <details style="border:1px solid #eaeaea;border-radius:8px;margin-bottom:8px;padding:12px 16px;">
                <summary style="cursor:pointer;font-weight:600;"><?= esc_html($faq['q']) ?></summary>
                <p style="margin:8px 0 0;opacity:.9;"><?= esc_html($faq['a']) ?></p>
            </details>
        <?php endforeach; ?>
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
            <div style="display:flex;gap:16px;justify-content:center;">
                <a href="#mps-sitter-grid" class="wp-block-button__link" style="background:var(--mps-teal, #0d7377) !important;border-radius:50px;padding:14px 32px;">Find a Sitter</a>
                <a href="/list-your-services/" class="wp-block-button__link" style="background:#fff !important;color:var(--mps-teal, #0d7377) !important;border:1px solid var(--mps-teal, #0d7377);border-radius:50px;padding:14px 32px;">List Your Services</a>
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