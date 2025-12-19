<?php
/**
 * Template: Rural Hub Page
 * URL: /regions/{state}/rural-pet-sitters/
 */
if (!defined('ABSPATH')) exit;

get_header();

// 1. Get Query Vars
$state_code = get_query_var('mps_state');

// 2. Validate & Get Names
$states = antigravity_v200_states_list();
$state_name = $states[strtoupper($state_code)] ?? strtoupper($state_code);

// 3. Render Page Content
?>
<style>
    /* HACK: Hide default content that might leak through in some themes */
    .ast-article-post, .entry-header, .entry-content { display: none !important; }
    /* Restore visibility for OUR content */
    .mps-hero .entry-header, .mps-wrap .entry-header,
    .mps-hero .entry-content, .mps-wrap .entry-content { display: block !important; }
</style>
<div id="primary" class="content-area" style="margin-top:0 !important;padding-top:0 !important;">
    <main id="main" class="site-main">
        
        <!-- Rural Hero -->
        <section class="mps-hero" style="background:linear-gradient(rgba(74, 124, 89, 0.85), rgba(13, 115, 119, 0.85)), url('/wp-content/uploads/2025/12/rural-hero-v2.jpg');background-size:cover;background-position:center;color:#fff;padding:100px 20px;text-align:center;border-radius:0 0 20px 20px;margin-bottom:60px;">
            <div style="max-width:1100px;margin:0 auto;">
                <p class="eyebrow" style="text-transform:uppercase;font-weight:700;letter-spacing:1px;margin:0 0 16px;color:#8fb996;">Farm & Acreage Care</p>
                <h1 style="font-size:3.5rem;margin:0 0 24px;color:#fff;">Rural Pet Sitters in <?= esc_html($state_name) ?></h1>
                <p class="lead" style="font-size:1.4rem;max-width:700px;margin:0 auto;color:#f0f0f0;">
                    Find experienced sitters for farms, acreages, and rural properties. 
                    Specialized care for livestock, horses, and working dogs.
                </p>
            </div>
        </section>

        <section class="mps-wrap" style="max-width:1100px;margin:0 auto;padding:0 20px 60px;">
            
            <!-- Intro Text -->
            <div style="max-width:800px;margin:0 auto 60px;text-align:center;">
                <h2 style="color:var(--mps-teal, #0d7377);">Professional Farm & Lifestyle Block Care</h2>
                <p style="font-size:1.1rem;line-height:1.8;color:#555;">
                    Finding a sitter for a rural property is different from finding a city dog walker. 
                    Our rural sitters understand checks on water troughs, feeding livestock, safe securing of gates, 
                    and the unique needs of property maintenance.
                </p>
            </div>

            <h2 style="margin-bottom:32px;">Featured Rural Sitters in <?= esc_html($state_name) ?></h2>

            <?php
            // 4. Custom Query for RURAL Sitters
            // Filter by: Location Type = 'Rural' AND State match
            
            $args = [
                'post_type'      => 'sitter',
                'post_status'    => 'publish',
                'posts_per_page' => 24,
                'meta_query'     => [
                    'relation' => 'AND',
                    [
                        'key'     => 'mps_state',
                        'value'   => $state_code,
                        'compare' => '='
                    ],
                    [
                        'key'     => 'mps_location_type',
                        'value'   => 'Rural',
                        'compare' => '='
                    ]
                ]
            ];

            $q = new WP_Query($args);

            if ($q->have_posts()) {
                echo '<div class="mps-sitter-grid-modern" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:24px;">';
                
                while ($q->have_posts()) {
                    $q->the_post();
                    $ID = get_the_ID();
                    
                    // Meta
                    $city_meta = get_post_meta($ID, 'mps_city', true);
                    $region_term = wp_get_post_terms($ID, 'mps_region', ['fields' => 'names']);
                    $region_name = !empty($region_term) ? $region_term[0] : $city_meta;
                    
                    $thumb = antigravity_v200_get_sitter_thumbnail($ID);
                    
                    echo '<div class="mps-sitter-card-modern" style="background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);border:1px solid #eee;">';
                        
                        if ($thumb) {
                            echo '<img src="' . esc_url($thumb) . '" style="width:100%;height:200px;object-fit:cover;">';
                        } else {
                            echo '<div style="width:100%;height:200px;background:#f5f5f5;display:flex;align-items:center;justify-content:center;color:#999;">No Photo</div>';
                        }
                        
                        echo '<div style="padding:20px;">';
                            echo '<h3 style="font-size:1.2rem;margin:0 0 4px;"><a href="' . get_permalink() . '" style="color:#111;text-decoration:none;">' . get_the_title() . '</a></h3>';
                            
                            echo '<p style="margin:0 0 12px;color:#666;font-size:0.9rem;">📍 ' . esc_html($region_name) . '</p>';
                            
                            echo '<div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px;">';
                                echo '<span class="mps-badge" style="background:#e8f0ea;color:#2e7d32;padding:4px 10px;border-radius:20px;font-size:0.8rem;">Rural Experience</span>';
                            echo '</div>';
                            
                            echo '<div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;padding-top:16px;border-top:1px solid #eee;">';
                                echo '<span style="color:#666;font-size:0.8rem;">View availability</span>';
                                echo '<a href="' . get_permalink() . '" style="background:var(--mps-teal, #0d7377);color:#fff;padding:8px 20px;border-radius:20px;text-decoration:none;font-size:0.9rem;">View</a>';
                            echo '</div>';
                        echo '</div>';
                    echo '</div>';
                }
                echo '</div>'; // Grid
                wp_reset_postdata();
                
            } else {
                echo '<div style="background:#f8f9fa;padding:40px;text-align:center;border-radius:12px;">';
                echo '<h3>No specialized rural sitters found in ' . esc_html($state_name) . ' yet.</h3>';
                echo '<p>Do you offer farm sitting? <a href="/list-your-services/">List your services for free</a>.</p>';
                echo '</div>';
            }
            ?>
            
        </section>
    </main>
</div>
<?php
get_footer();


