<?php
/**
 * Plugin Name: My Book Wizard
 * Description: Interactive book customization wizard.
 * Version: 1.2
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('MY_BOOK_WIZARD_DIR', plugin_dir_path(__FILE__));
define('MY_BOOK_WIZARD_URL', plugin_dir_url(__FILE__));

// Define product IDs (MAKE SURE THESE ARE CORRECT!)
define('MBW_DIGITAL_PRODUCT_ID', 1452); // Digital Only product
define('MBW_PHYSICAL_PRODUCT_ID', 64);  // Digital + Physical product

// Include core files
require_once MY_BOOK_WIZARD_DIR . 'includes/class-wizard.php';
require_once MY_BOOK_WIZARD_DIR . 'includes/cart-handler.php';
require_once MY_BOOK_WIZARD_DIR . 'includes/love-story-single-page.php';
require_once MY_BOOK_WIZARD_DIR . 'includes/love-story-6-pages.php';
require_once MY_BOOK_WIZARD_DIR . 'includes/kids-story-10-pages.php';
require_once MY_BOOK_WIZARD_DIR . 'includes/post-payment-generator.php';

// Initialize wizard
function my_book_wizard_init() {
    $wizard = new My_Book_Wizard();
    $wizard->run();
}
add_action('init', 'my_book_wizard_init');

// Optional: Add custom rewrite rule for nice URL
add_action('init', 'my_book_wizard_rewrite_rule');
function my_book_wizard_rewrite_rule() {
    add_rewrite_rule(
        '^create-your-book/?$',
        'index.php?pagename=create-your-book',
        'top'
    );
}

// Flush rewrite rules on activation
register_activation_hook(__FILE__, 'my_book_wizard_activate');
function my_book_wizard_activate() {
    my_book_wizard_rewrite_rule();
    flush_rewrite_rules();
    
    // Create download page on activation
    mbw_create_download_page();
}

// Flush on deactivation
register_deactivation_hook(__FILE__, 'my_book_wizard_deactivate');
function my_book_wizard_deactivate() {
    flush_rewrite_rules();
}

// Optional: Create admin menu item for easy access
add_action('admin_menu', 'my_book_wizard_admin_menu');
function my_book_wizard_admin_menu() {
    add_menu_page(
        'Book Wizard',
        'Book Wizard',
        'manage_options',
        'book-wizard-admin',
        'my_book_wizard_admin_page',
        'dashicons-book',
        30
    );
}

add_action('admin_menu', 'my_book_wizard_ai_settings');
function my_book_wizard_ai_settings() {
    add_submenu_page(
        'book-wizard-admin',
        'AI Settings',
        'AI Settings',
        'manage_options',
        'book-wizard-ai-settings',
        'my_book_wizard_ai_settings_page'
    );
}

function my_book_wizard_ai_settings_page() {
    if (isset($_POST['ai_api_key'])) {
        update_option('ai_api_key', sanitize_text_field($_POST['ai_api_key']));
        echo '<div class="notice notice-success"><p>API Key saved!</p></div>';
    }
    
    $api_key = get_option('ai_api_key', '');
    ?>
    <div class="wrap">
        <h1>🤖 AI Scene Generator Settings</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th><label for="ai_api_key">Google AI API Key</label></th>
                    <td>
                        <input type="password" 
                               id="ai_api_key" 
                               name="ai_api_key" 
                               value="<?php echo esc_attr($api_key); ?>"
                               class="regular-text" style="width: 400px;">
                        <p class="description">
                            Get from <a href="https://makersuite.google.com/app/apikey" target="_blank">Google AI Studio</a>
                        </p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Save API Key'); ?>
        </form>
        
        <hr>
        
        <h2>Product IDs Configuration</h2>
        <p><strong>Important:</strong> Make sure your WooCommerce products exist with these IDs:</p>
        <ul>
            <li>Digital Only Product: <strong>ID <?php echo MBW_DIGITAL_PRODUCT_ID; ?></strong></li>
            <li>Digital + Physical Product: <strong>ID <?php echo MBW_PHYSICAL_PRODUCT_ID; ?></strong></li>
        </ul>
        <p>To change these IDs, edit the constants at the top of <code>my-book-wizard.php</code> file.</p>
    </div>
    <?php
}

function my_book_wizard_admin_page() {
    ?>
    <div class="wrap">
        <h1>📖 Book Wizard Admin</h1>
        <div style="background: white; padding: 20px; border-radius: 10px; margin-top: 20px;">
            <h2>Quick Setup</h2>
            <ol>
                <li>Create a new page called "Create Your Book"</li>
                <li>Add this shortcode: <code>[book_wizard]</code></li>
                <li>Publish the page</li>
                <li>Visit: <a href="<?php echo admin_url('post-new.php?post_type=page'); ?>">Create New Page</a></li>
            </ol>
            <h3 style="margin-top: 30px;">Features:</h3>
            <ul>
                <li>✅ Kids Stories: 10 templates, 10 pages each</li>
                <li>✅ Love Stories: 20 pages with custom selections</li>
                <li>✅ AI Image Generation with Google Gemini</li>
                <li>✅ ReadyPlayerMe Avatar Integration</li>
                <li>✅ WooCommerce Cart Integration</li>
                <li>✅ Immediate Story Generation after payment</li>
            </ul>
            
            <h3 style="margin-top: 30px;">Pages Created:</h3>
            <?php
            $download_page = get_page_by_path('download-story');
            if ($download_page) {
                echo '<p>✅ Download page exists: <a href="' . get_permalink($download_page->ID) . '" target="_blank">View Download Page</a></p>';
            } else {
                echo '<p>❌ Download page not found. <button onclick="location.reload()">Check Again</button></p>';
            }
            ?>
            
            <h3 style="margin-top: 30px;">System Check:</h3>
            <?php
            // Check API key
            $api_key = get_option('ai_api_key', '');
            if ($api_key) {
                echo '<p>✅ API Key is configured</p>';
            } else {
                echo '<p>❌ API Key is NOT configured. Go to <a href="' . admin_url('admin.php?page=book-wizard-ai-settings') . '">AI Settings</a></p>';
            }
            
            // Check WooCommerce
            if (class_exists('WooCommerce')) {
                echo '<p>✅ WooCommerce is active</p>';
                
                // Check products
                $digital_product = wc_get_product(MBW_DIGITAL_PRODUCT_ID);
                $physical_product = wc_get_product(MBW_PHYSICAL_PRODUCT_ID);
                
                if ($digital_product && $digital_product->exists()) {
                    echo '<p>✅ Digital Product (ID ' . MBW_DIGITAL_PRODUCT_ID . ') exists: ' . $digital_product->get_name() . '</p>';
                } else {
                    echo '<p>❌ Digital Product (ID ' . MBW_DIGITAL_PRODUCT_ID . ') does not exist. Please create it.</p>';
                }
                
                if ($physical_product && $physical_product->exists()) {
                    echo '<p>✅ Physical Product (ID ' . MBW_PHYSICAL_PRODUCT_ID . ') exists: ' . $physical_product->get_name() . '</p>';
                } else {
                    echo '<p>❌ Physical Product (ID ' . MBW_PHYSICAL_PRODUCT_ID . ') does not exist. Please create it.</p>';
                }
            } else {
                echo '<p>❌ WooCommerce is NOT active. Required for this plugin.</p>';
            }
            ?>
        </div>
    </div>
    <?php
}

// Create download page on plugin activation
register_activation_hook(__FILE__, 'mbw_create_download_page');
function mbw_create_download_page() {
    $page_exists = get_page_by_path('download-story');
    
    if (!$page_exists) {
        $page_data = array(
            'post_title'    => 'Download Story',
            'post_name'     => 'download-story',
            'post_content'  => '<!-- This page is automatically managed by My Book Wizard plugin -->
                                [book_wizard_download]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_author'   => 1,
            'page_template' => 'download-story-page.php'
        );
        
        $page_id = wp_insert_post($page_data);
        
        if ($page_id) {
            update_post_meta($page_id, '_wp_page_template', 'download-story-page.php');
            error_log('My Book Wizard: Created download page with ID: ' . $page_id);
        }
    }
}

// Add AJAX handler for immediate story generation
add_action('wp_ajax_generate_full_story_immediate', 'mbw_generate_full_story_immediate');
add_action('wp_ajax_nopriv_generate_full_story_immediate', 'mbw_generate_full_story_immediate');

// Update the mbw_generate_full_story_immediate function:
function mbw_generate_full_story_immediate() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'immediate_story_gen')) {
        wp_send_json_error('Security check failed');
    }
    
    $order_id = intval($_POST['order_id']);
    $token = sanitize_text_field($_POST['token']);
    
    $order = wc_get_order($order_id);
    if (!$order) {
        wp_send_json_error('Order not found');
    }
    
    // Verify token
    $immediate_token = $order->get_meta('immediate_download_token');
    if ($token !== $immediate_token) {
        wp_send_json_error('Invalid token');
    }
    
    // Check if story already generated
    $story_generated = $order->get_meta('story_generated');
    if ($story_generated === 'yes') {
        wp_send_json_success('already_generated');
    }
    
    // Log start
    error_log("=== IMMEDIATE STORY GENERATION STARTED FOR ORDER: $order_id ===");
    
    $generation_success = false;
    $generated_type = '';
    
    foreach ($order->get_items() as $item_id => $item) {
        $product_id = $item->get_product_id();
        
        if ($product_id == MBW_DIGITAL_PRODUCT_ID || $product_id == MBW_PHYSICAL_PRODUCT_ID) {
            // Get story data
            $story_type = $item->get_meta('story_type');
            $generated_type = $story_type;
            
            if ($story_type === 'love') {
                // Use the function from post-payment-generator.php
                $result = generate_full_love_story($item, $order);
                if ($result) {
                    $generation_success = true;
                }
            } elseif ($story_type === 'kids') {
                // Use the function from post-payment-generator.php
                $result = generate_full_kids_story($item, $order);
                if ($result) {
                    $generation_success = true;
                }
            }
            
            if ($generation_success) {
                // Mark as generated
                $order->update_meta_data('story_generated', 'yes');
                $order->update_meta_data('story_generated_at', current_time('mysql'));
                $order->save();
                
                // Also send email notification
                send_story_download_email($order, $item);
                
                error_log("=== IMMEDIATE STORY GENERATION COMPLETED FOR ORDER: $order_id ($story_type) ===");
                
                wp_send_json_success('Story generated successfully');
                break;
            }
        }
    }
    
    if (!$generation_success) {
        error_log("=== IMMEDIATE STORY GENERATION FAILED FOR ORDER: $order_id ===");
        wp_send_json_error('Story generation failed. Please try again or contact support.');
    }
}

// Add shortcode for download page
add_shortcode('book_wizard_download', 'mbw_download_page_shortcode');
function mbw_download_page_shortcode() {
    // This will display the download page content
    // The actual template handling is done by download-story-page.php
    return '<!-- Story download will be displayed here -->';
}

// Register immediate generation nonce
add_action('wp_loaded', 'mbw_register_immediate_nonce');
function mbw_register_immediate_nonce() {
    // This ensures the nonce is available for AJAX calls
    if (function_exists('wp_localize_script')) {
        wp_localize_script('my-book-wizard-js', 'mbw_immediate_params', array(
            'nonce' => wp_create_nonce('immediate_story_gen')
        ));
    }
}

// Replace the existing mbw_redirect_after_payment function with this:
add_action('template_redirect', 'mbw_redirect_after_payment', 999); // Higher priority
function mbw_redirect_after_payment() {
    // Check if we're on the order received page
    if (is_wc_endpoint_url('order-received')) {
        global $wp;
        $order_id = absint($wp->query_vars['order-received']);
        
        if ($order_id > 0) {
            $order = wc_get_order($order_id);
            
            if ($order) {
                // Check if this order contains a story book product
                $has_story_product = false;
                $product_ids = [MBW_DIGITAL_PRODUCT_ID, MBW_PHYSICAL_PRODUCT_ID];
                
                foreach ($order->get_items() as $item) {
                    if (in_array($item->get_product_id(), $product_ids)) {
                        $has_story_product = true;
                        break;
                    }
                }
                
                if ($has_story_product) {
                    // Generate immediate download token
                    $download_token = wp_generate_password(32, false);
                    $order->update_meta_data('immediate_download_token', $download_token);
                    $order->update_meta_data('redirected_to_generation', 'yes');
                    $order->save();
                    
                    // Generate immediate download URL WITH immediate flag
                    $download_url = add_query_arg([
                        'token' => $download_token,
                        'order' => $order_id,
                        'immediate' => 'true',
                        'generate' => 'true'  // Explicit flag for generation
                    ], home_url('/download-story/'));
                    
                    // Clear any output and redirect
                    if (!headers_sent()) {
                        wp_redirect($download_url);
                        exit;
                    } else {
                        // Fallback: JavaScript redirect
                        echo '<script>window.location.href = "' . esc_url($download_url) . '";</script>';
                        exit;
                    }
                }
            }
        }
    }
}

// Debug function to check for errors
add_action('wp_footer', 'mbw_debug_info');
function mbw_debug_info() {
    if (current_user_can('administrator')) {
        echo '<!-- MBW Debug: Plugin loaded -->';
        echo '<!-- MBW Product IDs: Digital=' . MBW_DIGITAL_PRODUCT_ID . ', Physical=' . MBW_PHYSICAL_PRODUCT_ID . ' -->';
    }
}
// Add this function to my-book-wizard.php
add_action('wp_ajax_mbw_check_order_status', 'mbw_check_order_status');
add_action('wp_ajax_nopriv_mbw_check_order_status', 'mbw_check_order_status');

function mbw_check_order_status() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'immediate_story_gen')) {
        wp_send_json_error('Security check failed');
    }
    
    $order_id = intval($_POST['order_id']);
    $token = sanitize_text_field($_POST['token']);
    
    $order = wc_get_order($order_id);
    if (!$order) {
        wp_send_json_error('Order not found');
    }
    
    // Verify token
    $immediate_token = $order->get_meta('immediate_download_token');
    if ($token !== $immediate_token) {
        wp_send_json_error('Invalid token');
    }
    
    // Check story status
    $story_generated = $order->get_meta('story_generated');
    $pages_meta = '';
    
    foreach ($order->get_items() as $item) {
        $pages = $item->get_meta('full_story_pages');
        if ($pages) {
            $pages_meta = $pages;
            break;
        }
    }
    
    wp_send_json_success([
        'story_generated' => $story_generated,
        'has_pages' => !empty($pages_meta),
        'order_status' => $order->get_status()
    ]);
}
// Add this function to ensure proper page detection
add_action('wp_enqueue_scripts', 'my_book_wizard_conditional_assets', 999);
function my_book_wizard_conditional_assets() {
    // Only load wizard JS on wizard pages or download page
    $load_wizard_js = false;
    
    // Check if we're on the download page
    if (is_page('download-story') || 
        (is_page() && strpos(get_page_template(), 'download-story-page.php') !== false)) {
        $load_wizard_js = true;
    }
    
    // Check if we're on a page with the wizard shortcode
    global $post;
    if ($post && has_shortcode($post->post_content, 'book_wizard')) {
        $load_wizard_js = true;
    }
    
    // Check if we're on the order received page (might redirect to download)
    if (is_wc_endpoint_url('order-received')) {
        $load_wizard_js = true;
    }
    
    // If not needed, remove the script
    if (!$load_wizard_js) {
        wp_dequeue_script('my-book-wizard-js');
        wp_dequeue_style('my-book-wizard-css');
    }
}