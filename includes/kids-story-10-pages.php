<?php
// Kids Story Generator - Simple Version

// Security check
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Register AJAX handlers
add_action( 'wp_ajax_generate_single_kids_story_page', 'handle_kids_story_generation' );
add_action( 'wp_ajax_nopriv_generate_single_kids_story_page', 'handle_kids_story_generation' );

/**
 * Handle kids story page generation
 */
function handle_kids_story_generation() {
    // Verify nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'book_wizard_nonce' ) ) {
        wp_send_json_error( 'Security check failed' );
        return;
    }
    
    // Get API key
    $api_key = get_option( 'ai_api_key' );
    if ( empty( $api_key ) ) {
        wp_send_json_error( 'API key not configured' );
        return;
    }
    
    // Get data
    $image_data = isset( $_POST['image'] ) ? $_POST['image'] : '';
    $prompt = isset( $_POST['prompt'] ) ? sanitize_text_field( $_POST['prompt'] ) : '';
    $page_number = isset( $_POST['page_number'] ) ? intval( $_POST['page_number'] ) : 1;
    $kid_name = isset( $_POST['kid_name'] ) ? sanitize_text_field( $_POST['kid_name'] ) : 'the child';
    
    if ( empty( $image_data ) || empty( $prompt ) ) {
        wp_send_json_error( 'Missing required data' );
        return;
    }
    
    // Build the prompt
    $full_prompt = "GENERATE AN IMAGE of: " . $prompt . 
               " The main character should visually match the provided reference image of " . $kid_name . 
               ". Style: children's storybook illustration, colorful, joyful, cartoon style.";
    
    // Build the API request
    $request_data = array(
        'contents' => array(
            array(
                'parts' => array(
                    array(
                        'inline_data' => array(
                            'mime_type' => 'image/png',
                            'data' => $image_data
                        )
                    ),
                    array(
                        'text' => $full_prompt
                    )
                )
            )
        ),
        'generation_config' => array(
            'temperature' => 0.7,
            'max_output_tokens' => 2048
        )
    );
    
    // Make API call
    $response = wp_remote_post(
        'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-image-preview:generateContent?key=' . $api_key,
        array(
            'headers' => array( 'Content-Type' => 'application/json' ),
            'body' => json_encode( $request_data ),
            'timeout' => 60
        )
    );
    
    // Handle response
    if ( is_wp_error( $response ) ) {
        wp_send_json_error( 'API request failed: ' . $response->get_error_message() );
        return;
    }
    
    $response_code = wp_remote_retrieve_response_code( $response );
    $response_body = wp_remote_retrieve_body( $response );
    $response_data = json_decode( $response_body, true );
    
    if ( $response_code !== 200 ) {
        $error_message = isset( $response_data['error']['message'] ) ? $response_data['error']['message'] : 'Unknown API error';
        wp_send_json_error( 'API Error: ' . $error_message );
        return;
    }
    
    // Extract image from response
    if ( isset( $response_data['candidates'][0]['content']['parts'][0]['inlineData']['data'] ) ) {
        $image_base64 = $response_data['candidates'][0]['content']['parts'][0]['inlineData']['data'];
        $image_url = 'data:image/png;base64,' . $image_base64;
        
        wp_send_json_success( array(
            'image_url' => $image_url,
            'page_number' => $page_number,
            'kid_name' => $kid_name,
            'prompt' => $prompt
        ) );
    } else {
        wp_send_json_error( 'No image generated in response' );
    }
}