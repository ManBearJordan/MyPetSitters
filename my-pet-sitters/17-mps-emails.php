<?php
/**
 * MPS HTML EMAILS
 * 
 * Wraps outgoing emails in a branded HTML template.
 */

if (!defined('ABSPATH')) exit;

// 1. SET HTML CONTENT TYPE
add_filter('wp_mail_content_type', function() {
    return 'text/html';
});

// 2. WRAP EMAILS
add_filter('wp_mail', function($args) {
    // Only wrap if it's not already wrapped (basic check)
    if (strpos($args['message'], '<!DOCTYPE html>') === false && strpos($args['message'], '<html') === false) {
        $subject = $args['subject'];
        $body = $args['message'];
        
        // Use subject as heading usually, or generic
        $heading = $subject;
        
        $args['message'] = mps_get_email_template($body, $heading);
    }
    return $args;
});

function mps_get_email_template($content, $heading = 'My Pet Sitters') {
    // Basic formatting for newlines if plain text was passed
    if (strpos($content, '<p>') === false) {
        $content = wpautop($content);
    }
    
    // TEMPLATE
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title><?= esc_html($heading) ?></title>
        <style>
            body { margin:0; padding:0; background-color:#f4f6f8; font-family:'Helvetica Neue', Helvetica, Arial, sans-serif; }
            .wrapper { width:100%; table-layout:fixed; background-color:#f4f6f8; padding-bottom:60px; }
            .main { background-color:#ffffff; margin:0 auto; width:100%; max-width:600px; border-radius:8px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.05); }
            .header { background-color:#2e7d32; padding:30px 40px; text-align:center; }
            .header h1 { margin:0; color:#ffffff; font-size:24px; font-weight:bold; }
            .content { padding:40px; color:#333333; line-height:1.6; font-size:16px; }
            .footer { text-align:center; padding:20px; color:#888888; font-size:12px; }
            .btn { display:inline-block; background:#2e7d32; color:#ffffff; text-decoration:none; padding:12px 24px; border-radius:50px; font-weight:bold; margin-top:20px; }
            a { color:#2e7d32; text-decoration:none; }
        </style>
    </head>
    <body>
        <table class="wrapper" role="presentation">
            <tr>
                <td>
                    <div style="height:40px;"></div>
                    <table class="main" role="presentation" align="center">
                        <!-- HEADER -->
                        <tr>
                            <td class="header">
                                <!-- Logo could go here -->
                                <h1>My Pet Sitters</h1>
                            </td>
                        </tr>
                        
                        <!-- BODY -->
                        <tr>
                            <td class="content">
                                <h2 style="margin-top:0;color:#2c3e50;"><?= esc_html($heading) ?></h2>
                                <?= $content ?>
                            </td>
                        </tr>
                    </table>
                    
                    <!-- FOOTER -->
                    <div class="footer">
                        <p>&copy; <?= date('Y') ?> My Pet Sitters. All rights reserved.<br>
                        <a href="<?= home_url() ?>">Visit Website</a></p>
                    </div>
                </td>
            </tr>
        </table>
    </body>
    </html>
    <?php
    return ob_get_clean();
}
