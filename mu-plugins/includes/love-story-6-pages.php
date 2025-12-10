<?php
// 3-Page Love Story Generator - Single Page Handler (TEST MODE)

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Register AJAX handler
add_action('wp_ajax_generate_single_love_story_page', 'mbw_generate_single_love_story_page_handler');
add_action('wp_ajax_nopriv_generate_single_love_story_page', 'mbw_generate_single_love_story_page_handler');

function mbw_generate_single_love_story_page_handler() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'book_wizard_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    // Get API key
    $api_key = get_option('ai_api_key');
    if (empty($api_key)) {
        wp_send_json_error('API key not configured. Please configure Google AI API key in settings.');
        return;
    }
    
    // Get parameters with validation
    $base64_image1 = isset($_POST['image1']) ? $_POST['image1'] : '';
    $base64_image2 = isset($_POST['image2']) ? $_POST['image2'] : '';
    $prompt = isset($_POST['prompt']) ? sanitize_text_field($_POST['prompt']) : '';
    $page_number = isset($_POST['page_number']) ? intval($_POST['page_number']) : 1;
    $total_pages = isset($_POST['total_pages']) ? intval($_POST['total_pages']) : 3; // CHANGED FROM 6 TO 3
    
    if (empty($base64_image1) || empty($base64_image2) || empty($prompt)) {
        wp_send_json_error('Missing required data: images or prompt');
        return;
    }
    
    // Use Gemini model
    $model = 'gemini-2.5-flash-image-preview';
    
    // Build request with BOTH images
    $request_body = json_encode([
        'contents' => [
            [
                'parts' => [
                    [
                        'inline_data' => [
                            'mime_type' => 'image/png',
                            'data' => $base64_image1
                        ]
                    ],
                    [
                        'inline_data' => [
                            'mime_type' => 'image/png',
                            'data' => $base64_image2
                        ]
                    ],
                    [
                        'text' => $prompt . ' (Page ' . $page_number . ' of ' . $total_pages . ' - Test Mode: 3 pages only)'
                    ]
                ]
            ]
        ],
        'generation_config' => [
            'temperature' => 0.8,
            'candidate_count' => 1,
            'max_output_tokens' => 1024 // Reduced for testing
        ]
    ]);
    
    // Log for debugging
    error_log("MBW TEST MODE: Generating page {$page_number}/{$total_pages}");
    
    // Make API call with shorter timeout for testing
    $response = wp_remote_post(
        "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}",
        [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => $request_body,
            'timeout' => 60 // 60 second timeout for testing
        ]
    );
    
    if (is_wp_error($response)) {
        $error_msg = $response->get_error_message();
        error_log("MBW TEST: Page {$page_number} API error: " . $error_msg);
        wp_send_json_error('Page ' . $page_number . ': ' . $error_msg);
        return;
    }
    
    $status_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if ($status_code !== 200) {
        $error_msg = isset($data['error']['message']) ? $data['error']['message'] : 'Unknown API error';
        error_log("MBW TEST: Page {$page_number} API error {$status_code}: " . $error_msg);
        wp_send_json_error('Page ' . $page_number . ': ' . $error_msg);
        return;
    }
    
    // Extract generated image
    if (isset($data['candidates'][0]['content']['parts'])) {
        $parts = $data['candidates'][0]['content']['parts'];
        $image_url = null;
        
        foreach ($parts as $part) {
            if (isset($part['inlineData'])) {
                $image_data = $part['inlineData']['data'];
                $mime_type = isset($part['inlineData']['mime_type']) ? 
                            $part['inlineData']['mime_type'] : 
                            'image/png';
                $image_url = 'data:' . $mime_type . ';base64,' . $image_data;
                break;
            }
            
            if (isset($part['inline_data'])) {
                $image_data = $part['inline_data']['data'];
                $mime_type = isset($part['inline_data']['mime_type']) ? 
                            $part['inline_data']['mime_type'] : 
                            'image/png';
                $image_url = 'data:' . $mime_type . ';base64,' . $image_data;
                break;
            }
        }
        
        if ($image_url) {
            error_log("MBW TEST: Page {$page_number} generated successfully");
            wp_send_json_success([
                'image_url' => $image_url,
                'page_number' => $page_number,
                'total_pages' => $total_pages,
                'prompt' => $prompt
            ]);
        } else {
            error_log("MBW TEST: Page {$page_number}: AI did not generate an image");
            wp_send_json_error('Page ' . $page_number . ': AI did not generate an image');
        }
    } else {
        error_log("MBW TEST: Page {$page_number}: Invalid API response");
        wp_send_json_error('Page ' . $page_number . ': Invalid API response');
    }
}