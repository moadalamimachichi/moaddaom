<?php
// cart-handler.php - MINIMAL WORKING VERSION

// Save basic data to cart
add_filter('woocommerce_add_cart_item_data', 'my_book_save_custom_data_to_cart', 10, 2);
function my_book_save_custom_data_to_cart($cart_item_data, $product_id) {
    
    // Check if it's our story product
    if ($product_id == 1452 || $product_id == 64) {
        
        // Save story type
        if (isset($_REQUEST['story_type'])) {
            $cart_item_data['story_type'] = sanitize_text_field($_REQUEST['story_type']);
        }
        
        // Save purchase option
        if (isset($_REQUEST['purchase_option'])) {
            $cart_item_data['purchase_option'] = sanitize_text_field($_REQUEST['purchase_option']);
        }
        
        // Save character details
        if (isset($_REQUEST['character_details'])) {
            $cart_item_data['character_details'] = sanitize_text_field($_REQUEST['character_details']);
        }
        
        // Save avatar
        if (isset($_REQUEST['avatar'])) {
            $cart_item_data['custom_avatar'] = esc_url_raw($_REQUEST['avatar']);
        }
        
        // Save second avatar for love stories
        if (isset($_REQUEST['avatar2'])) {
            $cart_item_data['custom_avatar2'] = esc_url_raw($_REQUEST['avatar2']);
        }
        
        // Save preview image
        if (isset($_REQUEST['preview_image'])) {
            $cart_item_data['preview_image'] = esc_url_raw($_REQUEST['preview_image']);
        }
        
        // Add unique key
        $cart_item_data['unique_key'] = md5(microtime().rand());
    }
    
    return $cart_item_data;
}

// Display data in cart
add_filter('woocommerce_get_item_data', 'my_book_display_custom_data_in_cart', 10, 2);
function my_book_display_custom_data_in_cart($item_data, $cart_item) {
    
    // Show story type
    if (!empty($cart_item['story_type'])) {
        $type = $cart_item['story_type'] == 'love' ? 'Love Story' : "Kid's Story";
        $item_data[] = [
            'key' => 'Story Type',
            'value' => $type
        ];
    }
    
    // Show purchase option
    if (!empty($cart_item['purchase_option'])) {
        $option = $cart_item['purchase_option'] == 'digital' ? 'Digital Only' : 'Digital + Physical';
        $item_data[] = [
            'key' => 'Package',
            'value' => $option
        ];
    }
    
    // Show preview image
    if (!empty($cart_item['preview_image'])) {
        $item_data[] = [
            'key' => 'Preview',
            'value' => '<img src="' . esc_url($cart_item['preview_image']) . '" style="width: 200px; height: auto; border-radius: 8px;">'
        ];
    }
    
    return $item_data;
}

// Save to order
add_action('woocommerce_checkout_create_order_line_item', 'my_book_save_custom_data_to_order', 10, 4);
function my_book_save_custom_data_to_order($item, $cart_item_key, $values, $order) {
    
    // Save story type
    if (isset($values['story_type'])) {
        $item->add_meta_data('story_type', $values['story_type']);
    }
    
    // Save purchase option
    if (isset($values['purchase_option'])) {
        $item->add_meta_data('purchase_option', $values['purchase_option']);
    }
    
    // Save character details
    if (isset($values['character_details'])) {
        $item->add_meta_data('character_details', $values['character_details']);
    }
    
    // Save preview image
    if (isset($values['preview_image'])) {
        $item->add_meta_data('preview_image', $values['preview_image']);
    }
}

// Simple AJAX add to cart
add_action('wp_ajax_my_book_add_to_cart', 'my_book_ajax_add_to_cart');
add_action('wp_ajax_nopriv_my_book_add_to_cart', 'my_book_ajax_add_to_cart');
function my_book_ajax_add_to_cart() {
    
    // Get product ID
    $product_id = intval($_POST['product_id']);
    
    // Validate it's our product
    if ($product_id != 1452 && $product_id != 64) {
        wp_send_json_error('Invalid product');
    }
    
    // Redirect to cart
    $redirect = wc_get_cart_url();
    wp_send_json_success(['redirect' => $redirect]);
}