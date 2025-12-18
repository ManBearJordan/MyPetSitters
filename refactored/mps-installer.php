<?php
/**
 * MPS INSTALLER
 * 
 * Runs on Plugin Activation.
 * - Creates necessary Pages (Login, Dashboard, Landing, etc.)
 * - Sets up Primary Menu
 * - Configures Roles/Capabilities
 */

if (!defined('ABSPATH')) exit;


// register_activation_hook handled in mps-core-loader.php


function antigravity_v200_installer() {
    // 1. CREATE PAGES
    $pages = [
        'login' => [
            'title' => 'Login',
            'content' => '[mps_login]'
        ],
        'join' => [
            'title' => 'Join',
            'content' => '[mps_register]' // Corrected from mps_registration
        ],
        'account' => [
            'title' => 'My Account',
            'content' => '[mps_dashboard]'
        ],

        'list-your-services' => [
            'title' => 'List Your Services',
            'content' => '[mps_sitter_landing]'
        ],
        'edit-profile' => [
            'title' => 'Edit My Profile',
            'content' => '[mps_edit_profile]'
        ],
        'cities' => [
            'title' => 'Find a Pet Sitter',
            'content' => '[mps_cities]' // Corrected from mps_landing_page
        ],
        'services' => [
            'title' => 'Our Services',
            'content' => '[mps_services]' // Added V15
        ],
        'messages' => [
            'title' => 'Messages',
            'content' => '[mps_inbox]'
        ],
        'become-a-sitter' => [
            'title' => 'Become a Pet Sitter',
            'content' => '[mps_sitter_landing]'
        ],
        'lost-password' => [
            'title' => 'Lost Password',
            'content' => '[mps_lost_password]'
        ],
        'reset-password' => [
            'title' => 'Reset Password',
            'content' => '[mps_reset_password]'
        ],
        // LEGALS & CONTACT (Added V66)
        'privacy-policy' => [
            'title' => 'Privacy Policy',
            'content' => "<!-- wp:heading -->\n<h2>Privacy Policy</h2>\n<!-- /wp:heading -->\n<p>Last updated: December 2025</p>\n\n<h3>1. Who We Are</h3>\n<p>MyPetSitters.com.au (“My Pet Sitters”, “we”, “us”, “our”) operates as a sole trader under the business name My Pet Sitters, based in Australia. We operate an online directory that allows pet sitters to create public profiles and communicate with pet owners.</p>\n<p>For any privacy-related enquiries, contact: <a href='mailto:enquiries@mypetsitters.com.au'>enquiries@mypetsitters.com.au</a></p>\n\n<h3>2. Information We Collect</h3>\n<p>We may collect the following personal information:</p>\n<ul>\n<li>Name</li>\n<li>Email address</li>\n<li>Phone number</li>\n<li>Address or suburb</li>\n<li>Profile photos</li>\n<li>Pet information, including pet medical needs</li>\n<li>Messages sent between users via the platform</li>\n</ul>\n<p>We do not intentionally collect personal information unrelated to the operation of the platform.</p>\n\n<h3>3. How Information Is Collected</h3>\n<p>Information is collected when you:</p>\n<ul>\n<li>Register an account</li>\n<li>Create or edit a profile listing</li>\n<li>Use the messaging system</li>\n<li>Submit a contact form</li>\n<li>Interact with the website through cookies or analytics tools</li>\n</ul>\n\n<h3>4. Cookies & Analytics</h3>\n<p>We use cookies and analytics tools to understand how the website is used, improve functionality and performance, and monitor traffic and usage patterns. You may disable cookies in your browser, but this may affect site functionality.</p>\n\n<h3>5. Public Profiles</h3>\n<p>Pet sitter profiles are publicly visible on MyPetSitters.com.au. Any information you choose to include in your profile may be viewed by the public. You are responsible for the content you choose to publish.</p>\n\n<h3>6. Storage & Disclosure of Information</h3>\n<p>Data is stored in Australia or on infrastructure operated by trusted hosting providers. We may share information with hosting providers and technical service providers required to operate the site. We do not sell personal information.</p>\n\n<h3>7. Sensitive Pet Information</h3>\n<p>Any pet medical or care-related information is provided voluntarily by users and is shared at the user’s discretion. We do not independently verify this information.</p>\n\n<h3>8. Access, Updates & Deletion</h3>\n<p>Users may edit their profile information, delete their account, or request data deletion by emailing <a href='mailto:enquiries@mypetsitters.com.au'>enquiries@mypetsitters.com.au</a>. We may retain limited information where required for legal, operational, or dispute purposes.</p>\n\n<h3>9. Security</h3>\n<p>We take reasonable steps to protect personal information but cannot guarantee absolute security. Use of the platform is at your own risk.</p>\n\n<h3>10. Changes to This Policy</h3>\n<p>We may update this Privacy Policy at any time. Continued use of the site indicates acceptance of the updated policy.</p>"
        ],
        'terms-of-service' => [
            'title' => 'Terms of Service',
            'content' => "<!-- wp:heading -->\n<h2>Terms of Service</h2>\n<!-- /wp:heading -->\n<p>Last updated: December 2025</p>\n\n<h3>1. Platform Nature</h3>\n<p>MyPetSitters.com.au is a directory only. We do not provide pet sitting services, employ sitters, guarantee availability, quality, or conduct, or facilitate or enforce bookings. Users may communicate via the platform, but are not required to do so.</p>\n\n<h3>2. Independent Contractors</h3>\n<p>All pet sitters listed are independent contractors. Nothing on this website creates an employment relationship, a partnership, or an agency relationship.</p>\n\n<h3>3. No Liability</h3>\n<p>To the maximum extent permitted by law, My Pet Sitters is not responsible for injury, illness, loss, or death of pets, property damage, disputes between users, negligence, misconduct, or misrepresentation by users, or financial loss of any kind. All arrangements are made entirely at the user’s own risk.</p>\n\n<h3>4. Profile Review & Moderation</h3>\n<p>We may review profile submissions, edit, reject, suspend, or remove listings, and investigate complaints or concerns. We do not guarantee accuracy, suitability, or trustworthiness of any listing.</p>\n\n<h3>5. User Obligations</h3>\n<p>Users must provide accurate and truthful information, not create fake or misleading profiles, not post illicit, offensive, or unlawful content, and comply with all applicable laws. We reserve the right to remove any content at our sole discretion.</p>\n\n<h3>6. Account Suspension & Removal</h3>\n<p>We may suspend or terminate accounts without notice if we believe there has been a breach of these terms or behaviour that may harm the platform or other users.</p>\n\n<h3>7. Free Service</h3>\n<p>The platform is currently free to use. We reserve the right to introduce paid features or subscriptions in the future.</p>\n\n<h3>8. Intellectual Property</h3>\n<p>All website content, branding, and structure belongs to My Pet Sitters unless otherwise stated. Users grant us a licence to display submitted content for platform operation.</p>\n\n<h3>9. Governing Law</h3>\n<p>These Terms are governed by the laws of Queensland, Australia. Any disputes must be resolved under this jurisdiction.</p>\n\n<h3>10. Contact</h3>\n<p>For legal or platform enquiries: <a href='mailto:enquiries@mypetsitters.com.au'>enquiries@mypetsitters.com.au</a></p>"
        ],
        'contact-us' => [
            'title' => 'Contact Us',
            'content' => "<!-- wp:heading -->\n<h2>Contact Us</h2>\n<!-- /wp:heading -->\n<p>Have a question or need assistance? Fill out the form below and our team will get back to you.</p>\n\n[mps_contact]"
        ],
        'faqs' => [
            'title' => 'Frequently Asked Questions',
            'content' => '[mps_faq_full]'
        ]
    ];
    
    foreach ($pages as $slug => $data) {
        if (!get_page_by_path($slug)) {
            wp_insert_post([
                'post_title' => $data['title'],
                'post_name' => $slug,
                'post_content' => $data['content'],
                'post_status' => 'publish',
                'post_type' => 'page'
            ]);
        }
    }
    
    // 2. SETUP MENU (Only if 'Primary Menu' lacks items)
    $menu_name = 'Primary Menu';
    $menu = wp_get_nav_menu_object($menu_name);
    
    if (!$menu) {
        $menu_id = wp_create_nav_menu($menu_name);
        if (!is_wp_error($menu_id)) {
            // Add Items
            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title' => 'Home',
                'menu-item-url' => home_url('/'),
                'menu-item-status' => 'publish'
            ]);
            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title' => 'Find a Sitter',
                'menu-item-url' => home_url('/cities/'),
                'menu-item-status' => 'publish'
            ]);
            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title' => 'Become a Sitter',
                'menu-item-url' => home_url('/become-a-sitter/'),
                'menu-item-status' => 'publish'
            ]);
             wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title' => 'Join',
                'menu-item-url' => home_url('/join/'),
                'menu-item-status' => 'publish'
            ]);
             wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title' => 'Login',
                'menu-item-url' => home_url('/login/'),
                'menu-item-status' => 'publish'
            ]);
             
             // List Your Services Button
             wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title' => 'List Your Services',
                'menu-item-url' => home_url('/list-your-services/'),
                'menu-item-status' => 'publish',
                'menu-item-classes' => 'menu-cta' // Triggers CSS style
            ]);
            
            // Assign to locations
            $locations = get_theme_mod('nav_menu_locations');
            $locations['primary'] = $menu_id;
            $locations['primary_menu'] = $menu_id;
            set_theme_mod('nav_menu_locations', $locations);
        }
    }
    

    // 3. FLUSH PERMALINKS
    flush_rewrite_rules();
}


