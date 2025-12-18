<?php
/**
 * MPS AVAILABILITY CALENDAR
 * 
 * Manages Sitter Availability.
 * - Storage: User Meta 'mps_unavailable_dates' (Array of YYYY-MM-DD strings)
 * - UI: Dashboard Calendar (Click to toggle)
 * - Integration: Blocks dates in Booking Form
 */

if (!defined('ABSPATH')) exit;

// 1. GET/SET HELPERS
function mps_get_unavailable_dates($user_id) {
    $dates = get_user_meta($user_id, 'mps_unavailable_dates', true);
    return is_array($dates) ? $dates : [];
}

function mps_toggle_date_availability($user_id, $date) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) return false;
    
    $dates = mps_get_unavailable_dates($user_id);
    
    if (in_array($date, $dates)) {
        // Remove (Make Available)
        $dates = array_diff($dates, [$date]);
    } else {
        // Add (Make Unavailable)
        $dates[] = $date;
    }
    
    update_user_meta($user_id, 'mps_unavailable_dates', array_values($dates));
    return true;
}

// 2. AJAX HANDLER
add_action('wp_ajax_mps_toggle_date', 'mps_ajax_toggle_date');
function mps_ajax_toggle_date() {
    if (!is_user_logged_in()) wp_die();
    check_ajax_referer('mps_cal_nonce', 'nonce');
    
    $date = sanitize_text_field($_POST['date']);
    $user_id = get_current_user_id();
    
    // Only 'pro' / 'sitter' can do this? 
    // Assuming yes, but capability check is implicitly "can edit own profile"
    
    mps_toggle_date_availability($user_id, $date);
    
    wp_send_json_success(['dates' => mps_get_unavailable_dates($user_id)]);
}

// 3. DASHBOARD EDITOR SHORTCODE
add_shortcode('mps_availability_editor', function() {
    if (!is_user_logged_in()) return '';
    
    $user_id = get_current_user_id();
    // Enqueue Flatpickr for the UI (using inline styles/scripts for simplicity in snippet)
    
    // We will render a simple visual calendar for the next 3 months
    $months_to_show = 3;
    $unavailable = mps_get_unavailable_dates($user_id);
    
    ob_start();
    ?>
    <div class="mps-calendar-wrapper">
        <h3 style="margin-bottom:10px;">My Availability</h3>
        <p style="font-size:14px;color:#666;margin-bottom:20px;">Click date to block/unblock.</p>
        
        <div class="mps-cal-grid" style="display:flex;flex-wrap:wrap;gap:20px;">
            <?php 
            $current_month = new DateTime('first day of this month');
            
            for ($i = 0; $i < $months_to_show; $i++) {
                $month_name = $current_month->format('F Y');
                $year = $current_month->format('Y');
                $month = $current_month->format('m');
                $days_in_month = $current_month->format('t');
                $start_day_offset = $current_month->format('w'); // 0 (Sun) - 6 (Sat)
                
                echo '<div class="mps-month" style="border:1px solid #ddd;border-radius:8px;padding:16px;width:280px;">';
                echo '<h4 style="text-align:center;margin:0 0 12px;">' . $month_name . '</h4>';
                
                // Header
                echo '<div style="display:grid;grid-template-columns:repeat(7, 1fr);font-size:12px;font-weight:bold;text-align:center;margin-bottom:8px;">';
                foreach (['S','M','T','W','T','F','S'] as $d) echo "<div>$d</div>";
                echo '</div>';
                
                // Days
                echo '<div style="display:grid;grid-template-columns:repeat(7, 1fr);gap:4px;">';
                
                // Empty slots
                for ($j = 0; $j < $start_day_offset; $j++) echo '<div></div>';
                
                // Dates
                for ($d = 1; $d <= $days_in_month; $d++) {
                    $date_str = sprintf('%s-%s-%02d', $year, $month, $d);
                    $is_off = in_array($date_str, $unavailable);
                    $bg = $is_off ? '#ffcccc' : '#f0fff4';
                    $color = $is_off ? '#cc0000' : '#2e7d32';
                    
                    echo sprintf(
                        '<div class="mps-day-cell" data-date="%s" style="aspect-ratio:1;display:flex;align-items:center;justify-content:center;background:%s;color:%s;border-radius:4px;cursor:pointer;font-size:13px;">%s</div>',
                        $date_str, $bg, $color, $d
                    );
                }
                echo '</div>'; // End days grid
                echo '</div>'; // End Month card
                
                $current_month->modify('+1 month');
            }
            ?>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cells = document.querySelectorAll('.mps-day-cell');
            
            cells.forEach(cell => {
                cell.addEventListener('click', function() {
                    const date = this.getAttribute('data-date');
                    const el = this;
                    
                    // Optimistic UI update
                    const isOff = el.style.backgroundColor === 'rgb(255, 204, 204)' || el.style.backgroundColor === '#ffcccc';
                    if (isOff) {
                        el.style.backgroundColor = '#f0fff4';
                        el.style.color = '#2e7d32';
                    } else {
                        el.style.backgroundColor = '#ffcccc';
                        el.style.color = '#cc0000';
                    }
                    
                    // AJAX
                    const formData = new FormData();
                    formData.append('action', 'mps_toggle_date');
                    formData.append('date', date);
                    formData.append('nonce', '<?= wp_create_nonce('mps_cal_nonce') ?>');
                    
                    fetch('<?= admin_url('admin-ajax.php') ?>', {
                        method: 'POST',
                        body: formData
                    });
                });
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
});

// 4. LOAD FLATPICKR ASSETS (Frontend support)
add_action('wp_footer', function() {
    if (is_singular('sitter') || is_page('account')) {
        echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">';
        echo '<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>';
    }
});
