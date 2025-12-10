<?php
/* Template Name: Download Story */
/*
Template Post Type: page
*/

get_header();

// Check token and order
$token = isset($_GET['token']) ? sanitize_text_field($_GET['token']) : '';
$order_id = isset($_GET['order']) ? intval($_GET['order']) : 0;
$immediate = isset($_GET['immediate']) && $_GET['immediate'] === 'true';
$generate = isset($_GET['generate']) && $_GET['generate'] === 'true';

// Validate parameters
if (!$token || !$order_id) {
    display_error_message('❌ Invalid Download Link', 'This download link is invalid or has expired.');
    get_footer();
    return;
}

// Verify order
$order = wc_get_order($order_id);
if (!$order) {
    display_error_message('❌ Order Not Found', 'The order associated with this link was not found.');
    get_footer();
    return;
}

// Verify token
$stored_token = $order->get_meta('download_token');
$immediate_token = $order->get_meta('immediate_download_token');

// Check both regular token and immediate token
if ($token !== $stored_token && $token !== $immediate_token) {
    display_error_message('❌ Access Denied', 'Invalid download token.');
    get_footer();
    return;
}

// Get story data
$story_data = get_story_data_from_order($order);

// ============================================
// CRITICAL: IMMEDIATE GENERATION AFTER PAYMENT
// ============================================
if ($immediate && $generate && empty($story_data['pages'])) {
    // This is a fresh order - generate NOW
    display_story_generation_page($order_id, $token);
    get_footer();
    return;
}

// If story is still processing
if (empty($story_data['pages']) && $immediate) {
    display_processing_page($order_id);
    get_footer();
    return;
}

// If story exists, show download page
if (!empty($story_data['pages'])) {
    display_story_download_page($order, $story_data);
    get_footer();
    return;
}

// Fallback
display_error_message('❌ No Story Found', 'Your story could not be found. Please contact support.');
get_footer();

// ============================================
// HELPER FUNCTIONS
// ============================================

function display_error_message($title, $message) {
    echo '<div style="text-align: center; padding: 100px; max-width: 600px; margin: 0 auto;">
            <h2>' . esc_html($title) . '</h2>
            <p>' . esc_html($message) . '</p>
            <a href="' . home_url() . '" style="
                display: inline-block;
                margin-top: 20px;
                padding: 12px 24px;
                background: #d32f2f;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                font-weight: bold;
            ">Return to Home</a>
          </div>';
}

function get_story_data_from_order($order) {
    $story_data = [
        'type' => '',
        'pages' => [],
        'character_details' => [],
        'pdf_url' => ''
    ];
    
    foreach ($order->get_items() as $item) {
        $full_pages = $item->get_meta('full_story_pages');
        if ($full_pages) {
            $story_data['pages'] = json_decode(stripslashes($full_pages), true);
            $story_data['type'] = $item->get_meta('story_type');
            
            $character_details_json = $item->get_meta('character_details');
            if ($character_details_json) {
                $story_data['character_details'] = json_decode(stripslashes($character_details_json), true);
            }
            
            $story_data['pdf_url'] = $item->get_meta('pdf_url') ?: $order->get_meta('_pdf_file');
            break;
        }
    }
    
    return $story_data;
}

function display_story_generation_page($order_id, $token) {
    $nonce = wp_create_nonce('immediate_story_gen');
    ?>
    <div id="story-generation-container" style="text-align: center; padding: 60px 20px; max-width: 800px; margin: 0 auto;">
        <div style="margin-bottom: 40px;">
            <h2 style="color: #d32f2f; font-size: 28px; margin-bottom: 15px;">🎨 Creating Your Personalized Story Book!</h2>
            <p style="color: #666; font-size: 18px; margin-bottom: 10px;">Thank you for your purchase! We're now generating your complete story book.</p>
            <p style="color: #888; font-size: 14px;">This will take 2-3 minutes. Please don't close this page.</p>
        </div>
        
        <!-- Progress Animation -->
        <div style="margin: 40px auto; max-width: 600px;">
            <div class="ai-loading-spinner" style="
                margin: 0 auto 30px;
                width: 80px;
                height: 80px;
                border: 8px solid #f3f3f3;
                border-top: 8px solid #d32f2f;
                border-radius: 50%;
                animation: spin 1.5s linear infinite;
            "></div>
            
            <div id="generation-status" style="
                font-size: 20px;
                color: #333;
                margin-bottom: 20px;
                font-weight: bold;
            ">Starting story generation...</div>
            
            <!-- Progress Bar -->
            <div style="
                background: #f0f0f0;
                border-radius: 10px;
                height: 12px;
                margin: 30px 0;
                overflow: hidden;
                box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
            ">
                <div id="generation-progress-bar" style="
                    height: 100%;
                    background: linear-gradient(90deg, #d32f2f, #ff5252);
                    width: 5%;
                    transition: width 0.5s ease;
                    border-radius: 10px;
                "></div>
            </div>
            
            <!-- Detailed Status -->
            <div id="detailed-status" style="
                color: #666;
                font-size: 14px;
                margin: 20px 0;
                min-height: 40px;
            "></div>
            
            <!-- Estimated Time -->
            <div id="estimated-time" style="
                color: #888;
                font-size: 14px;
                margin-top: 20px;
            ">Estimated time remaining: 2-3 minutes</div>
        </div>
        
        <!-- Order Info -->
        <div style="
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            margin-top: 40px;
            border-left: 4px solid #d32f2f;
        ">
            <p style="margin: 0; color: #666;">
                <strong>Order #:</strong> <?php echo $order_id; ?><br>
                <strong>Status:</strong> <span id="order-status">Processing...</span>
            </p>
        </div>
    </div>
    
    <!-- JavaScript for Generation -->
    <script>
    jQuery(document).ready(function($) {
        let generationAttempts = 0;
        const maxAttempts = 15; // Increased for reliability
        let totalPages = 0;
        let completedPages = 0;
        let isGenerating = false;
        let startTime = Date.now();
        
        // Update status helper
        function updateStatus(status, progressPercent, details) {
            $('#generation-status').text(status);
            $('#generation-progress-bar').css('width', progressPercent + '%');
            
            if (details) {
                $('#detailed-status').html(details);
            }
            
            // Update estimated time
            if (progressPercent > 5) {
                const elapsed = (Date.now() - startTime) / 1000; // seconds
                const totalEstimated = elapsed / (progressPercent / 100);
                const remaining = Math.max(0, Math.ceil((totalEstimated - elapsed) / 60)); // minutes
                $('#estimated-time').html('Estimated time remaining: ' + remaining + ' minute' + (remaining !== 1 ? 's' : ''));
            }
        }
        
        // Start generation
        function startStoryGeneration() {
            if (isGenerating) return;
            isGenerating = true;
            
            updateStatus('Initializing story generation...', 5, 'Preparing your story data');
            
            // First, check order status
            $.ajax({
                url: '<?php echo admin_url("admin-ajax.php"); ?>',
                type: 'POST',
                data: {
                    action: 'mbw_check_order_status',
                    order_id: <?php echo $order_id; ?>,
                    token: '<?php echo $token; ?>',
                    nonce: '<?php echo $nonce; ?>'
                },
                success: function(response) {
                    console.log('Order check:', response);
                    
                    if (response.success) {
                        if (response.data.story_generated === 'yes') {
                            // Story already generated
                            updateStatus('✅ Story already generated!', 100, 'Redirecting to download...');
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            // Start actual generation
                            generateFullStory();
                        }
                    } else {
                        // Try generation anyway
                        generateFullStory();
                    }
                },
                error: function() {
                    // Fallback to generation
                    generateFullStory();
                }
            });
        }
        
        // Main generation function
        function generateFullStory() {
            updateStatus('Starting story generation...', 10, 'Contacting AI services');
            
            $.ajax({
                url: '<?php echo admin_url("admin-ajax.php"); ?>',
                type: 'POST',
                data: {
                    action: 'generate_full_story_immediate',
                    order_id: <?php echo $order_id; ?>,
                    token: '<?php echo $token; ?>',
                    nonce: '<?php echo $nonce; ?>'
                },
                success: function(response) {
                    console.log('Generation response:', response);
                    
                    if (response.success) {
                        if (response.data === 'already_generated') {
                            updateStatus('✅ Story already generated!', 100, 'Redirecting...');
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            // Success!
                            updateStatus('✅ Story generated successfully!', 100, 'Creating PDF and sending email...');
                            $('#order-status').text('Completed');
                            
                            // Show success message and redirect
                            setTimeout(function() {
                                updateStatus('✅ All done! Redirecting...', 100, 'Your story is ready!');
                                setTimeout(function() {
                                    location.reload(); // Reload to show download page
                                }, 2000);
                            }, 3000);
                        }
                    } else {
                        // Handle error with retry
                        handleGenerationError(response.data || 'Unknown error');
                    }
                },
                error: function(xhr, status, error) {
                    handleGenerationError('Connection error: ' + error);
                }
            });
        }
        
        // Error handling with retry
        function handleGenerationError(errorMsg) {
            generationAttempts++;
            
            if (generationAttempts < maxAttempts) {
                const nextAttempt = generationAttempts + 1;
                const progress = Math.min(5 + (generationAttempts * 5), 90);
                
                updateStatus(
                    'Attempt ' + nextAttempt + '/' + maxAttempts + ': ' + errorMsg,
                    progress,
                    'Retrying in 5 seconds...'
                );
                
                setTimeout(function() {
                    updateStatus('Retrying... (' + nextAttempt + '/' + maxAttempts + ')', progress);
                    generateFullStory();
                }, 5000);
            } else {
                updateStatus(
                    '❌ Generation failed after ' + maxAttempts + ' attempts',
                    100,
                    'Please contact support with order #<?php echo $order_id; ?>'
                );
                $('#order-status').text('Failed - Contact Support');
            }
        }
        
        // Start after a short delay
        setTimeout(startStoryGeneration, 1000);
        
        // Add CSS animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.7; }
            }
            .pulse { animation: pulse 2s infinite; }
        `;
        document.head.appendChild(style);
    });
    </script>
    <?php
}

function display_processing_page($order_id) {
    ?>
    <div style="text-align: center; padding: 100px; max-width: 600px; margin: 0 auto;">
        <div class="ai-loading-spinner" style="
            margin: 0 auto 30px;
            width: 60px;
            height: 60px;
            border: 6px solid #f3f3f3;
            border-top: 6px solid #d32f2f;
            border-radius: 50%;
            animation: spin 1.5s linear infinite;
        "></div>
        
        <h2 style="color: #333; margin-bottom: 20px;">⏳ Your Story is Being Generated</h2>
        <p style="color: #666; margin-bottom: 30px; font-size: 16px;">
            We're creating your personalized story book. This may take a few minutes.
        </p>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 30px;">
            <p style="margin: 0; color: #555;">
                <strong>Order #<?php echo $order_id; ?></strong><br>
                <small>Page will refresh automatically when ready</small>
            </p>
        </div>
        
        <button onclick="location.reload()" style="
            padding: 12px 30px;
            background: #d32f2f;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px;
        ">
            🔄 Refresh Status
        </button>
        
        <a href="<?php echo home_url(); ?>" style="
            display: inline-block;
            padding: 12px 30px;
            background: #666;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            margin: 10px;
        ">
            Return to Home
        </a>
        
        <script>
        // Auto-refresh every 10 seconds
        setTimeout(function() {
            location.reload();
        }, 10000);
        
        // Add spinner animation
        const style = document.createElement('style');
        style.textContent = '@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }';
        document.head.appendChild(style);
        </script>
    </div>
    <?php
}

function display_story_download_page($order, $story_data) {
    $order_id = $order->get_id();
    $pages_count = count($story_data['pages']);
    $story_type = $story_data['type'];
    $character_details = $story_data['character_details'];
    $pdf_url = $story_data['pdf_url'];
    
    // Get names for display
    $main_character = '';
    if ($story_type === 'kids' && isset($character_details['kid_name'])) {
        $main_character = $character_details['kid_name'];
    } elseif ($story_type === 'love' && isset($character_details['your_name']) && isset($character_details['lover_name'])) {
        $main_character = $character_details['your_name'] . ' & ' . $character_details['lover_name'];
    }
    ?>
    
    <div style="max-width: 1200px; margin: 0 auto; padding: 40px 20px;">
        <!-- Success Header -->
        <div style="text-align: center; margin-bottom: 40px;">
            <div style="font-size: 48px; margin-bottom: 20px;">🎉</div>
            <h1 style="color: #d32f2f; margin-bottom: 10px;">Your Story Book is Ready!</h1>
            <p style="color: #666; font-size: 18px; margin-bottom: 20px;">
                <?php echo $pages_count; ?>-page personalized story for <?php echo esc_html($main_character); ?>
            </p>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; display: inline-block;">
                <p style="margin: 0; color: #555;">
                    <strong>Order #<?php echo $order_id; ?></strong> • 
                    Downloaded on <?php echo date('F j, Y'); ?>
                </p>
            </div>
        </div>
        
        <!-- Story Preview Grid -->
        <div style="background: white; border-radius: 15px; padding: 30px; margin: 30px 0; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
            <h2 style="color: #333; margin-bottom: 25px; text-align: center;">Your Complete Story (<?php echo $pages_count; ?> Pages)</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; margin: 30px 0;">
                <?php foreach ($story_data['pages'] as $page): ?>
                <div style="border: 2px solid #eaeaea; border-radius: 10px; overflow: hidden; transition: transform 0.3s, box-shadow 0.3s; background: white;">
                    <div style="position: relative; overflow: hidden;">
                        <img src="<?php echo esc_url($page['image_url']); ?>" 
                             alt="Page <?php echo $page['page_number']; ?>"
                             style="width: 100%; height: 150px; object-fit: cover; cursor: pointer;"
                             onclick="viewFullSizeImage('<?php echo esc_url($page['image_url']); ?>')">
                        <div style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.7); color: white; padding: 3px 8px; border-radius: 12px; font-size: 12px;">
                            <?php echo $page['page_number']; ?>/<?php echo $pages_count; ?>
                        </div>
                    </div>
                    <div style="padding: 15px;">
                        <strong style="display: block; margin-bottom: 8px; color: #333;">Page <?php echo $page['page_number']; ?></strong>
                        <div style="display: flex; gap: 8px;">
                            <a href="<?php echo esc_url($page['image_url']); ?>" 
                               download="story-page-<?php echo $page['page_number']; ?>.png"
                               style="flex: 1; text-align: center; background: #4CAF50; color: white; padding: 8px; border-radius: 5px; text-decoration: none; font-size: 14px;">
                                💾 Save
                            </a>
                            <button onclick="viewFullSizeImage('<?php echo esc_url($page['image_url']); ?>')"
                                    style="flex: 1; background: #2196F3; color: white; border: none; padding: 8px; border-radius: 5px; cursor: pointer; font-size: 14px;">
                                👁️ View
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div style="text-align: center; margin-top: 30px; color: #666; font-size: 14px;">
                Click any image to view full size • <?php echo $pages_count; ?> pages total
            </div>
        </div>
        
        <!-- Download Options -->
        <div style="text-align: center; margin: 50px 0;">
            <h3 style="color: #333; margin-bottom: 30px;">📥 Download Options</h3>
            
            <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
                <?php if ($pdf_url): ?>
                <a href="<?php echo esc_url($pdf_url); ?>" 
                   download="my-story-book-<?php echo $order_id; ?>.pdf"
                   style="padding: 20px 40px; background: linear-gradient(135deg, #FF9800, #FF5722); color: white; 
                          text-decoration: none; border-radius: 10px; font-size: 18px; font-weight: bold;
                          display: flex; align-items: center; gap: 15px; min-width: 250px; justify-content: center;">
                    <span style="font-size: 24px;">📄</span>
                    <div>
                        <div>Download PDF</div>
                        <small style="opacity: 0.9; font-weight: normal;">Complete story book</small>
                    </div>
                </a>
                <?php endif; ?>
                
                <button onclick="downloadAllPages()" 
                        style="padding: 20px 40px; background: linear-gradient(135deg, #4CAF50, #2E7D32); color: white; 
                               border: none; border-radius: 10px; font-size: 18px; font-weight: bold; cursor: pointer;
                               display: flex; align-items: center; gap: 15px; min-width: 250px; justify-content: center;">
                    <span style="font-size: 24px;">💾</span>
                    <div>
                        <div>Download All Images</div>
                        <small style="opacity: 0.9; font-weight: normal;">Individual pages (ZIP)</small>
                    </div>
                </button>
                
                <button onclick="printStory()" 
                        style="padding: 20px 40px; background: linear-gradient(135deg, #2196F3, #1976D2); color: white; 
                               border: none; border-radius: 10px; font-size: 18px; font-weight: bold; cursor: pointer;
                               display: flex; align-items: center; gap: 15px; min-width: 250px; justify-content: center;">
                    <span style="font-size: 24px;">🖨️</span>
                    <div>
                        <div>Print Story</div>
                        <small style="opacity: 0.9; font-weight: normal;">Printer-friendly version</small>
                    </div>
                </button>
            </div>
            
            <p style="color: #666; margin-top: 30px; font-size: 14px;">
                <strong>Note:</strong> Download links expire in 7 days
            </p>
        </div>
        
        <!-- Additional Info -->
        <div style="background: #f8f9fa; padding: 25px; border-radius: 10px; margin-top: 40px;">
            <h4 style="color: #333; margin-bottom: 15px;">📧 Email Sent</h4>
            <p style="color: #666; margin: 0;">
                A download link has been sent to <strong><?php echo esc_html($order->get_billing_email()); ?></strong>.
                You can also share this page URL with others.
            </p>
        </div>
    </div>
    
    <!-- JavaScript Functions -->
    <script>
    // Global image viewer
    function viewFullSizeImage(imageUrl) {
        const modal = document.createElement('div');
        modal.style.cssText = `
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.95); z-index: 9999; display: flex;
            align-items: center; justify-content: center; animation: fadeIn 0.3s;
        `;
        
        modal.innerHTML = `
            <div style="position: relative; max-width: 90%; max-height: 90%;">
                <button onclick="this.parentElement.parentElement.remove();" style="
                    position: absolute; top: -40px; right: 0; background: #ff5252;
                    color: white; border: none; border-radius: 50%; width: 30px;
                    height: 30px; font-size: 20px; cursor: pointer; z-index: 10000;
                ">×</button>
                
                <img src="${imageUrl}" 
                     style="max-width: 100%; max-height: 85vh; object-fit: contain;"
                     alt="Full size image">
                
                <div style="text-align: center; margin-top: 20px;">
                    <a href="${imageUrl}" 
                       download="story-page-${Date.now()}.png"
                       style="background: #4CAF50; color: white; padding: 12px 24px;
                              border-radius: 5px; text-decoration: none; display: inline-block;
                              margin: 0 10px; font-weight: bold;">
                        💾 Download This Page
                    </a>
                    <button onclick="this.parentElement.parentElement.parentElement.remove();" style="
                        background: #666; color: white; border: none; padding: 12px 24px;
                        border-radius: 5px; cursor: pointer; margin: 0 10px; font-weight: bold;
                    ">Close</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        document.body.style.overflow = 'hidden';
        
        // Add animation style
        const style = document.createElement('style');
        style.textContent = '@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }';
        document.head.appendChild(style);
    }
    
    // Download all pages
    async function downloadAllPages() {
        alert('To download all pages:\n\n1. Right-click each image\n2. Choose "Save image as..."\n3. Save all images to a folder\n\nPDF download is also available above!');
    }
    
    // Print story
    function printStory() {
        window.print();
    }
    </script>
    <?php
}
?>
<?php get_footer(); ?>