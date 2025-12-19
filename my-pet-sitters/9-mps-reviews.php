<?php
/**
 * MPS REVIEWS & RATINGS
 * 
 * Extends WordPress Comments to serve as a Review system.
 * - Adds Star Rating (1-5) to comments
 * - Calculates Averages
 * - Customizes Comment Form
 */

if (!defined('ABSPATH')) exit;

// HANDLE FORM SUBMISSION
add_action('admin_post_mps_submit_review', 'antigravity_v200_handle_review_submission');
add_action('admin_post_nopriv_mps_submit_review', 'antigravity_v200_handle_review_submission'); // For non-logged-in users
function antigravity_v200_handle_review_submission() {
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'mps_submit_review_nonce')) {
        wp_die('Security check failed.');
    }

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $comment_content = isset($_POST['comment_content']) ? sanitize_textarea_field($_POST['comment_content']) : '';
    $author = isset($_POST['author']) ? sanitize_text_field($_POST['author']) : '';
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';

    if (!$post_id || !get_post($post_id)) {
        wp_die('Invalid post ID.');
    }

    if ($rating < 1 || $rating > 5) {
        wp_die('Please select a valid rating.');
    }

    if (empty($comment_content)) {
        wp_die('Please enter your review.');
    }

    $comment_data = array(
        'comment_post_ID'      => $post_id,
        'comment_author'       => $author,
        'comment_author_email' => $email,
        'comment_author_url'   => $url,
        'comment_content'      => $comment_content,
        'comment_type'         => 'review', // Custom comment type
        'comment_parent'       => 0,
        'user_id'              => get_current_user_id(),
        'comment_approved'     => 0, // Moderate comments by default
    );

    $comment_id = wp_insert_comment($comment_data);

    if ($comment_id) {
        add_comment_meta($comment_id, 'mps_rating', $rating);
        wp_redirect(get_permalink($post_id) . '#comment-' . $comment_id);
        exit;
    } else {
        wp_die('Error submitting review.');
    }
}

// [mps_review_form] SHORTCODE
add_shortcode('mps_review_form', 'antigravity_v200_review_form_shortcode');
function antigravity_v200_review_form_shortcode($atts) {
    global $post;
    if (!$post) return ''; // Ensure we are on a post/page

    $output = '';
    $output .= '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="post" class="mps-review-form">';
    $output .= wp_nonce_field('mps_submit_review_nonce', '_wpnonce', true, false);
    $output .= '<input type="hidden" name="action" value="mps_submit_review">';
    $output .= '<input type="hidden" name="post_id" value="' . esc_attr($post->ID) . '">';

    // Rating field
    $output .= '<p class="comment-form-rating" style="margin-bottom:20px;">
        <label for="rating" style="display:block;margin-bottom:8px;font-weight:bold;">Rating *</label>
        <select name="rating" id="rating" required style="padding:8px;border-radius:4px;border:1px solid #ddd;min-width:150px;">
            <option value="">Select a rating...</option>
            <option value="5">★★★★★ Excellent (5)</option>
            <option value="4">★★★★☆ Good (4)</option>
            <option value="3">★★★☆☆ Average (3)</option>
            <option value="2">★★☆☆☆ Poor (2)</option>
            <option value="1">★☆☆☆☆ Terrible (1)</option>
        </select>
    </p>';

    // Comment content
    $output .= '<p class="comment-form-comment">
        <label for="comment_content" style="display:block;margin-bottom:8px;font-weight:bold;">Your Review *</label>
        <textarea id="comment_content" name="comment_content" cols="45" rows="8" required style="width:100%;padding:8px;border-radius:4px;border:1px solid #ddd;"></textarea>
    </p>';

    // Author, Email, URL fields (if not logged in)
    if (!is_user_logged_in()) {
        $output .= '<p class="comment-form-author">
            <label for="author" style="display:block;margin-bottom:8px;font-weight:bold;">Name *</label>
            <input id="author" name="author" type="text" value="" size="30" required style="width:100%;padding:8px;border-radius:4px;border:1px solid #ddd;">
        </p>';
        $output .= '<p class="comment-form-email">
            <label for="email" style="display:block;margin-bottom:8px;font-weight:bold;">Email *</label>
            <input id="email" name="email" type="email" value="" size="30" required style="width:100%;padding:8px;border-radius:4px;border:1px solid #ddd;">
        </p>';
        $output .= '<p class="comment-form-url">
            <label for="url" style="display:block;margin-bottom:8px;font-weight:bold;">Website</label>
            <input id="url" name="url" type="url" value="" size="30" style="width:100%;padding:8px;border-radius:4px;border:1px solid #ddd;">
        </p>';
    }

    // Submit button
    $output .= '<p class="form-submit">
        <input name="submit" type="submit" id="submit" class="submit" value="Submit Review" style="background:#0073aa;color:#fff;padding:10px 20px;border:none;border-radius:4px;cursor:pointer;">
    </p>';

    $output .= '</form>';

    return $output;
}

// HELPER: Get Reviews for Sitter
function antigravity_v200_get_sitter_rating($post_id) {
    $args = [
        'post_id' => $post_id,
        'status'  => 'approve',
        'parent'  => 0, // Top level only
        'type'    => 'review', // Only get comments of type 'review'
    ];
    
    $comments = get_comments($args);
    
    if (empty($comments)) {
        return ['count' => 0, 'average' => 0];
    }
    
    $total = 0;
    $count = 0;
    
    foreach ($comments as $comment) {
        $rating = get_comment_meta($comment->comment_ID, 'mps_rating', true);
        if ($rating) {
            $total += intval($rating);
            $count++;
        }
    }
    
    if ($count === 0) return ['count' => 0, 'average' => 0];
    
    return [
        'count' => $count,
        'average' => round($total / $count, 1)
    ];
}

// 4. RENDER BADGE OR STARS (Helper)
function antigravity_v200_get_rating_html($post_id, $show_count = true) {
    $stats = antigravity_v200_get_sitter_rating($post_id);
    
    if ($stats['count'] === 0) {
        return '<span class="mps-badge-new" style="background:#e6fffa;color:#0d7377;border:1px solid #b2f5ea;padding:2px 8px;border-radius:12px;font-size:12px;font-weight:600;display:inline-block;">New Sitter</span>';
    }
    
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= round($stats['average'])) {
            $stars .= '<span style="color:#fdc500;">★</span>';
        } else {
            $stars .= '<span style="color:#ddd;">★</span>';
        }
    }
    
    $html = '<div class="mps-rating" style="display:inline-flex;align-items:center;gap:4px;">';
    $html .= '<span style="font-weight:bold;color:#333;">' . $stats['average'] . '</span>';
    $html .= '<span class="stars" style="font-size:14px;line-height:1;">' . $stars . '</span>';
    if ($show_count) {
        $html .= '<span style="font-size:12px;color:#888;margin-left:2px;">(' . $stats['count'] . ')</span>';
    }
    $html .= '</div>';
    
    return $html;
}

// 5. CUSTOMIZE COMMENTS LIST
function antigravity_v200_review_callback($comment, $args, $depth) {
    $rating = get_comment_meta($comment->comment_ID, 'mps_rating', true);
    ?>
    <li id="comment-<?php comment_ID(); ?>" style="list-style:none;margin-bottom:20px;border-bottom:1px solid #eee;padding-bottom:20px;">
        <article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
            <div class="comment-meta" style="display:flex;justify-content:space-between;margin-bottom:8px;">
                <div class="comment-author vcard" style="font-weight:bold;">
                    <?php echo get_avatar($comment, 40, '', '', ['style' => 'border-radius:50%;vertical-align:middle;margin-right:8px;']); ?>
                    <?php printf(__('%s'), get_comment_author_link()); ?>
                </div>
                <div class="comment-metadata">
                    <span style="font-size:12px;color:#888;">
                        <?php printf(__('%1$s at %2$s'), get_comment_date(), get_comment_time()); ?>
                    </span>
                </div>
            </div>

            <?php if ($rating): ?>
            <div class="comment-rating" style="margin-bottom:8px;color:#fdc500;">
                <?php 
                for ($i=1; $i<=5; $i++) {
                    echo ($i <= $rating) ? '★' : '<span style="color:#eee">★</span>';
                }
                ?>
            </div>
            <?php endif; ?>

            <div class="comment-content">
                <?php comment_text(); ?>
            </div>
        </article>
    </li>
    <?php
}


