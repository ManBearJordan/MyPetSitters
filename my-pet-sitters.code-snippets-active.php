<?php

/**
 * Search Form Top Nav
 *
 * Search form for Top Nav
 */
function mp_add_search_to_menu( $items, $args ) {
    if ( $args->theme_location == 'primary' ) {
        $items .= '<li class="menu-item menu-search">' . get_search_form(false) . '</li>';
    }
    return $items;
}
add_filter( 'wp_nav_menu_items','mp_add_search_to_menu', 10, 2);

/**
 * MPS – Page Renderer Shortcode
 */
/* Shortcode: [mps_page city="Brisbane" service="Dog Walking"] */
add_shortcode('mps_page', function($atts){
  $a = shortcode_atts(['city'=>'','service'=>''], $atts, 'mps_page');
  $city    = trim($a['city']);
  $service = trim($a['service']);

  $cities = ['Brisbane','Sydney','Melbourne','Perth','Adelaide'];
  $services = [
    'Dog Walking'     => 'dog-walking',
    'Overnight Stays' => 'overnight-stays',
    'Daycare'         => 'daycare',
    'Home Visits'     => 'home-visits',
  ];
  $blurbs = [
    'Dog Walking'     => 'Local dog walkers offering reliable 30–60 min walks with photo updates.',
    'Overnight Stays' => 'In-home boarding or a sitter at your place — message before booking.',
    'Daycare'         => 'Safe daytime care with play and rest in verified sitter homes.',
    'Home Visits'     => 'Pop-in visits for feeding, meds and welfare checks while you’re out.',
  ];

  $esc  = function($s){ return esc_html($s); };
  $slug = function($s){ return sanitize_title($s); };

  /* Card renderer */
  $render_cards = function($q, $city) {
    if ($q->have_posts()){
      echo '<div class="mps-cards">';
      while($q->have_posts()){ $q->the_post();
        $permalink = get_permalink();
        $title     = get_the_title();

        // Thumbnail with robust fallbacks
        $thumb_id = get_post_thumbnail_id();
        $thumb    = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'large') : '';
        if (!$thumb && $thumb_id) {
          $thumb = wp_get_attachment_image_url($thumb_id, 'full');
        }
        if (!$thumb) {
          $attachments = get_children([
            'post_parent'    => get_the_ID(),
            'post_type'      => 'attachment',
            'post_mime_type' => 'image',
            'numberposts'    => 1,
            'orderby'        => 'menu_order ID',
            'order'          => 'ASC',
          ]);
          if ($attachments) {
            $first = array_shift($attachments);
            $thumb = wp_get_attachment_image_url($first->ID, 'large');
            if (!$thumb) $thumb = wp_get_attachment_image_url($first->ID, 'full');
          }
        }

        $suburb  = get_post_meta(get_the_ID(),'mps_suburb',true);
        $price   = get_post_meta(get_the_ID(),'mps_price',true);

        // Normalise service labels from CSV meta
        $svc_str = (string) get_post_meta(get_the_ID(),'mps_services', true);
        $svc_arr = array_values(array_filter(array_map(function($v){
          $v = trim($v);
          if ($v === '') return '';
          if (strcasecmp($v,'Dog Walk')===0 || strcasecmp($v,'Dog Walks')===0) $v = 'Dog Walking';
          if (strcasecmp($v,'Home Visit')===0) $v = 'Home Visits';
          $allowed = ['Dog Walking','Overnight Stays','Daycare','Home Visits'];
          return in_array($v, $allowed, true) ? $v : '';
        }, explode(',', $svc_str))));

        $meta_parts = [];
        if ($suburb) $meta_parts[]=$suburb;
        if ($city)   $meta_parts[]=$city;
        if ($price)  $meta_parts[]='From '.$price;
        $meta_line = implode(' • ',$meta_parts);

        echo '<article class="mps-card">';
          if ($thumb)  echo '<a class="mps-card-img" href="'.esc_url($permalink).'"><img src="'.esc_url($thumb).'" alt="'.esc_attr($title).'"></a>';
          else         echo '<a class="mps-card-img" href="'.esc_url($permalink).'"><div class="mps-card-ph">No photo</div></a>';
          echo '<div class="mps-card-body">';
            echo '<h4 class="mps-card-title"><a href="'.esc_url($permalink).'">'.esc_html($title).'</a></h4>';
            if ($meta_line) echo '<p class="mps-card-meta">'.esc_html($meta_line).'</p>';
            if (!empty($svc_arr)) {
              echo '<p class="mps-card-services">';
              foreach($svc_arr as $lab){ echo '<span class="pill">'.esc_html($lab).'</span>'; }
              echo '</p>';
            }
            echo '<a class="mps-card-cta" href="'.esc_url($permalink).'">View Profile</a>';
          echo '</div>';
        echo '</article>';
      }
      echo '</div>';
      wp_reset_postdata();
      return true;
    }
    return false;
  };

  /* FAQs helpers */
  $build_faqs = function($city, $service){
    $faqs = [];
    if ($city && $service) {
      $sLower = strtolower($service);
      $faqs[] = ["How much does {$service} cost in {$city}?","Rates vary by sitter and suburb. Filter by price on the search page and compare profiles before booking."];
      $faqs[] = ["Are {$sLower} sitters in {$city} background-checked?","Sitters verify identity and add reviews from local pet owners. Always review profiles and message before booking."];
      $faqs[] = ["What areas of {$city} do sitters cover?","Most inner and outer suburbs are covered. Use the search to find sitters near your street or landmark."];
      $faqs[] = ["What’s included in {$service}?","{$service} typically includes agreed activities and care based on your pet’s needs. Check each sitter’s profile for details."];
      $faqs[] = ["How do I book {$sLower} in {$city}?","Search, message the sitter to confirm fit, then book securely online."];
    } elseif ($city) {
      $faqs[] = ["How do I find pet sitters in {$city}?","Pick your service, enter your suburb, compare verified profiles and reviews, then book."];
      $faqs[] = ["Do sitters in {$city} set their own rates?","Yes. Rates are set by sitters. You keep 100% of your sitter payments — we don’t take commission from bookings."];
      $faqs[] = ["Can I meet a sitter before booking?","Yes. Message the sitter to arrange a meet-and-greet first."];
    } elseif ($service) {
      $sLower = strtolower($service);
      $faqs[] = ["How does {$service} work?","{$service} is offered by local, reviewed sitters. Compare profiles, message, then book securely."];
      $faqs[] = ["Which cities offer {$sLower}?","Top cities include Brisbane, Sydney, Melbourne, Perth and Adelaide — more regions are added over time."];
      $faqs[] = ["Can I list my {$sLower} services?","Yes — create your free listing to start receiving enquiries."];
    }
    return $faqs;
  };
  $render_faqs = function($faqs){
    if (empty($faqs)) return '';
    $html  = '<section class="mps-faq" style="max-width:900px;margin:24px auto 0;"><h2>FAQs</h2>';
    foreach ($faqs as [$q,$a]) {
      $html .= '<details style="margin:8px 0;border:1px solid #eee;border-radius:8px;padding:10px;">';
      $html .= '<summary style="font-weight:700;cursor:pointer;">'.esc_html($q).'</summary>';
      $html .= '<div style="margin-top:8px;"><p>'.esc_html($a).'</p></div></details>';
    }
    $html .= '</section>';
    $items = [];
    foreach ($faqs as [$q,$a]) {
      $items[] = ['@type'=>'Question','name'=>wp_strip_all_tags($q),'acceptedAnswer'=>['@type'=>'Answer','text'=>wp_strip_all_tags($a)]];
    }
    $html .= '<script type="application/ld+json">'.wp_json_encode(['@context'=>'https://schema.org','@type'=>'FAQPage','mainEntity'=>$items], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).'</script>';
    return $html;
  };

  ob_start(); ?>
  <section class="mps-wrap" style="max-width:1100px;margin:0 auto;padding:24px 16px;">

  <?php if ($city && $service): ?>
    <?php $citySlug=$slug($city); $svcSlug=$services[$service] ?? $slug($service); ?>
    <p class="eyebrow" style="text-transform:uppercase;font-weight:600;margin:0 0 8px;">Verified pet-care marketplace</p>
    <h1 style="margin:.25rem 0 8px;"><?= $esc($service) ?> in <?= $esc($city) ?></h1>
    <p class="lead">Find trusted <?= strtolower($esc($service)) ?> in <?= $esc($city) ?>. Compare verified sitters, read reviews, and book securely in minutes.</p>

    <p class="mps-inline" style="margin:0 0 16px;">
      <a href="/cities/<?= $citySlug ?>/">← Back to <?= $esc($city) ?></a> ·
      <a href="/services/<?= $svcSlug ?>/">About <?= $esc($service) ?></a>
    </p>

    <div class="wp-block-buttons" style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin:12px 0 24px;">
      <div class="wp-block-button"><a class="wp-block-button__link" href="/?s=<?= urlencode($city.' '.$service) ?>">Find Local Sitters</a></div>
      <div class="wp-block-button is-style-outline"><a class="wp-block-button__link" href="/list-your-services/">List Your Services</a></div>
    </div>

    <?php
    echo '<div class="mps-results"><h2>Local Sitters in '.$esc($city).'</h2>';
    if ( post_type_exists('sitter') ) {
      // City + Service
            $q = new WP_Query([
        'post_type'=>'sitter','post_status'=>'publish','posts_per_page'=>6,
        'tax_query'=>[
          'relation' => 'AND',
          [
            'taxonomy' => 'mps_city',
            'field'    => 'name',
            'terms'    => $city,
          ],
          [
            'taxonomy' => 'mps_service',
            'field'    => 'name',
            'terms'    => $service,
          ],
        ],
      ]);
      if (!$render_cards($q, $city)) {
        echo '<p>No sitters listed yet for this service in '.$esc($city).'.</p>';
      } else {
        echo '<p class="mps-more" style="text-align:center;margin-top:12px;"><a href="/?s='.urlencode($city.' '.$service).'">See more sitters</a></p>';
      }
    } else {
      echo '<p>Listings coming soon. <a href="/list-your-services/">Create your free listing</a>.</p>';
    }
    echo '</div>';
    ?>

    <h2>About <?= $esc($service) ?> in <?= $esc($city) ?></h2>
    <p><?= $esc($blurbs[$service] ?? '') ?></p>

    <h3>Other services in <?= $esc($city) ?></h3>
    <p class="mps-inline">
      <?php foreach ($services as $name=>$sSlug): if ($name === $service) continue; ?>
        <a href="/cities/<?= $citySlug ?>/<?= $sSlug ?>/"><?= $esc($name) ?></a>
      <?php endforeach; ?>
    </p>

    <h3>Top cities for <?= $esc($service) ?></h3>
    <p class="mps-inline">
      <?php foreach ($cities as $c): ?>
        <a href="/cities/<?= $slug($c) ?>/<?= $svcSlug ?>/"><?= $esc($c) ?></a>
      <?php endforeach; ?>
    </p>

    <?= $render_faqs( $build_faqs($city, $service) ); ?>

  <?php elseif ($city): ?>
    <?php $citySlug=$slug($city); ?>
    <p class="eyebrow" style="text-transform:uppercase;font-weight:600;margin:0 0 8px;">Verified pet-care marketplace</p>
    <h1>Pet Sitters in <?= $esc($city) ?></h1>
    <p>Find daycare, dog walking, overnight stays and home visits across <?= $esc($city) ?>. Compare profiles, reviews and pricing.</p>

    <h2>Browse services in <?= $esc($city) ?></h2>
    <p class="mps-inline">
      <?php foreach ($services as $name=>$sSlug): ?>
        <a href="/cities/<?= $citySlug ?>/<?= $sSlug ?>/"><?= $esc($name) ?></a>
      <?php endforeach; ?>
    </p>

    <?php
    // City hub: ANY service
    echo '<div class="mps-results"><h2>Local Sitters in '.$esc($city).'</h2>';
    if ( post_type_exists('sitter') ) {
            $q = new WP_Query([
        'post_type'      => 'sitter',
        'post_status'    => 'publish',
        'posts_per_page' => 12,
        'tax_query'      => [
          [
            'taxonomy' => 'mps_city',
            'field'    => 'name',
            'terms'    => $city,
          ],
        ],
      ]);
      if (!$render_cards($q, $city)) {
        echo '<p>No sitters listed yet in '.$esc($city).'. <a href="/list-your-services/">Be the first to list</a>.</p>';
      }
    } else {
      echo '<p>Listings coming soon. <a href="/list-your-services/">Create your free listing</a>.</p>';
    }
    echo '</div>';
    ?>

    <h3>Explore other cities</h3>
    <p class="mps-inline">
      <?php foreach ($cities as $c): if ($c === $city) continue; ?>
        <a href="/cities/<?= $slug($c) ?>/"><?= $esc($c) ?></a>
      <?php endforeach; ?>
    </p>

    <?= $render_faqs( $build_faqs($city, '') ); ?>

  <?php elseif ($service): ?>
    <?php $svcSlug=$services[$service] ?? $slug($service); ?>
    <p class="eyebrow" style="text-transform:uppercase;font-weight:600;margin:0 0 8px;">Verified pet-care marketplace</p>
    <h1><?= $esc($service) ?> in Australia</h1>
    <p><?= $esc($blurbs[$service] ?? '') ?></p>

    <h2>Top cities</h2>
    <p class="mps-inline">
      <?php foreach ($cities as $c): ?>
        <a href="/cities/<?= $slug($c) ?>/<?= $svcSlug ?>/"><?= $esc($c) ?></a>
      <?php endforeach; ?>
    </p>

    <h3>Browse other services</h3>
    <p class="mps-inline">
      <?php foreach ($services as $name=>$sSlug): if ($name === $service) continue; ?>
        <a href="/services/<?= $sSlug ?>/"><?= $esc($name) ?></a>
      <?php endforeach; ?>
    </p>

    <?= $render_faqs( $build_faqs('', $service) ); ?>

  <?php else: ?>
    <p>Shortcode needs at least a city or a service.</p>
  <?php endif; ?>

  </section>
  <?php
  return ob_get_clean();
});

/**
 * MPS – Auto SEO Meta
 */
/* Auto Title + Meta Description for pages using [mps_page].
   Works with Rank Math (free). No per-page editing required. */

function mps_extract_ctx_from_content($content){
  $ctx = ['city'=>'','service'=>''];
  if (preg_match('/\[mps_page([^\]]*)\]/i', $content, $m)) {
    $atts = $m[1];
    if (preg_match('/city="([^"]*)"/i', $atts, $mm))    $ctx['city'] = trim($mm[1]);
    if (preg_match('/service="([^"]*)"/i', $atts, $mm)) $ctx['service'] = trim($mm[1]);
  }
  return $ctx;
}

add_filter('rank_math/frontend/title', function($title){
  if (!is_page()) return $title;
  global $post; if (!$post) return $title;

  $ctx = mps_extract_ctx_from_content($post->post_content);
  $site = 'My Pet Sitters';

  if ($ctx['city'] && $ctx['service']) {
    return "{$ctx['service']} in {$ctx['city']} | {$site}";
  } elseif ($ctx['city']) {
    return "Pet Sitters in {$ctx['city']} | {$site}";
  } elseif ($ctx['service']) {
    return "{$ctx['service']} in Australia | {$site}";
  } else {
    // Fallbacks for static hub pages with no shortcode
    $slug = $post->post_name;
    if ($slug === 'cities')   return "Cities | {$site}";
    if ($slug === 'services') return "Services | {$site}";
    return $title; // keep whatever Rank Math had
  }
}, 20);

add_filter('rank_math/frontend/description', function($desc){
  if (!is_page()) return $desc;
  global $post; if (!$post) return $desc;

  $ctx = mps_extract_ctx_from_content($post->post_content);
  if ($ctx['city'] && $ctx['service']) {
    $sLower = strtolower($ctx['service']);
    return "Find trusted {$sLower} in {$ctx['city']}. Compare verified sitters, read reviews, and book securely in minutes.";
  } elseif ($ctx['city']) {
    return "Find pet sitters in {$ctx['city']} for daycare, dog walking, overnight stays and home visits. Compare profiles, reviews and pricing.";
  } elseif ($ctx['service']) {
    $sLower = strtolower($ctx['service']);
    return "Discover {$sLower} across Australia. Compare sitter profiles, reviews and pricing, then book securely.";
  } else {
    // Fallbacks for static hub pages
    $slug = $post->post_name;
    if ($slug === 'cities')   return "Browse pet sitters by city: Brisbane, Sydney, Melbourne, Perth and Adelaide.";
    if ($slug === 'services') return "Choose a service to get started: Dog Walking, Overnight Stays, Daycare, Home Visits.";
    return $desc;
  }
}, 20);

/**
 * MPS – Sitter Profiles (CPT) + Frontend Submit + Admin UI
 */
/**
 * MPS – Sitter Profiles (CPT) + Frontend Submit + Admin UI (SAFE)
 */

if (!defined('ABSPATH')) { exit; }

if (!defined('MPS_ADMIN_EMAIL')) {
  define('MPS_ADMIN_EMAIL', 'enquiries@mypetsitters.com.au');
}

if (!defined('MPS_SITTER_BOOTSTRAPPED')) {
  define('MPS_SITTER_BOOTSTRAPPED', true);

  /* ---------- Helpers (guarded) ---------- */
  if (!function_exists('mps_services_map')) {
    function mps_services_map() {
      return [
        'Dog Walking'     => 'dog-walking',
        'Overnight Stays' => 'overnight-stays',
        'Daycare'         => 'daycare',
        'Home Visits'     => 'home-visits',
      ];
    }
  }

  if (!function_exists('mps_cities_list')) {
    function mps_cities_list() {
      return ['Brisbane','Sydney','Melbourne','Perth','Adelaide'];
    }
  }

  if (!function_exists('mps_normalise_services_labels')) {
    function mps_normalise_services_labels($labels) {
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
  }

  if (!function_exists('mps_slugify')) {
    function mps_slugify($text) {
      $text = strtolower(remove_accents($text));
      $text = preg_replace('/[^a-z0-9]+/','-', $text);
      return trim($text,'-');
    }
  }

  /* ---------- Register CPT + Taxonomies ---------- */
  add_action('init', function () {
    // CPT: sitter
    register_post_type('sitter', [
      'label'        => 'Sitters',
      'labels'       => ['name'=>'Sitters','singular_name'=>'Sitter'],
      'public'       => true,
      'publicly_queryable' => true,
      'show_ui'      => true,
      'show_in_menu' => true,
      'show_in_rest' => true,
      'has_archive'  => false,
      'rewrite'      => ['slug'=>'sitter','with_front'=>false],
      'menu_icon'    => 'dashicons-id',
      'supports'     => ['title','editor','excerpt','thumbnail','custom-fields'],
    ]);

    // Taxonomies: ADMIN ONLY (no public URLs)
    register_taxonomy('mps_city', ['sitter'], [
      'label'              => 'Cities',
      'public'             => false,
      'publicly_queryable' => false,
      'show_ui'            => true,
      'show_admin_column'  => true,
      'show_in_rest'       => true,
      'hierarchical'       => true,
      'query_var'          => false,
      'rewrite'            => false,
    ]);

    register_taxonomy('mps_service', ['sitter'], [
      'label'              => 'Services',
      'public'             => false,
      'publicly_queryable' => false,
      'show_ui'            => true,
      'show_admin_column'  => true,
      'show_in_rest'       => true,
      'hierarchical'       => true,
      'query_var'          => false,
      'rewrite'            => false,
    ]);
  }, 0);

  /* One-time rewrite flush */
  add_action('admin_init', function () {
    if (!get_option('mps_sitter_rewrite_flushed_v5')) {
      delete_option('rewrite_rules');
      flush_rewrite_rules(false);
      update_option('mps_sitter_rewrite_flushed_v5', 1, true);
    }
  });

  /* ---------- Admin Meta Box ---------- */
  add_action('add_meta_boxes', function(){
    add_meta_box('mps_sitter_meta', 'Sitter Details', 'mps_sitter_meta_cb', 'sitter', 'normal', 'high');
  });

  if (!function_exists('mps_sitter_meta_cb')) {
    function mps_sitter_meta_cb($post){
      wp_nonce_field('mps_sitter_meta','mps_sitter_meta_nonce');
      $city    = get_post_meta($post->ID,'mps_city',true);
      $suburb  = get_post_meta($post->ID,'mps_suburb',true);
      $price   = get_post_meta($post->ID,'mps_price',true);
      $email   = get_post_meta($post->ID,'mps_email',true);
      $phone   = get_post_meta($post->ID,'mps_phone',true);
      $svcsStr = get_post_meta($post->ID,'mps_services',true);
      $selected = array_filter(array_map('trim', explode(',', (string)$svcsStr)));
      ?>
      <style>.mps-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}</style>
      <div class="mps-grid">
        <p><label>City<br>
          <select name="mps_city">
            <option value="">—</option>
            <?php foreach(mps_cities_list() as $c): ?>
              <option <?php selected($city,$c); ?>><?php echo esc_html($c); ?></option>
            <?php endforeach; ?>
          </select>
        </label></p>
        <p><label>Suburb<br>
          <input type="text" name="mps_suburb" value="<?php echo esc_attr($suburb); ?>">
        </label></p>
        <p><label>Starting Price<br>
          <input type="text" name="mps_price" value="<?php echo esc_attr($price); ?>" placeholder="$ per hour / stay">
        </label></p>
        <p><label>Email<br>
          <input type="email" name="mps_email" value="<?php echo esc_attr($email); ?>">
        </label></p>
        <p><label>Phone<br>
          <input type="text" name="mps_phone" value="<?php echo esc_attr($phone); ?>">
        </label></p>
        <p><strong>Services</strong><br>
          <?php foreach(array_keys(mps_services_map()) as $svc): ?>
            <label style="display:inline-flex;align-items:center;gap:6px;margin:6px 10px 0 0;">
              <input type="checkbox" name="mps_services[]" value="<?php echo esc_attr($svc); ?>" <?php checked(in_array($svc,$selected,true)); ?>>
              <?php echo esc_html($svc); ?>
            </label>
          <?php endforeach; ?>
        </p>
      </div>
      <?php
    }
  }

  /* ---------- Save Meta + Sync Taxonomies ---------- */
  add_action('save_post_sitter', function($post_id){
    if (!isset($_POST['mps_sitter_meta_nonce']) || !wp_verify_nonce($_POST['mps_sitter_meta_nonce'],'mps_sitter_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post',$post_id)) return;

    $city    = isset($_POST['mps_city'])    ? sanitize_text_field($_POST['mps_city'])    : '';
    $suburb  = isset($_POST['mps_suburb'])  ? sanitize_text_field($_POST['mps_suburb'])  : '';
    $price   = isset($_POST['mps_price'])   ? sanitize_text_field($_POST['mps_price'])   : '';
    $email   = isset($_POST['mps_email'])   ? sanitize_email($_POST['mps_email'])        : '';
    $phone   = isset($_POST['mps_phone'])   ? sanitize_text_field($_POST['mps_phone'])   : '';
    $svcsSel = (isset($_POST['mps_services'])&&is_array($_POST['mps_services'])) ? mps_normalise_services_labels($_POST['mps_services']) : [];

    update_post_meta($post_id,'mps_city',$city);
    update_post_meta($post_id,'mps_suburb',$suburb);
    update_post_meta($post_id,'mps_price',$price);
    update_post_meta($post_id,'mps_email',$email);
    update_post_meta($post_id,'mps_phone',$phone);
    update_post_meta($post_id,'mps_services', implode(', ', $svcsSel));

    // Sync to taxonomies
    if ($city && taxonomy_exists('mps_city')) {
      $city_slug = mps_slugify($city);
      $term = term_exists($city, 'mps_city');
      if (!$term) {
        $term = wp_insert_term($city, 'mps_city', ['slug'=>$city_slug]);
      }
      if (!is_wp_error($term)) {
        wp_set_object_terms($post_id, (int)($term['term_id'] ?? $term), 'mps_city', false);
      }
    } else {
      wp_set_object_terms($post_id, [], 'mps_city', false);
    }

    if (taxonomy_exists('mps_service')) {
      $map = mps_services_map();
      $service_term_ids = [];
      foreach ($svcsSel as $label) {
        $slug = $map[$label] ?? mps_slugify($label);
        $t = term_exists($label, 'mps_service');
        if (!$t) { $t = wp_insert_term($label, 'mps_service', ['slug'=>$slug]); }
        if (!is_wp_error($t)) { $service_term_ids[] = (int)($t['term_id'] ?? $t); }
      }
      wp_set_object_terms($post_id, $service_term_ids, 'mps_service', false);
    }
  }, 10);

  add_filter('manage_sitter_posts_columns', function($cols){
    $cols['mps_city']     = 'City';
    $cols['mps_services'] = 'Services';
    return $cols;
  });
  add_action('manage_sitter_posts_custom_column', function($col,$post_id){
    if ($col==='mps_city')     echo esc_html(get_post_meta($post_id,'mps_city',true));
    if ($col==='mps_services') echo esc_html(get_post_meta($post_id,'mps_services',true));
  },10,2);
}

/**
 * MPS – Search includes Sitters
 */
/**
 * MPS – Search Includes Sitters
 * - Adds the 'sitter' CPT to default WordPress search
 * - Keeps posts/pages in results
 */
add_action('pre_get_posts', function($q){
  if (is_admin() || !$q->is_main_query()) return;
  if ($q->is_search()) {
    // Include sitters alongside posts/pages
    $q->set('post_type', ['post','page','sitter']);
    // Reasonable page size for mixed results
    if (!$q->get('posts_per_page')) $q->set('posts_per_page', 10);
  }
});

/**
 * MPS – Sitter Single Renderer & Cleanup
 */
/**
 * MPS – Sitter Single Renderer & Cleanup (FULL)
 * - Replaces the_content on single 'sitter' with a custom profile layout
 * - Prints <h1> title, robust image fallback, normalised service pills
 */

add_filter('the_content', function($content){
  if (!is_singular('sitter') || !in_the_loop() || !is_main_query()) return $content;

  $ID     = get_the_ID();
  $title  = get_the_title($ID);
  $city   = get_post_meta($ID,'mps_city',true);
  $suburb = get_post_meta($ID,'mps_suburb',true);
  $price  = get_post_meta($ID,'mps_price',true);
  $email  = get_post_meta($ID,'mps_email',true);
  $phone  = get_post_meta($ID,'mps_phone',true);

  // Image fallback
  $thumb_id = get_post_thumbnail_id($ID);
  $img      = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'large') : '';
  if (!$img && $thumb_id) $img = wp_get_attachment_image_url($thumb_id, 'full');
  if (!$img) {
    $attachments = get_children([
      'post_parent'    => $ID,
      'post_type'      => 'attachment',
      'post_mime_type' => 'image',
      'numberposts'    => 1,
      'orderby'        => 'menu_order ID',
      'order'          => 'ASC',
    ]);
    if ($attachments) {
      $first = array_shift($attachments);
      $img   = wp_get_attachment_image_url($first->ID, 'large');
      if (!$img) $img = wp_get_attachment_image_url($first->ID, 'full');
    }
  }

  // Services (normalised)
  $svc_str = (string) get_post_meta($ID,'mps_services', true);
  $services = array_values(array_filter(array_map(function($v){
    $v = trim($v);
    if ($v === '') return '';
    if (strcasecmp($v,'Dog Walk')===0 || strcasecmp($v,'Dog Walks')===0) $v = 'Dog Walking';
    if (strcasecmp($v,'Home Visit')===0) $v = 'Home Visits';
    $allowed = ['Dog Walking','Overnight Stays','Daycare','Home Visits'];
    return in_array($v, $allowed, true) ? $v : '';
  }, explode(',', $svc_str))));

  // Meta line
  $meta = [];
  if ($suburb) $meta[] = $suburb;
  if ($city)   $meta[] = $city;
  if ($price)  $meta[] = 'From '.$price;
  $meta_line = $meta ? implode(' • ', $meta) : '';

  ob_start();
  ?>
  <section class="mps-sitter-single" style="max-width:1000px;margin:0 auto;padding:24px 16px;">
    <h1 class="mps-sitter-title" style="margin:0 0 12px;"><?php echo esc_html($title); ?></h1>

    <div class="mps-hero" style="display:grid;grid-template-columns:320px 1fr;gap:20px;align-items:start;">
      <div class="mps-photo">
        <?php if ($img): ?>
          <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($title); ?>" style="width:100%;height:auto;border-radius:12px;">
        <?php else: ?>
          <div style="width:100%;aspect-ratio:4/3;background:#f3f3f3;border-radius:12px;display:flex;justify-content:center;align-items:center;">No photo</div>
        <?php endif; ?>
      </div>
      <div class="mps-head">
        <?php if ($meta_line): ?><p class="mps-meta" style="margin:0 0 8px;"><?php echo esc_html($meta_line); ?></p><?php endif; ?>
        <?php if (!empty($services)): ?>
          <p class="mps-pills" style="margin:8px 0 16px;">
            <?php foreach ($services as $lab): ?>
              <span class="pill" style="display:inline-block;border:1px solid #e1e1e1;border-radius:999px;padding:.25rem .6rem;margin:0 .4rem .4rem 0;font-size:.9rem;"><?php echo esc_html($lab); ?></span>
            <?php endforeach; ?>
          </p>
        <?php endif; ?>

        <div class="mps-ctas" style="display:flex;gap:10px;flex-wrap:wrap;margin-top:8px;">
          <?php if ($email): ?><a class="wp-block-button__link" href="mailto:<?php echo esc_attr($email); ?>">Email</a><?php endif; ?>
          <?php if ($phone): ?><a class="wp-block-button__link is-style-outline" href="tel:<?php echo esc_attr(preg_replace('/\s+/','',$phone)); ?>">Call</a><?php endif; ?>
        </div>
      </div>
    </div>

    <div class="mps-body" style="margin-top:24px;">
      <?php
        // original content (bio)
        $bio = get_post_field('post_content', $ID);
        echo wpautop(wp_kses_post($bio));
      ?>
    </div>
  </section>
  <?php
  return ob_get_clean();
});

/**
 * MPS – About Page Shortcode
 */
/* About page – title included */
add_shortcode('mps_about', function () {
  ob_start(); ?>
  <section class="mps-wrap mps-about">
    <h1 class="mps-title">About My Pet Sitters</h1>

    <p class="lead">We connect pet owners with trusted, local sitters across Australia.</p>

    <div class="mps-inline" style="margin-top:6px;">
      <a href="/cities/">Browse Cities</a> ·
      <a href="/services/">Browse Services</a> ·
      <a href="/list-your-services/">Create a Free Listing</a>
    </div>

    <h2>Our Mission</h2>
    <p>Make pet care simple, safe, and local. Sitters set their own rates and keep 100% of their earnings — we don’t take commission on bookings.</p>

    <h2>How It Works</h2>
    <ol>
      <li><strong>Search:</strong> find sitters near you by city or service.</li>
      <li><strong>Chat first:</strong> message and meet to make sure it’s a good fit.</li>
      <li><strong>Book:</strong> agree dates and pay your sitter directly.</li>
    </ol>

    <h2>Safety &amp; Trust</h2>
    <p>Every sitter profile shows services, price guidance, and contact details. We encourage meet-and-greets before the first booking and clear expectations (feeding, exercise, meds, home rules).</p>

    <div class="wp-block-buttons" style="margin-top:16px;">
      <div class="wp-block-button">
        <a class="wp-block-button__link" href="/cities/">Find Sitters</a>
      </div>
      <div class="wp-block-button is-style-outline">
        <a class="wp-block-button__link" href="/list-your-services/">Become A Sitter</a>
      </div>
    </div>
  </section>
  <?php
  return ob_get_clean();
});

/**
 * MPS – Front-end services array support
 */
// MPS – Front-end services array support
add_action('init', function () {
  if (!is_admin() && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'
      && !empty($_POST['post_type']) && $_POST['post_type'] === 'sitter') {

    if (isset($_POST['mps_services']) && is_array($_POST['mps_services'])) {
      // Sanitize each selected service label from checkbox inputs
      $_POST['mps_services'] = array_values(array_filter(array_map(function($v){
        return sanitize_text_field(wp_unslash($v));
      }, $_POST['mps_services'])));
    }
  }
});

/**
 * MPS – Sitter title fallback on create
 */
// MPS – Sitter title fallback on create
add_action('save_post_sitter', function($post_id, $post, $update){
  // Only on initial create (not updates)
  if ($update) return;

  $title = get_the_title($post_id);
  if (!$title || $title === 'Auto Draft') {
    $name   = get_post_meta($post_id, 'mps_name', true);
    $city   = get_post_meta($post_id, 'mps_city', true);
    $suburb = get_post_meta($post_id, 'mps_suburb', true);

    $pieces   = array_filter([$name, $city, $suburb]);
    $fallback = $pieces ? implode(' – ', $pieces) : 'New Sitter';
    wp_update_post([
      'ID'         => $post_id,
      'post_title' => $fallback,
      'post_name'  => sanitize_title($fallback),
    ]);
  }
}, 20, 3);

/**
 * MPS – Append sitter previews to city pages (non-destructive)
 */
/**
 * MPS – Append sitter previews to city pages (non-destructive)
 *
 * Appends a "Local Sitters" grid BELOW your existing Page content on:
 *   /cities/{city}/
 *   /cities/{city}/{service}/
 * If there are NO sitters, it appends nothing (page remains exactly as written).
 */

if (!function_exists('mps_services_map')) {
  function mps_services_map(){
    return [
      'Dog Walking'     => 'dog-walking',
      'Overnight Stays' => 'overnight-stays',
      'Daycare'         => 'daycare',
      'Home Visits'     => 'home-visits',
    ];
  }
}
if (!function_exists('mps_normalise_services_labels')) {
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
}

/* Card renderer */
function mps_render_sitter_cards_nd(WP_Query $q, $city = ''){
  ob_start();
  if ($q->have_posts()){
    echo '<section class="mps-results" style="max-width:1100px;margin:32px auto 0;padding:0 16px;">';
    echo '<h2 style="margin:0 0 12px;">Local Sitters'.($city ? ' in '.esc_html($city) : '').'</h2>';
    echo '<div class="mps-cards" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;">';
    while($q->have_posts()){ $q->the_post();
      $permalink = get_permalink();
      $title     = get_the_title();

      // robust thumbnail fallback
      $thumb_id = get_post_thumbnail_id();
      $thumb    = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'large') : '';
      if (!$thumb && $thumb_id) $thumb = wp_get_attachment_image_url($thumb_id, 'full');
      if (!$thumb) {
        $attachments = get_children([
          'post_parent'    => get_the_ID(),
          'post_type'      => 'attachment',
          'post_mime_type' => 'image',
          'numberposts'    => 1,
          'orderby'        => 'menu_order ID',
          'order'          => 'ASC',
        ]);
        if ($attachments) {
          $first = array_shift($attachments);
          $thumb = wp_get_attachment_image_url($first->ID, 'large');
          if (!$thumb) $thumb = wp_get_attachment_image_url($first->ID, 'full');
        }
      }

      $suburb = get_post_meta(get_the_ID(),'mps_suburb',true);
      $price  = get_post_meta(get_the_ID(),'mps_price',true);

      $svc_str = (string) get_post_meta(get_the_ID(),'mps_services', true);
      $svc_arr = mps_normalise_services_labels(array_map('trim', explode(',', $svc_str)));

      $meta_parts = [];
      if ($suburb) $meta_parts[] = $suburb;
      if ($city)   $meta_parts[] = $city;
      if ($price)  $meta_parts[] = 'From '.$price;
      $meta_line = implode(' • ',$meta_parts);

      echo '<article class="mps-card" style="border:1px solid #eaeaea;border-radius:12px;overflow:hidden;background:#fff;">';
        if ($thumb)  echo '<a class="mps-card-img" href="'.esc_url($permalink).'"><img style="display:block;width:100%;height:auto" src="'.esc_url($thumb).'" alt="'.esc_attr($title).'"></a>';
        else         echo '<a class="mps-card-img" href="'.esc_url($permalink).'"><div style="aspect-ratio:4/3;background:#f3f3f3;display:flex;align-items:center;justify-content:center;">No photo</div></a>';
        echo '<div class="mps-card-body" style="padding:12px 14px;">';
          echo '<h4 class="mps-card-title" style="margin:.2rem 0 .4rem;font-size:1.05rem;"><a href="'.esc_url($permalink).'" style="text-decoration:none;">'.esc_html($title).'</a></h4>';
          if ($meta_line) echo '<p class="mps-card-meta" style="margin:0 0 .5rem;opacity:.8;">'.esc_html($meta_line).'</p>';
          if (!empty($svc_arr)) {
            echo '<p class="mps-card-services" style="margin:0 0 .75rem;">';
            foreach($svc_arr as $lab){
              echo '<span class="pill" style="display:inline-block;border:1px solid #e1e1e1;border-radius:999px;padding:.2rem .55rem;margin:0 .35rem .35rem 0;font-size:.85rem;">'.esc_html($lab).'</span>';
            }
            echo '</p>';
          }
          echo '<a class="mps-card-cta" href="'.esc_url($permalink).'" style="display:inline-block;border:1px solid #2d8a39;border-radius:10px;padding:.45rem .8rem;text-decoration:none;">View Profile</a>';
        echo '</div>';
      echo '</article>';
    }
    echo '</div>';
    echo '</section>';
  }
  return ob_get_clean();
}

/* Append sitter grid AFTER page content (only when there are results) */
add_filter('the_content', function($content){
  if (!is_page() || !in_the_loop() || !is_main_query()) return $content;

  // Only target URLs like /cities/{city}/ or /cities/{city}/{service}/
  $req  = trim(parse_url(add_query_arg([]), PHP_URL_PATH), '/');
  $parts = explode('/', $req);
  if (count($parts) < 2 || $parts[0] !== 'cities') return $content;

  $city_slug = sanitize_title($parts[1]);
  if (!$city_slug) return $content;

  // Resolve a nice city name (use page title first)
  $city_name = '';
  $page_title = get_the_title();
  if ($page_title && sanitize_title($page_title) === $city_slug) $city_name = $page_title;
  if (!$city_name) {
    $known = ['Brisbane','Sydney','Melbourne','Perth','Adelaide'];
    foreach($known as $c){ if (sanitize_title($c)===$city_slug){ $city_name=$c; break; } }
  }

  // Optional service
  $service_label = '';
  if (isset($parts[2]) && $parts[2] !== '') {
    $service_slug = sanitize_title($parts[2]);
    foreach (mps_services_map() as $label=>$slug) {
      if ($slug === $service_slug) { $service_label = $label; break; }
    }
  }

  $meta_query = [];
  if ($city_name)    $meta_query[] = ['key'=>'mps_city','value'=>$city_name,'compare'=>'='];
  if ($service_label)$meta_query[] = ['key'=>'mps_services','value'=>$service_label,'compare'=>'LIKE'];

  if (empty($meta_query)) return $content;

  $q = new WP_Query([
    'post_type'      => 'sitter',
    'post_status'    => 'publish',
    'posts_per_page' => 12,
    'meta_query'     => $meta_query,
  ]);

  if ($q->have_posts()){
    $append = mps_render_sitter_cards_nd($q, $city_name);
    return $content . $append; // append only
  }

  return $content; // no sitters => leave page exactly as-is
});

/**
 * MPS – Guard sitter publishes (require City + at least one Service)
 */
/**
 * MPS – Guard sitter publishes (require City + at least one Service)
 */
if (!defined('MPS_ADMIN_EMAIL')) {
  define('MPS_ADMIN_EMAIL', 'enquiries@mypetsitters.com.au');
}

add_action('transition_post_status', function($new, $old, $post){
  if ($post->post_type !== 'sitter') return;
  if ($old === 'publish' && $new === 'publish') return; // already live
  if ($new !== 'publish') return;

  $city = trim((string) get_post_meta($post->ID, 'mps_city', true));
  $csv  = trim((string) get_post_meta($post->ID, 'mps_services', true));
  $services = array_filter(array_map('trim', explode(',', $csv)));

  if (!$city || empty($services)) {
    // revert to pending
    remove_action('transition_post_status', __FUNCTION__, 10); // prevent loop
    wp_update_post(['ID' => $post->ID, 'post_status' => 'pending']);
    add_action('transition_post_status', __FUNCTION__, 10, 3);

    $why = !$city ? 'missing City' : 'missing Services';
    $edit = admin_url('post.php?post='.$post->ID.'&action=edit');
    wp_mail(
      MPS_ADMIN_EMAIL,
      'Sitter moved back to Pending (incomplete)',
      "Sitter ID {$post->ID} was set to pending: {$why}.\nReview/edit:\n{$edit}",
      ['Content-Type: text/plain; charset=UTF-8']
    );
  }
}, 10, 3);

/**
 * MPS – Nightly sitter term/meta resync
 */
/**
 * MPS – Nightly sitter term/meta resync
 * Keeps 'mps_service' terms and the 'mps_services' CSV meta in sync,
 * and ensures 'mps_city' term mirrors the 'mps_city' meta.
 * Runs daily via WP-Cron.
 */

/* Service slug → label map used when re-writing canonical CSV */
if (!function_exists('mps_services_map')) {
  function mps_services_map(){
    return [
      'Dog Walking'     => 'dog-walking',
      'Overnight Stays' => 'overnight-stays',
      'Daycare'         => 'daycare',
      'Home Visits'     => 'home-visits',
    ];
  }
}
function mps_services_slug_to_label($slug){
  $labels = array_flip(mps_services_map()); // slug => label
  return $labels[$slug] ?? ucwords(str_replace('-', ' ', $slug));
}

/* Schedule the job once (safe to leave in production) */
add_action('init', function(){
  if (!wp_next_scheduled('mps_nightly_resync')) {
    // schedule ~1 minute from now, then daily thereafter
    wp_schedule_event(time() + 60, 'daily', 'mps_nightly_resync');
  }
});

/* Optional: unschedule on theme switch (harmless if you leave it running) */
add_action('switch_theme', function(){
  $ts = wp_next_scheduled('mps_nightly_resync');
  if ($ts) wp_unschedule_event($ts, 'mps_nightly_resync');
});

/* The resync task */
add_action('mps_nightly_resync', function(){
  $q = new WP_Query([
    'post_type'      => 'sitter',
    'post_status'    => ['publish','pending','draft'],
    'posts_per_page' => 500,
    'fields'         => 'ids',
    'no_found_rows'  => true,
  ]);
  if (empty($q->posts)) return;

  foreach ($q->posts as $post_id) {
    // 1) Ensure mps_city term mirrors mps_city meta
    $city = (string) get_post_meta($post_id, 'mps_city', true);
    if ($city !== '') {
      $city_slug = sanitize_title($city);
      $term = term_exists($city_slug, 'mps_city');
      if (!$term || is_wp_error($term)) {
        $ins = wp_insert_term($city, 'mps_city', ['slug' => $city_slug]);
        if (!is_wp_error($ins)) { $term_id = (int) ($ins['term_id'] ?? 0); }
        else { $term_id = 0; }
      } else {
        $term_id = (int) ($term['term_id'] ?? 0);
      }
      if ($term_id > 0) {
        wp_set_object_terms($post_id, $term_id, 'mps_city', false);
      }
    }

    // 2) Build canonical service labels from assigned terms
    $service_terms = wp_get_object_terms($post_id, 'mps_service', ['fields'=>'all']);
    if (!is_wp_error($service_terms)) {
      $labels = [];
      foreach ($service_terms as $t) {
        $labels[] = mps_services_slug_to_label($t->slug);
      }
      $labels = array_unique(array_filter(array_map('trim', $labels)));
      $csv = implode(', ', $labels);

      // Write back canonical CSV if different
      if ($csv !== (string) get_post_meta($post_id, 'mps_services', true)) {
        update_post_meta($post_id, 'mps_services', $csv);
      }
    }
  }
});

/**
 * MPS – Header Auth Pills (after Search, before CTA)
 */
/**
 * MPS – Header Auth Pills (safe, desktop only; after Search, before CTA)
 * - Moves Primary Menu items with class `mps-auth` to a pill row after Search.
 * - If none exist, renders fallback Join/Login (or Account).
 * - Hard guards: does not run in wp-admin or Customizer.
 */

add_action('wp_enqueue_scripts', function () {

  // Safety: don't touch admin or customizer
  if ( is_admin() || is_customize_preview() ) { return; }

  // Styles for the pills + keep header tidy (no global hide of .mps-auth)
  $css = '
  .mps-auth-wrap{display:flex;gap:8px;align-items:center}
  .mps-auth-wrap a{
    display:inline-flex;align-items:center;
    padding:8px 14px;line-height:1.1;border-radius:999px;
    border:1px solid #2f8a2f;color:#2f8a2f;text-decoration:none;white-space:nowrap
  }
  .mps-auth-wrap a:hover{background:#52b142;border-color:#52b142;color:#fff}
  .ast-desktop .ast-builder-grid-row{flex-wrap:nowrap}
  ';
  if ( wp_style_is('astra-theme-css','enqueued') || wp_style_is('astra-theme-css','registered') ) {
    wp_add_inline_style('astra-theme-css', $css);
  } else {
    add_action('wp_head', function() use ($css){ echo '<style>'.$css.'</style>'; });
  }

  // Data for fallback links
  $data = array(
    'isLoggedIn' => is_user_logged_in() ? 1 : 0,
    'joinUrl'    => site_url('/join/'),
    'loginUrl'   => site_url('/login/'),
    'accountUrl' => site_url('/account/'),
  );

  // Inline JS – desktop only; runs once; resilient to Astra DOM
  $js = 'window.MPS_AUTH_DATA='.wp_json_encode($data).';
  (function(){
    if (!window.matchMedia("(min-width:1025px)").matches) return;

    function el(tag, attrs, text){
      var n=document.createElement(tag);
      if(attrs){ for(var k in attrs){ if(attrs[k]!=null) n.setAttribute(k, attrs[k]); } }
      if(text){ n.appendChild(document.createTextNode(text)); }
      return n;
    }

    function findCenter(){
      return document.querySelector(".ast-primary-header-bar .site-header-section-center");
    }
    function findSearch(center){
      return (center && center.querySelector(".ast-header-search")) ||
             document.querySelector(".ast-primary-header-bar .site-header-section-center .ast-header-search") ||
             document.querySelector(".ast-header-search .ast-search-menu-icon");
    }

    function placeAuth(){
      if (!window.matchMedia("(min-width:1025px)").matches) return;
      var center = findCenter();
      if(!center || center.getAttribute("data-mps-auth-mounted")==="1") return;

      var search = findSearch(center);
      if(!search) return;

      // Create a wrapper once
      var wrap = center.querySelector(".mps-auth-wrap");
      if(!wrap){
        wrap = el("div", {"class":"mps-auth-wrap","aria-label":"Account links"});
        var cta = center.querySelector(".ast-header-button-1, .ast-header-button-2");
        if(cta && cta.parentNode===search.parentNode){
          search.parentNode.insertBefore(wrap, cta);
        }else{
          search.insertAdjacentElement("afterend", wrap);
        }
      }

      // Move (clone) menu anchors from any li.mps-auth; hide original li to avoid layout jump
      var moved=false;
      var menuUL = center.querySelector(".ast-builder-menu-1 .main-header-menu");
      if(menuUL){
        menuUL.querySelectorAll("li.mps-auth").forEach(function(li){
          var a = li.querySelector("a");
          if(a){
            var clone=a.cloneNode(true);
            wrap.appendChild(clone);
            li.style.display="none";
            moved=true;
          }
        });
      }

      // Fallback if nothing to move
      if(!moved && !wrap.querySelector("a")){
        var d=window.MPS_AUTH_DATA||{};
        if(d.isLoggedIn){
          wrap.appendChild(el("a",{href:d.accountUrl},"Account"));
        }else{
          wrap.appendChild(el("a",{href:d.joinUrl},"Join"));
          wrap.appendChild(el("a",{href:d.loginUrl},"Login"));
        }
      }

      center.setAttribute("data-mps-auth-mounted","1");
    }

    function run(){ try{ placeAuth(); }catch(e){} }

    if(document.readyState!=="loading"){ run(); }
    else{ document.addEventListener("DOMContentLoaded", run, {once:true}); }

    // If Astra swaps DOM at desktop breakpoint, re-run once
    var reran=false;
    window.addEventListener("resize", function(){
      if(!reran && window.matchMedia("(min-width:1025px)").matches){
        reran=true; setTimeout(run,250);
      }
    }, {passive:true});
  })();';

  // jQuery is guaranteed in Astra; use its handle for a reliable inline attach
  wp_add_inline_script('jquery-core', $js, 'after');
}, 99);

/**
 * MPS – Taxonomies (Cities & Services)
 */
/* MPS — Taxonomies (no front-end rewrite) + light term sync */

add_action('init', function () {
    // Cities tag
    register_taxonomy('city', ['sitter'], [
        'labels' => ['name' => 'Cities', 'singular_name' => 'City'],
        'public' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'hierarchical' => false,
        // CRITICAL: no front-end rewrite => no /cities/ archive to clash with Pages
        'rewrite' => false,
    ]);

    // Services tag
    register_taxonomy('service', ['sitter'], [
        'labels' => ['name' => 'Services', 'singular_name' => 'Service'],
        'public' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'hierarchical' => false,
        // CRITICAL: no front-end rewrite => no /services/ archive to clash with Pages
        'rewrite' => false,
    ]);
});

// Optional light sync: ensure selected city/service strings become terms on submit
function mps_ensure_terms_exist($taxonomy, $values) {
    foreach ((array)$values as $v) {
        $name = is_array($v) ? ($v['name'] ?? '') : (string)$v;
        if ($name === '') continue;
        if (!term_exists($name, $taxonomy)) {
            wp_insert_term($name, $taxonomy, ['slug' => sanitize_title($name)]);
        }
    }
}

/**
 * MPS – Front-end Submit Shortcode ([mps_submit])
 */
// 3) MPS – Front-end Submit Shortcode
add_shortcode('mps_submit', function () {
  if (!is_user_logged_in()) {
    return '<p>Please <a href="/login/">log in</a> to submit your profile.</p>';
  }
  if (!empty($_POST['mps_submit_nonce']) && wp_verify_nonce($_POST['mps_submit_nonce'],'mps_submit')) {
    $title = sanitize_text_field($_POST['mps_title'] ?? '');
    $content = wp_kses_post($_POST['mps_bio'] ?? '');
    $city = intval($_POST['mps_city'] ?? 0);
    $services = array_map('intval', (array)($_POST['mps_services'] ?? []));

    if ($title && $content) {
      $post_id = wp_insert_post([
        'post_type' => 'mps_sitter',
        'post_title' => $title,
        'post_content' => $content,
        'post_status' => 'pending',
        'post_author' => get_current_user_id(),
      ]);
      if ($post_id && !is_wp_error($post_id)) {
        if ($city) wp_set_post_terms($post_id, [$city], 'mps_city', false);
        if (!empty($services)) wp_set_post_terms($post_id, $services, 'mps_service', false);
        return '<div class="notice success">Submitted! We\'ll review shortly.</div>';
      }
      return '<div class="notice error">Couldn\'t save. Please try again.</div>';
    }
    return '<div class="notice error">Title & bio required.</div>';
  }

  // Build simple form
  $cities = get_terms(['taxonomy'=>'mps_city','hide_empty'=>false]);
  $services = get_terms(['taxonomy'=>'mps_service','hide_empty'=>false]);

  ob_start(); ?>
  <form method="post" class="mps-form">
    <?php wp_nonce_field('mps_submit','mps_submit_nonce'); ?>
    <p><label>Title<br><input type="text" name="mps_title" required></label></p>
    <p><label>Bio<br><textarea name="mps_bio" rows="6" required></textarea></label></p>
    <p><label>City<br>
      <select name="mps_city">
        <option value="">— Select —</option>
        <?php foreach ($cities as $t): ?>
          <option value="<?php echo esc_attr($t->term_id); ?>"><?php echo esc_html($t->name); ?></option>
        <?php endforeach; ?>
      </select>
    </label></p>
    <p><label>Services<br>
      <select name="mps_services[]" multiple size="5" style="min-width:240px;">
        <?php foreach ($services as $t): ?>
          <option value="<?php echo esc_attr($t->term_id); ?>"><?php echo esc_html($t->name); ?></option>
        <?php endforeach; ?>
      </select>
    </label></p>
    <p><button type="submit" class="button button-primary">Submit</button></p>
  </form>
  <?php
  return ob_get_clean();
});

/**
 * MPS – Sitter Title Fallback on Create
 */
// 4) MPS – Sitter title fallback on create
add_action('save_post_mps_sitter', function ($post_id, $post, $update) {
  if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) return;
  if (!$update && empty($post->post_title)) {
    $author = get_user_by('id', $post->post_author);
    $author_name = $author ? $author->display_name : 'Sitter';
    $city_names = wp_get_post_terms($post_id, 'mps_city', ['fields'=>'names']);
    $services = wp_get_post_terms($post_id, 'mps_service', ['fields'=>'names']);
    $title = trim($author_name . ' ' . (!empty($services)? '– '.implode(', ', array_slice($services,0,2)) : '') . (!empty($city_names)? ' in '.$city_names[0] : ''));
    if ($title) {
      wp_update_post(['ID'=>$post_id,'post_title'=>$title]);
    }
  }
}, 10, 3);

/**
 * MPS – Search includes Sitters
 */
// 5) MPS – Search includes Sitters
add_action('pre_get_posts', function ($q) {
  if (is_admin() || !$q->is_main_query()) return;
  if ($q->is_search()) {
    $types = (array) $q->get('post_type');
    if (empty($types)) $types = ['post'];
    $types[] = 'mps_sitter';
    $q->set('post_type', array_unique($types));
  }
});

/**
 * MPS – Sitter Single Renderer & Cleanup
 */
// 6) MPS – Sitter Single Renderer & Cleanup
// Remove default meta/sidebars on sitter single (theme-agnostic best effort)
add_action('wp', function () {
  if (is_singular('mps_sitter')) {
    add_filter('astra_advanced_hooks_support', '__return_false'); // Astra hint
  }
});

// Minimal template wrapper if theme falls back improperly
add_filter('the_content', function ($content) {
  if (!is_singular('mps_sitter')) return $content;

  $id = get_the_ID();
  $city = wp_get_post_terms($id, 'mps_city', ['fields'=>'names']);
  $services = wp_get_post_terms($id, 'mps_service', ['fields'=>'names']);

  ob_start(); ?>
  <div class="mps-profile">
    <div class="mps-profile__head">
      <div class="mps-profile__img"><?php echo get_the_post_thumbnail($id, 'large'); ?></div>
      <div class="mps-profile__top">
        <h1><?php echo esc_html(get_the_title()); ?></h1>
        <?php if ($city): ?><div class="mps-profile__loc"><?php echo esc_html($city[0]); ?></div><?php endif; ?>
        <?php if ($services): ?>
          <div class="mps-profile__pills">
            <?php foreach ($services as $s): ?><span class="pill"><?php echo esc_html($s); ?></span><?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <div class="mps-profile__bio"><?php echo wpautop(get_the_content()); ?></div>
  </div>
  <?php
  return ob_get_clean();
}, 20);

/**
 * MPS – Append sitter previews to City pages (non-destructive)
 */
// 8) MPS – Append sitter previews to City pages (non-destructive)
add_filter('the_content', function ($content) {
  if (!is_tax('mps_city') || !in_the_loop() || !is_main_query()) return $content;

  $term = get_queried_object();
  $q = new WP_Query([
    'post_type' => 'mps_sitter',
    'posts_per_page' => 12,
    'tax_query' => [[
      'taxonomy' => 'mps_city',
      'field'    => 'term_id',
      'terms'    => $term->term_id,
    ]],
  ]);

  if (!$q->have_posts()) return $content;

  ob_start(); ?>
  <div class="mps-results">
    <div class="mps-cards">
      <?php while ($q->have_posts()): $q->the_post(); ?>
        <article class="mps-card">
          <a href="<?php the_permalink(); ?>" class="mps-card-img">
            <?php if (has_post_thumbnail()) { the_post_thumbnail('medium'); } else { echo '<div class="mps-card-ph">No image</div>'; } ?>
          </a>
          <h3 class="mps-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
          <div class="mps-card-meta"><?php echo esc_html(strip_tags(get_the_excerpt())); ?></div>
          <a class="mps-card-cta" href="<?php the_permalink(); ?>">View profile</a>
        </article>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
  </div>
  <?php
  return $content . ob_get_clean();
});

/**
 * MPS – Guard sitter publishes (require City + at least one Service)
 */
// 9) MPS – Guard sitter publishes (require City + at least one Service)
add_action('transition_post_status', function($new, $old, $post){
  if ($post->post_type !== 'mps_sitter') return;
  if ($new !== 'publish') return;

  $has_city = wp_get_post_terms($post->ID, 'mps_city', ['fields'=>'ids']);
  $has_service = wp_get_post_terms($post->ID, 'mps_service', ['fields'=>'ids']);

  if (empty($has_city) || empty($has_service)) {
    // roll back to draft with message
    remove_action('transition_post_status', __FUNCTION__, 10);
    wp_update_post(['ID'=>$post->ID, 'post_status'=>'draft']);
    add_action('admin_notices', function () {
      echo '<div class="notice notice-error"><p>City and at least one Service are required to publish a sitter profile.</p></div>';
    });
    add_action('transition_post_status', __FUNCTION__, 10, 3);
  }
}, 10, 3);

/**
 * MPS – Nightly sitter term/meta resync
 */
// 10a) MPS – Nightly sitter term/meta resync (safe no-op if event exists)
add_action('init', function () {
  if (!wp_next_scheduled('mps_nightly_resync')) {
    wp_schedule_event(time()+600, 'daily', 'mps_nightly_resync');
  }
});
add_action('mps_nightly_resync', function () {
  $q = new WP_Query(['post_type'=>'mps_sitter', 'post_status'=>'any', 'posts_per_page'=>-1, 'fields'=>'ids']);
  foreach ($q->posts as $id) {
    // Example: ensure post title fallback if empty
    if (!get_post_field('post_title', $id)) {
      do_action('save_post_mps_sitter', $id, get_post($id), true);
    }
  }
});

/**
 * MPS – stop archive hijack (temp fix)
 */
/**
 * Disable archives/tax slugs that conflict with Pages and give them unique rewrite bases.
 * After enabling, go to Settings → Permalinks → Save.
 */
add_action('init', function () {
    // If these exist in your project, re-register with safe slugs and no archives.
    // Adjust the handles to match your actual names (examples below).

    // Example taxonomy: city
    if (taxonomy_exists('city')) {
        register_taxonomy('city', ['sitter'], [
            'label'        => 'Cities',
            'public'       => true,
            'hierarchical' => true,
            'rewrite'      => ['slug' => 'mps-city', 'with_front' => false], // <-- no longer /cities
            'show_ui'      => true,
            'show_in_rest' => true,
        ]);
    }

    // Example taxonomy: service
    if (taxonomy_exists('service')) {
        register_taxonomy('service', ['sitter'], [
            'label'        => 'Services',
            'public'       => true,
            'hierarchical' => true,
            'rewrite'      => ['slug' => 'mps-service', 'with_front' => false], // <-- no longer /services
            'show_ui'      => true,
            'show_in_rest' => true,
        ]);
    }

    // Example CPT: sitter (turn off has_archive if it was set)
    if (post_type_exists('sitter')) {
        $pt = get_post_type_object('sitter');
        // Re-register with has_archive false (keeps single URLs working)
        register_post_type('sitter', array_replace((array) $pt, [
            'has_archive' => false,
            'rewrite'     => ['slug' => $pt->rewrite['slug'] ?? 'sitter', 'with_front' => false],
        ]));
    }
}, 20);

// Safety: if someone hits the old archive URLs, 301 them to your Pages.
add_action('template_redirect', function () {
    // Old conflicting bases → redirect to your Pages
    if (is_tax('city') || (function_exists('is_post_type_archive') && is_post_type_archive('sitter'))) {
        wp_redirect(home_url('/cities/'), 301); exit;
    }
    if (is_tax('service')) {
        wp_redirect(home_url('/services/'), 301); exit;
    }
});

/**
 * MPS – Archive Output + Layout + Hub Shortcodes
 */
/**
 * NEW: MPS – Archive Output + Layout + Hub Shortcodes
 * - Ensures /city/* and /service/* show sitters (no blog sidebar)
 * - Prints term Description as SEO intro
 * - Shortcodes for your /cities and /services Pages
 */

// Make taxonomy archives query Sitters and set page size
add_action('pre_get_posts', function ($q) {
  if ($q->is_main_query() && !is_admin() && (is_tax('mps_city') || is_tax('mps_service'))) {
    $q->set('post_type', ['mps_sitter']);
    if (!$q->get('posts_per_page')) $q->set('posts_per_page', 24);
  }
});

// Full-width on Astra for our tax archives
add_filter('astra_page_layout', function ($layout) {
  return (is_tax(['mps_city','mps_service'])) ? 'no-sidebar' : $layout;
});

// Print SEO intro from Term Description at top of archive
add_action('astra_content_top', function () {
  if (is_tax(['mps_city','mps_service'])) {
    $term = get_queried_object();
    if (!empty($term->description)) {
      echo '<div class="term-intro" style="margin:12px 0 24px;max-width:70ch;">';
      echo wpautop( wp_kses_post($term->description) );
      echo '</div>';
    }
  }
});

// Shortcode: list top-level Cities (use on /cities Page)
add_shortcode('mps_cities_index', function () {
  $terms = get_terms(['taxonomy'=>'mps_city','hide_empty'=>false,'parent'=>0,'orderby'=>'name','order'=>'ASC']);
  if (is_wp_error($terms) || empty($terms)) return '';
  $out = '<ul class="mps-term-index" style="display:flex;flex-wrap:wrap;gap:12px;padding-left:0;list-style:none;">';
  foreach ($terms as $t) $out .= '<li><a href="'.esc_url(get_term_link($t)).'">'.esc_html($t->name).'</a></li>';
  return $out.'</ul>';
});

// Shortcode: list top-level Services (use on /services Page)
add_shortcode('mps_services_index', function () {
  $terms = get_terms(['taxonomy'=>'mps_service','hide_empty'=>false,'parent'=>0,'orderby'=>'name','order'=>'ASC']);
  if (is_wp_error($terms) || empty($terms)) return '';
  $out = '<ul class="mps-term-index" style="display:flex;flex-wrap:wrap;gap:12px;padding-left:0;list-style:none;">';
  foreach ($terms as $t) $out .= '<li><a href="'.esc_url(get_term_link($t)).'">'.esc_html($t->name).'</a></li>';
  return $out.'</ul>';
});

/**
 * MPS – HOTFIX: Guarantee Pages win for /cities and /services
 */
/**
 * MPS – HOTFIX: Guarantee Pages win for /cities and /services
 * - If a taxonomy rewrite still tries to capture these paths, force the Page template.
 * - Does not affect /city/{term}/ or /service/{term}/ archives.
 */

if (!defined('ABSPATH')) { exit; }

add_action('template_redirect', function () {
  // Only act on main query and only for clean slugs
  if (!is_main_query() || is_admin()) return;

  // Protect the /cities Page
  if (get_query_var('pagename') === 'cities') {
    // If some taxonomy query vars leaked in, clear them.
    set_query_var('mps_city',    null);
    set_query_var('mps_service', null);
    // Ensure we render the Page.
    return;
  }

  // Protect the /services Page
  if (get_query_var('pagename') === 'services') {
    set_query_var('mps_city',    null);
    set_query_var('mps_service', null);
    return;
  }
}, 0);

/**
 * MPS – Routing Lock (FINAL)
 */
/**
 * MPS – Routing Lock (FINAL)
 * Force /cities/* and /services/* to ALWAYS load as Pages
 */

if (!defined('ABSPATH')) exit;

// Register taxonomies with NO public URLs
add_action('init', function () {
  if (taxonomy_exists('mps_city')) unregister_taxonomy('mps_city');
  if (taxonomy_exists('mps_service')) unregister_taxonomy('mps_service');

  register_taxonomy('mps_city', ['sitter'], [
    'label'               => 'Cities',
    'public'              => true,
    'publicly_queryable'  => false,
    'hierarchical'        => true,
    'show_ui'             => true,
    'show_in_rest'        => true,
    'show_admin_column'   => true,
    'rewrite'             => false,
  ]);

  register_taxonomy('mps_service', ['sitter'], [
    'label'               => 'Services',
    'public'              => true,
    'publicly_queryable'  => false,
    'hierarchical'        => true,
    'show_ui'             => true,
    'show_in_rest'        => true,
    'show_admin_column'   => true,
    'rewrite'             => false,
  ]);
}, 0);

// FORCE pages to load instead of redirecting
add_action('template_redirect', function() {
  $url_path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
  
  // If URL starts with "cities" or "services", find and load the page
  if (strpos($url_path, 'cities') === 0 || strpos($url_path, 'services') === 0) {
    $page_slug = str_replace('/', '', $url_path);
    $page = get_page_by_path($page_slug, OBJECT, 'page');
    
    if (!$page) {
      // Try with slashes for nested pages
      $page = get_page_by_path($url_path, OBJECT, 'page');
    }
    
    if ($page && $page->post_status === 'publish') {
      global $wp_query, $post;
      $wp_query->is_404 = false;
      $wp_query->is_page = true;
      $wp_query->is_singular = true;
      $wp_query->queried_object = $page;
      $wp_query->queried_object_id = $page->ID;
      $post = $page;
      setup_postdata($page);
      status_header(200);
      return;
    }
  }
}, 1);

// Disable canonical redirects for cities/services pages
add_filter('redirect_canonical', function($redirect_url, $requested_url) {
  $path = parse_url($requested_url, PHP_URL_PATH);
  if (strpos($path, '/cities') !== false || strpos($path, '/services') !== false) {
    return false;
  }
  return $redirect_url;
}, 10, 2);

// One-time flush
add_action('init', function () {
  if (!get_option('mps_routing_lock_flushed_v3')) {
    delete_option('rewrite_rules');
    flush_rewrite_rules(false);
    update_option('mps_routing_lock_flushed_v3', 1, true);
  }
}, 99);

/**
 * MPS – FORCE LINKS TO PAGES (Cities/Services) + optional 301s
 */
/**
 * MPS – FORCE LINKS TO PAGES (Cities/Services) + optional 301s
 * Goal: ANYTHING that tries to link to /city/{term} or /service/{term}
 *       will instead output your Page URLs: /cities/{slug} and /services/{slug}.
 *
 * What it does:
 *  1) Filters get_term_link()/term_link so ALL generated links for mps_city/mps_service
 *     point to your Page tree (/cities/* and /services/*) — even if taxonomies exist.
 *  2) (Optional) 301-redirects live traffic from /city/* → /cities/* and /service/* → /services/*.
 *
 * Use: Add as a new snippet and ACTIVATE. Then Settings → Permalinks → Save. Purge cache.
 */

if (!defined('ABSPATH')) exit;

/* ---------- Helper: resolve Page URL under a parent slug ---------- */
if (!function_exists('mps_find_child_page_url')) {
  function mps_find_child_page_url($parent_slug, $child_slug) {
    $parent = get_page_by_path($parent_slug);
    if ($parent) {
      // Try direct child by full path first (fast, works for nested builders)
      $child = get_page_by_path($parent_slug . '/' . $child_slug);
      if ($child) return get_permalink($child->ID);
      // Fallback: construct predictable path
      return home_url('/' . trailingslashit($parent_slug) . trailingslashit($child_slug));
    }
    // Parent page missing — still return the intended pretty URL
    return home_url('/' . trailingslashit($parent_slug) . trailingslashit($child_slug));
  }
}

/* ---------- 1) FORCE get_term_link()/term_link to point to your PAGES ---------- */
add_filter('term_link', function ($url, $term, $taxonomy) {
  if (is_wp_error($url) || !is_object($term)) return $url;

  if ($taxonomy === 'mps_city') {
    // City term → /cities/{slug}/ (Page)
    return mps_find_child_page_url('cities', $term->slug);
  }

  if ($taxonomy === 'mps_service') {
    // Service term → /services/{slug}/ (Page)
    return mps_find_child_page_url('services', $term->slug);
  }

  return $url;
}, 10, 3);

/* ---------- 2) OPTIONAL: 301 redirect real traffic from tax URLs to Page URLs ---------- */
/* Set to true to enable the redirects; false leaves them alone but links are already fixed */
$MPS_ENABLE_CITY_SERVICE_REDIRECTS = true;

add_action('template_redirect', function () use ($MPS_ENABLE_CITY_SERVICE_REDIRECTS) {
  if (!$MPS_ENABLE_CITY_SERVICE_REDIRECTS || is_admin()) return;

  $path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

  // /city/{slug} → /cities/{slug}
  if (preg_match('#^city/([^/]+)/?$#i', $path, $m)) {
    $target = mps_find_child_page_url('cities', sanitize_title($m[1]));
    if ($target) {
      wp_redirect($target, 301);
      exit;
    }
  }

  // /service/{slug} → /services/{slug}
  if (preg_match('#^service/([^/]+)/?$#i', $path, $m)) {
    $target = mps_find_child_page_url('services', sanitize_title($m[1]));
    if ($target) {
      wp_redirect($target, 301);
      exit;
    }
  }
}, 1);

/* ---------- 3) Safety: flush rewrites once (harmless) ---------- */
add_action('init', function () {
  if (!get_option('mps_force_links_flush_done')) {
    flush_rewrite_rules(false);
    update_option('mps_force_links_flush_done', 1, true);
  }
}, 99);

/**
 * MPS – Sitter Profile Create/Edit Form
 */
/**
 * MPS – Sitter Profile Create/Edit Form
 *
 * Shortcode: [mps_sitter_profile]
 * - Logged in sitters (“pro” role) can create or edit their own sitter listing.
 * - Uses the same fields as your existing [mps_sitter_submit] form.
 * - On successful save, sets the post status to “pending” and updates meta.
 * - Non‑sitters will see a notice to log in or register.
 */

if (!defined('ABSPATH')) exit;

add_action('init', function () {
  add_shortcode('mps_sitter_profile', function () {
    // Must be logged in
    if (!is_user_logged_in()) {
      return '<p>Please <a href="/login/">log in</a> as a sitter to create or edit your profile.</p>';
    }

    $user = wp_get_current_user();
    // Must have the “pro” role (adjust if you use a different role slug)
    if (!in_array('pro', (array) $user->roles, true)) {
      return '<p>You must be registered as a sitter to use this form.</p>';
    }

    // Look up existing sitter post by email
    $email = $user->user_email;
    $existing = get_posts([
      'post_type'      => 'sitter',
      'post_status'    => ['publish', 'pending', 'draft'],
      'meta_key'       => 'mps_email',
      'meta_value'     => $email,
      'numberposts'    => 1,
    ]);
    $post_id = $existing ? $existing[0]->ID : 0;

    $errors = [];

    // If the form was submitted, process the fields
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['mps_profile_flag'])) {
      // Honeypot + nonce
      if (!empty($_POST['website'])) $errors[] = 'Spam detected.';
      if (!isset($_POST['mps_profile_nonce']) || !wp_verify_nonce($_POST['mps_profile_nonce'], 'mps_profile')) {
        $errors[] = 'Security check failed.';
      }

      // Collect and sanitize fields
      $name   = sanitize_text_field($_POST['mps_name'] ?? '');
      $city   = sanitize_text_field($_POST['mps_city'] ?? '');
      $suburb = sanitize_text_field($_POST['mps_suburb'] ?? '');
      $price  = sanitize_text_field($_POST['mps_price'] ?? '');
      $phone  = sanitize_text_field($_POST['mps_phone'] ?? '');
      $bio    = wp_kses_post($_POST['mps_bio'] ?? '');
      $svcsSel = (isset($_POST['mps_services']) && is_array($_POST['mps_services']))
        ? mps_normalise_services_labels($_POST['mps_services'])
        : [];

      // Basic validation
      if (!$name)                         $errors[] = 'Please enter your name or business name.';
      if (!$city || !in_array($city, mps_cities_list(), true)) $errors[] = 'Please select a valid city.';
      if (empty($svcsSel))                $errors[] = 'Please select at least one service.';

      if (!$errors) {
        // Prepare post data
        $title   = $name . ' — ' . $city . ($suburb ? ' (' . $suburb . ')' : '');
        $excerpt = $price ? ('From: ' . $price) : '';

        $postarr = [
          'post_type'    => 'sitter',
          'post_status'  => 'pending', // always send to review
          'post_title'   => $title,
          'post_content' => $bio,
          'post_excerpt' => $excerpt,
        ];

        // Update or create
        if ($post_id) {
          $postarr['ID'] = $post_id;
          $post_id = wp_update_post($postarr, true);
        } else {
          $post_id = wp_insert_post($postarr, true);
        }

        if (!is_wp_error($post_id)) {
          // Update metadata
          update_post_meta($post_id, 'mps_city', $city);
          update_post_meta($post_id, 'mps_suburb', $suburb);
          update_post_meta($post_id, 'mps_price', $price);
          update_post_meta($post_id, 'mps_email', $email);
          update_post_meta($post_id, 'mps_phone', $phone);
          update_post_meta($post_id, 'mps_services', implode(', ', $svcsSel));

          // Handle featured image upload (optional)
          if (!empty($_FILES['mps_photo']['name'])) {
            require_once ABSPATH.'wp-admin/includes/file.php';
            require_once ABSPATH.'wp-admin/includes/media.php';
            require_once ABSPATH.'wp-admin/includes/image.php';
            $att_id = media_handle_upload('mps_photo', $post_id);
            if (!is_wp_error($att_id)) {
              set_post_thumbnail($post_id, $att_id);
            }
          }

          // Set taxonomy terms (if taxonomies are registered)
          if (taxonomy_exists('mps_city')) {
            $city_term = term_exists($city, 'mps_city');
            if ($city_term) {
              wp_set_object_terms($post_id, (int)($city_term['term_id'] ?? $city_term), 'mps_city', false);
            }
          }
          if (taxonomy_exists('mps_service')) {
            $map = mps_services_map();
            $service_term_ids = [];
            foreach ($svcsSel as $label) {
              $slug = $map[$label] ?? mps_slugify($label);
              $term = term_exists($label, 'mps_service');
              if (!$term) {
                $term = wp_insert_term($label, 'mps_service', ['slug' => $slug]);
              }
              if (!is_wp_error($term)) {
                $service_term_ids[] = (int)($term['term_id'] ?? $term);
              }
            }
            wp_set_object_terms($post_id, $service_term_ids, 'mps_service', false);
          }

          // Redirect with success parameter
          wp_safe_redirect(add_query_arg('profile_saved', '1', get_permalink(get_page_by_path('account'))));
          exit;
        } else {
          $errors[] = 'Could not save your profile. Please try again.';
        }
      }
    }

    // Pre-fill fields if editing
    $defaults = [
      'name'    => '',
      'city'    => '',
      'suburb'  => '',
      'price'   => '',
      'phone'   => '',
      'bio'     => '',
      'services'=> [],
    ];
    if ($post_id) {
      $defaults['name']    = get_the_title($post_id);
      // Parse title to extract name/city if needed (optional)
      $defaults['city']    = get_post_meta($post_id, 'mps_city', true);
      $defaults['suburb']  = get_post_meta($post_id, 'mps_suburb', true);
      $defaults['price']   = get_post_meta($post_id, 'mps_price', true);
      $defaults['phone']   = get_post_meta($post_id, 'mps_phone', true);
      $defaults['bio']     = get_post_field('post_content', $post_id);
      $defaults['services']= mps_normalise_services_labels(explode(',', (string)get_post_meta($post_id, 'mps_services', true)));
    }

    ob_start();

    if (!empty($_GET['profile_saved'])) {
      echo '<div style="border:1px solid #d4edda;background:#d4edda;padding:12px;border-radius:8px;margin-bottom:12px">';
      echo '<strong>Your profile has been updated and is awaiting approval.</strong></div>';
    }

    if ($errors) {
      echo '<div style="border:1px solid #f3d7d7;background:#fff2f2;padding:12px;border-radius:8px;margin-bottom:12px">';
      echo '<strong>Please fix the following:</strong><ul style="margin:.5em 0 0 1.25em">';
      foreach ($errors as $e) {
        echo '<li>'.esc_html($e).'</li>';
      }
      echo '</ul></div>';
    }
    ?>
    <form method="post" enctype="multipart/form-data" class="mps-form" style="max-width:860px">
      <?php wp_nonce_field('mps_profile', 'mps_profile_nonce'); ?>
      <input type="hidden" name="mps_profile_flag" value="1">
      <div style="display:none"><label>Website <input type="text" name="website" value=""></label></div>

      <p><label>Name / Business*<br>
        <input type="text" name="mps_name" value="<?php echo esc_attr($defaults['name']); ?>" required style="width:100%"></label></p>

      <p><label>City*<br>
        <select name="mps_city" required style="width:100%">
          <option value="">Select a city…</option>
          <?php foreach (mps_cities_list() as $c): ?>
            <option value="<?php echo esc_attr($c); ?>" <?php selected($defaults['city'], $c); ?>><?php echo esc_html($c); ?></option>
          <?php endforeach; ?>
        </select></label></p>

      <p><label>Suburb<br>
        <input type="text" name="mps_suburb" value="<?php echo esc_attr($defaults['suburb']); ?>" style="width:100%" placeholder="e.g., New Farm"></label></p>

      <fieldset style="border:1px solid #e6f2e6;padding:10px;border-radius:8px;margin-bottom:12px">
        <legend style="font-weight:700;">Services*</legend>
        <?php foreach (array_keys(mps_services_map()) as $svc): ?>
          <label style="display:inline-flex;align-items:center;gap:6px;margin:6px 10px 0 0;">
            <input type="checkbox" name="mps_services[]" value="<?php echo esc_attr($svc); ?>"
              <?php checked(in_array($svc, $defaults['services'], true)); ?>>
            <?php echo esc_html($svc); ?>
          </label>
        <?php endforeach; ?>
      </fieldset>

      <p><label>Starting Price<br>
        <input type="text" name="mps_price" value="<?php echo esc_attr($defaults['price']); ?>" style="width:100%" placeholder="$ per hour / stay"></label></p>

      <p><label>Profile Photo (JPG/PNG/WebP)<br>
        <input type="file" name="mps_photo" accept="image/*"></label></p>

      <p><label>Bio (what you offer, experience, pet types)*<br>
        <textarea name="mps_bio" rows="6" required style="width:100%"><?php echo esc_textarea($defaults['bio']); ?></textarea></label></p>

      <p><label>Phone<br>
        <input type="text" name="mps_phone" value="<?php echo esc_attr($defaults['phone']); ?>" style="width:100%"></label></p>

      <p><button type="submit" class="wp-block-button__link">Save profile</button></p>
      <p style="font-size:.9rem;opacity:.8">By submitting you agree to our <a href="/terms/">Terms</a> and <a href="/privacy-policy/">Privacy Policy</a>.</p>
    </form>
    <?php
    return ob_get_clean();
  });
});

/**
 * MPS – Associate existing sitter listing with newly registered user
 */
/**
 * MPS – Associate existing sitter listing with newly registered user
 *
 * When someone registers or logs in with an email that matches the mps_email
 * meta on a “sitter” post, automatically set that sitter post’s author to the
 * new user so the sitter can edit it in their dashboard.
 *
 * NOTE: This does not override any existing register shortcode; you still need
 * to install/activate a [mps_register] handler for the join form to work.
 */

if (!defined('ABSPATH')) {
    exit;
}

function mps_link_sitter_to_user($user) {
    // $user can be either a user ID or WP_User object depending on the hook.
    $user_obj = is_numeric($user) ? get_userdata($user) : $user;
    if (!$user_obj) {
        return;
    }

    $email = $user_obj->user_email;
    if (!$email) {
        return;
    }

    // Find all sitter posts where mps_email matches this email.
    $listings = get_posts([
        'post_type'      => 'sitter',
        'post_status'    => ['publish', 'pending', 'draft'],
        'meta_key'       => 'mps_email',
        'meta_value'     => $email,
        'numberposts'    => -1,
        'fields'         => 'ids',
    ]);

    if ($listings) {
        foreach ($listings as $listing_id) {
            // Assign the post author to this user ID.
            wp_update_post([
                'ID'          => $listing_id,
                'post_author' => $user_obj->ID,
            ]);
        }
    }
}

// Hook on registration (user just created).
add_action('user_register', 'mps_link_sitter_to_user', 10, 1);

// Hook on login (in case accounts were made before adding this).
add_action('wp_login', 'mps_link_sitter_to_user', 10, 2);

/**
 * Be sure the “pro” (sitter) role has capability to edit their own “sitter” posts,
 * otherwise they won’t see an “Edit” link in the dashboard. You can grant this
 * capability by adding something like:
 *
 * $role = get_role('pro');
 * if ($role && !$role->has_cap('edit_sitter')) {
 *     $role->add_cap('edit_sitter');
 *     $role->add_cap('edit_sitters');      // list in admin
 *     $role->add_cap('edit_published_sitters');
 * }
 *
 * Run that once (in a snippet) to set up capabilities.
 */

/**
 * MPS – Safe Register Shortcode
 */
/**
 * MPS – Register Shortcode (Override)
 * This always installs our own `[mps_register]` handler and removes any conflicting one.
 * It does not register taxonomies or affect routing.
 */

if (!defined('ABSPATH')) exit;

add_action('init', function () {

  // Always remove any existing mps_register handler so ours wins.
  remove_shortcode('mps_register');

  add_shortcode('mps_register', function ($atts) {
    // Accept role and redirect args like [mps_register role="pro" redirect="/account/"]
    $a = shortcode_atts([
      'role'     => 'customer',
      'redirect' => '/account/',
    ], $atts, 'mps_register');

    // Already logged in? Point to the account page.
    if (is_user_logged_in()) {
      $dest = home_url($a['redirect']);
      return '<p>You’re already logged in. <a href="' . esc_url($dest) . '">Go to your account</a>.</p>';
    }

    $errors = [];

    // Process form submission on POST.
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mps_reg_flag'])) {

      // Honeypot: hidden field must be empty.
      if (!empty($_POST['website'])) {
        $errors[] = 'Spam detected.';
      }

      // Verify nonce.
      if (!isset($_POST['mps_reg_nonce']) || !wp_verify_nonce($_POST['mps_reg_nonce'], 'mps_reg')) {
        $errors[] = 'Security check failed.';
      }

      $name  = sanitize_text_field($_POST['name']  ?? '');
      $email = sanitize_email($_POST['email'] ?? '');
      $p1    = (string)($_POST['pass1'] ?? '');
      $p2    = (string)($_POST['pass2'] ?? '');

      if (!is_email($email)) {
        $errors[] = 'Please enter a valid email address.';
      }
      if (email_exists($email)) {
        $errors[] = 'An account with that email already exists.';
      }
      if ($p1 === '' || $p2 === '') {
        $errors[] = 'Please enter your password twice.';
      }
      if ($p1 !== $p2) {
        $errors[] = 'Passwords do not match.';
      }

      if (!$errors) {
        // Create the user; use email as both username and email.
        $user_id = wp_create_user($email, $p1, $email);
        if (is_wp_error($user_id)) {
          $errors[] = 'Could not create the account. Please try again.';
        } else {
          // Choose the requested role if it exists; default to subscriber.
          $role_slug = sanitize_key($a['role']);
          $chosen = get_role($role_slug) ? $role_slug : 'subscriber';
          wp_update_user([
            'ID'           => $user_id,
            'display_name' => $name ?: preg_replace('/@.*/', '', $email),
            'nickname'     => $name ?: preg_replace('/@.*/', '', $email),
          ]);
          $u = new WP_User($user_id);
          $u->set_role($chosen);

          // Log them in and redirect.
          wp_set_current_user($user_id);
          wp_set_auth_cookie($user_id, true);
          wp_safe_redirect(home_url($a['redirect']));
          exit;
        }
      }
    }

    // Render the registration form.
    ob_start();
    if ($errors) {
      echo '<div style="border:1px solid #f3d7d7;background:#fff2f2;padding:12px;border-radius:8px;margin-bottom:12px;">';
      echo '<strong>Please fix the following:</strong><ul style="margin:.5em 0 0 1.25em">';
      foreach ($errors as $e) {
        echo '<li>' . esc_html($e) . '</li>';
      }
      echo '</ul></div>';
    }
    ?>
    <form method="post" class="mps-register-form" style="max-width:480px">
      <?php wp_nonce_field('mps_reg', 'mps_reg_nonce'); ?>
      <input type="hidden" name="mps_reg_flag" value="1">
      <div style="display:none"><label>Website <input type="text" name="website" value=""></label></div>

      <p><label>Full name<br><input type="text" name="name" required style="width:100%"></label></p>
      <p><label>Email<br><input type="email" name="email" required style="width:100%"></label></p>
      <p><label>Password<br><input type="password" name="pass1" required style="width:100%"></label></p>
      <p><label>Confirm password<br><input type="password" name="pass2" required style="width:100%"></label></p>

      <p><button type="submit" class="wp-block-button__link">Create account</button></p>
      <p style="opacity:.8">By registering you agree to our <a href="/terms/">Terms</a> and <a href="/privacy-policy/">Privacy Policy</a>.</p>
    </form>
    <?php
    return ob_get_clean();
  });
});

/**
 * MPS – Create "pro" role and assign it to an existing user
 */
/**
 * MPS – Create "pro" role and assign it to an existing user.
 *
 * 1. Adds a “pro” role called “Sitter” if it doesn’t already exist.
 * 2. Gives that role the capabilities needed to read and edit sitter posts.
 * 3. Assigns the “pro” role to a specific user (by email).
 *
 * Adjust the email address below if your admin account uses a different email.
 * Activate this snippet once, then you can deactivate or delete it.
 */

if (!defined('ABSPATH')) exit;

add_action('init', function () {
    // Step 1: Create the role if it's missing.
    if (!get_role('pro')) {
        add_role('pro', 'Sitter', ['read' => true]);
    }

    // Step 2: Add/ensure capabilities for managing sitter posts.
    $pro_role = get_role('pro');
    if ($pro_role) {
        $caps = [
            'read',
            'edit_sitter',            // edit a single sitter
            'edit_sitters',           // list sitter posts in admin
            'edit_published_sitters',
            'publish_sitters',
            'upload_files',
        ];
        foreach ($caps as $cap) {
            if (!$pro_role->has_cap($cap)) {
                $pro_role->add_cap($cap);
            }
        }
    }

    // Step 3: Assign the pro role to your admin user.
    $user_email = 'newfarmdogwalk@gmail.com'; // change this if your admin uses another email
    $user = get_user_by('email', $user_email);
    if ($user && !in_array('pro', (array)$user->roles)) {
        $user->add_role('pro');
    }
});

/**
 * MPS – Login + Account (override, safe)
 */
/**
 * MPS – Login + Account (override, safe)
 * - Replaces any existing [mps_login] and [mps_account] handlers.
 * - Keeps scope to front-end only; no rewrites or CPT changes.
 */

if (!defined('ABSPATH')) exit;

add_action('init', function () {

  /* ---------- [mps_login] ---------- */
  // Always override to avoid collisions with old handlers.
  remove_shortcode('mps_login');
  add_shortcode('mps_login', function ($atts = []) {
    $a = shortcode_atts([
      'redirect' => '/account/',
    ], $atts, 'mps_login');

    // Already logged in → nudge to account
    if (is_user_logged_in()) {
      $dest = esc_url(home_url($a['redirect']));
      return '<p>You’re already logged in. <a href="'.$dest.'">Go to your account</a>.</p>';
    }

    $errors = [];

    // Handle submit
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && !empty($_POST['mps_login_flag'])) {
      // basic nonce
      if (!isset($_POST['mps_login_nonce']) || !wp_verify_nonce($_POST['mps_login_nonce'], 'mps_login')) {
        $errors[] = 'Security check failed. Please reload the page and try again.';
      } else {
        $login_raw = trim((string)($_POST['login'] ?? ''));
        $pass_raw  = (string)($_POST['pass'] ?? '');
        $remember  = !empty($_POST['remember']);

        if ($login_raw === '' || $pass_raw === '') {
          $errors[] = 'Please enter both your email/username and password.';
        } else {
          // Accept either username or email
          $user_login = $login_raw;
          if (strpos($login_raw, '@') !== false) {
            $u = get_user_by('email', $login_raw);
            if ($u) { $user_login = $u->user_login; }
          }

          $creds = [
            'user_login'    => $user_login,
            'user_password' => $pass_raw,
            'remember'      => $remember,
          ];
          $signed = wp_signon($creds, is_ssl());
          if (is_wp_error($signed)) {
            $errors[] = 'Invalid credentials. Please check your details and try again.';
          } else {
            // Success → redirect
            $target = esc_url_raw( home_url( $a['redirect'] ) );
            wp_safe_redirect($target);
            exit;
          }
        }
      }
    }

    // Form UI
    ob_start();
    if ($errors) {
      echo '<div style="border:1px solid #f3d7d7;background:#fff2f2;padding:12px;border-radius:8px;margin-bottom:12px;">';
      echo '<strong>There was a problem:</strong><ul style="margin:.5em 0 0 1.25em">';
      foreach ($errors as $e) echo '<li>'.esc_html($e).'</li>';
      echo '</ul></div>';
    }
    ?>
    <form method="post" class="mps-login-form" style="max-width:420px">
      <?php wp_nonce_field('mps_login', 'mps_login_nonce'); ?>
      <input type="hidden" name="mps_login_flag" value="1">

      <p><label>Email or Username<br>
        <input type="text" name="login" required style="width:100%">
      </label></p>

      <p><label>Password<br>
        <input type="password" name="pass" required style="width:100%">
      </label></p>

      <p style="display:flex;align-items:center;gap:8px">
        <label style="display:inline-flex;align-items:center;gap:6px">
          <input type="checkbox" name="remember" value="1"> Remember me
        </label>
        <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" style="margin-left:auto">Forgot password?</a>
      </p>

      <p><button type="submit" class="wp-block-button__link">Log in</button></p>
    </form>
    <?php
    return ob_get_clean();
  });


  /* ---------- [mps_account] ---------- */
  remove_shortcode('mps_account');
  add_shortcode('mps_account', function () {

    if (!is_user_logged_in()) {
      $login_url = esc_url( home_url('/login/') );
      return '<p>Please <a href="'.$login_url.'">log in</a> to view your account.</p>';
    }

    $u = wp_get_current_user();
    $logout_url = esc_url( wp_logout_url( home_url('/login/') ) );

    // Try to locate sitter listing(s) for this user:
    // 1) By author
    // 2) By email meta (mps_email)
    $sitter_posts = [];
    // By author
    $author_posts = get_posts([
      'post_type'      => 'sitter',
      'author'         => $u->ID,
      'posts_per_page' => 10,
      'post_status'    => ['publish','pending','draft'],
      'fields'         => 'ids',
    ]);
    if ($author_posts) $sitter_posts = array_merge($sitter_posts, $author_posts);

    // By email meta
    $email = (string)$u->user_email;
    if ($email) {
      $meta_posts = get_posts([
        'post_type'      => 'sitter',
        'posts_per_page' => 10,
        'post_status'    => ['publish','pending','draft'],
        'fields'         => 'ids',
        'meta_key'       => 'mps_email',
        'meta_value'     => $email,
      ]);
      if ($meta_posts) $sitter_posts = array_merge($sitter_posts, $meta_posts);
    }

    // Deduplicate
    $sitter_posts = array_values(array_unique(array_map('intval', $sitter_posts)));

    // Best-guess URL for creating a listing (front-end form)
    $create_url = home_url('/list-your-services/');
    if (get_page_by_path('list-your-services-2')) {
      $create_url = get_permalink( get_page_by_path('list-your-services-2') );
    } elseif ($p = get_page_by_path('list-your-services')) {
      $create_url = get_permalink($p);
    } else {
      // ultimate fallback
      $create_url = home_url('/join/');
    }

    ob_start(); ?>
    <section class="mps-account" style="max-width:860px">
      <h2 style="margin-top:0">Account</h2>
      <p><strong><?php echo esc_html($u->display_name ?: $u->user_login); ?></strong><br>
         <?php echo esc_html($u->user_email); ?><br>
         Roles: <?php echo esc_html(implode(', ', $u->roles)); ?>
         &nbsp;|&nbsp; <a href="<?php echo $logout_url; ?>">Log out</a>
      </p>

      <?php if (in_array('pro', (array)$u->roles, true)) : ?>
        <hr>
        <h3>Your sitter listing(s)</h3>
        <?php if ($sitter_posts) : ?>
          <ul>
            <?php foreach ($sitter_posts as $pid) :
              $view = get_permalink($pid);
              $edit = admin_url('post.php?post='.$pid.'&action=edit');
              $status = get_post_status($pid);
              ?>
              <li>
                <strong><?php echo esc_html( get_the_title($pid) ); ?></strong>
                <em>(<?php echo esc_html($status); ?>)</em>
                — <a href="<?php echo esc_url($view); ?>">View</a>
                <?php if ( current_user_can('edit_post', $pid) ) : ?>
                  | <a href="<?php echo esc_url($edit); ?>">Edit</a>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else : ?>
          <p>No listing found linked to your account.</p>
          <p><a class="wp-block-button__link" href="<?php echo esc_url($create_url); ?>">Create your listing</a></p>
        <?php endif; ?>
      <?php else : ?>
        <hr>
        <p>Your account is currently an Owner (not a Sitter). If you need a sitter listing, please register as a sitter or contact support.</p>
      <?php endif; ?>
    </section>
    <?php
    return ob_get_clean();
  });

});

/**
 * MPS SITTER LANDING PAGE
 * 
 * A high-converting landing page for prospective sitters.
 * Shortcode: [mps_sitter_landing]
 */

if (!defined('ABSPATH')) exit;

add_shortcode('mps_sitter_landing', function() {
    ob_start();
    ?>
    <div class="mps-landing-wrapper" style="font-family: 'Inter', sans-serif; color: #333; line-height: 1.6;">
        
        <!-- HERO SECTION -->
        <section style="text-align:center;padding:80px 20px;background:linear-gradient(135deg, #e0f7fa 0%, #ffffff 100%);border-radius:20px;margin-bottom:60px;">
            <h1 style="font-size:3.5rem;margin-bottom:20px;color:#2c3e50;font-weight:800;letter-spacing:-1px;">Turn Your Love for Pets<br>Into <span style="color:#2e7d32;">Extra Income</span></h1>
            <p style="font-size:1.25rem;color:#555;max-width:700px;margin:0 auto 40px;">Join thousands of pet lovers earning money by walking dogs and sitting cats. Set your own schedule, prices, and rules.</p>
            <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;">
                <a href="/join/" class="wp-block-button__link" style="background:#2e7d32;color:#fff;padding:16px 48px;font-size:1.2rem;border-radius:50px!important;text-decoration:none;transition:transform 0.2s;">Become a Sitter</a>
                <a href="#how-it-works" style="padding:16px 32px;font-size:1.1rem;color:#2e7d32;text-decoration:none;border:2px solid #2e7d32;border-radius:50px;font-weight:600;">How it Works</a>
            </div>
        </section>

        <!-- STATS -->
        <section style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:40px;text-align:center;margin-bottom:80px;max-width:1000px;margin-left:auto;margin-right:auto;">
            <div>
                <div style="font-size:3rem;font-weight:bold;color:#2e7d32;">$1k+</div>
                <div style="color:#666;">Top sitters earn monthly</div>
            </div>
            <div>
                <div style="font-size:3rem;font-weight:bold;color:#2e7d32;">0%</div>
                <div style="color:#666;">Listing fees</div>
            </div>
            <div>
                <div style="font-size:3rem;font-weight:bold;color:#2e7d32;">100%</div>
                <div style="color:#666;">Background Checked</div>
            </div>
        </section>

        <!-- BENEFITS -->
        <section style="margin-bottom:80px;">
            <h2 style="text-align:center;font-size:2.5rem;margin-bottom:50px;">Why join My Pet Sitters?</h2>
            
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));gap:40px;">
                <!-- Benefit 1 -->
                <div style="padding:30px;background:#fff;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.05);">
                    <div style="font-size:40px;margin-bottom:20px;">🗓️</div>
                    <h3 style="margin:0 0 10px;">Freedom & Flexibility</h3>
                    <p style="color:#666;margin:0;">You are the boss. Choose exactly when you want to work and which services you offer. No minimum hours required.</p>
                </div>
                
                <!-- Benefit 2 -->
                <div style="padding:30px;background:#fff;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.05);">
                    <div style="font-size:40px;margin-bottom:20px;">💰</div>
                    <h3 style="margin:0 0 10px;">Set Your Own Rates</h3>
                    <p style="color:#666;margin:0;">You define your worth. Set your own nightly rates for sitting, walking, or drop-in visits. Keep what you earn.</p>
                </div>
                
                <!-- Benefit 3 -->
                <div style="padding:30px;background:#fff;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.05);">
                    <div style="font-size:40px;margin-bottom:20px;">🛡️</div>
                    <h3 style="margin:0 0 10px;">Safe & Secure</h3>
                    <p style="color:#666;margin:0;">Our secure messaging and booking system keeps you safe. We verify all owners and support you 24/7.</p>
                </div>
            </div>
        </section>

        <!-- HOW GLORIOUS IT WORKS -->
        <section id="how-it-works" style="background:#f9f9f9;padding:80px 20px;margin:0 -20px 80px;text-align:center;">
             <h2 style="font-size:2.5rem;margin-bottom:50px;">How it works</h2>
             
             <div style="max-width:800px;margin:0 auto;display:flex;flex-direction:column;gap:40px;text-align:left;">
                 <div style="display:flex;gap:24px;align-items:flex-start;">
                     <div style="background:#2e7d32;color:#fff;width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;flex-shrink:0;">1</div>
                     <div>
                         <h3 style="margin:0 0 8px;">Create your free profile</h3>
                         <p style="margin:0;color:#666;">Upload photos, write a bio, and set your preferences. It takes less than 5 minutes to get listed.</p>
                     </div>
                 </div>
                 
                 <div style="display:flex;gap:24px;align-items:flex-start;">
                     <div style="background:#2e7d32;color:#fff;width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;flex-shrink:0;">2</div>
                     <div>
                         <h3 style="margin:0 0 8px;">Accept booking requests</h3>
                         <p style="margin:0;color:#666;">Owners in your area will contact you via our secure messaging system. Chat, meet up, and accept the jobs you want.</p>
                     </div>
                 </div>
                 
                 <div style="display:flex;gap:24px;align-items:flex-start;">
                     <div style="background:#2e7d32;color:#fff;width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;flex-shrink:0;">3</div>
                     <div>
                         <h3 style="margin:0 0 8px;">Get paid</h3>
                         <p style="margin:0;color:#666;">Complete the booking and enjoy getting paid for playing with pets! Build your reputation with 5-star reviews.</p>
                     </div>
                 </div>
             </div>
        </section>

        <!-- FAQ -->
        <section style="max-width:800px;margin:0 auto 80px;">
            <h2 style="text-align:center;font-size:2.5rem;margin-bottom:40px;">Frequently Asked Questions</h2>
            
            <details style="margin-bottom:16px;border:1px solid #eee;border-radius:8px;padding:16px;">
                <summary style="font-weight:bold;cursor:pointer;list-style:none;">Is it free to join?</summary>
                <div style="margin-top:12px;color:#555;">Yes! Creating a profile and listing your services is 100% free. We charge a small service fee only on completed bookings.</div>
            </details>
            
            <details style="margin-bottom:16px;border:1px solid #eee;border-radius:8px;padding:16px;">
                <summary style="font-weight:bold;cursor:pointer;list-style:none;">How do I get paid?</summary>
                <div style="margin-top:12px;color:#555;">Payments are processed securely. You can withdraw your earnings directly to your bank account weekly.</div>
            </details>
            
            <details style="margin-bottom:16px;border:1px solid #eee;border-radius:8px;padding:16px;">
                <summary style="font-weight:bold;cursor:pointer;list-style:none;">Do I need experience?</summary>
                <div style="margin-top:12px;color:#555;">Previous professional experience isn't required, but a genuine love for animals and reliability are must-haves. References help your profile stand out!</div>
            </details>
        </section>

        <!-- CTA FOOTER -->
        <section style="text-align:center;padding:80px 20px;background:#e8f5e9;border-radius:20px;">
            <h2 style="font-size:2.5rem;margin-bottom:20px;color:#1b5e20;">Ready to start sitting?</h2>
            <p style="margin-bottom:30px;font-size:1.2rem;color:#444;">Join the community today and start earning.</p>
            <a href="/join/" class="wp-block-button__link" style="background:#2e7d32;color:#fff;padding:16px 48px;font-size:1.2rem;border-radius:50px!important;text-decoration:none;">Create My Sitter Profile</a>
        </section>

    </div>
    <?php
    return ob_get_clean();
});
