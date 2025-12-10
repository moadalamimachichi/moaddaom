<?php
class My_Book_Wizard {
    
    public function __construct() {
        // Constructor
    }
    
    public function run() {
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        // Add shortcode for wizard page
        add_shortcode('book_wizard', array($this, 'render_wizard'));
        // Force full-width layout on wizard page
        add_filter('body_class', array($this, 'add_body_class'));
    }
    
    public function add_body_class($classes) {
        global $post;
        if ($post && has_shortcode($post->post_content, 'book_wizard')) {
            $classes[] = 'book-wizard-page';
        }
        return $classes;
    }
    
    public function enqueue_assets() {
        // Load on all front‑end for now (we'll restrict later)
        if (is_admin()) return;

        // CSS
        wp_enqueue_style(
            'my-book-wizard-css',
            MY_BOOK_WIZARD_URL . 'assets/css/wizard.css',
            array(),
            '1.2'
        );
        
        // JS
        wp_enqueue_script(
            'my-book-wizard-js',
            MY_BOOK_WIZARD_URL . 'assets/js/wizard.js',
            array('jquery'),
            '1.2',
            true
        );
        
        // Localize script
        wp_localize_script('my-book-wizard-js', 'book_wizard_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'product_id' => 64,
            'cart_url' => wc_get_cart_url(),
            'nonce' => wp_create_nonce('book_wizard_nonce')
        ));
    }
    
    public function render_wizard() {
        ob_start();
        ?>
        <div id="book-wizard">
<!-- Update the progress indicator (5 steps) -->
<div class="wizard-progress">
    <div class="progress-step active" data-step="1">
        <div class="progress-step-number">1</div>
        <span>Choose Story Type</span>
    </div>
    <div class="progress-step" data-step="2">
        <div class="progress-step-number">2</div>
        <span>Character Details</span>
    </div>
    <div class="progress-step" data-step="3">
        <div class="progress-step-number">3</div>
        <span>Create Character(s)</span>
    </div>
    <div class="progress-step" data-step="4">
        <div class="progress-step-number">4</div>
        <span>Preview & Purchase</span>
    </div>
    <div class="progress-step" data-step="5">
        <div class="progress-step-number">5</div>
        <span>Full Book Generation</span>
    </div>
</div>
            
            <!-- Step 1: Story Type -->
            <div class="wizard-step step-active" data-step="1">
                <h2 style="text-align: center; margin-bottom: 20px; color: #333;">Step 1: Choose Your Story Type</h2>
                <p style="text-align: center; color: #666; margin-bottom: 40px; font-size: 1.1em;">Select the type of storybook you want to create</p>
                
                <div class="template-choices">
                    <div class="template-choice" data-template="kids">
                        <img src="<?php echo MY_BOOK_WIZARD_URL; ?>assets/images/kids-story.jpg" alt="Story for Your Kid">
                        <h4>Story for Your Kid</h4>
                        <p>Create a magical adventure with your child as the hero! Perfect for birthdays, holidays, or just because.</p>
                        <div style="margin-top: 15px; padding: 10px; background: #e8f4fd; border-radius: 8px;">
                            <small><strong>Includes:</strong> 1 avatar creation</small>
                        </div>
                    </div>
                    <div class="template-choice" data-template="love">
                        <img src="<?php echo MY_BOOK_WIZARD_URL; ?>assets/images/love-story.jpg" alt="Love Story">
                        <h4>Love Story</h4>
                        <p>Celebrate your romance! Create a beautiful love story featuring you and your partner as the main characters.</p>
                        <div style="margin-top: 15px; padding: 10px; background: #ffeaea; border-radius: 8px;">
                            <small><strong>Includes:</strong> 2 avatar creations</small>
                        </div>
                    </div>
                </div>
                
                <div class="wizard-buttons">
                    <button class="wizard-next">Next: Enter Character Details</button>
                </div>
            </div>
            
            <!-- Step 2: Character Details -->
            <div class="wizard-step" data-step="2">
                <div id="step2-content-kids" style="display: none;">
                    <h2 style="text-align: center; margin-bottom: 20px; color: #333;">Step 2: Tell Us About Your Child</h2>
                    <p style="text-align: center; color: #666; margin-bottom: 40px; font-size: 1.1em;">We'll use these details to personalize the story</p>
                    
                    <div style="max-width: 500px; margin: 0 auto; padding: 30px; background: #f8f9fa; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
                        <div style="margin-bottom: 25px;">
                            <label style="display: block; margin-bottom: 8px; color: #333; font-weight: bold;">Child's Name</label>
                            <input type="text" 
                                   id="kid-name" 
                                   placeholder="Enter your child's name"
                                   style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;">
                        </div>
                        
                        <div style="margin-bottom: 25px;">
                            <label style="display: block; margin-bottom: 8px; color: #333; font-weight: bold;">Child's Gender</label>
                            <select id="kid-gender" style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;">
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div id="step2-content-love" style="display: none;">
                    <h2 style="text-align: center; margin-bottom: 20px; color: #333;">Step 2: Tell Us About Your Love Story</h2>
                    <p style="text-align: center; color: #666; margin-bottom: 40px; font-size: 1.1em;">We'll use these details to personalize your romantic story</p>
                    
                    <div style="max-width: 600px; margin: 0 auto;">
                        <!-- Your Details -->
                        <div style="background: #fff5f5; padding: 25px; border-radius: 15px; margin-bottom: 30px; border: 2px solid #d32f2f;">
                            <h3 style="color: #d32f2f; margin-bottom: 20px; text-align: center;">👤 Your Details</h3>
                            <div style="margin-bottom: 20px;">
                                <label style="display: block; margin-bottom: 8px; color: #333; font-weight: bold;">Your Name</label>
                                <input type="text" 
                                       id="your-name" 
                                       placeholder="Enter your name"
                                       style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;">
                            </div>
                            
                            <div style="margin-bottom: 20px;">
                                <label style="display: block; margin-bottom: 8px; color: #333; font-weight: bold;">Your Gender</label>
                                <select id="your-gender" style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;">
                                    <option value="">Select Your Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Partner Details -->
                        <div style="background: #fff5f5; padding: 25px; border-radius: 15px; border: 2px solid #d32f2f;">
                            <h3 style="color: #d32f2f; margin-bottom: 20px; text-align: center;">💖 Partner's Details</h3>
                            <div style="margin-bottom: 20px;">
                                <label style="display: block; margin-bottom: 8px; color: #333; font-weight: bold;">Partner's Name</label>
                                <input type="text" 
                                       id="lover-name" 
                                       placeholder="Enter your partner's name"
                                       style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;">
                            </div>
                            
                            <div style="margin-bottom: 20px;">
                                <label style="display: block; margin-bottom: 8px; color: #333; font-weight: bold;">Partner's Gender</label>
                                <select id="lover-gender" style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;">
                                    <option value="">Select Partner's Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="step2-placeholder" style="text-align: center; padding: 40px;">
                    <p>Please select a story type in Step 1 first.</p>
                </div>
                
                <div class="wizard-buttons">
                    <button class="wizard-prev">← Back to Story Type</button>
                    <button class="wizard-next">Next: Create Character(s)</button>
                </div>
            </div>
            
            <!-- Step 3: Avatar Creation (FINAL STEP) -->
            <div class="wizard-step" data-step="3">
                <div id="step3-content-kids" style="display: none;">
                    <h2 style="text-align: center; margin-bottom: 20px; color: #333;">Step 3: Create Your Child's Avatar</h2>
                    <p style="text-align: center; color: #666; margin-bottom: 40px; font-size: 1.1em;">Design your child's character - start by uploading a photo!</p>
                    
                    <div id="readyplayerme-section">
                        <div id="rpm-creation-kids">
                            <iframe
                                id="readyplayerme-iframe-kids"
                                src="https://lovebookstory.readyplayer.me/avatar?frameApi&clearAvatarConfig=1&bodyType=fullbody&gender=female&selectBodyType=false&selectGender=false&quickStart=photo&uiTheme=light&step=photo"
                                width="100%"
                                height="600px"
                                allow="camera *; microphone *"
                                allowfullscreen>
                            </iframe>
                            
                            <div style="margin: 30px 0; padding: 25px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 15px; border: 3px solid #d32f2f;">
                                <h3 style="color: #d32f2f; margin-bottom: 25px; text-align: center;">📸 How to Create Your Avatar</h3>
                                
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 25px;">
                                    <!-- Step 1 -->
                                    <div style="text-align: center; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                                        <div style="width: 70px; height: 70px; background: #d32f2f; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 30px; font-weight: bold;">1</div>
                                        <div style="font-size: 48px; margin-bottom: 15px;">📤</div>
                                        <h4 style="color: #333; margin-bottom: 10px;">Upload Photo</h4>
                                        <p style="color: #666; font-size: 0.9em; margin: 0;">
                                            Click the <strong>"UPLOAD PHOTO" button</strong> in the widget above
                                        </p>
                                    </div>
                                    
                                    <!-- Step 2 -->
                                    <div style="text-align: center; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                                        <div style="width: 70px; height: 70px; background: #d32f2f; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 30px; font-weight: bold;">2</div>
                                        <div style="font-size: 48px; margin-bottom: 15px;">✨</div>
                                        <h4 style="color: #333; margin-bottom: 10px;">Customize</h4>
                                        <p style="color: #666; font-size: 0.9em; margin: 0;">
                                            Adjust hair, face, and features to match your child
                                        </p>
                                    </div>
                                    
                                    <!-- Step 3 -->
                                    <div style="text-align: center; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                                        <div style="width: 70px; height: 70px; background: #d32f2f; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 30px; font-weight: bold;">3</div>
                                        <div style="font-size: 48px; margin-bottom: 15px;">💾</div>
                                        <h4 style="color: #333; margin-bottom: 10px;">Save & Copy</h4>
                                        <p style="color: #666; font-size: 0.9em; margin: 0;">
                                            Click <strong>"NEXT" → "DONE"</strong>, then copy the URL and paste below
                                        </p>
                                    </div>
                                </div>
                                
                                <div style="background: #fff5f5; padding: 15px; border-radius: 10px; border-left: 4px solid #d32f2f;">
                                    <h4 style="color: #d32f2f; margin-bottom: 10px;">💡 Quick Tips:</h4>
                                    <ul style="color: #666; margin: 0; padding-left: 20px;">
                                        <li>The <strong>"Upload Photo" option is selected by default</strong> - just click it to start!</li>
                                        <li>Use a clear, front-facing photo for best results</li>
                                        <li>Customize everything except clothing (we'll add story outfits)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <!-- URL Input Section -->
                        <div id="rpm-url-section-kids" style="display: none; text-align: center; padding: 30px; background: #f0fff0; border-radius: 15px; margin: 20px 0; border: 3px solid #4CAF50;">
                            <h3 style="color: #4CAF50; margin-bottom: 20px;">✅ Avatar Created Successfully!</h3>
                            <div style="font-size: 48px; margin-bottom: 20px;">🎉</div>
                            <p style="color: #666; margin-bottom: 15px; font-size: 1.1em;">Now paste the avatar URL you received:</p>
                            
                            <div style="max-width: 600px; margin: 0 auto;">
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                                    <div style="font-size: 24px; color: #d32f2f;">🔗</div>
                                    <input type="text" 
                                           id="avatar-url-input-kids" 
                                           placeholder="Paste your ReadyPlayerMe URL here (e.g., https://models.lovebookstory.readyplayer.me/...)"
                                           style="width: 100%; padding: 15px; border: 2px solid #4CAF50; border-radius: 8px; font-size: 16px;">
                                </div>
                                
                                <button id="save-avatar-url-kids" 
                                        style="padding: 15px 40px; background: linear-gradient(45deg, #4CAF50, #45a049); color: white; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; font-weight: bold; box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);">
                                    <span style="margin-right: 10px;">💾</span> Save Child's Avatar
                                </button>
                                
                                <p style="color: #666; margin-top: 20px; font-size: 0.9em;">
                                    <strong>Where to find the URL:</strong> After clicking "DONE", copy the URL from your browser's address bar.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="step3-content-love" style="display: none;">
                    <h2 style="text-align: center; margin-bottom: 20px; color: #333;">Step 3: Create Your Love Story Avatars</h2>
                    <p style="text-align: center; color: #666; margin-bottom: 40px; font-size: 1.1em;">Design both you and your partner's characters</p>
                    
                    <!-- Your Avatar -->
                    <div id="love-avatar-1">
                        <h3 style="text-align: center; color: #d32f2f; margin-bottom: 20px; background: #fff5f5; padding: 15px; border-radius: 10px;">Your Avatar</h3>
                        <div id="readyplayerme-section-love1">
                            <div id="rpm-creation-love1">
                                <iframe
                                    id="readyplayerme-iframe-love1"
                                    src="https://lovebookstory.readyplayer.me/avatar?frameApi&clearAvatarConfig=1&bodyType=fullbody&gender=male&selectBodyType=false&selectGender=false&quickStart=photo&uiTheme=light&step=photo"
                                    width="100%"
                                    height="500px"
                                    allow="camera *; microphone *"
                                    allowfullscreen>
                                </iframe>
                                
                                <div style="margin: 20px 0; padding: 20px; background: #fff5f5; border-radius: 10px; border: 2px solid #d32f2f;">
                                    <h4 style="color: #d32f2f; margin-bottom: 15px; text-align: center;">📸 Quick Start Guide</h4>
                                    <div style="display: flex; align-items: center; justify-content: center; gap: 30px; margin-bottom: 15px; flex-wrap: wrap;">
                                        <div style="text-align: center;">
                                            <div style="width: 60px; height: 60px; background: #d32f2f; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-size: 24px;">1</div>
                                            <div style="font-size: 36px;">📤</div>
                                            <p style="color: #666; font-size: 0.9em; margin: 5px 0 0 0;">Click <strong>UPLOAD PHOTO</strong><br>in the widget</p>
                                        </div>
                                        <div style="font-size: 24px; color: #d32f2f;">→</div>
                                        <div style="text-align: center;">
                                            <div style="width: 60px; height: 60px; background: #d32f2f; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-size: 24px;">2</div>
                                            <div style="font-size: 36px;">✨</div>
                                            <p style="color: #666; font-size: 0.9em; margin: 5px 0 0 0;">Customize your<br>avatar's features</p>
                                        </div>
                                        <div style="font-size: 24px; color: #d32f2f;">→</div>
                                        <div style="text-align: center;">
                                            <div style="width: 60px; height: 60px; background: #d32f2f; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-size: 24px;">3</div>
                                            <div style="font-size: 36px;">💾</div>
                                            <p style="color: #666; font-size: 0.9em; margin: 5px 0 0 0;">Click <strong>NEXT → DONE</strong><br>then copy URL</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="rpm-url-section-love1" style="display: none; text-align: center; padding: 30px; background: #fff5f5; border-radius: 15px; margin: 20px 0; border: 2px solid #d32f2f;">
                                <h4 style="color: #d32f2f; margin-bottom: 20px;">✅ Your Avatar Created Successfully!</h4>
                                
                                <div style="max-width: 600px; margin: 0 auto;">
                                    <input type="text" 
                                           id="avatar-url-input-love1" 
                                           placeholder="Paste your ReadyPlayerMe URL here"
                                           style="width: 100%; padding: 15px; border: 2px solid #d32f2f; border-radius: 8px; font-size: 16px; margin-bottom: 15px;">
                                    
                                    <button id="save-avatar-url-love1" 
                                            style="padding: 12px 30px; background: #d32f2f; color: white; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; font-weight: bold;">
                                        Save Your Avatar URL
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Partner Avatar -->
                    <div id="love-avatar-2" style="margin-top: 40px;">
                        <h3 style="text-align: center; color: #d32f2f; margin-bottom: 20px; background: #fff5f5; padding: 15px; border-radius: 10px;">Partner's Avatar</h3>
                        <div id="readyplayerme-section-love2">
                            <div id="rpm-creation-love2">
                                <iframe
                                    id="readyplayerme-iframe-love2"
                                    src="https://lovebookstory.readyplayer.me/avatar?frameApi&clearAvatarConfig=1&bodyType=fullbody&gender=female&selectBodyType=false&selectGender=false&quickStart=photo&uiTheme=light&step=photo"
                                    width="100%"
                                    height="500px"
                                    allow="camera *; microphone *"
                                    allowfullscreen>
                                </iframe>
                                
                                <div style="margin: 20px 0; padding: 20px; background: #fff5f5; border-radius: 10px; border: 2px solid #d32f2f;">
                                    <h4 style="color: #d32f2f; margin-bottom: 15px; text-align: center;">📸 Quick Start Guide</h4>
                                    <div style="display: flex; align-items: center; justify-content: center; gap: 30px; margin-bottom: 15px; flex-wrap: wrap;">
                                        <div style="text-align: center;">
                                            <div style="width: 60px; height: 60px; background: #d32f2f; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-size: 24px;">1</div>
                                            <div style="font-size: 36px;">📤</div>
                                            <p style="color: #666; font-size: 0.9em; margin: 5px 0 0 0;">Click <strong>UPLOAD PHOTO</strong><br>in the widget</p>
                                        </div>
                                        <div style="font-size: 24px; color: #d32f2f;">→</div>
                                        <div style="text-align: center;">
                                            <div style="width: 60px; height: 60px; background: #d32f2f; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-size: 24px;">2</div>
                                            <div style="font-size: 36px;">✨</div>
                                            <p style="color: #666; font-size: 0.9em; margin: 5px 0 0 0;">Customize your<br>partner's features</p>
                                        </div>
                                        <div style="font-size: 24px; color: #d32f2f;">→</div>
                                        <div style="text-align: center;">
                                            <div style="width: 60px; height: 60px; background: #d32f2f; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-size: 24px;">3</div>
                                            <div style="font-size: 36px;">💾</div>
                                            <p style="color: #666; font-size: 0.9em; margin: 5px 0 0 0;">Click <strong>NEXT → DONE</strong><br>then copy URL</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="rpm-url-section-love2" style="display: none; text-align: center; padding: 30px; background: #fff5f5; border-radius: 15px; margin: 20px 0; border: 2px solid #d32f2f;">
                                <h4 style="color: #d32f2f; margin-bottom: 20px;">✅ Partner's Avatar Created Successfully!</h4>
                                
                                <div style="max-width: 600px; margin: 0 auto;">
                                    <input type="text" 
                                           id="avatar-url-input-love2" 
                                           placeholder="Paste your partner's ReadyPlayerMe URL here"
                                           style="width: 100%; padding: 15px; border: 2px solid #d32f2f; border-radius: 8px; font-size: 16px; margin-bottom: 15px;">
                                    
                                    <button id="save-avatar-url-love2" 
                                            style="padding: 12px 30px; background: #d32f2f; color: white; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; font-weight: bold;">
                                        Save Partner's Avatar URL
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="step3-placeholder" style="text-align: center; padding: 40px;">
                    <p>Please complete Step 2 first.</p>
                </div>
                
               <div class="wizard-buttons" style="margin-top: 40px;">
    <button class="wizard-prev">← Back to Details</button>
    <button class="wizard-next">Next: AI Scene Generation →</button>
</div>
            </div>
        </div>
       

<!-- Step 4: Build Your Love Story -->
<div class="wizard-step" data-step="4">
    <div id="step4-content-love" style="display: none;">
        <h2 style="text-align: center; margin-bottom: 20px; color: #333;">Step 4: Build Your Love Story</h2>
        <p style="text-align: center; color: #666; margin-bottom: 40px; font-size: 1.1em;">Choose your story chapters - each choice creates 3-4 pages in your 20-page book</p>
        
        <div style="max-width: 800px; margin: 0 auto;">
            
            <!-- Avatars Preview -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 40px;">
                <div style="text-align: center;">
                    <h4 style="color: #333; margin-bottom: 15px;">Your Avatar</h4>
                    <img id="love-avatar1-preview" src="" style="width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 4px solid #d32f2f;">
                </div>
                <div style="text-align: center;">
                    <h4 style="color: #333; margin-bottom: 15px;">Partner's Avatar</h4>
                    <img id="love-avatar2-preview" src="" style="width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 4px solid #d32f2f;">
                </div>
            </div>
            
            <!-- Chapter Selection Form -->
            <div id="love-story-form" style="background: #fff5f5; padding: 30px; border-radius: 15px; margin: 30px 0; border: 3px solid #d32f2f;">
                
                <!-- 1. How We Met -->
                <div class="chapter-section" style="margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px dashed #ffcdd2;">
                    <h3 style="color: #d32f2f; margin-bottom: 15px;">1. How We Met</h3>
                    <p style="color: #666; margin-bottom: 15px;">Choose where you first met (creates 3 pages)</p>
                    
                    <div class="choice-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px;">
                        <label class="choice-option" style="cursor: pointer;">
                            <input type="radio" name="how_we_met" value="school" style="display: none;">
                            <div class="choice-box" style="padding: 15px; text-align: center; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s;">
                                🏫 School
                            </div>
                        </label>
                        <label class="choice-option" style="cursor: pointer;">
                            <input type="radio" name="how_we_met" value="work" style="display: none;">
                            <div class="choice-box" style="padding: 15px; text-align: center; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s;">
                                💼 Work
                            </div>
                        </label>
                        <label class="choice-option" style="cursor: pointer;">
                            <input type="radio" name="how_we_met" value="friends" style="display: none;">
                            <div class="choice-box" style="padding: 15px; text-align: center; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s;">
                                👫 Friends
                            </div>
                        </label>
                        <label class="choice-option" style="cursor: pointer;">
                            <input type="radio" name="how_we_met" value="cafe" style="display: none;">
                            <div class="choice-box" style="padding: 15px; text-align: center; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s;">
                                ☕ Café
                            </div>
                        </label>
                        <label class="choice-option" style="cursor: pointer;">
                            <input type="radio" name="how_we_met" value="travel" style="display: none;">
                            <div class="choice-box" style="padding: 15px; text-align: center; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s;">
                                ✈️ Travel
                            </div>
                        </label>
                        <label class="choice-option" style="cursor: pointer;">
                            <input type="radio" name="how_we_met" value="online" style="display: none;">
                            <div class="choice-box" style="padding: 15px; text-align: center; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s;">
                                💻 Online
                            </div>
                        </label>
                    </div>
                    
                    <div style="margin-top: 15px;">
                        <input type="text" id="how_we_met_other" placeholder="Other (specify)" style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 8px; display: none;">
                    </div>
                </div>
                
                <!-- 2. First Date -->
                <div class="chapter-section" style="margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px dashed #ffcdd2;">
                    <h3 style="color: #d32f2f; margin-bottom: 15px;">2. First Date</h3>
                    <p style="color: #666; margin-bottom: 15px;">Choose your first date location (creates 3 pages)</p>
                    
                    <div class="choice-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px;">
                        <label class="choice-option" style="cursor: pointer;">
                            <input type="radio" name="first_date" value="restaurant" style="display: none;">
                            <div class="choice-box" style="padding: 15px; text-align: center; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s;">
                                🍽️ Restaurant
                            </div>
                        </label>
                        <label class="choice-option" style="cursor: pointer;">
                            <input type="radio" name="first_date" value="movies" style="display: none;">
                            <div class="choice-box" style="padding: 15px; text-align: center; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s;">
                                🎬 Movies
                            </div>
                        </label>
                        <label class="choice-option" style="cursor: pointer;">
                            <input type="radio" name="first_date" value="park" style="display: none;">
                            <div class="choice-box" style="padding: 15px; text-align: center; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s;">
                                🌳 Park
                            </div>
                        </label>
                        <label class="choice-option" style="cursor: pointer;">
                            <input type="radio" name="first_date" value="beach" style="display: none;">
                            <div class="choice-box" style="padding: 15px; text-align: center; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s;">
                                🏖️ Beach
                            </div>
                        </label>
                        <label class="choice-option" style="cursor: pointer;">
                            <input type="radio" name="first_date" value="adventure" style="display: none;">
                            <div class="choice-box" style="padding: 15px; text-align: center; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s;">
                                🧗 Adventure
                            </div>
                        </label>
                    </div>
                    
                    <div style="margin-top: 15px;">
                        <input type="text" id="first_date_other" placeholder="Other (specify)" style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 8px; display: none;">
                    </div>
                </div>
                
                <!-- 3. Favorite Memories (Pick 2) -->
                <div class="chapter-section" style="margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px dashed #ffcdd2;">
                    <h3 style="color: #d32f2f; margin-bottom: 15px;">3. Favorite Memories</h3>
                    <p style="color: #666; margin-bottom: 15px;">Pick 2 favorite memories (each creates 2 pages)</p>
                    
                    <div class="choice-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px;">
                        <label class="choice-option" style="cursor: pointer;">
                            <input type="checkbox" name="favorite_memory[]" value="trip" style="display: none;">
                            <div class="choice-box" style="padding: 15px; text-align: center; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s;">
                                ✈️ Trip
                            </div>
                        </label>
                        <label class="choice-option" style="cursor: pointer;">
                            <input type="checkbox" name="favorite_memory[]" value="celebration" style="display: none;">
                            <div class="choice-box" style="padding: 15px; text-align: center; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s;">
                                🎉 Celebration
                            </div>
                        </label>
                        <label class="choice-option" style="cursor: pointer;">
                            <input type="checkbox" name="favorite_memory[]" value="quiet_night" style="display: none;">
                            <div class="choice-box" style="padding: 15px; text-align: center; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s;">
                                🌙 Quiet Night
                            </div>
                        </label>
                        <label class="choice-option" style="cursor: pointer;">
                            <input type="checkbox" name="favorite_memory[]" value="funny_moment" style="display: none;">
                            <div class="choice-box" style="padding: 15px; text-align: center; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s;">
                                😂 Funny Moment
                            </div>
                        </label>
                        <label class="choice-option" style="cursor: pointer;">
                            <input type="checkbox" name="favorite_memory[]" value="cooking" style="display: none;">
                            <div class="choice-box" style="padding: 15px; text-align: center; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s;">
                                🍳 Cooking
                            </div>
                        </label>
                    </div>
                    
                    <div style="margin-top: 15px;">
                        <input type="text" id="favorite_memory_other" placeholder="Other (specify)" style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 8px; display: none;">
                        <p id="memory-counter" style="color: #666; font-size: 14px; margin-top: 10px;">Selected: 0/2</p>
                    </div>
                </div>
                
                <!-- 4. What I Love About You (Pick 3) -->
                <div class="chapter-section" style="margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px dashed #ffcdd2;">
                    <h3 style="color: #d32f2f; margin-bottom: 15px;">4. What I Love About You</h3>
                    <p style="color: #666; margin-bottom: 15px;">Pick 3 things you love (each creates 1 page)</p>
                    
                    <div class="choice-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px;">
                        <label class="choice-option" style="cursor: pointer;">
                            <input type="checkbox" name="love_about[]" value="smile" style="display: none;">
                            <div class="choice-box" style="padding: 15px; text-align: center; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s;">
                                😊 Your Smile
                            </div>
                        </label>
                        <label class="choice-option" style="cursor: pointer;">
                            <input type="checkbox" name="love_about[]" value="kindness" style="display: none;">
                            <div class="choice-box" style="padding: 15px; text-align: center; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s;">
                                🤗 Kindness
                            </div>
                        </label>
                        <label class="choice-option" style="cursor: pointer;">
                            <input type="checkbox" name="love_about[]" value="humor" style="display: none;">
                            <div class="choice-box" style="padding: 15px; text-align: center; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s;">
                                😂 Humor
                            </div>
                        </label>
                        <label class="choice-option" style="cursor: pointer;">
                            <input type="checkbox" name="love_about[]" value="support" style="display: none;">
                            <div class="choice-box" style="padding: 15px; text-align: center; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s;">
                                🤝 Support
                            </div>
                        </label>
                        <label class="choice-option" style="cursor: pointer;">
                            <input type="checkbox" name="love_about[]" value="passion" style="display: none;">
                            <div class="choice-box" style="padding: 15px; text-align: center; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s;">
                                ❤️ Passion
                            </div>
                        </label>
                    </div>
                    
                    <div style="margin-top: 15px;">
                        <input type="text" id="love_about_other" placeholder="Other (specify)" style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 8px; display: none;">
                        <p id="love-counter" style="color: #666; font-size: 14px; margin-top: 10px;">Selected: 0/3</p>
                    </div>
                </div>
                
                <!-- 5. Our Future Dreams (Pick 1) -->
                <div class="chapter-section" style="margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px dashed #ffcdd2;">
                    <h3 style="color: #d32f2f; margin-bottom: 15px;">5. Our Future Dreams</h3>
                    <p style="color: #666; margin-bottom: 15px;">Pick 1 future dream (creates 3 pages)</p>
                    
                    <div class="choice-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px;">
                        <label class="choice-option" style="cursor: pointer;">
                            <input type="radio" name="future_dreams" value="travel_world" style="display: none;">
                            <div class="choice-box" style="padding: 15px; text-align: center; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s;">
                                🌍 Travel World
                            </div>
                        </label>
                        <label class="choice-option" style="cursor: pointer;">
                            <input type="radio" name="future_dreams" value="build_home" style="display: none;">
                            <div class="choice-box" style="padding: 15px; text-align: center; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s;">
                                🏠 Build Home
                            </div>
                        </label>
                        <label class="choice-option" style="cursor: pointer;">
                            <input type="radio" name="future_dreams" value="start_family" style="display: none;">
                            <div class="choice-box" style="padding: 15px; text-align: center; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s;">
                                👨‍👩‍👧‍👦 Start Family
                            </div>
                        </label>
                        <label class="choice-option" style="cursor: pointer;">
                            <input type="radio" name="future_dreams" value="grow_old" style="display: none;">
                            <div class="choice-box" style="padding: 15px; text-align: center; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s;">
                                👵👴 Grow Old
                            </div>
                        </label>
                    </div>
                    
                    <div style="margin-top: 15px;">
                        <input type="text" id="future_dreams_other" placeholder="Other (specify)" style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 8px; display: none;">
                    </div>
                </div>
                
                <!-- 6. Book Style (Pick 1) -->
                <div class="chapter-section" style="margin-bottom: 30px;">
                    <h3 style="color: #d32f2f; margin-bottom: 15px;">6. Book Style</h3>
                    <p style="color: #666; margin-bottom: 15px;">Choose your book's artistic style</p>
                    
                    <div class="choice-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px;">
                        <label class="choice-option" style="cursor: pointer;">
                            <input type="radio" name="book_style" value="classic_romance" style="display: none;">
                            <div class="choice-box" style="padding: 15px; text-align: center; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s;">
                                💖 Classic Romance
                            </div>
                        </label>
                        <label class="choice-option" style="cursor: pointer;">
                            <input type="radio" name="book_style" value="modern" style="display: none;">
                            <div class="choice-box" style="padding: 15px; text-align: center; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s;">
                                🏙️ Modern
                            </div>
                        </label>
                        <label class="choice-option" style="cursor: pointer;">
                            <input type="radio" name="book_style" value="adventure" style="display: none;">
                            <div class="choice-box" style="padding: 15px; text-align: center; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s;">
                                🗺️ Adventure
                            </div>
                        </label>
                        <label class="choice-option" style="cursor: pointer;">
                            <input type="radio" name="book_style" value="dreamy" style="display: none;">
                            <div class="choice-box" style="padding: 15px; text-align: center; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s;">
                                ✨ Dreamy
                            </div>
                        </label>
                    </div>
                    
                    <div style="margin-top: 15px;">
                        <input type="text" id="book_style_other" placeholder="Other (specify)" style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 8px; display: none;">
                    </div>
                </div>
                
                <!-- Story Summary -->
                <div id="story-summary" style="background: #e8f5e9; padding: 20px; border-radius: 10px; margin-top: 30px; display: none;">
                    <h4 style="color: #2e7d32; margin-bottom: 15px;">📖 Your Story Summary:</h4>
                    <div id="summary-content" style="color: #555;"></div>
                    <p style="color: #666; margin-top: 15px; font-size: 14px;"><strong>Total Pages:</strong> <span id="page-count">20</span> pages</p>
                </div>
                
            </div>
            
            <!-- Generate Button -->
<div style="text-align: center; margin: 40px 0;">
    <button id="generate-love-story" class="ai-generate-btn" style="padding: 18px 50px; font-size: 18px;">
        <span style="margin-right: 10px;">✨</span> Generate Preview 
    </button>
    <p style="color: #666; margin-top: 15px; font-size: 14px;">
        AI will create 1 preview page to show you the quality. Full story generated after payment.
    </p>
    <p style="color: #666; margin-top: 15px; font-size: 14px;">
        AI will create 20 unique pages based on your choices.
    </p>
</div>

<!-- Loading State -->
<div id="story-loading" style="display: none; text-align: center; padding: 60px;">
    <div class="ai-loading-spinner" style="margin: 0 auto 30px; width: 100px; height: 100px; border-width: 8px;"></div>
    <p style="font-size: 22px; color: #673ab7; margin-bottom: 15px;">
        AI is creating your 20-page love story...
    </p>
    <p style="color: #666; font-size: 16px; margin-bottom: 10px;">
        This may take 2-3 minutes
    </p>
    <div style="max-width: 500px; margin: 30px auto; background: #f5f5f5; border-radius: 10px; overflow: hidden;">
        <div id="story-progress-bar" style="height: 10px; background: linear-gradient(90deg, #673ab7, #9c27b0); width: 0%; transition: width 0.5s;"></div>
    </div>
    <p id="story-progress-text" style="color: #666; font-size: 14px; margin-top: 15px;">Generating pages: 0/20</p>
</div>

<!-- Results Preview -->
<div id="story-results" style="display: none;">
    <div style="text-align: center; margin: 40px 0;">
        <h3 style="color: #d32f2f; margin-bottom: 20px;">🎉 Your Love Story Book is Ready!</h3>
        <p style="color: #666; margin-bottom: 30px; font-size: 1.1em;">
            Preview of your 20-page personalized storybook
        </p>
    </div>

    <div id="book-preview-container" style="background: #f8f9fa; padding: 30px; border-radius: 15px; margin: 30px 0;"></div>

    <div style="text-align: center; margin-top: 40px;">
        <button id="view-full-story" style="padding: 15px 40px; background: linear-gradient(45deg, #d32f2f, #ff5252); color: white; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; font-weight: bold; margin: 0 10px;">
            📖 View Full Story (20 Pages)
        </button>
        <button id="download-story-pdf" style="padding: 15px 40px; background: linear-gradient(45deg, #4CAF50, #45a049); color: white; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; font-weight: bold; margin: 0 10px;">
            💾 Download PDF Preview
        </button>
    </div>
</div>

<!-- Purchase Options -->
<div id="purchase-options" style="display: none; text-align: center; margin: 40px 0;">
    <h3 style="color: #d32f2f; margin-bottom: 20px;">🎉 Ready to Complete Your Story?</h3>
    <p style="color: #666; margin-bottom: 30px; font-size: 1.1em;">
        Choose how you want to receive your complete storybook
    </p>

    <div style="display: flex; justify-content: center; gap: 30px; margin-top: 30px; flex-wrap: wrap;">
        <button class="purchase-option" data-product-id="1452" data-price="49" data-option="digital" style="padding: 30px; border: 3px solid #ddd; border-radius: 15px; cursor: pointer; background: white; width: 280px; transition: all 0.3s;">
            <div style="font-size: 48px; margin-bottom: 15px;">📱</div>
            <h4 style="color: #333; margin-bottom: 10px;">Digital Book Only</h4>
            <p style="font-size: 24px; font-weight: bold; color: #d32f2f; margin-bottom: 10px;">49 DH</p>
            <p style="color: #666; font-size: 14px; margin: 0;">PDF download only</p>
        </button>

        <button class="purchase-option" data-product-id="64" data-price="289" data-option="physical" style="padding: 30px; border: 3px solid #ddd; border-radius: 15px; cursor: pointer; background: white; width: 280px; transition: all 0.3s;">
            <div style="font-size: 48px; margin-bottom: 15px;">📦</div>
            <h4 style="color: #333; margin-bottom: 10px;">Digital + Physical</h4>
            <p style="font-size: 24px; font-weight: bold; color: #d32f2f; margin-bottom: 10px;">289 DH</p>
            <p style="color: #666; font-size: 14px; margin: 0;">PDF + Printed book shipped to you</p>
        </button>
    </div>
</div>

<!-- Wizard Buttons -->
<div class="wizard-buttons" style="margin-top: 40px;">
    <button class="wizard-prev">← Back to Avatars</button>
    <button class="add-to-cart-btn">Add to Cart</button>
</div>

    
    <!-- Step 4: Build Your Child's Story -->
<div class="wizard-step" data-step="4">
    <div id="step4-content-kids" style="display: none;">
        <h2 style="text-align: center; margin-bottom: 20px; color: #333;">Step 4: Build Your Child's Story</h2>
        <p style="text-align: center; color: #666; margin-bottom: 40px; font-size: 1.1em;">Choose a story template - each creates a 10-page adventure book!</p>
        
        <div style="max-width: 1000px; margin: 0 auto;">
            
            <!-- Avatar Preview -->
            <div style="text-align: center; margin-bottom: 40px; padding: 20px; background: #e8f4fd; border-radius: 15px;">
                <h4 style="color: #1976d2; margin-bottom: 15px;">Your Child's Avatar</h4>
                <img id="kid-avatar-preview" src="" style="width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 4px solid #1976d2;">
            </div>
            
            <!-- Template Selection Form -->
            <div id="kids-story-form" style="background: white; padding: 30px; border-radius: 15px; margin: 30px 0; border: 3px solid #1976d2;">
                
                <h3 style="color: #1976d2; margin-bottom: 25px; text-align: center;">📚 Choose Your Story Template</h3>
                <p style="color: #666; margin-bottom: 30px; text-align: center; font-size: 1.1em;">
                    Select one story template below. Each creates a <strong>10-page personalized storybook</strong> featuring your child!
                </p>
                
                <!-- Template Grid (2 columns on desktop) -->
                <div class="template-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin: 30px 0;">
                    
                    <!-- Template 1: The Little Helper -->
                    <label class="template-choice-kids" style="cursor: pointer;">
                        <input type="radio" name="kids_template" value="little_helper" style="display: none;">
                        <div class="template-box" style="padding: 20px; border: 2px solid #ddd; border-radius: 12px; transition: all 0.3s; height: 100%;">
                            <div style="font-size: 40px; text-align: center; margin-bottom: 15px;">🤝</div>
                            <h4 style="color: #333; margin-bottom: 10px; text-align: center;">The Little Helper</h4>
                            <p style="color: #666; font-size: 14px; margin-bottom: 15px; text-align: center;">
                                A heartwarming story about kindness and helping others in the community.
                            </p>
                            <div style="background: #e8f5e9; padding: 10px; border-radius: 8px; margin-top: 10px;">
                                <p style="color: #2e7d32; font-size: 12px; margin: 0; text-align: center;">
                                    <strong>10 Pages:</strong> Helping neighbors, solving problems, community star
                                </p>
                            </div>
                        </div>
                    </label>
                    
                    <!-- Template 2: The Space Explorer -->
                    <label class="template-choice-kids" style="cursor: pointer;">
                        <input type="radio" name="kids_template" value="space_explorer" style="display: none;">
                        <div class="template-box" style="padding: 20px; border: 2px solid #ddd; border-radius: 12px; transition: all 0.3s; height: 100%;">
                            <div style="font-size: 40px; text-align: center; margin-bottom: 15px;">🚀</div>
                            <h4 style="color: #333; margin-bottom: 10px; text-align: center;">The Space Explorer</h4>
                            <p style="color: #666; font-size: 14px; margin-bottom: 15px; text-align: center;">
                                An exciting adventure to the moon and beyond in a dream space mission.
                            </p>
                            <div style="background: #e3f2fd; padding: 10px; border-radius: 8px; margin-top: 10px;">
                                <p style="color: #1565c0; font-size: 12px; margin: 0; text-align: center;">
                                    <strong>10 Pages:</strong> Moon landing, space discoveries, magical rocks
                                </p>
                            </div>
                        </div>
                    </label>
                    
                    <!-- Template 3: The Animal Rescuer -->
                    <label class="template-choice-kids" style="cursor: pointer;">
                        <input type="radio" name="kids_template" value="animal_rescuer" style="display: none;">
                        <div class="template-box" style="padding: 20px; border: 2px solid #ddd; border-radius: 12px; transition: all 0.3s; height: 100%;">
                            <div style="font-size: 40px; text-align: center; margin-bottom: 15px;">🐾</div>
                            <h4 style="color: #333; margin-bottom: 10px; text-align: center;">The Animal Rescuer</h4>
                            <p style="color: #666; font-size: 14px; margin-bottom: 15px; text-align: center;">
                                A caring story about finding and helping lost animals in the neighborhood.
                            </p>
                            <div style="background: #fce4ec; padding: 10px; border-radius: 8px; margin-top: 10px;">
                                <p style="color: #ad1457; font-size: 12px; margin: 0; text-align: center;">
                                    <strong>10 Pages:</strong> Lost puppy, caregiving, happy reunions
                                </p>
                            </div>
                        </div>
                    </label>
                    
                    <!-- Template 4: The Future Chef -->
                    <label class="template-choice-kids" style="cursor: pointer;">
                        <input type="radio" name="kids_template" value="future_chef" style="display: none;">
                        <div class="template-box" style="padding: 20px; border: 2px solid #ddd; border-radius: 12px; transition: all 0.3s; height: 100%;">
                            <div style="font-size: 40px; text-align: center; margin-bottom: 15px;">👨‍🍳</div>
                            <h4 style="color: #333; margin-bottom: 10px; text-align: center;">The Future Chef</h4>
                            <p style="color: #666; font-size: 14px; margin-bottom: 15px; text-align: center;">
                                A delicious adventure in the kitchen making a magical rainbow cake.
                            </p>
                            <div style="background: #fff3e0; padding: 10px; border-radius: 8px; margin-top: 10px;">
                                <p style="color: #ef6c00; font-size: 12px; margin: 0; text-align: center;">
                                    <strong>10 Pages:</strong> Baking, colorful layers, birthday surprise
                                </p>
                            </div>
                        </div>
                    </label>
                    
                    <!-- Template 5: The Garden Magician -->
                    <label class="template-choice-kids" style="cursor: pointer;">
                        <input type="radio" name="kids_template" value="garden_magician" style="display: none;">
                        <div class="template-box" style="padding: 20px; border: 2px solid #ddd; border-radius: 12px; transition: all 0.3s; height: 100%;">
                            <div style="font-size: 40px; text-align: center; margin-bottom: 15px;">🌻</div>
                            <h4 style="color: #333; margin-bottom: 10px; text-align: center;">The Garden Magician</h4>
                            <p style="color: #666; font-size: 14px; margin-bottom: 15px; text-align: center;">
                                A magical journey growing a giant sunflower from a tiny seed.
                            </p>
                            <div style="background: #e8f5e9; padding: 10px; border-radius: 8px; margin-top: 10px;">
                                <p style="color: #2e7d32; font-size: 12px; margin: 0; text-align: center;">
                                    <strong>10 Pages:</strong> Planting, growing, blooming, sharing seeds
                                </p>
                            </div>
                        </div>
                    </label>
                    
                    <!-- Template 6: The Curiosity Detective -->
                    <label class="template-choice-kids" style="cursor: pointer;">
                        <input type="radio" name="kids_template" value="curiosity_detective" style="display: none;">
                        <div class="template-box" style="padding: 20px; border: 2px solid #ddd; border-radius: 12px; transition: all 0.3s; height: 100%;">
                            <div style="font-size: 40px; text-align: center; margin-bottom: 15px;">🔍</div>
                            <h4 style="color: #333; margin-bottom: 10px; text-align: center;">The Curiosity Detective</h4>
                            <p style="color: #666; font-size: 14px; margin-bottom: 15px; text-align: center;">
                                A mystery-solving adventure finding a missing sock with detective skills.
                            </p>
                            <div style="background: #f3e5f5; padding: 10px; border-radius: 8px; margin-top: 10px;">
                                <p style="color: #7b1fa2; font-size: 12px; margin: 0; text-align: center;">
                                    <strong>10 Pages:</strong> Investigation, clues, paw prints, mystery solved
                                </p>
                            </div>
                        </div>
                    </label>
                    
                    <!-- Template 7: The Junior Doctor -->
                    <label class="template-choice-kids" style="cursor: pointer;">
                        <input type="radio" name="kids_template" value="junior_doctor" style="display: none;">
                        <div class="template-box" style="padding: 20px; border: 2px solid #ddd; border-radius: 12px; transition: all 0.3s; height: 100%;">
                            <div style="font-size: 40px; text-align: center; margin-bottom: 15px;">👨‍⚕️</div>
                            <h4 style="color: #333; margin-bottom: 10px; text-align: center;">The Junior Doctor</h4>
                            <p style="color: #666; font-size: 14px; margin-bottom: 15px; text-align: center;">
                                A caring story about helping others feel better with medical check-ups.
                            </p>
                            <div style="background: #e8f4fd; padding: 10px; border-radius: 8px; margin-top: 10px;">
                                <p style="color: #1565c0; font-size: 12px; margin: 0; text-align: center;">
                                    <strong>10 Pages:</strong> Teddy check-up, real doctor visit, helping family
                                </p>
                            </div>
                        </div>
                    </label>
                    
                    <!-- Template 8: The Brave Adventurer -->
                    <label class="template-choice-kids" style="cursor: pointer;">
                        <input type="radio" name="kids_template" value="brave_adventurer" style="display: none;">
                        <div class="template-box" style="padding: 20px; border: 2px solid #ddd; border-radius: 12px; transition: all 0.3s; height: 100%;">
                            <div style="font-size: 40px; text-align: center; margin-bottom: 15px;">🧗</div>
                            <h4 style="color: #333; margin-bottom: 10px; text-align: center;">The Brave Adventurer</h4>
                            <p style="color: #666; font-size: 14px; margin-bottom: 15px; text-align: center;">
                                An encouraging story about overcoming fears on the playground slide.
                            </p>
                            <div style="background: #fff3e0; padding: 10px; border-radius: 8px; margin-top: 10px;">
                                <p style="color: #ef6c00; font-size: 12px; margin: 0; text-align: center;">
                                    <strong>10 Pages:</strong> Facing fears, encouragement, triumphant success
                                </p>
                            </div>
                        </div>
                    </label>
                    
                    <!-- Template 9: The Junior Engineer -->
                    <label class="template-choice-kids" style="cursor: pointer;">
                        <input type="radio" name="kids_template" value="junior_engineer" style="display: none;">
                        <div class="template-box" style="padding: 20px; border: 2px solid #ddd; border-radius: 12px; transition: all 0.3s; height: 100%;">
                            <div style="font-size: 40px; text-align: center; margin-bottom: 15px;">⚙️</div>
                            <h4 style="color: #333; margin-bottom: 10px; text-align: center;">The Junior Engineer</h4>
                            <p style="color: #666; font-size: 14px; margin-bottom: 15px; text-align: center;">
                                A creative building story about constructing a bridge to solve a problem.
                            </p>
                            <div style="background: #e8eaf6; padding: 10px; border-radius: 8px; margin-top: 10px;">
                                <p style="color: #3949ab; font-size: 12px; margin: 0; text-align: center;">
                                    <strong>10 Pages:</strong> Planning, building, testing, project success
                                </p>
                            </div>
                        </div>
                    </label>
                    
                    <!-- Template 10: The Ocean Protector -->
                    <label class="template-choice-kids" style="cursor: pointer;">
                        <input type="radio" name="kids_template" value="ocean_protector" style="display: none;">
                        <div class="template-box" style="padding: 20px; border: 2px solid #ddd; border-radius: 12px; transition: all 0.3s; height: 100%;">
                            <div style="font-size: 40px; text-align: center; margin-bottom: 15px;">🌊</div>
                            <h4 style="color: #333; margin-bottom: 10px; text-align: center;">The Ocean Protector</h4>
                            <p style="color: #666; font-size: 14px; margin-bottom: 15px; text-align: center;">
                                An eco-friendly story about cleaning the beach and protecting nature.
                            </p>
                            <div style="background: #e0f2f1; padding: 10px; border-radius: 8px; margin-top: 10px;">
                                <p style="color: #00695c; font-size: 12px; margin: 0; text-align: center;">
                                    <strong>10 Pages:</strong> Beach cleanup, community help, environmental care
                                </p>
                            </div>
                        </div>
                    </label>
                    
                </div>
                
                <!-- Book Style Selection -->
                <div style="margin-top: 40px; padding: 25px; background: #f0f7ff; border-radius: 12px;">
                    <h4 style="color: #1976d2; margin-bottom: 15px; text-align: center;">🎨 Choose Book Style</h4>
                    <p style="color: #666; margin-bottom: 20px; text-align: center;">Select the artistic style for your storybook</p>
                    
                    <div style="display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
                        <label class="style-choice-kids" style="cursor: pointer;">
                            <input type="radio" name="kids_book_style" value="storybook" style="display: none;">
                            <div class="style-box" style="padding: 15px 25px; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s; text-align: center;">
                                <div style="font-size: 24px;">📖</div>
                                <div>Classic Storybook</div>
                            </div>
                        </label>
                        
                        <label class="style-choice-kids" style="cursor: pointer;">
                            <input type="radio" name="kids_book_style" value="cartoon" style="display: none;">
                            <div class="style-box" style="padding: 15px 25px; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s; text-align: center;">
                                <div style="font-size: 24px;">🖼️</div>
                                <div>Cartoon Fun</div>
                            </div>
                        </label>
                        
                        <label class="style-choice-kids" style="cursor: pointer;">
                            <input type="radio" name="kids_book_style" value="watercolor" style="display: none;">
                            <div class="style-box" style="padding: 15px 25px; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s; text-align: center;">
                                <div style="font-size: 24px;">🎨</div>
                                <div>Watercolor</div>
                            </div>
                        </label>
                        
                        <label class="style-choice-kids" style="cursor: pointer;">
                            <input type="radio" name="kids_book_style" value="digital" style="display: none;">
                            <div class="style-box" style="padding: 15px 25px; border: 2px solid #ddd; border-radius: 8px; transition: all 0.3s; text-align: center;">
                                <div style="font-size: 24px;">💻</div>
                                <div>Digital Art</div>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- Story Summary -->
                <div id="kids-story-summary" style="background: #e8f5e9; padding: 20px; border-radius: 10px; margin-top: 30px; display: none;">
                    <h4 style="color: #2e7d32; margin-bottom: 15px;">📖 Your Story Summary:</h4>
                    <div id="kids-summary-content" style="color: #555;"></div>
                    <p style="color: #666; margin-top: 15px; font-size: 14px;"><strong>Total Pages:</strong> <span id="kids-page-count">10</span> pages</p>
                </div>
                
            </div>
            
            <!-- Generate PREVIEW Button -->
<div style="text-align: center; margin: 40px 0;">
    <button id="generate-kids-story" class="ai-generate-btn" style="padding: 18px 50px; font-size: 18px; background: linear-gradient(45deg, #1976d2, #2196f3);">
        <span style="margin-right: 10px;">✨</span> Generate Preview 
    </button>
    <p style="color: #666; margin-top: 15px; font-size: 14px;">AI will create 1 preview page to show you the quality. Complete story generated after payment.</p>
</div>
            <!-- Progress Indicator (add this before the loading state) -->
<div id="progress-indicator" style="display: none; text-align: center; padding: 20px; background: #f8f9fa; border-radius: 10px; margin: 20px 0;">
    <div style="margin-bottom: 15px;">
        <div class="ai-loading-spinner" style="margin: 0 auto 15px; width: 60px; height: 60px; border-width: 6px; border-top-color: #1976d2;"></div>
        <p id="progress-message" style="font-size: 18px; color: #1976d2; margin-bottom: 10px;">Starting story generation...</p>
        <p id="progress-percent" style="font-size: 24px; font-weight: bold; color: #1976d2;">0%</p>
    </div>
    <div style="max-width: 500px; margin: 0 auto; background: #e0e0e0; border-radius: 10px; overflow: hidden; height: 10px;">
        <div id="progress-bar" style="height: 100%; background: linear-gradient(90deg, #1976d2, #2196f3); width: 0%; transition: width 0.5s;"></div>
    </div>
</div>
            <!-- Loading State -->
            <div id="kids-story-loading" style="display: none; text-align: center; padding: 60px;">
                <div class="ai-loading-spinner" style="margin: 0 auto 30px; width: 100px; height: 100px; border-width: 8px; border-top-color: #1976d2;"></div>
                <p style="font-size: 22px; color: #1976d2; margin-bottom: 15px;">AI is creating your 10-page story...</p>
                <p style="color: #666; font-size: 16px; margin-bottom: 10px;">This may take 2-3 minutes</p>
                <div style="max-width: 500px; margin: 30px auto; background: #f5f5f5; border-radius: 10px; overflow: hidden;">
                    <div id="kids-story-progress-bar" style="height: 10px; background: linear-gradient(90deg, #1976d2, #2196f3); width: 0%; transition: width 0.5s;"></div>
                </div>
                <p id="kids-story-progress-text" style="color: #666; font-size: 14px; margin-top: 15px;">Generating pages: 0/10</p>
            </div>
            
                      <!-- Results Preview -->
            <div id="kids-story-results" style="display: none;">
                <div style="text-align: center; margin: 40px 0;">
                    <h3 style="color: #1976d2; margin-bottom: 20px;">🎉 Your Child's Story Book is Ready!</h3>
                    <p style="color: #666; margin-bottom: 30px; font-size: 1.1em;">Preview of 1 page of your 10-pages personalized storybook</p>
                </div>
                
                <!-- Book Preview -->
                <div id="kids-book-preview-container" style="background: #f8f9fa; padding: 30px; border-radius: 15px; margin: 30px 0;">
                    <!-- Preview will be loaded here by JavaScript -->
                </div>
                
                <!-- Action Buttons -->
                <div style="text-align: center; margin-top: 40px;">
                    <button id="view-full-kids-story" style="padding: 15px 40px; background: linear-gradient(45deg, #1976d2, #2196f3); color: white; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; font-weight: bold; margin: 0 10px;">
                        📖 View Full Story (10 Pages)
                    </button>
                    <button id="download-kids-story-pdf" style="padding: 15px 40px; background: linear-gradient(45deg, #4CAF50, #45a049); color: white; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; font-weight: bold; margin: 0 10px;">
                        💾 Download PDF Preview
                    </button>
                </div>
            </div>
            
            <!-- ========== ADD THIS SECTION HERE ========== -->
            <!-- Purchase Options for Kids Story -->
            <div id="kids-purchase-options" style="display: none; text-align: center; margin: 40px 0;">
                <h3 style="color: #1976d2; margin-bottom: 20px;">🎉 Ready to Complete Your Child's Story?</h3>
                <p style="color: #666; margin-bottom: 30px; font-size: 1.1em;">Choose how you want to receive the complete storybook</p>
                
                <div style="display: flex; justify-content: center; gap: 30px; margin-top: 30px; flex-wrap: wrap;">
                    <!-- Digital Only Option -->
                    <button class="purchase-option kids-purchase" 
                            data-product-id="1452" 
                            data-price="49" 
                            data-option="digital"
                            style="padding: 30px; border: 3px solid #ddd; border-radius: 15px; cursor: pointer; background: white; width: 280px; transition: all 0.3s;">
                        <div style="font-size: 48px; margin-bottom: 15px;">📱</div>
                        <h4 style="color: #333; margin-bottom: 10px;">Digital Book Only</h4>
                        <p style="font-size: 24px; font-weight: bold; color: #1976d2; margin-bottom: 10px;">49 DH</p>
                        <p style="color: #666; font-size: 14px; margin: 0;">PDF download only</p>
                    </button>
                    
                    <!-- Physical + Digital Option -->
                    <button class="purchase-option kids-purchase" 
                            data-product-id="64" 
                            data-price="289" 
                            data-option="physical"
                            style="padding: 30px; border: 3px solid #ddd; border-radius: 15px; cursor: pointer; background: white; width: 280px; transition: all 0.3s;">
                        <div style="font-size: 48px; margin-bottom: 15px;">📦</div>
                        <h4 style="color: #333; margin-bottom: 10px;">Digital + Physical</h4>
                        <p style="font-size: 24px; font-weight: bold; color: #1976d2; margin-bottom: 10px;">289 DH</p>
                        <p style="color: #666; font-size: 14px; margin: 0;">PDF + Printed book shipped to you</p>
                    </button>
                </div>
            </div>
            <!-- ========== END OF ADDED SECTION ========== -->
            
            <!-- Wizard Buttons -->
            <div class="wizard-buttons" style="margin-top: 40px;">
                <button class="wizard-prev">← Back to Avatar</button>
                <button class="add-to-cart-btn">Add to Cart</button>
            </div>
    
    <!-- Love story content stays the same -->
    <div id="step4-content-love" style="display: none;">
        <!-- Existing love story content... -->
    </div>
    
    <div id="step4-placeholder" style="text-align: center; padding: 40px;">
        <p>Please complete Step 3 first.</p>
    </div>
</div>
    
    <div id="step4-placeholder" style="text-align: center; padding: 40px;">
        <p>Please complete Step 3 first.</p>
    </div>
</div>

        <!-- Hidden data storage -->
        <div id="book-data-storage" style="display: none;">
            <input type="hidden" id="story-type" value="">
            <input type="hidden" id="character-details" value="">
            <input type="hidden" id="avatar-kids-url" value="">
            <input type="hidden" id="avatar-love1-url" value="">
            <input type="hidden" id="avatar-love2-url" value="">
			<input type="hidden" id="generated-scene-url" value="">
    <input type="hidden" id="generated-scene-url-love" value="">
	<!-- Add to hidden data storage section -->
<input type="hidden" id="purchase-product-id" value="">
<input type="hidden" id="purchase-option" value="">
<input type="hidden" id="preview-image-url" value="">
        </div>
        <?php
        return ob_get_clean();
    }
}