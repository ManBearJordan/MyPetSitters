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

// 1. ADD RATING FIELD TO COMMENT FORM
add_action('comment_form_logged_in_after', function() {
    echo '<p class="comment-form-rating" style="margin-bottom:20px;">
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
});

// 2. SAVE RATING
add_action('comment_post', function($comment_id) {
    if (!empty($_POST['rating'])) {
        $rating = intval($_POST['rating']);
        if ($rating >= 1 && $rating <= 5) {
            add_comment_meta($comment_id, 'mps_rating', $rating);
        }
    }
});

// 3. CALCULATE AVERAGE (Helper)
function mps_get_sitter_rating($post_id) {
    $args = [
        'post_id' => $post_id,
        'status'  => 'approve',
        'parent'  => 0 // Top level only
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
function mps_get_rating_html($post_id, $show_count = true) {
    $stats = mps_get_sitter_rating($post_id);
    
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
function mps_review_callback($comment, $args, $depth) {
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
