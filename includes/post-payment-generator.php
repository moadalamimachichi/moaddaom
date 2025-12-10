<?php
// post-payment-generator.php - ONLY POST-PAYMENT FUNCTIONS

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Register AJAX handler for immediate generation
add_action('wp_ajax_generate_full_love_story', 'generate_full_love_story_handler');
add_action('wp_ajax_nopriv_generate_full_love_story', 'generate_full_love_story_handler');

add_action('wp_ajax_generate_full_kids_story', 'generate_full_kids_story_handler');
add_action('wp_ajax_nopriv_generate_full_kids_story', 'generate_full_kids_story_handler');

/**
 * Generate full love story after payment
 */
function generate_full_love_story_handler() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'book_wizard_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    // Get order/item data
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    
    if (!$order_id || !$item_id) {
        wp_send_json_error('Missing order data');
        return;
    }
    
    $order = wc_get_order($order_id);
    $item = $order->get_item($item_id);
    
    if (!$order || !$item) {
        wp_send_json_error('Order or item not found');
        return;
    }
    
    // Generate full story
    $result = generate_full_love_story($item, $order);
    
    if ($result) {
        wp_send_json_success('Full love story generated successfully');
    } else {
        wp_send_json_error('Failed to generate story');
    }
}

/**
 * Generate full kids story after payment
 */
function generate_full_kids_story_handler() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'book_wizard_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    // Get order/item data
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    
    if (!$order_id || !$item_id) {
        wp_send_json_error('Missing order data');
        return;
    }
    
    $order = wc_get_order($order_id);
    $item = $order->get_item($item_id);
    
    if (!$order || !$item) {
        wp_send_json_error('Order or item not found');
        return;
    }
    
    // Generate full story
    $result = generate_full_kids_story($item, $order);
    
    if ($result) {
        wp_send_json_success('Full kids story generated successfully');
    } else {
        wp_send_json_error('Failed to generate story');
    }
}

/**
 * Generate full 20-page love story
 */
function generate_full_love_story($item, $order) {
    error_log("=== GENERATING FULL LOVE STORY FOR ORDER: " . $order->get_id() . " ===");
    
    try {
        // Get story data from item meta
        $character_details_json = $item->get_meta('character_details');
        $avatar1_url = $item->get_meta('avatar');
        $avatar2_url = $item->get_meta('avatar2');
        
        if (!$character_details_json || !$avatar1_url || !$avatar2_url) {
            error_log("Missing story data for full generation");
            return false;
        }
        
        $character_details = json_decode(stripslashes($character_details_json), true);
        
        // Get API key
        $api_key = get_option('ai_api_key');
        if (empty($api_key)) {
            error_log("API key not configured");
            return false;
        }
        
        // Generate prompts for 20 pages
        $page_prompts = [];
        $total_pages = 20;
        
        // Example: Generate different prompts for each page
        for ($i = 1; $i <= $total_pages; $i++) {
            $page_prompts[] = "Create romantic illustration page $i/$total_pages for " . 
                             $character_details['your_name'] . " and " . 
                             $character_details['lover_name'] . ". Beautiful love story scene.";
        }
        
        // Convert avatars to base64
        $avatar1_base64 = get_base64_from_url($avatar1_url);
        $avatar2_base64 = get_base64_from_url($avatar2_url);
        
        if (!$avatar1_base64 || !$avatar2_base64) {
            error_log("Failed to convert avatars to base64");
            return false;
        }
        
        $generated_pages = [];
        
        // Generate each page (you may want to batch these or use async)
        foreach ($page_prompts as $index => $prompt) {
            $page_number = $index + 1;
            
            $result = generate_single_love_page_api(
                $avatar1_base64,
                $avatar2_base64,
                $prompt,
                $page_number,
                $total_pages,
                $api_key
            );
            
            if ($result) {
                $generated_pages[] = [
                    'page_number' => $page_number,
                    'image_url' => $result,
                    'prompt' => $prompt
                ];
                error_log("Generated page $page_number/$total_pages");
            } else {
                error_log("Failed to generate page $page_number");
            }
            
            // Small delay between requests
            if ($page_number < $total_pages) {
                sleep(1);
            }
        }
        
        if (count($generated_pages) > 0) {
            // Save to order/item meta
            $item->update_meta_data('full_story_pages', json_encode($generated_pages));
            $item->update_meta_data('full_story_generated', 'yes');
            $item->update_meta_data('full_story_generated_at', current_time('mysql'));
            
            $order->update_meta_data('story_generated', 'yes');
            $order->update_meta_data('story_generated_at', current_time('mysql'));
            $order->save();
            
            // Generate PDF
            $pdf_url = generate_pdf_from_pages($generated_pages, 'love_story_' . $order->get_id());
            if ($pdf_url) {
                $item->update_meta_data('pdf_url', $pdf_url);
                $order->update_meta_data('_pdf_file', $pdf_url);
                $order->save();
            }
            
            // Send download email
            send_story_download_email($order, $item);
            
            error_log("Successfully generated " . count($generated_pages) . " pages");
            return true;
        }
        
        return false;
        
    } catch (Exception $e) {
        error_log("Error generating full love story: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate full 10-page kids story
 */
function generate_full_kids_story($item, $order) {
    error_log("=== GENERATING FULL KIDS STORY FOR ORDER: " . $order->get_id() . " ===");
    
    try {
        // Get story data from item meta
        $character_details_json = $item->get_meta('character_details');
        $avatar_url = $item->get_meta('avatar');
        $template = $item->get_meta('kids_template');
        $book_style = $item->get_meta('kids_book_style');
        
        if (!$character_details_json || !$avatar_url) {
            error_log("Missing story data for full generation");
            return false;
        }
        
        $character_details = json_decode(stripslashes($character_details_json), true);
        
        // Get API key
        $api_key = get_option('ai_api_key');
        if (empty($api_key)) {
            error_log("API key not configured");
            return false;
        }
        
        // Generate prompts for 10 pages
        $page_prompts = [];
        $total_pages = 10;
        $kid_name = $character_details['kid_name'];
        
        // Example: Generate story pages based on template
        for ($i = 1; $i <= $total_pages; $i++) {
            $page_prompts[] = "Create children's story illustration page $i/$total_pages for $kid_name. " .
                             "Template: $template. Style: $book_style. Bright, colorful, fun scene.";
        }
        
        // Convert avatar to base64
        $avatar_base64 = get_base64_from_url($avatar_url);
        
        if (!$avatar_base64) {
            error_log("Failed to convert avatar to base64");
            return false;
        }
        
        $generated_pages = [];
        
        // Generate each page
        foreach ($page_prompts as $index => $prompt) {
            $page_number = $index + 1;
            
            $result = generate_single_kids_page_api(
                $avatar_base64,
                $prompt,
                $page_number,
                $total_pages,
                $kid_name,
                $api_key
            );
            
            if ($result) {
                $generated_pages[] = [
                    'page_number' => $page_number,
                    'image_url' => $result,
                    'prompt' => $prompt
                ];
                error_log("Generated kids page $page_number/$total_pages");
            } else {
                error_log("Failed to generate kids page $page_number");
            }
            
            // Small delay between requests
            if ($page_number < $total_pages) {
                sleep(1);
            }
        }
        
        if (count($generated_pages) > 0) {
            // Save to order/item meta
            $item->update_meta_data('full_story_pages', json_encode($generated_pages));
            $item->update_meta_data('full_story_generated', 'yes');
            $item->update_meta_data('full_story_generated_at', current_time('mysql'));
            
            $order->update_meta_data('story_generated', 'yes');
            $order->update_meta_data('story_generated_at', current_time('mysql'));
            $order->save();
            
            // Generate PDF
            $pdf_url = generate_pdf_from_pages($generated_pages, 'kids_story_' . $order->get_id());
            if ($pdf_url) {
                $item->update_meta_data('pdf_url', $pdf_url);
                $order->update_meta_data('_pdf_file', $pdf_url);
                $order->save();
            }
            
            // Send download email
            send_story_download_email($order, $item);
            
            error_log("Successfully generated " . count($generated_pages) . " kids pages");
            return true;
        }
        
        return false;
        
    } catch (Exception $e) {
        error_log("Error generating full kids story: " . $e->getMessage());
        return false;
    }
}

/**
 * Helper: Generate single love page via API
 */
function generate_single_love_page_api($image1_base64, $image2_base64, $prompt, $page_number, $total_pages, $api_key) {
    $model = 'gemini-2.5-flash-image-preview';
    
    $request_body = json_encode([
        'contents' => [
            [
                'parts' => [
                    [
                        'inline_data' => [
                            'mime_type' => 'image/png',
                            'data' => $image1_base64
                        ]
                    ],
                    [
                        'inline_data' => [
                            'mime_type' => 'image/png',
                            'data' => $image2_base64
                        ]
                    ],
                    [
                        'text' => $prompt . ' (Page ' . $page_number . ' of ' . $total_pages . ')'
                    ]
                ]
            ]
        ],
        'generation_config' => [
            'temperature' => 0.8,
            'candidate_count' => 1,
            'max_output_tokens' => 2048
        ]
    ]);
    
    $response = wp_remote_post(
        "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}",
        [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => $request_body,
            'timeout' => 120
        ]
    );
    
    if (is_wp_error($response)) {
        error_log("API error for page $page_number: " . $response->get_error_message());
        return false;
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if (isset($body['candidates'][0]['content']['parts'])) {
        $parts = $body['candidates'][0]['content']['parts'];
        
        foreach ($parts as $part) {
            if (isset($part['inlineData'])) {
                $image_data = $part['inlineData']['data'];
                $mime_type = isset($part['inlineData']['mime_type']) ? 
                            $part['inlineData']['mime_type'] : 
                            'image/png';
                return 'data:' . $mime_type . ';base64,' . $image_data;
            }
        }
    }
    
    return false;
}

/**
 * Helper: Generate single kids page via API
 */
function generate_single_kids_page_api($image_base64, $prompt, $page_number, $total_pages, $kid_name, $api_key) {
    $model = 'gemini-2.5-flash-image-preview';
    
    $request_body = json_encode([
        'contents' => [
            [
                'parts' => [
                    [
                        'inline_data' => [
                            'mime_type' => 'image/png',
                            'data' => $image_base64
                        ]
                    ],
                    [
                        'text' => "GENERATE AN IMAGE of: " . $prompt . 
                                 " The main character should visually match the provided reference image of " . $kid_name . 
                                 ". Style: children's storybook illustration, colorful, joyful, cartoon style."
                    ]
                ]
            ]
        ],
        'generation_config' => [
            'temperature' => 0.7,
            'max_output_tokens' => 2048
        ]
    ]);
    
    $response = wp_remote_post(
        "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}",
        [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => $request_body,
            'timeout' => 120
        ]
    );
    
    if (is_wp_error($response)) {
        error_log("Kids API error for page $page_number: " . $response->get_error_message());
        return false;
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if (isset($body['candidates'][0]['content']['parts'][0]['inlineData']['data'])) {
        $image_data = $body['candidates'][0]['content']['parts'][0]['inlineData']['data'];
        return 'data:image/png;base64,' . $image_data;
    }
    
    return false;
}

/**
 * Helper: Convert image URL to base64
 */
function get_base64_from_url($url) {
    try {
        $response = wp_remote_get($url);
        
        if (is_wp_error($response)) {
            error_log("Failed to fetch image from URL: " . $url);
            return false;
        }
        
        $image_data = wp_remote_retrieve_body($response);
        
        if (!$image_data) {
            error_log("Empty image data from URL: " . $url);
            return false;
        }
        
        return base64_encode($image_data);
        
    } catch (Exception $e) {
        error_log("Error converting URL to base64: " . $e->getMessage());
        return false;
    }
}

/**
 * Helper: Generate PDF from pages
 */
function generate_pdf_from_pages($pages, $filename) {
    // This is a placeholder - you'll need to implement actual PDF generation
    // using a library like mPDF, TCPDF, or Dompdf
    
    error_log("PDF generation requested for $filename with " . count($pages) . " pages");
    
    // For now, return a placeholder
    // You should implement actual PDF generation here
    
    return false; // Return PDF URL when implemented
}

// Update the send_story_download_email function to include immediate download link:
function send_story_download_email($order, $item) {
    try {
        $customer_email = $order->get_billing_email();
        $order_id = $order->get_id();
        $customer_name = $order->get_billing_first_name() ?: 'Customer';
        
        // If immediate token exists, use it
        $download_token = $order->get_meta('immediate_download_token');
        if (!$download_token) {
            $download_token = wp_generate_password(32, false);
            $order->update_meta_data('download_token', $download_token);
            $order->save();
        }
        
        // Generate download URL
        $download_url = home_url("/download-story/?token={$download_token}&order={$order_id}");
        
        // Get story type
        $story_type = $item->get_meta('story_type');
        $story_name = $story_type === 'love' ? 'Love Story' : "Child's Story";
        
        // Email subject and content
        $subject = '🎉 Your Personalized ' . $story_name . ' is Ready!';
        
        $message = '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #d32f2f, #ff5252); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; }
                .button { display: inline-block; background: #d32f2f; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
                .footer { background: #333; color: white; padding: 20px; text-align: center; border-radius: 0 0 10px 10px; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Your Story Book is Ready!</h1>
                </div>
                <div class="content">
                    <h2>Hello ' . esc_html($customer_name) . ',</h2>
                    <p>Thank you for your purchase! Your personalized ' . $story_name . ' has been generated and is ready to download.</p>
                    
                    <p><strong>Order #:</strong> ' . $order_id . '</p>
                    <p><strong>Story Type:</strong> ' . $story_name . '</p>
                    
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="' . $download_url . '" class="button">📖 Download Your Story Book</a>
                    </div>
                    
                    <p><strong>Important:</strong></p>
                    <ul>
                        <li>The download link will expire in 7 days</li>
                        <li>You can download both individual images and a complete PDF</li>
                        <li>Share the link with friends and family</li>
                    </ul>
                    
                    <p>If you have any questions or need assistance, please contact our support team.</p>
                </div>
                <div class="footer">
                    <p>© ' . date('Y') . ' Your Story Book Team. All rights reserved.</p>
                    <p>This email was sent to ' . $customer_email . '</p>
                </div>
            </div>
        </body>
        </html>';
        
        // Send email
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Story Book Team <noreply@' . $_SERVER['HTTP_HOST'] . '>'
        );
        
        wp_mail($customer_email, $subject, $message, $headers);
        
        error_log("Download email sent to $customer_email for order $order_id");
        
    } catch (Exception $e) {
        error_log("Error sending download email: " . $e->getMessage());
    }
}