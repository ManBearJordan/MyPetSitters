<?php
/**
 * HOST: MPS SEARCH RESULTS
 * Purpose: Display Search Results using the Sitter Grid
 */

if (!defined('ABSPATH')) exit;

get_header();

$search_query = get_search_query();
if (!$search_query && isset($_GET['s'])) {
    $search_query = sanitize_text_field($_GET['s']);
}
$count = $wp_query->found_posts;
?>

<div class="mps-search-page-wrapper" style="background:#f8faf9;min-height:80vh;padding:40px 0;">
    <section class="mps-wrap" style="max-width:1100px;margin:0 auto;padding:0 16px;">
        
        <div style="text-align:center;margin-bottom:40px;">
            <p class="eyebrow" style="text-transform:uppercase;font-weight:600;margin:0 0 8px;color:var(--mps-teal, #0d7377);">Search Results</p>
            <h1>Sitters matching "<?= esc_html($search_query) ?>"</h1>
            <?php if ($count): ?>
                <p class="lead">Found <?= $count ?> local <?= ($count === 1) ? 'sitter' : 'sitters' ?> for you.</p>
            <?php else: ?>
                <p class="lead">We couldn't find any sitters matching that exact term.</p>
                <p><a href="/" style="color:var(--mps-primary);text-decoration:underline;">Browse all cities</a> or try a broader search.</p>
            <?php endif; ?>
        </div>

        <?php
        // RENDER GRID
        // We use the Main Loop which has already been filtered by WordPress (or our pre_get_posts)
        
        if (have_posts()) {
            echo '<div class="mps-sitter-grid-modern" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px;">';
            
            while (have_posts()) {
                the_post();
                // Reuse Card Logic from antigravity_v200_render_sitter_grid
                $ID = get_the_ID();
                $name = get_the_title();
                $thumb = antigravity_v200_get_sitter_thumbnail($ID);
                $city_meta = get_post_meta($ID, 'mps_city', true);
                $suburb_meta = get_post_meta($ID, 'mps_suburb', true);
                
                $price = '';
                try {
                    $price = antigravity_v200_get_lowest_price($ID); // Helper if exists, or manual
                    
                    // Manual Price Logic (Reuse from V80)
                    if (!function_exists('antigravity_v200_get_lowest_price')) {
                         $min_price = 99999;
                         $services_map = antigravity_v200_services_map();
                         foreach ($services_map as $svc_slug) {
                             $p = get_post_meta($ID, 'mps_price_' . $svc_slug, true);
                             if ($p && is_numeric($p) && $p > 0 && $p < $min_price) $min_price = $p;
                         }
                         $price = ($min_price < 99999) ? $min_price : '';
                    }
                } catch (Exception $e) {
                     error_log('MPS Search Grid Error (ID '.$ID.'): ' . $e->getMessage());
                     // Continue graceful failure (price remains empty string)
                }

                $loc = $suburb_meta ? "$suburb_meta, $city_meta" : $city_meta;
                
                ?>
                <div class="mps-sitter-card-modern" style="background: #fff; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: transform 0.3s, box-shadow 0.3s; border:1px solid #eee;">
                    <a href="<?= get_permalink() ?>" style="text-decoration:none;color:inherit;">
                        <?php if ($thumb): ?>
                            <img src="<?= esc_url($thumb) ?>" alt="<?= esc_attr($name) ?>" style="width: 100%; height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div style="width:100%;height:200px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;color:#999;">No Photo</div>
                        <?php endif; ?>
                        
                        <div style="padding: 20px;">
                            <h3 style="margin: 0 0 4px;font-size: 1.25rem; color: #1a2b3c;"><?= esc_html($name) ?></h3>
                            <div style="color: #5a6b7c; font-size: 14px; margin: 0 0 12px;">📍 <?= esc_html($loc) ?></div>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 12px; padding-top: 12px; border-top: 1px solid #eee;">
                                <div>
                                    <?php if ($price): ?>
                                        <span style="font-size: 1.1rem; font-weight: 600; color: #0d7377;">from $<?= esc_html($price) ?></span>
                                    <?php else: ?>
                                        <span style="font-size: 0.9rem; color: #999;">Contact for rates</span>
                                    <?php endif; ?>
                                </div>
                                <span style="background: #0d7377; color: #fff; padding: 8px 16px; border-radius: 25px; font-size: 13px; font-weight: 500;">View</span>
                            </div>
                        </div>
                    </a>
                </div>
                <?php
            }
            echo '</div>';
            
            // Pagination
            echo '<div style="margin-top:40px;text-align:center;">';
            echo paginate_links([
                'prev_text' => '&larr; Previous',
                'next_text' => 'Next &rarr;',
            ]);
            echo '</div>';
            
        } else {
            // No Results Content? Already handled in header
        }
        ?>
        
    </section>
</div>

<?php get_footer(); ?>


