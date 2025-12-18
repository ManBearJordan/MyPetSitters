<?php

/**
 * Make upload filenames lowercase
 *
 * Makes sure that image and file uploads have lowercase filenames.
 * 
 * This is a sample snippet. Feel free to use it, edit it, or remove it.
 */
add_filter( 'sanitize_file_name', 'mb_strtolower' );

/**
 * Disable admin bar
 *
 * Turns off the WordPress admin bar for everyone except administrators.
 * 
 * This is a sample snippet. Feel free to use it, edit it, or remove it.
 */
add_action( 'wp', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		show_admin_bar( false );
	}
} );

/**
 * Allow smilies
 *
 * Allows smiley conversion in obscure places.
 * 
 * This is a sample snippet. Feel free to use it, edit it, or remove it.
 */
add_filter( 'widget_text', 'convert_smilies' );
add_filter( 'the_title', 'convert_smilies' );
add_filter( 'wp_title', 'convert_smilies' );
add_filter( 'get_bloginfo', 'convert_smilies' );

/**
 * Current year
 *
 * Shortcode for inserting the current year into a post or page..
 * 
 * This is a sample snippet. Feel free to use it, edit it, or remove it.
 */
add_shortcode( 'code_snippets_export_4', function () {
	ob_start();
	?>

	<?php echo date( 'Y' ); ?>

	<?php
	return ob_get_clean();
} );

/**
 * MPS – Seed Pages
 */
/* MPS – Seed Pages (run once). Creates Services/Cities pages + city/service children.
   Safe: skips pages that already exist. Deactivate after it runs. */

add_action('admin_init', function () {
  if (!current_user_can('manage_options')) return;
  if (get_option('mps_seed_done')) return; // already ran

  // ---- Helpers
  $get_page_id_by_path = function($path) {
    $p = get_page_by_path($path);
    return $p ? (int)$p->ID : 0;
  };
  $get_or_create = function($title, $slug, $content = '', $parent_id = 0) use ($get_page_id_by_path) {
    // If parent supplied, page path is parent/slug; else just slug
    $path = $parent_id ? (get_post_field('post_name', $parent_id) . '/' . $slug) : $slug;
    $existing_id = $get_page_id_by_path($path);
    if ($existing_id) return $existing_id;
    $id = wp_insert_post([
      'post_title'   => $title,
      'post_name'    => $slug,
      'post_type'    => 'page',
      'post_status'  => 'publish',
      'post_parent'  => $parent_id,
      'post_content' => $content,
    ]);
    return (int)$id;
  };

  // ---- Data
  $cities = ['Brisbane','Sydney','Melbourne','Perth','Adelaide'];
  $services = [
    'Dog Walking'     => 'dog-walking',
    'Overnight Stays' => 'overnight-stays',
    'Daycare'         => 'daycare',
    'Home Visits'     => 'home-visits',
  ];

  // Simple content for top-level pages
  $simple = function($h1, $p = '') {
    return "<!-- wp:heading --><h1>{$h1}</h1><!-- /wp:heading -->"
         . ($p ? "<!-- wp:paragraph --><p>{$p}</p><!-- /wp:paragraph -->" : "");
  };

  // Block template for City page
  $city_tpl = function($city, $services) {
    $city_slug = sanitize_title($city);
    $links = [];
    foreach ($services as $name => $slug) {
      $links[] = "<a href='/" . esc_attr($city_slug) . "/" . esc_attr($slug) . "/'>{$name}</a>";
    }
    $links_html = implode(' · ', $links);
    $h1 = "Pet Sitters in {$city}";
    $p1 = "Find trusted sitters and dog walkers across {$city}. Compare profiles, reviews and pricing.";
    return <<<HTML
<!-- wp:heading --><h1>{$h1}</h1><!-- /wp:heading -->
<!-- wp:paragraph --><p>{$p1}</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>Browse services in {$city}</h2><!-- /wp:heading -->
<!-- wp:paragraph {"className":"mps-inline"} --><p class="mps-inline">{$links_html}</p><!-- /wp:paragraph -->
HTML;
  };

  // Block template for City + Service page
  $city_service_tpl = function($city, $service, $service_slug) {
    $city_slug = sanitize_title($city);
    $service_lower = strtolower($service);
    $h1 = "{$service} in {$city}";
    $p1 = "Find trusted {$service_lower} in {$city}. Compare verified sitters, read reviews, and book securely in minutes.";
    return <<<HTML
<!-- wp:heading {"level":1} --><h1>{$h1}</h1><!-- /wp:heading -->
<!-- wp:paragraph --><p>{$p1}</p><!-- /wp:paragraph -->
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><div class="wp-block-button">
<a class="wp-block-button__link" href="/?s={$city}+{$service}">Find Local Sitters</a>
</div></div>
<!-- /wp:buttons -->
<!-- wp:heading --><h2>Other services in {$city}</h2><!-- /wp:heading -->
<!-- wp:paragraph {"className":"mps-inline"} -->
<p class="mps-inline">
  <a href="/{$city_slug}/dog-walking/">Dog Walking</a> · 
  <a href="/{$city_slug}/daycare/">Daycare</a> · 
  <a href="/{$city_slug}/overnight-stays/">Overnight Stays</a> · 
  <a href="/{$city_slug}/home-visits/">Home Visits</a>
</p>
<!-- /wp:paragraph -->
<!-- wp:heading --><h2>Top cities</h2><!-- /wp:heading -->
<!-- wp:paragraph {"className":"mps-inline"} -->
<p class="mps-inline">
  <a href="/brisbane/">Brisbane</a> ·
  <a href="/sydney/">Sydney</a> ·
  <a href="/melbourne/">Melbourne</a> ·
  <a href="/perth/">Perth</a> ·
  <a href="/adelaide/">Adelaide</a>
</p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><div class="wp-block-button">
<a class="wp-block-button__link" href="/create-listing/">Create Listing</a>
</div></div>
<!-- /wp:buttons -->
HTML;
  };

  // ---- Create top-level pages first (parents)
  $about_id  = $get_or_create('About', 'about', $simple('About My Pet Sitters','We help pet owners find trusted local sitters and dog walkers across Australia.'));
  $blog_id   = $get_or_create('Blog', 'blog', $simple('Blog','Tips, guides, and updates for pet owners and sitters.'));
  $services_id = $get_or_create('Services', 'services', $simple('Services','Choose a service to get started.'));
  $cities_id   = $get_or_create('Cities', 'cities', $simple('Cities', 'Browse sitters by city.'));

  // Service hubs (children of Services)
  foreach ($services as $svc => $slug) {
    $get_or_create($svc, $slug, $simple($svc), $services_id);
  }

  // City pages under Cities
  $city_ids = [];
  foreach ($cities as $city) {
    $city_slug = sanitize_title($city);
    $city_ids[$city] = $get_or_create($city, $city_slug, $city_tpl($city, $services), $cities_id);
  }

  // City + Service pages under each city
  foreach ($cities as $city) {
    $parent = $city_ids[$city] ?? 0;
    foreach ($services as $svc => $svc_slug) {
      $get_or_create("{$city} – {$svc}", $svc_slug, $city_service_tpl($city, $svc, $svc_slug), $parent);
    }
  }

  // Other standalone pages
  $get_or_create('List Your Services', 'list-your-services', $simple('List Your Services','Get local bookings, keep your rates, and pay no commission on bookings.'));
  $get_or_create('Sitter Profiles', 'sitters', $simple('Sitter Profiles','Browse verified sitter profiles by suburb (coming soon).'));
  $get_or_create('Pricing', 'pricing', $simple('Pricing','Clear, transparent pricing for sitters and owners.'));
  $get_or_create('Reviews', 'reviews', $simple('Reviews','Read genuine feedback from local pet owners.'));
  $get_or_create('How It Works', 'how-it-works', $simple('How It Works','1) Search sitters near you  2) Message and meet first  3) Book securely'));
  $get_or_create('FAQ', 'faq', $simple('Frequently Asked Questions','Answers to common questions from owners and sitters.'));
  $get_or_create('Contact', 'contact', $simple('Contact','Email enquiries@mypetsitters.com.au or call 0400 000 000.'));
  $get_or_create('Privacy Policy', 'privacy', $simple('Privacy Policy'));
  $get_or_create('Terms & Conditions', 'terms', $simple('Terms & Conditions'));
  $get_or_create('Sitemap', 'sitemap', $simple('Sitemap'));

  // Mark as done
  update_option('mps_seed_done', 1);
});

/**
 * MPS – Dedupe Pages
 */
/* MPS – Dedupe Pages (UI)
   Tools → MPS Dedupe: Dry Run or Apply
   - De-dupes service children under each City
   - De-dupes services under Services
   - Removes flat pages like brisbane-dog-walking if nested page exists
*/

add_action('admin_menu', function () {
  add_management_page('MPS Dedupe','MPS Dedupe','manage_options','mps-dedupe','mps_dedupe_admin_page');
});

function mps_dedupe_admin_page(){
  if (!current_user_can('manage_options')) return;

  $apply = (isset($_POST['mps_action']) && $_POST['mps_action']==='apply' && check_admin_referer('mps_dedupe'));
  $result = mps_run_dedupe($apply);

  echo '<div class="wrap"><h1>MPS Dedupe</h1>';
  echo '<p>This tool finds duplicate pages and trashes the extras. It keeps the oldest copy.</p>';

  echo '<h2>Results '.($apply?'(Applied)':'(Dry Run)').'</h2>';
  if (empty($result)) {
    echo '<p><strong>No duplicates found.</strong></p>';
  } else {
    echo '<ul style="margin-left:1.2em">';
    foreach ($result as $line) echo '<li>'.esc_html($line).'</li>';
    echo '</ul>';
  }

  echo '<form method="post" style="margin-top:20px;">';
  wp_nonce_field('mps_dedupe');
  echo '<button class="button button-secondary" name="mps_action" value="dry" formnovalidate>Dry Run</button> ';
  echo '<button class="button button-primary" name="mps_action" value="apply">Apply (Trash duplicates)</button>';
  echo '</form></div>';
}

/* Core logic */
function mps_run_dedupe($apply=false){
  $log = [];

  // Adjust if you renamed these parent slugs
  $cities_slug   = 'cities';
  $services_slug = 'services';

  $service_slugs = ['dog-walking','overnight-stays','daycare','home-visits'];
  $cities = ['brisbane','sydney','melbourne','perth','adelaide']; // slugs

  $cities_parent   = mps_get_page_id_by_path($cities_slug);
  $services_parent = mps_get_page_id_by_path($services_slug);

  // 1) Dedupe service children under each city
  if ($cities_parent) {
    foreach ($cities as $city_slug) {
      $city_id = mps_get_child_id_by_slug($cities_parent, $city_slug);
      if (!$city_id) { $log[] = "Skip: city page '/{$cities_slug}/{$city_slug}/' not found."; continue; }

      $children = get_children([
        'post_parent' => $city_id,
        'post_type'   => 'page',
        'post_status' => 'any',
        'numberposts' => -1,
      ]);

      if (!$children) continue;

      // Group by base slug (strip -2/-3)
      $groups = [];
      foreach ($children as $p) {
        $base = preg_replace('/-\d+$/','', $p->post_name);
        $groups[$base][] = $p;
      }

      foreach ($groups as $base => $pages) {
        if (count($pages) <= 1) continue;
        if (!in_array($base, $service_slugs, true)) continue;

        usort($pages, function($a,$b){ return strcmp($a->post_date_gmt, $b->post_date_gmt); }); // oldest first
        $keep = array_shift($pages);
        foreach ($pages as $dup) {
          $log[] = "City '{$city_slug}': keep {$keep->post_name} (#{$keep->ID}), trash {$dup->post_name} (#{$dup->ID})";
          if ($apply) wp_trash_post($dup->ID);
        }
      }
    }
  } else {
    $log[] = "Warning: parent '/{$cities_slug}/' not found.";
  }

  // 2) Dedupe services under Services
  if ($services_parent) {
    $children = get_children([
      'post_parent' => $services_parent,
      'post_type'   => 'page',
      'post_status' => 'any',
      'numberposts' => -1,
    ]);
    if ($children) {
      $groups = [];
      foreach ($children as $p) {
        $base = preg_replace('/-\d+$/','', $p->post_name);
        $groups[$base][] = $p;
      }
      foreach ($groups as $base => $pages) {
        if (count($pages) <= 1) continue;
        if (!in_array($base, $service_slugs, true)) continue;

        usort($pages, function($a,$b){ return strcmp($a->post_date_gmt, $b->post_date_gmt); });
        $keep = array_shift($pages);
        foreach ($pages as $dup) {
          $log[] = "Services: keep {$keep->post_name} (#{$keep->ID}), trash {$dup->post_name} (#{$dup->ID})";
          if ($apply) wp_trash_post($dup->ID);
        }
      }
    }
  } else {
    $log[] = "Warning: parent '/{$services_slug}/' not found.";
  }

  // 3) Remove flat pages 'city-service' if nested exists
  if ($cities_parent) {
    foreach ($cities as $city_slug) {
      $city_id = mps_get_child_id_by_slug($cities_parent, $city_slug);
      if (!$city_id) continue;

      foreach ($service_slugs as $svc_slug) {
        $nested_id = mps_get_child_id_by_slug($city_id, $svc_slug);
        if (!$nested_id) continue;

        $flat_slug = $city_slug.'-'.$svc_slug; // e.g., brisbane-dog-walking
        $flat_id   = mps_get_page_id_by_path($flat_slug);
        if ($flat_id && $flat_id != $nested_id) {
          $log[] = "Flat vs nested: nested '/{$city_slug}/{$svc_slug}/' exists; trash flat '/{$flat_slug}/' (#{$flat_id})";
          if ($apply) wp_trash_post($flat_id);
        }
      }
    }
  }

  return $log;
}

/* Helpers */
function mps_get_page_id_by_path($path){
  $p = get_page_by_path($path, OBJECT, 'page');
  return $p ? (int)$p->ID : 0;
}
function mps_get_child_id_by_slug($parent_id, $child_slug){
  $kids = get_children([
    'post_parent' => $parent_id,
    'name'        => $child_slug,
    'post_type'   => 'page',
    'post_status' => 'any',
    'numberposts' => -1,
  ]);
  if (!$kids) {
    // fallback: scan all children
    $kids = get_children([
      'post_parent' => $parent_id,
      'post_type'   => 'page',
      'post_status' => 'any',
      'numberposts' => -1,
    ]);
    foreach ($kids as $k) {
      if ($k->post_name === $child_slug) return (int)$k->ID;
    }
    return 0;
  }
  $kid = array_shift($kids);
  return (int)$kid->ID;
}

/**
 * MPS – Convert Pages to Shortcode
 */
/* Converts City, Service, and City+Service pages to use [mps_page] shortcode.
   Dry-run first; set $APPLY=true to write changes. */
add_action('admin_init', function(){
  if (!current_user_can('manage_options')) return;

  $APPLY = true; // <-- change to true after reviewing the dry run output below

  $services = ['Dog Walking'=>'dog-walking','Overnight Stays'=>'overnight-stays','Daycare'=>'daycare','Home Visits'=>'home-visits'];
  $cities   = ['Brisbane','Sydney','Melbourne','Perth','Adelaide'];

  $log = [];

  // Helpers
  $get_id_by_title = function($t){
    $p = get_page_by_title($t, OBJECT, 'page'); return $p ? (int)$p->ID : 0;
  };
  $children = function($parent_id){
    return get_children(['post_parent'=>$parent_id,'post_type'=>'page','post_status'=>'any','numberposts'=>-1]);
  };
  $set_content = function($id,$content) use (&$log,$APPLY){
    $title = get_the_title($id);
    $log[] = ($APPLY ? 'SET' : 'WOULD SET')." #$id ($title)";
    if ($APPLY) wp_update_post(['ID'=>$id,'post_content'=>$content]);
  };

  // 1) Service hubs under Services
  $services_parent = $get_id_by_title('Services');
  if ($services_parent){
    foreach ($children($services_parent) as $p){
      $name = array_search($p->post_name, $services, true);
      if ($name){
        $short = '[mps_page service="'.esc_attr($name).'"]';
        $set_content($p->ID, $short);
      }
    }
  }

  // 2) Cities under Cities
  $cities_parent = $get_id_by_title('Cities');
  if ($cities_parent){
    foreach ($children($cities_parent) as $city_page){
      $city_name = $city_page->post_title;
      if (in_array($city_name, $cities, true)){
        // City page
        $set_content($city_page->ID, '[mps_page city="'.esc_attr($city_name).'"]');
        // City + Service children
        foreach ($children($city_page->ID) as $svc_page){
          foreach ($services as $svc_name=>$svc_slug){
            // match by base slug (handles -2 duplicates)
            $base = preg_replace('/-\d+$/','', $svc_page->post_name);
            if ($base === $svc_slug){
              $set_content($svc_page->ID, '[mps_page city="'.esc_attr($city_name).'" service="'.esc_attr($svc_name).'"]');
            }
          }
        }
      }
    }
  }

  // Show summary
  add_action('admin_notices', function() use ($log,$APPLY){
    echo '<div class="notice notice-success"><p><strong>MPS Convert → '.($APPLY?'APPLIED':'DRY RUN').'</strong></p><ul>';
    if (!$log) echo '<li>No target pages found.</li>';
    foreach ($log as $l) echo '<li>'.esc_html($l).'</li>';
    echo '</ul></div>';
  });
});

/**
 * MPS – Taxonomies & Term Sync
 */
/**
 * MPS – Taxonomies & Term Sync (NO ARCHIVES)
 * - Registers mps_city and mps_service for 'sitter' WITHOUT front-end archives
 *   so /cities/... and your existing Pages keep working.
 * - Syncs terms from meta (mps_city, mps_services CSV) on save_post_sitter
 * - Writes back a canonical CSV to mps_services so admin checkboxes stay correct
 */

function mps_services_map(){
  return [
    'Dog Walking'     => 'dog-walking',
    'Overnight Stays' => 'overnight-stays',
    'Daycare'         => 'daycare',
    'Home Visits'     => 'home-visits',
  ];
}
function mps_normalise_services_labels($labels){
  $allowed = array_keys(mps_services_map());
  $clean = [];
  foreach ((array)$labels as $lab) {
    $lab = sanitize_text_field($lab);
    if (strcasecmp($lab,'Dog Walk')===0 || strcasecmp($lab,'Dog Walks')===0) $lab = 'Dog Walking';
    if (strcasecmp($lab,'Home Visit')===0) $lab = 'Home Visits';
    if (in_array($lab, $allowed, true)) $clean[$lab] = true;
  }
  return array_keys($clean);
}

/* -------- Register taxonomies WITHOUT public archives or rewrites -------- */
add_action('init', function(){
  register_taxonomy('mps_city', 'sitter', [
    'label'              => 'Cities',
    'public'             => true,
    'show_ui'            => true,
    'show_in_rest'       => true,
    'hierarchical'       => false,
    'publicly_queryable' => false,   // <— no front-end queries
    'query_var'          => false,   // <— no ?mps_city=
    'rewrite'            => false,   // <— no /mps_city/... URLs
  ]);
  register_taxonomy('mps_service', 'sitter', [
    'label'              => 'Services',
    'public'             => true,
    'show_ui'            => true,
    'show_in_rest'       => true,
    'hierarchical'       => false,
    'publicly_queryable' => false,
    'query_var'          => false,
    'rewrite'            => false,
  ]);
}, 5);

/* -------- Helpers -------- */
function mps_service_label_to_slug($label){
  $map = mps_services_map(); // label => slug
  if (isset($map[$label])) return $map[$label];
  $l = trim($label);
  if (strcasecmp($l,'Dog Walk')===0 || strcasecmp($l,'Dog Walks')===0) return 'dog-walking';
  if (strcasecmp($l,'Home Visit')===0) return 'home-visits';
  return sanitize_title($l);
}
function mps_ensure_term($name, $taxonomy, $slug=null){
  if (!$name) return 0;
  $slug = $slug ?: sanitize_title($name);
  $term = term_exists($slug, $taxonomy);
  if ($term && !is_wp_error($term)) return (int)$term['term_id'];
  $res = wp_insert_term($name, $taxonomy, ['slug'=>$slug]);
  return is_wp_error($res) ? 0 : (int)$res['term_id'];
}

/* -------- Sync terms from meta on save -------- */
add_action('save_post_sitter', function($post_id){
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

  // City term from meta
  $city = get_post_meta($post_id,'mps_city',true);
  $city_ids = [];
  if ($city) $city_ids[] = mps_ensure_term($city, 'mps_city', sanitize_title($city));
  wp_set_object_terms($post_id, array_filter($city_ids), 'mps_city', false);

  // Service terms from meta CSV labels (normalised)
  $svc_str   = (string) get_post_meta($post_id, 'mps_services', true);
  $labels_in = array_filter(array_map('trim', explode(',', $svc_str)));
  $labels    = mps_normalise_services_labels($labels_in);

  $svc_ids = [];
  foreach ($labels as $lab) {
    $svc_ids[] = mps_ensure_term($lab, 'mps_service', mps_service_label_to_slug($lab));
  }
  wp_set_object_terms($post_id, array_filter($svc_ids), 'mps_service', false);

  // Write back canonical CSV so admin checkboxes mirror the assigned terms
  $service_terms = wp_get_object_terms($post_id, 'mps_service', ['fields'=>'all']);
  if (!is_wp_error($service_terms) && $service_terms) {
    $labels_canonical = [];
    foreach ($service_terms as $t) {
      switch ($t->slug) {
        case 'dog-walking':      $labels_canonical[] = 'Dog Walking'; break;
        case 'overnight-stays':  $labels_canonical[] = 'Overnight Stays'; break;
        case 'daycare':          $labels_canonical[] = 'Daycare'; break;
        case 'home-visits':      $labels_canonical[] = 'Home Visits'; break;
        default:                 $labels_canonical[] = $t->name;
      }
    }
    update_post_meta($post_id, 'mps_services', implode(', ', array_unique($labels_canonical)));
  } else {
    delete_post_meta($post_id, 'mps_services');
  }
}, 20);

/**
 * MPS – Sitter Profiles (CPT)
 */
// 2) MPS – Sitter Profiles (CPT)
add_action('init', function () {
  register_post_type('mps_sitter', [
    'label' => 'Sitters',
    'public' => true,
    'has_archive' => false,
    'menu_position' => 21,
    'show_in_rest' => true,
    'supports' => ['title','editor','thumbnail','excerpt','author','custom-fields'],
    'rewrite' => ['slug' => 'sitter', 'with_front' => false],
  ]);
});

/**
 * MPS – HOTFIX: Force non-colliding tax slugs + immediate flush
 */
/**
 * MPS – HOTFIX: Force non-colliding tax slugs + immediate flush (runs once)
 * - Re-registers mps_city → /city/{term}/ and mps_service → /service/{term}/
 * - Prevents collisions with your Pages /cities/ and /services/
 * - Flushes rewrite rules immediately (front or admin) exactly once
 */

if (!defined('ABSPATH')) { exit; }

add_action('init', function () {

  // If something previously registered these taxonomies with bad slugs, unregister first.
  if (function_exists('unregister_taxonomy')) {
    // Suppress notices if not yet registered.
    if (taxonomy_exists('mps_city'))    unregister_taxonomy('mps_city');
    if (taxonomy_exists('mps_service')) unregister_taxonomy('mps_service');
  }

  // Re-register with SAFE slugs that won't collide with your Pages.
  register_taxonomy('mps_city', ['sitter'], [
    'label'         => 'Cities',
    'public'        => true,
    'publicly_queryable' => true,
    'hierarchical'  => true,
    'show_ui'       => true,
    'show_in_rest'  => true,
    'rewrite'       => ['slug' => ''sitter-city', 'hierarchical' => true, 'with_front' => false],
  ]);

  register_taxonomy('mps_service', ['sitter'], [
    'label'         => 'Services',
    'public'        => true,
    'publicly_queryable' => true,
    'hierarchical'  => true,
    'show_ui'       => true,
    'show_in_rest'  => true,
    'rewrite'       => ['slug' => 'sitter-service', 'hierarchical' => true, 'with_front' => false],
  ]);

}, 0); // priority 0 so we override anything else

// One-time aggressive flush (works on front or admin)
add_action('init', function () {
  if (!get_option('mps_hotfix_flush_done')) {
    flush_rewrite_rules(false);
    update_option('mps_hotfix_flush_done', 1, true);
  }
}, 99);

/**
 * MPS – ONE-TIME: Sync Sitters to Taxonomies
 */
// Find all sitters
$sitters = get_posts([
    'post_type' => 'sitter',
    'post_status' => 'any',
    'numberposts' => -1
]);

echo "<h2>Syncing " . count($sitters) . " sitters to taxonomies</h2>";

foreach ($sitters as $sitter) {
    echo "<p>Processing: {$sitter->post_title} (ID: {$sitter->ID})</p>";
    
    // Get city from meta
    $city = get_post_meta($sitter->ID, 'mps_city', true);
    if ($city) {
        // Create term if doesn't exist
        if (!term_exists($city, 'mps_city')) {
            wp_insert_term($city, 'mps_city', ['slug' => sanitize_title($city)]);
            echo "  - Created city term: {$city}<br>";
        }
        // Assign to sitter
        wp_set_object_terms($sitter->ID, $city, 'mps_city', false);
        echo "  - Assigned city: {$city}<br>";
    }
    
    // Get services from meta (CSV)
    $services_csv = get_post_meta($sitter->ID, 'mps_services', true);
    if ($services_csv) {
        $services = array_filter(array_map('trim', explode(',', $services_csv)));
        foreach ($services as $service) {
            // Create term if doesn't exist
            if (!term_exists($service, 'mps_service')) {
                wp_insert_term($service, 'mps_service', ['slug' => sanitize_title($service)]);
                echo "  - Created service term: {$service}<br>";
            }
        }
        // Assign all services to sitter
        wp_set_object_terms($sitter->ID, $services, 'mps_service', false);
        echo "  - Assigned services: " . implode(', ', $services) . "<br>";
    }
}

// Flush permalinks
flush_rewrite_rules(false);
echo "<p><strong>Done! Permalinks flushed.</strong></p>";

/**
 * debug
 */
delete_option('rewrite_rules');
delete_option('mps_routing_lock_flushed_v2');
delete_option('mps_hotfix_flush_done');
delete_option('mps_sitter_rewrite_flushed_v2');
flush_rewrite_rules(true);
echo 'Rewrite rules cleared and rebuilt.';
