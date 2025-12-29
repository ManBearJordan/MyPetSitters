<?php
/**
 * Template: Regional Service Page
 * URL: /regions/{state}/{region}/{service}/
 */
if (!defined('ABSPATH')) exit;

get_header();

// 1. Get Query Vars
$state_code = get_query_var('mps_state');
$region_slug = get_query_var('mps_region_slug');
$service_slug = get_query_var('mps_service_slug');

// 2. Validate & Get Names
$states = antigravity_v200_states_list();
$state_name = $states[strtoupper($state_code)] ?? strtoupper($state_code);

$service_label = antigravity_v200_service_slug_to_label($service_slug);

// Resolve Region Name from Slug
$region_term = get_term_by('slug', $region_slug, 'mps_region');
$region_name = $region_term ? $region_term->name : ucwords(str_replace('-', ' ', $region_slug));

// 3. Render Page Content
?>
<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <section class="mps-wrap" style="max-width:1100px;margin:0 auto;padding:40px 20px;">
            
            <p class="eyebrow" style="text-transform:uppercase;font-weight:600;margin:0 0 8px;color:var(--mps-teal, #0d7377);">
                Regional Network &bull; <?= esc_html($state_name) ?>
            </p>
            
            <h1 style="margin:.25rem 0 16px;"><?= esc_html($service_label) ?> in <?= esc_html($region_name) ?></h1>
            
            <p class="lead" style="font-size:1.2rem;color:#555;margin-bottom:32px;">
                Find trusted <?= strtolower(esc_html($service_label)) ?> providers in <?= esc_html($region_name) ?>, <?= esc_html($state_name) ?>. 
                Compare profiles, check availability, and book your local sitter.
            </p>

            <div class="mps-breadcrumb" style="margin-bottom:32px;font-size:0.9rem;">
                <a href="/services/<?= esc_attr($service_slug) ?>/" style="color:inherit;">Services</a> &rsaquo; 
                <span><?= esc_html($state_name) ?></span> &rsaquo; 
                <span><?= esc_html($region_name) ?></span>
            </div>

            <?php
            // 4. Custom Query for Sitters
            // Filter by: Service AND/OR Suburb
            // V237: Support Smart Router (Suburb vs Service)
            
            $suburb_name = get_query_var('mps_suburb');
            
            $args = [
                'post_type'      => 'sitter',
                'post_status'    => 'publish',
                'posts_per_page' => 24,
                'tax_query'      => [
                    'relation' => 'AND'
                ],
                'meta_query'     => [
                    'relation' => 'AND',
                    [
                        'key'     => 'mps_state',
                        'value'   => $state_code,
                        'compare' => '='
                    ]
                ]
            ];
            
            // Filter by Service (if set)
            if ($service_slug) {
                $args['tax_query'][] = [
                    'taxonomy' => 'mps_service',
                    'field'    => 'slug',
                    'terms'    => $service_slug,
                ];
            }
            
            // Filter by Suburb (if set - V237)
            if ($suburb_name) {
                $args['meta_query'][] = [
                    'key'     => 'mps_suburb',
                    'value'   => $suburb_name,
                    'compare' => '=', // Exact match due to Smart Validation
                ];
            }
            
            // Filter by Region (always set)
            $args['tax_query'][] = [
                'taxonomy' => 'mps_region',
                'field'    => 'slug',
                'terms'    => $region_slug,
            ];

            $q = new WP_Query($args);

            if ($q->have_posts()) {
                // Reuse the grid renderer logic? 
                // antigravity_v200_render_sitter_grid() uses tax inputs, but we have a custom WP_Query here.
                // We'll replicate the grid loop for flexibility.
                echo '<div class="mps-sitter-grid-modern" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:24px;">';
                
                while ($q->have_posts()) {
                    $q->the_post();
                    $ID = get_the_ID();
                    
                    // Meta
                    $city_meta = get_post_meta($ID, 'mps_city', true);
                    $suburb_meta = get_post_meta($ID, 'mps_suburb', true);
                    $radius = get_post_meta($ID, 'mps_radius', true);
                    $thumb = antigravity_v200_get_sitter_thumbnail($ID);
                    
                    // Calculate Price (specific to this service)
                    $p_key = 'mps_price_' . $service_slug;
                    $price = get_post_meta($ID, $p_key, true);
                    
                    echo '<div class="mps-sitter-card-modern" style="background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);border:1px solid #eee;">';
                        
                        if ($thumb) {
                            echo '<img src="' . esc_url($thumb) . '" style="width:100%;height:200px;object-fit:cover;">';
                        } else {
                            echo '<div style="width:100%;height:200px;background:#f5f5f5;display:flex;align-items:center;justify-content:center;color:#999;">No Photo</div>';
                        }
                        
                        echo '<div style="padding:20px;">';
                            echo '<h3 style="font-size:1.2rem;margin:0 0 4px;"><a href="' . get_permalink() . '" style="color:#111;text-decoration:none;">' . get_the_title() . '</a></h3>';
                            
                            echo '<p style="margin:0 0 12px;color:#666;font-size:0.9rem;">📍 ' . esc_html($suburb_meta ? "$suburb_meta" : $city_meta);
                            if ($radius) echo " &bull; {$radius}km radius";
                            echo '</p>';
                            
                            echo '<div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;padding-top:16px;border-top:1px solid #eee;">';
                                if ($price) {
                                    echo '<span style="font-weight:700;color:var(--mps-teal, #0d7377);font-size:1.1rem;">From $' . esc_html($price) . '</span>';
                                } else {
                                    echo '<span style="color:#666;font-size:0.8rem;">Contact for price</span>';
                                }
                                echo '<a href="' . get_permalink() . '" style="background:var(--mps-teal, #0d7377);color:#fff;padding:8px 20px;border-radius:20px;text-decoration:none;font-size:0.9rem;">View</a>';
                            echo '</div>';
                        echo '</div>';
                    echo '</div>';
                }
                echo '</div>'; // Grid
                wp_reset_postdata();
                
            } else {
                echo '<div style="background:#f8f9fa;padding:40px;text-align:center;border-radius:12px;">';
                echo '<h3>No sitters found in this region yet.</h3>';
                echo '<p>Are you a pet sitter in ' . esc_html($region_name) . '? <a href="/list-your-services/">List your services for free</a>.</p>';
                echo '</div>';
            }
            ?>

        </section>
    </main>
</div>
<?php
get_footer();


