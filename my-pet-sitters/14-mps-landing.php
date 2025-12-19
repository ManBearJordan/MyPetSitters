<?php
/**
 * MPS SITTER LANDING PAGE
 * 
 * A high-converting landing page for prospective sitters.
 * Shortcode: [mps_sitter_landing]
 */

if (!defined('ABSPATH')) exit;

// Converted to named function (V120 Fix)
function antigravity_v200_render_sitter_landing() {
    if (is_user_logged_in()) {
        echo '<script>window.location.href="/edit-profile/";</script>';
        return;
    }
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
                <div style="font-size:3rem;font-weight:bold;color:#2e7d32;">$7k+</div>
                <div style="color:#666;">Top sitters earn monthly</div>
            </div>
            <div>
                <div style="font-size:3rem;font-weight:bold;color:#2e7d32;">0%</div>
                <div style="color:#666;">Listing fees</div>
            </div>
            <div>
                <div style="font-size:3rem;font-weight:bold;color:#2e7d32;">100%</div>
                <div style="color:#666;">Verified Reviews</div>
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
                <div style="margin-top:12px;color:#555;">Yes! Creating a profile and listing your services is 100% free at the moment. We will never take commissions on bookings.</div>
            </details>
            
            <details style="margin-bottom:16px;border:1px solid #eee;border-radius:8px;padding:16px;">
                <summary style="font-weight:bold;cursor:pointer;list-style:none;">How do I get paid?</summary>
                <div style="margin-top:12px;color:#555;">Payments are not currently processed through the site. You have control to use your own trusted banking service or use a service like Stripe to send invoices. We will add more features in the future to help you get and keep clients.</div>
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
}
// Register BOTH names (V120 Fix)
add_shortcode('mps_sitter_landing', 'antigravity_v200_render_sitter_landing');
add_shortcode('mps_sitter_registration', 'antigravity_v200_render_sitter_landing');


