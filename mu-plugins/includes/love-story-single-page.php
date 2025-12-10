<?php
// Single Page Love Story Generator

add_action('wp_ajax_generate_single_love_story_page', 'generate_single_love_story_page_handler');
add_action('wp_ajax_nopriv_generate_single_love_story_page', 'generate_single_love_story_page_handler');

function generate_single_love_story_page_handler() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'book_wizard_nonce')) {
        wp_send_json_error('Security check failed');
    }
    
    // Get API key
    $api_key = get_option('ai_api_key');
    if (empty($api_key)) {
        wp_send_json_error('API key not configured. Please configure Google AI API key in settings.');
    }
    
    // Get parameters
    $base64_image1 = $_POST['image1'];
    $base64_image2 = $_POST['image2'];
    $prompt = sanitize_text_field($_POST['prompt']);
    $page_number = intval($_POST['page_number']);
    $total_pages = intval($_POST['total_pages']);
    
    if (empty($base64_image1) || empty($base64_image2) || empty($prompt)) {
        wp_send_json_error('Missing required data');
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
                        'text' => $prompt
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
    
    // Log for debugging
    error_log("Generating page {$page_number}/{$total_pages}");
    
    // Make API call
    $response = wp_remote_post(
        "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}",
        [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => $request_body,
            'timeout' => 120 // 120 second timeout
        ]
    );
    
    if (is_wp_error($response)) {
        error_log("Page {$page_number} API error: " . $response->get_error_message());
        wp_send_json_error('Page ' . $page_number . ': ' . $response->get_error_message());
    }
    
    $status_code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if ($status_code !== 200) {
        $error_msg = isset($body['error']['message']) ? $body['error']['message'] : 'Unknown API error';
        error_log("Page {$page_number} API error {$status_code}: " . $error_msg);
        wp_send_json_error('Page ' . $page_number . ': ' . $error_msg);
    }
    
    // Extract generated image
    if (isset($body['candidates'][0]['content']['parts'])) {
        $parts = $body['candidates'][0]['content']['parts'];
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
            error_log("Page {$page_number} generated successfully");
            wp_send_json_success([
                'image_url' => $image_url,
                'page_number' => $page_number,
                'total_pages' => $total_pages,
                'prompt' => $prompt
            ]);
        } else {
            error_log("Page {$page_number}: AI did not generate an image");
            wp_send_json_error('Page ' . $page_number . ': AI did not generate an image');
        }
    } else {
        error_log("Page {$page_number}: Invalid API response");
        wp_send_json_error('Page ' . $page_number . ': Invalid API response');
    }
}