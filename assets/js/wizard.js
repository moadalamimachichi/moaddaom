// wizard.js - UPDATED for Download Page + Wizard Detection
(function($) {
    'use strict';
    
    // Global variables
    window.originalLovePreviewState = null;
    window.originalKidsPreviewState = null;
    window.isInFullLovePreview = false;
    window.isInFullKidsPreview = false;
    
    // Check if we're on the download page (looking for story generation)
    function isOnDownloadPage() {
        const path = window.location.pathname;
        return path.includes('download-story') || 
               document.body.classList.contains('page-template-download-story-page') ||
               $('body:contains("story-generation-container")').length > 0;
    }
    
    // Check if we're on the wizard page
    function isOnWizardPage() {
        return $('#book-wizard').length > 0 || 
               $('body:has([data-step])').length > 0 ||
               document.body.classList.contains('book-wizard-page');
    }
    
    // Auto-save avatar function for custom subdomain
    function autoSaveAvatar(avatarUrl, type) {
        console.log('Auto-saving avatar for type:', type, 'URL:', avatarUrl);
        
        // Convert GLB to PNG if needed
        if (avatarUrl.includes('.glb')) {
            avatarUrl = avatarUrl.replace('.glb', '.png');
        }
        
        // Update appropriate hidden field and show success
        switch(type) {
            case 'kids':
                $('#avatar-kids-url').val(avatarUrl);
                $('#save-avatar-url-kids').html('✓ Auto-saved!').css('background', '#4CAF50').prop('disabled', true);
                $('#avatar-url-input-kids').prop('disabled', true);
                
                // Enable Next button in Step 3
                $('.wizard-step[data-step="3"] .wizard-next').prop('disabled', false);
                console.log('Kids avatar auto-saved');
                break;
                
            case 'love1':
                window.firstAvatarSaved = true;
                $('#avatar-love1-url').val(avatarUrl);
                $('#save-avatar-url-love1').html('✓ Auto-saved!').css('background', '#4CAF50').prop('disabled', true);
                $('#avatar-url-input-love1').prop('disabled', true);
                
                // Show the second avatar creation section
                $('#love2-avatar-section').show(500, function() {
                    // Scroll to the second avatar section
                    $('html, body').animate({
                        scrollTop: $('#love2-avatar-section').offset().top - 50
                    }, 500);
                });
                $('#rpm-creation-love2').show();
                console.log('Love avatar 1 auto-saved');
                break;
                
            case 'love2':
                $('#avatar-love2-url').val(avatarUrl);
                $('#save-avatar-url-love2').html('✓ Auto-saved!').css('background', '#4CAF50').prop('disabled', true);
                $('#avatar-url-input-love2').prop('disabled', true);
                
                // Enable Next button in Step 3
                $('.wizard-step[data-step="3"] .wizard-next').prop('disabled', false);
                console.log('Love avatar 2 auto-saved');
                break;
        }
        
        // Trigger change event on the hidden field
        $('#avatar-' + (type === 'love1' ? 'love1' : type === 'love2' ? 'love2' : type) + '-url').trigger('change');
    }
    
    // Global restore functions
    window.restoreLovePreview = function() {
        console.log('Restoring love preview...');
        const $container = $('#book-preview-container');
        
        if (window.originalLovePreviewState) {
            $container.html(window.originalLovePreviewState);
            window.isInFullLovePreview = false;
            
            // Re-initialize the navigation
            if (typeof initLovePageNavigation === 'function') {
                console.log('Re-initializing love navigation');
                initLovePageNavigation();
            }
            if (typeof enableLoveActionButtons === 'function') {
                enableLoveActionButtons();
            }
        } else {
            console.log('No saved preview state, regenerating...');
            // Fallback: regenerate the preview
            if (typeof generateLoveBookPreview === 'function') {
                generateLoveBookPreview();
            }
        }
    };
    
    window.restoreKidsPreview = function() {
        console.log('Restoring kids preview...');
        const $container = $('#kids-book-preview-container');
        
        if (window.originalKidsPreviewState) {
            $container.html(window.originalKidsPreviewState);
            window.isInFullKidsPreview = false;
            
            // Re-initialize the navigation
            if (typeof initKidsPageNavigation === 'function') {
                initKidsPageNavigation();
            }
            if (typeof enableKidsActionButtons === 'function') {
                enableKidsActionButtons();
            }
        } else {
            console.log('No saved preview state, regenerating...');
            // Fallback: regenerate the preview
            if (typeof generateKidsBookPreview === 'function') {
                generateKidsBookPreview();
            }
        }
    };
    
    // Global function for full-size image viewing
    window.viewFullSizeImage = function(imageUrl) {
        console.log('Opening full size image:', imageUrl);
        
        // Create modal for full-size image
        const modalHtml = `
            <div id="image-viewer-modal" style="
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.9);
                z-index: 9999;
                display: flex;
                align-items: center;
                justify-content: center;
                animation: fadeIn 0.3s ease;
            ">
                <div style="position: relative; max-width: 90%; max-height: 90%;">
                    <button onclick="closeImageViewer()" style="
                        position: absolute;
                        top: -40px;
                        right: 0;
                        background: #ff5252;
                        color: white;
                        border: none;
                        border-radius: 50%;
                        width: 30px;
                        height: 30px;
                        font-size: 20px;
                        cursor: pointer;
                        z-index: 10000;
                    ">×</button>
                    
                    <img src="${imageUrl}" 
                         style="max-width: 100%; max-height: 90vh; object-fit: contain;"
                         alt="Full size image">
                    
                    <div style="text-align: center; margin-top: 20px;">
                        <a href="${imageUrl}" 
                           download="story-page-${Date.now()}.png"
                           style="
                               background: #4CAF50;
                               color: white;
                               padding: 10px 20px;
                               border-radius: 5px;
                               text-decoration: none;
                               display: inline-block;
                               margin: 0 10px;
                           ">💾 Download</a>
                        <button onclick="closeImageViewer()" style="
                            background: #666;
                            color: white;
                            border: none;
                            padding: 10px 20px;
                            border-radius: 5px;
                            cursor: pointer;
                            margin: 0 10px;
                        ">Close</button>
                    </div>
                </div>
            </div>
        `;
        
        // Remove any existing modal
        $('#image-viewer-modal').remove();
        
        // Add new modal
        $('body').append(modalHtml);
        
        // Prevent body scrolling
        $('body').css('overflow', 'hidden');
    };
    
    // Global function to close image viewer
    window.closeImageViewer = function() {
        $('#image-viewer-modal').remove();
        $('body').css('overflow', 'auto');
    };
    
    // ============================================
    // MAIN INITIALIZATION FUNCTION
    // ============================================
    function initPage() {
        console.log('=== INITIALIZING PAGE ===');
        console.log('URL:', window.location.href);
        console.log('Body classes:', document.body.className);
        
        // Check what page we're on
        if (isOnWizardPage()) {
            console.log('On Wizard Page - Initializing wizard...');
            initWizard();
        } else if (isOnDownloadPage()) {
            console.log('On Download Page - Setting up download functions...');
            initDownloadPageFunctions();
        } else {
            console.log('Not on wizard or download page - minimal initialization');
            initCommonFunctions();
        }
    }
    
    // ============================================
    // DOWNLOAD PAGE FUNCTIONS
    // ============================================
    function initDownloadPageFunctions() {
        console.log('Setting up download page functions...');
        
        // Check if we need to auto-start generation
        const urlParams = new URLSearchParams(window.location.search);
        const immediate = urlParams.get('immediate');
        const generate = urlParams.get('generate');
        
        if ((immediate === 'true' && generate === 'true') || $('#story-generation-container').length > 0) {
            console.log('Download page in generation mode');
            // The PHP template already handles generation - just add CSS
            addDownloadPageStyles();
        } else if ($('#kids-book-preview-container').length > 0 || $('#book-preview-container').length > 0) {
            console.log('Download page showing story preview');
            addDownloadPageStyles();
            initDownloadPageNavigation();
        }
    }
    
    function addDownloadPageStyles() {
        // Add spinner animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            .ai-loading-spinner {
                border: 8px solid #f3f3f3;
                border-top: 8px solid #d32f2f;
                border-radius: 50%;
                animation: spin 1.5s linear infinite;
            }
            .page-turn {
                animation: fadeIn 0.3s ease;
            }
        `;
        document.head.appendChild(style);
    }
    
    function initDownloadPageNavigation() {
        // If there are thumbnails, make them clickable
        $('.thumbnail img').on('click', function() {
            const imgUrl = $(this).attr('src');
            if (imgUrl) {
                window.viewFullSizeImage(imgUrl);
            }
        });
        
        // Make all story images clickable
        $('img[src*="data:image"]').on('click', function() {
            window.viewFullSizeImage($(this).attr('src'));
        });
    }
    
    // ============================================
    // WIZARD FUNCTIONS
    // ============================================
    function initWizard() {
        const $wizard = $('#book-wizard');
        if (!$wizard.length) {
            console.error('Wizard container (#book-wizard) not found!');
            return;
        }

        console.log('=== INITIALIZING BOOK WIZARD ===');
        console.log('=== WITH KIDS STORY 10-PAGE SUPPORT ===');
    
        // Check if Step 4 is inside #book-wizard
        const $step4 = $('.wizard-step[data-step="4"]');
        if ($step4.length && $step4.closest('#book-wizard').length === 0) {
            console.log('Step 4 is outside #book-wizard. Moving it inside...');
            
            // Move Step 4 inside #book-wizard (after Step 3)
            const $step3 = $('.wizard-step[data-step="3"]');
            if ($step3.length) {
                $step3.after($step4);
                console.log('Moved Step 4 inside #book-wizard after Step 3');
            } else {
                $wizard.append($step4);
                console.log('Appended Step 4 to #book-wizard');
            }
        }
        
        // Store selected story type
        let selectedStoryType = '';
        let characterDetails = {};
        
        // AI Scene URLs
        let generatedSceneUrl = '';
        let generatedSceneLoveUrl = '';
        
        // Story data storage
        window.generatedStoryPages = null;
        window.storySelections = null;
        window.generatedKidsPages = null;
        window.kidsSelections = null;
        
        // Track if we're in preview mode
        window.isInFullKidsPreview = false;
        window.isInFullLovePreview = false;
        window.originalKidsPreviewState = null;
        window.originalLovePreviewState = null;
        
        // Update progress indicator
        function updateProgress(step) {
            console.log('Updating progress to step:', step);
            $('.progress-step').removeClass('active');
            $(`.progress-step[data-step="${step}"]`).addClass('active');
        }

        // Initial progress
        updateProgress(1);
        
        // Debug: Check all steps on load
        console.log('Total wizard steps:', $wizard.find('.wizard-step').length);
        $wizard.find('.wizard-step').each(function(i) {
            const $step = $(this);
            console.log('Step', $step.data('step'), 
                       'index:', i, 
                       'active:', $step.hasClass('step-active'),
                       'visible:', $step.is(':visible'));
        });

        // Function to validate step 2
        function validateStep2() {
            if (!selectedStoryType) {
                return { isValid: false, message: 'Please select a story type first.' };
            }
            
            if (selectedStoryType === 'kids') {
                const kidName = $('#kid-name').val().trim();
                const kidGender = $('#kid-gender').val();
                
                if (!kidName) {
                    return { isValid: false, message: 'Please enter your child\'s name.' };
                }
                if (!kidGender) {
                    return { isValid: false, message: 'Please select your child\'s gender.' };
                }
                
                characterDetails = {
                    kidName: kidName,
                    kidGender: kidGender
                };
                return { isValid: true, data: characterDetails };
                
            } else if (selectedStoryType === 'love') {
                const yourName = $('#your-name').val().trim();
                const yourGender = $('#your-gender').val();
                const loverName = $('#lover-name').val().trim();
                const loverGender = $('#lover-gender').val();
                
                if (!yourName) {
                    return { isValid: false, message: 'Please enter your name.' };
                }
                if (!yourGender) {
                    return { isValid: false, message: 'Please select your gender.' };
                }
                if (!loverName) {
                    return { isValid: false, message: 'Please enter your partner\'s name.' };
                }
                if (!loverGender) {
                    return { isValid: false, message: 'Please select your partner\'s gender.' };
                }
                
                characterDetails = {
                    yourName: yourName,
                    yourGender: yourGender,
                    loverName: loverName,
                    loverGender: loverGender
                };
                return { isValid: true, data: characterDetails };
            }
            
            return { isValid: false, message: 'Please select a story type.' };
        }

        // Function to validate step 3 (avatar creation)
        function validateStep3() {
            if (selectedStoryType === 'kids') {
                const avatarUrl = $('#avatar-kids-url').val();
                if (!avatarUrl) {
                    return { isValid: false, message: 'Please create and save your child\'s avatar first.' };
                }
                return { isValid: true };
                
            } else if (selectedStoryType === 'love') {
                const avatar1Url = $('#avatar-love1-url').val();
                const avatar2Url = $('#avatar-love2-url').val();
                if (!avatar1Url || !avatar2Url) {
                    return { isValid: false, message: 'Please create and save both avatars first.' };
                }
                return { isValid: true };
            }
            
            return { isValid: false, message: 'Please complete previous steps.' };
        }

        // Step navigation
        $wizard.on('click', '.wizard-next', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('=== NEXT BUTTON CLICKED ===');
            
            const $currentStep = $(this).closest('.wizard-step');
            const currentStepNum = parseInt($currentStep.data('step'));
            const nextStepNum = currentStepNum + 1;
            
            console.log('Current step:', currentStepNum, 'Looking for step:', nextStepNum);
            
            // Find the next step within the wizard container
            const $nextStep = $wizard.find('.wizard-step[data-step="' + nextStepNum + '"]');
            console.log('Found next step?', $nextStep.length);
            
            if (!$nextStep.length) {
                console.error('Step ' + nextStepNum + ' not found in wizard!');
                console.log('Available steps in wizard:');
                $wizard.find('.wizard-step').each(function() {
                    console.log('  Step', $(this).data('step'));
                });
                return;
            }
            
            // Validation per step
            let validation = { isValid: true, message: '' };
            
            if (currentStepNum === 1) {
                // Must select a story type
                if (!$('.template-choice.selected').length) {
                    validation = { isValid: false, message: 'Please select a story type first.' };
                }
            } else if (currentStepNum === 2) {
                validation = validateStep2();
                if (validation.isValid) {
                    $('#character-details').val(JSON.stringify(validation.data));
                }
            } else if (currentStepNum === 3) {
                validation = validateStep3();
            }
            
            if (!validation.isValid) {
                alert(validation.message);
                return;
            }
            
            // Hide current step, show next step
            $currentStep.removeClass('step-active').hide();
            $nextStep.addClass('step-active').show();
            updateProgress(nextStepNum);
            
            // Show appropriate content
            if (currentStepNum === 1) {
                // Show step 2 content based on story type
                $('#step2-placeholder').hide();
                $('#step2-content-kids, #step2-content-love').hide();
                
                if (selectedStoryType === 'kids') {
                    $('#step2-content-kids').show();
                } else if (selectedStoryType === 'love') {
                    $('#step2-content-love').show();
                }
            } else if (currentStepNum === 2) {
                // Show step 3 content based on story type
                $('#step3-placeholder').hide();
                $('#step3-content-kids, #step3-content-love').hide();
                
                if (selectedStoryType === 'kids') {
                    $('#step3-content-kids').show();
                    initRPMKids();
                } else if (selectedStoryType === 'love') {
                    $('#step3-content-love').show();
                    initRPMLove();
                }
            } else if (currentStepNum === 3) {
                // Show step 4 content based on story type
                showStep4Content();
            }
            
            console.log('Successfully moved to Step', nextStepNum);
        });

        $wizard.on('click', '.wizard-prev', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('=== PREVIOUS BUTTON CLICKED ===');
            
            const $currentStep = $(this).closest('.wizard-step');
            const currentStepNum = parseInt($currentStep.data('step'));
            const prevStepNum = currentStepNum - 1;
            
            console.log('Current step:', currentStepNum, 'Looking for previous step:', prevStepNum);
            
            // Find the previous step within the wizard container
            const $prevStep = $wizard.find('.wizard-step[data-step="' + prevStepNum + '"]');
            
            if ($prevStep.length) {
                $currentStep.removeClass('step-active').hide();
                $prevStep.addClass('step-active').show();
                updateProgress(prevStepNum);
                console.log('Successfully moved back to Step', prevStepNum);
            }
        });

        // Story type selection
        $wizard.on('click', '.template-choice', function() {
            $('.template-choice').removeClass('selected');
            $(this).addClass('selected');
            selectedStoryType = $(this).data('template');
            $('#story-type').val(selectedStoryType);
            
            console.log('Selected story type:', selectedStoryType);
            
            // Enable Next button in Step 1
            $('.wizard-step[data-step="1"] .wizard-next').prop('disabled', false);
        });

        // Show Step 4 content based on story type
        function showStep4Content() {
            console.log('=== SHOWING STEP 4 CONTENT ===');
            console.log('Story type:', selectedStoryType);
            
            $('#step4-placeholder').hide();
            $('#step4-content-kids, #step4-content-love').hide();
            
            if (selectedStoryType === 'kids') {
                console.log('Showing kids content for Step 4');
                $('#step4-content-kids').show();
                
                // Set avatar preview
                const avatarUrl = $('#avatar-kids-url').val();
                console.log('Kids avatar URL:', avatarUrl);
                if (avatarUrl) {
                    $('#kid-avatar-preview').attr('src', avatarUrl);
                    $('#original-avatar-kids').attr('src', avatarUrl);
                }
                
                // ========== KIDS STORY BUILDER INITIALIZATION ==========
                console.log('Initializing kids story builder');
                
                // Initialize template selection
                initKidsTemplateSelection();
                
                // Initialize style selection
                initKidsStyleSelection();
                
                // Enable Generate Story button if avatar exists
                if (avatarUrl) {
                    $('#generate-kids-story').prop('disabled', false);
                    console.log('Enabled generate-kids-story button');
                }
                
                $('#generate-kids-story').off('click.kidsStory').on('click.kidsStory', function() {
                    generateKidsPreviewPage();
                });
                
            } else if (selectedStoryType === 'love') {
                console.log('Showing love content for Step 4');
                $('#step4-content-love').show();
                
                // ========== HIDE EXTRA FIELDS FOR 3-PAGE TEST MODE ==========
                // Hide unnecessary sections for 3-page test
                $('#first-date-section, #love-about-section').hide();
                
                // Update labels to show test mode
                $('.section-title').each(function() {
                    const text = $(this).text();
                    if (text.includes('First Date')) {
                        $(this).text(text + ' (Optional for 3-page test)');
                    }
                    if (text.includes('What I Love')) {
                        $(this).text(text + ' (Optional for 3-page test)');
                    }
                });
                
                // Set avatar previews
                const avatarUrl1 = $('#avatar-love1-url').val();
                const avatarUrl2 = $('#avatar-love2-url').val();
                console.log('Love avatars URLs:', avatarUrl1, avatarUrl2);
                
                if (avatarUrl1) {
                    $('#love-avatar1-preview').attr('src', avatarUrl1);
                    $('#original-avatar-love1').attr('src', avatarUrl1);
                }
                if (avatarUrl2) {
                    $('#love-avatar2-preview').attr('src', avatarUrl2);
                    $('#original-avatar-love2').attr('src', avatarUrl2);
                }
                
                // ========== LOVE STORY BUILDER INITIALIZATION ==========
                console.log('Initializing love story builder');
                
                // Initialize selection counters (adjusted for 3 pages)
                updateLoveSelectionCounters();
                
                // Initialize choice selection logic
                initLoveChoiceSelections();
                
                // Update story summary initially
                updateLoveStorySummary();
                
                // Enable Generate Story button if avatars exist
                if (avatarUrl1 && avatarUrl2) {
                    $('#generate-love-story').prop('disabled', false);
                    console.log('Enabled generate-love-story button');
                }
                
                $('#generate-love-story').off('click.loveStory').on('click.loveStory', function() {
                    generateSinglePreviewPage();
                });
            }
        }

        // ========== KIDS STORY BUILDER FUNCTIONS ==========

        // Initialize kids template selection
        function initKidsTemplateSelection() {
            $('.template-choice-kids').off('click.kidsStory').on('click.kidsStory', function() {
                $('.template-choice-kids').find('.template-box').css({
                    'border-color': '#ddd',
                    'background': 'white',
                    'transform': 'scale(1)'
                });
                
                $(this).find('.template-box').css({
                    'border-color': '#1976d2',
                    'background': '#f0f7ff',
                    'transform': 'scale(1.02)'
                });
                
                updateKidsStorySummary();
            });
        }

        // Initialize kids style selection
        function initKidsStyleSelection() {
            $('.style-choice-kids').off('click.kidsStory').on('click.kidsStory', function() {
                $('.style-choice-kids').find('.style-box').css({
                    'border-color': '#ddd',
                    'background': 'white'
                });
                
                $(this).find('.style-box').css({
                    'border-color': '#1976d2',
                    'background': '#f0f7ff'
                });
                
                updateKidsStorySummary();
            });
        }

        // Update kids story summary
        function updateKidsStorySummary() {
            const template = $('input[name="kids_template"]:checked').val();
            const style = $('input[name="kids_book_style"]:checked').val();
            
            if (!template || !style) {
                $('#kids-story-summary').hide();
                return;
            }
            
            const templateNames = {
                'little_helper': 'The Little Helper',
                'space_explorer': 'The Space Explorer',
                'animal_rescuer': 'The Animal Rescuer',
                'future_chef': 'The Future Chef',
                'garden_magician': 'The Garden Magician',
                'curiosity_detective': 'The Curiosity Detective',
                'junior_doctor': 'The Junior Doctor',
                'brave_adventurer': 'The Brave Adventurer',
                'junior_engineer': 'The Junior Engineer',
                'ocean_protector': 'The Ocean Protector'
            };
            
            const styleNames = {
                'storybook': 'Classic Storybook',
                'cartoon': 'Cartoon Fun',
                'watercolor': 'Watercolor',
                'digital': 'Digital Art'
            };
            
            let summary = '';
            const pageCount = 10;
            
            if (template) {
                summary += `<p><strong>Story Template:</strong> ${templateNames[template] || template}</p>`;
            }
            
            if (style) {
                summary += `<p><strong>Art Style:</strong> ${styleNames[style] || style}</p>`;
            }
            
            summary += `<p><strong>Pages:</strong> ${pageCount} pages of personalized adventure</p>`;
            
            $('#kids-summary-content').html(summary);
            $('#kids-page-count').text(pageCount);
            $('#kids-story-summary').show();
        }

        // Get kids story selections
        function getKidsStorySelections() {
            return {
                template: $('input[name="kids_template"]:checked').val(),
                book_style: $('input[name="kids_book_style"]:checked').val(),
                character_details: characterDetails
            };
        }

        // Validate kids story selections
        function validateKidsStorySelections() {
            const selections = getKidsStorySelections();
            const errors = [];
            
            if (!selections.template) {
                errors.push('Please choose a story template');
            }
            if (!selections.book_style) {
                errors.push('Please choose a book style');
            }
            
            return {
                isValid: errors.length === 0,
                errors: errors,
                selections: selections
            };
        }

        // NEW FUNCTION: Generate kids preview page
        async function generateKidsPreviewPage() {
            console.log('Generating KIDS preview page...');
            
            // Validate selections
            const validation = validateKidsStorySelections();
            if (!validation.isValid) {
                alert('Please complete all selections:\n\n' + validation.errors.join('\n'));
                return;
            }
            
            // Get avatar
            const avatarUrl = $('#avatar-kids-url').val();
            if (!avatarUrl) {
                alert('Please create and save your child\'s avatar first.');
                return;
            }
            
            // Show loading
            $('#kids-story-form').hide();
            $('#kids-story-loading').show();
            $('#generate-kids-story').prop('disabled', true);
            
            try {
                // Convert avatar to base64
                const imageData = await getBase64FromImageUrl(avatarUrl);
                
                // Generate preview prompt
                const selections = validation.selections;
                const previewPrompt = `Create a children's story illustration for ${selections.template}. Style: Bright cartoon illustration for kids. This is a preview page.`;
                
                // Generate single preview page
                const previewImage = await generateSingleKidsPage({
                    image: imageData,
                    prompt: previewPrompt,
                    pageNumber: 1,
                    totalPages: 1,
                    kidName: characterDetails.kidName
                });
                
                // Store preview image
                window.previewImage = previewImage;
                
                // Hide loading
                $('#kids-story-loading').hide();
                
                // Show preview results
                $('#kids-story-results').show();
                
                // Show ONLY the preview
                $('#kids-book-preview-container').html(`
                    <div style="text-align: center;">
                        <h4 style="color: #1976d2;">Preview - Page 1</h4>
                        <img src="${previewImage}" 
                             style="max-width: 300px; height: auto; border-radius: 10px; border: 3px solid #1976d2; margin: 20px 0;">
                        <p style="color: #666; margin-top: 15px;">Preview only! Your full 10-page story will be generated after payment.
        Each page is created using powerful AI systems that consume real computing resources, so we only generate the full book for confirmed customers. Thank you for understanding!</p>
                    </div>
                `);
                
                // Hide existing buttons
                $('#view-full-kids-story, #download-kids-story-pdf').hide();
                
                // Show purchase options
                $('#kids-purchase-options').show();
                
                // Reset Add to Cart button
                $('#step4-content-kids .add-to-cart-btn').prop('disabled', true).css('opacity', 0.7).html('Add to Cart');
                
                console.log('Kids preview generated successfully');
                
            } catch (error) {
                console.error('Kids Preview Generation Error:', error);
                $('#kids-story-loading').hide();
                $('#kids-story-form').show();
                $('#generate-kids-story').prop('disabled', false);
                alert('Preview Generation Failed: ' + error.message);
            }
        }

        // Helper function to generate a single kids page
        async function generateSingleKidsPage(data) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    type: 'POST',
                    url: book_wizard_params.ajax_url,
                    data: {
                        action: 'generate_single_kids_story_page',
                        nonce: book_wizard_params.nonce,
                        image: data.image,
                        prompt: data.prompt,
                        page_number: data.pageNumber,
                        total_pages: data.totalPages,
                        kid_name: data.kidName
                    },
                    success: function(response) {
                        if (response.success && response.data && response.data.image_url) {
                            resolve(response.data.image_url);
                        } else {
                            reject(new Error(response.data || 'Page generation failed'));
                        }
                    },
                    error: function(xhr, status, error) {
                        reject(new Error(`AJAX Error: ${status} - ${error}`));
                    },
                    timeout: 60000 // 60 second timeout per page
                });
            });
        }

        // NEW FUNCTION: Generate only 1 preview page for love story
        async function generateSinglePreviewPage() {
            console.log('Generating SINGLE preview page only...');
            
            // Validate selections first
            const validation = validateLoveStorySelections();
            if (!validation.isValid) {
                alert('Please complete all selections:\n\n' + validation.errors.join('\n'));
                return;
            }
            
            // Get avatars
            const avatarUrl1 = $('#avatar-love1-url').val();
            const avatarUrl2 = $('#avatar-love2-url').val();
            
            if (!avatarUrl1 || !avatarUrl2) {
                alert('Please create and save both avatars first.');
                return;
            }
            
            // Show loading
            $('#love-story-form').hide();
            $('#story-loading').show();
            $('#generate-love-story').prop('disabled', true);
            
            // Update loading text
            $('#story-progress-text').text('Generating preview page...');
            
            try {
                // Convert avatars to base64
                const base64Image1 = await getBase64FromImageUrl(avatarUrl1);
                const base64Image2 = await getBase64FromImageUrl(avatarUrl2);
                
                // Generate only 1 preview prompt
                const selections = validation.selections;
                const previewPrompt = `Create a romantic scene showing ${selections.how_we_met ? selections.how_we_met.replace('_', ' ') : 'love'}. Style: Beautiful romantic illustration. This is a preview page.`;
                
                // Generate single preview page
                const previewImage = await generateSingleLovePage({
                    image1: base64Image1,
                    image2: base64Image2,
                    prompt: previewPrompt,
                    pageNumber: 1,
                    totalPages: 1
                });
                
                // Store preview image
                window.previewImage = previewImage;
                $('#preview-image-url').val(previewImage);
                
                // Hide loading
                $('#story-loading').hide();
                
                // Show preview results
                $('#story-results').show();
                
                // Show ONLY the preview (not full book)
                $('#book-preview-container').html(`
                    <div style="text-align: center;">
                        <h4 style="color: #d32f2f;">Preview - Page 1</h4>
                        <img src="${previewImage}" 
                             style="max-width: 300px; height: auto; border-radius: 10px; border: 3px solid #d32f2f; margin: 20px 0;">
                        <p style="color: #666; margin-top: 15px;">Preview only! Your full 10-page story will be generated after payment.
        Each page is created using powerful AI systems that consume real computing resources, so we only generate the full book for confirmed customers. Thank you for understanding!</p>
                    </div>
                `);
                
                // HIDE the existing buttons (View Full Story, Download PDF)
                $('#view-full-story, #download-story-pdf').hide();
                
                // SHOW purchase options
                $('#purchase-options').show();
                
                // Reset Add to Cart button
                $('#step4-content-love .add-to-cart-btn').prop('disabled', true).css('opacity', 0.7).html('Add to Cart');
                
                console.log('Preview generated successfully');
                
            } catch (error) {
                console.error('Preview Generation Error:', error);
                $('#story-loading').hide();
                $('#love-story-form').show();
                $('#generate-love-story').prop('disabled', false);
                alert('Preview Generation Failed: ' + error.message);
            }
        }

        // ========== LOVE STORY BUILDER FUNCTIONS ==========

        // Initialize choice selection logic for love story
        function initLoveChoiceSelections() {
            // Choice selection logic
            $('.choice-option').off('click.loveStory').on('click.loveStory', function() {
                const $choiceBox = $(this).find('.choice-box');
                const $input = $(this).find('input');
                
                if ($input.attr('type') === 'radio') {
                    // Radio buttons: deselect all in this group, select this one
                    const name = $input.attr('name');
                    $(`input[name="${name}"]`).prop('checked', false);
                    $(`.choice-option input[name="${name}"]`).siblings('.choice-box').css({
                        'border-color': '#ddd',
                        'background': 'white',
                        'transform': 'scale(1)'
                    });
                    
                    $input.prop('checked', true);
                    $choiceBox.css({
                        'border-color': '#d32f2f',
                        'background': '#fff5f5',
                        'transform': 'scale(1.02)'
                    });
                    
                    // Show "other" field if needed
                    updateLoveOtherFields();
                    
                } else {
                    // Checkboxes: toggle selection with limits
                    const maxSelections = getLoveMaxSelections($input);
                    const currentSelections = $(`input[name="${$input.attr('name')}"]:checked`).length;
                    
                    if ($input.prop('checked')) {
                        // Unselect
                        $input.prop('checked', false);
                        $choiceBox.css({
                            'border-color': '#ddd',
                            'background': 'white',
                            'transform': 'scale(1)'
                        });
                    } else {
                        // Check if we can select more
                        if (currentSelections < maxSelections) {
                            $input.prop('checked', true);
                            $choiceBox.css({
                                'border-color': '#d32f2f',
                                'background': '#fff5f5',
                                'transform': 'scale(1.02)'
                            });
                        } else {
                            alert(`Maximum ${maxSelections} selections allowed`);
                        }
                    }
                    
                    // Update counters
                    updateLoveSelectionCounters();
                    
                    // Show/hide "other" fields
                    updateLoveOtherFields();
                }
                
                // Update story summary
                updateLoveStorySummary();
            });
            
            // "Other" field input handling
            $('input[id$="_other"]').off('input.loveStory').on('input.loveStory', function() {
                updateLoveStorySummary();
            });
        }

        // Get maximum selections for a checkbox group
        function getLoveMaxSelections($input) {
            const name = $input.attr('name');
            if (name === 'favorite_memory[]') return 1;
            if (name === 'love_about[]') return 2;
            return 0;
        }

        // Update selection counters
        function updateLoveSelectionCounters() {
            const memoryCount = $('input[name="favorite_memory[]"]:checked').length;
            const loveCount = $('input[name="love_about[]"]:checked').length;
            
            $('#memory-counter').text(`Selected: ${memoryCount}/1`);
            $('#love-counter').text(`Selected: ${loveCount}/2`);
        }

        // Update story summary
        function updateLoveStorySummary() {
            const selections = getLoveStorySelections();
            
            if (!selections.how_we_met && !selections.future_dreams) {
                $('#story-summary').hide();
                return;
            }
            
            let summary = '';
            let pageCount = 3; // CHANGED FROM 6 TO 3
            
            // Build summary for 3 pages
            if (selections.how_we_met) {
                summary += `<p><strong>How We Met:</strong> ${formatLoveSelection(selections.how_we_met)} (1 page)</p>`;
            }
            
            if (selections.favorite_memories.length > 0) {
                summary += `<p><strong>Favorite Memory:</strong> ${selections.favorite_memories.map(m => formatLoveSelection(m)).join(', ')} (1 page)</p>`;
            }
            
            if (selections.future_dreams) {
                summary += `<p><strong>Future Dreams:</strong> ${formatLoveSelection(selections.future_dreams)} (1 page)</p>`;
            }
            
            if (selections.book_style) {
                summary += `<p><strong>Book Style:</strong> ${formatLoveSelection(selections.book_style)}</p>`;
            }
            
            summary += `<p><strong>Note:</strong> Test mode - 3 pages only</p>`;
            
            $('#summary-content').html(summary);
            $('#page-count').text(pageCount);
            $('#story-summary').show();
        }

        // Format selection for display
        function formatLoveSelection(value) {
            const map = {
                'school': 'School',
                'work': 'Work',
                'friends': 'Friends',
                'cafe': 'Café',
                'travel': 'Travel',
                'online': 'Online',
                'restaurant': 'Restaurant',
                'movies': 'Movies',
                'park': 'Park',
                'beach': 'Beach',
                'adventure': 'Adventure',
                'trip': 'Trip',
                'celebration': 'Celebration',
                'quiet_night': 'Quiet Night',
                'funny_moment': 'Funny Moment',
                'cooking': 'Cooking',
                'smile': 'Your Smile',
                'kindness': 'Kindness',
                'humor': 'Humor',
                'support': 'Support',
                'passion': 'Passion',
                'travel_world': 'Travel World',
                'build_home': 'Build Home',
                'start_family': 'Start Family',
                'grow_old': 'Grow Old',
                'classic_romance': 'Classic Romance',
                'modern': 'Modern',
                'dreamy': 'Dreamy'
            };
            
            return map[value] || value;
        }

        // Get all story selections
        function getLoveStorySelections() {
            const selections = {
                how_we_met: $('input[name="how_we_met"]:checked').val(),
                first_date: $('input[name="first_date"]:checked').val(),
                favorite_memories: [],
                love_about: [],
                future_dreams: $('input[name="future_dreams"]:checked').val(),
                book_style: $('input[name="book_style"]:checked').val()
            };
            
            // Get checkbox values
            $('input[name="favorite_memory[]"]:checked').each(function() {
                selections.favorite_memories.push($(this).val());
            });
            
            $('input[name="love_about[]"]:checked').each(function() {
                selections.love_about.push($(this).val());
            });
            
            // Check "other" fields
            if (selections.how_we_met === 'other') {
                selections.how_we_met = $('#how_we_met_other').val() || 'other';
            }
            
            if (selections.first_date === 'other') {
                selections.first_date = $('#first_date_other').val() || 'other';
            }
            
            // Check "other" fields for checkboxes
            $('input[name="favorite_memory[]"]:checked').each(function() {
                if ($(this).val() === 'other') {
                    const otherValue = $('#favorite_memory_other').val();
                    if (otherValue) {
                        const index = selections.favorite_memories.indexOf('other');
                        if (index !== -1) {
                            selections.favorite_memories[index] = otherValue;
                        }
                    }
                }
            });
            
            $('input[name="love_about[]"]:checked').each(function() {
                if ($(this).val() === 'other') {
                    const otherValue = $('#love_about_other').val();
                    if (otherValue) {
                        const index = selections.love_about.indexOf('other');
                        if (index !== -1) {
                            selections.love_about[index] = otherValue;
                        }
                    }
                }
            });
            
            return selections;
        }

        // Update "other" fields visibility
        function updateLoveOtherFields() {
            // How we met
            if ($('input[name="how_we_met"]:checked').val() === 'other') {
                $('#how_we_met_other').show().focus();
            } else {
                $('#how_we_met_other').hide().val('');
            }
            
            // First date
            if ($('input[name="first_date"]:checked').val() === 'other') {
                $('#first_date_other').show().focus();
            } else {
                $('#first_date_other').hide().val('');
            }
            
            // Favorite memories
            const hasMemoryOther = $('input[name="favorite_memory[]"][value="other"]').is(':checked');
            if (hasMemoryOther) {
                $('#favorite_memory_other').show().focus();
            } else {
                $('#favorite_memory_other').hide().val('');
            }
            
            // Love about
            const hasLoveOther = $('input[name="love_about[]"][value="other"]').is(':checked');
            if (hasLoveOther) {
                $('#love_about_other').show().focus();
            } else {
                $('#love_about_other').hide().val('');
            }
        }

        // Validate story selections - UPDATED FOR 3 PAGES
        function validateLoveStorySelections() {
            const selections = getLoveStorySelections();
            const errors = [];
            
            if (!selections.how_we_met || selections.how_we_met === 'other') {
                errors.push('Please choose "How We Met"');
            }
            if (selections.favorite_memories.length !== 1) {
                errors.push('Please choose exactly 1 Favorite Memory');
            }
            if (!selections.future_dreams || selections.future_dreams === 'other') {
                errors.push('Please choose "Our Future Dreams"');
            }
            if (!selections.book_style) {
                errors.push('Please choose "Book Style"');
            }
            
            // NOTE: We're NOT requiring first_date or love_about for 3-page test
            
            return {
                isValid: errors.length === 0,
                errors: errors,
                selections: selections
            };
        }

        // Helper function to generate a single page for love story
        async function generateSingleLovePage(data) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    type: 'POST',
                    url: book_wizard_params.ajax_url,
                    data: {
                        action: 'generate_single_love_story_page',
                        nonce: book_wizard_params.nonce,
                        image1: data.image1,
                        image2: data.image2,
                        prompt: data.prompt,
                        page_number: data.pageNumber,
                        total_pages: data.totalPages
                    },
                    success: function(response) {
                        if (response.success && response.data && response.data.image_url) {
                            resolve(response.data.image_url);
                        } else {
                            reject(new Error(response.data || 'Page generation failed'));
                        }
                    },
                    error: function(xhr, status, error) {
                        reject(new Error(`AJAX Error: ${status} - ${error}`));
                    },
                    timeout: 60000 // 60 second timeout per page
                });
            });
        }

        // Generate kids book preview
        function generateKidsBookPreview() {
            const $container = $('#kids-book-preview-container');
            $container.empty();
            
            if (!window.generatedKidsPages || window.generatedKidsPages.length === 0) {
                $container.html('<p style="text-align: center; color: #666; padding: 40px;">No preview available.</p>');
                return;
            }
            
            // Update the title to show 10 pages
            const previewTitle = `${characterDetails.kidName}'s Story Preview (${window.generatedKidsPages.length} Pages)`;
            
            // Create book preview with navigation
            let html = `
                <div class="book-preview-wrapper">
                    <div class="book-preview-header">
                        <h3 style="color: #1976d2; margin: 0;">${previewTitle}</h3>
                        <div class="page-counter">Click images to view full size</div>
                    </div>
                    
                    <div class="book-viewer">
                        <button class="nav-btn prev-btn" id="kids-prev-page-btn" style="visibility: hidden;">
                            ← Previous
                        </button>
                        
                        <div class="book-pages" id="kids-book-pages-container">
                            <div class="preview-page active" id="kids-current-page">
                                <div class="page-content">
                                    <div class="page-header">
                                        <div class="page-number">Page 1</div>
                                        <div class="template-name">${characterDetails.kidName}'s Story</div>
                                    </div>
                                    <div class="page-preview">
                                        <div class="background-container">
                                            <img src="${window.generatedKidsPages[0].image_url}" 
                                                 alt="Page 1" 
                                                 class="page-background" 
                                                 style="cursor: pointer; max-height: 300px; object-fit: contain;"
                                                 onclick="viewFullSizeImage('${window.generatedKidsPages[0].image_url}')">
                                        </div>
                                        <div class="page-text-preview">
                                            <div class="text-content">
                                                ${window.generatedKidsPages[0].text.substring(0, 80)}...
                                            </div>
                                        </div>
                                    </div>
                                    <div class="page-footer">
                                        <div class="text-summary">
                                            Click image to view full size • Page 1 of ${window.generatedKidsPages.length}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button class="nav-btn next-btn" id="kids-next-page-btn">
                            Next →
                        </button>
                    </div>
                    
                    <div class="page-thumbnails" id="kids-page-thumbnails">
            `;
            
            // Add thumbnails
            window.generatedKidsPages.forEach((page, index) => {
                html += `
                    <div class="thumbnail ${index === 0 ? 'active' : ''}" data-page="${index}">
                        <img src="${page.image_url}" 
                             alt="Page ${index + 1}" 
                             style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;"
                             onclick="viewFullSizeImage('${page.image_url}')">
                        <span>${index + 1}</span>
                    </div>
                `;
            });
            
            html += `
                    </div>
                    
                    <div style="text-align: center; margin: 20px 0;">
                        <button onclick="downloadAllKidsPagesAsZip()" style="
                            background: #4CAF50;
                            color: white;
                            border: none;
                            padding: 10px 20px;
                            border-radius: 5px;
                            cursor: pointer;
                            margin: 0 5px;
                        ">
                            💾 Download All Pages
                        </button>
                        <button onclick="printKidsAllPages()" style="
                            background: #2196F3;
                            color: white;
                            border: none;
                            padding: 10px 20px;
                            border-radius: 5px;
                            cursor: pointer;
                            margin: 0 5px;
                        ">
                            🖨️ Print Story
                        </button>
                    </div>
                </div>
            `;
            
            $container.html(html);
            
            // Initialize page navigation for kids
            initKidsPageNavigation();
            
            // Enable all action buttons for kids
            enableKidsActionButtons();
        }

        // Initialize kids page navigation
        function initKidsPageNavigation() {
            let currentPage = 0;
            const totalPages = window.generatedKidsPages.length;
            
            // Update navigation buttons
            function updateNavigation() {
                $('#kids-prev-page-btn').css('visibility', currentPage === 0 ? 'hidden' : 'visible');
                $('#kids-next-page-btn').css('visibility', currentPage === totalPages - 1 ? 'hidden' : 'visible');
                
                // Update thumbnails
                $('.thumbnail').removeClass('active');
                $(`.thumbnail[data-page="${currentPage}"]`).addClass('active');
                
                // Update current page display
                updateKidsCurrentPageDisplay(currentPage);
            }
            
            // Update current page display
            function updateKidsCurrentPageDisplay(pageIndex) {
                const page = window.generatedKidsPages[pageIndex];
                const $currentPage = $('#kids-current-page');
                
                $currentPage.html(`
                    <div class="page-content">
                        <div class="page-header">
                            <div class="page-number">Page ${pageIndex + 1}</div>
                            <div class="template-name">${characterDetails.kidName}'s Story</div>
                        </div>
                        <div class="page-preview">
                            <div class="background-container">
                                <img src="${page.image_url}" 
                                     alt="Page ${pageIndex + 1}" 
                                     class="page-background" 
                                     style="cursor: pointer; max-height: 300px; object-fit: contain;"
                                     onclick="viewFullSizeImage('${page.image_url}')">
                            </div>
                            <div class="page-text-preview">
                                <div class="text-content">
                                    ${page.text.substring(0, 80)}...
                                </div>
                            </div>
                        </div>
                        <div class="page-footer">
                            <div class="text-summary">
                                Page ${pageIndex + 1} of ${totalPages} • Click image to view full size
                            </div>
                        </div>
                    </div>
                `);
                
                // Add page turn animation
                $currentPage.addClass('page-turn');
                setTimeout(() => $currentPage.removeClass('page-turn'), 300);
            }
            
            // Next page button
            $('#kids-next-page-btn').off('click').on('click', function() {
                if (currentPage < totalPages - 1) {
                    currentPage++;
                    updateNavigation();
                }
            });
            
            // Previous page button
            $('#kids-prev-page-btn').off('click').on('click', function() {
                if (currentPage > 0) {
                    currentPage--;
                    updateNavigation();
                }
            });
            
            // Thumbnail clicks
            $('.thumbnail').off('click').on('click', function() {
                const pageIndex = parseInt($(this).data('page'));
                if (pageIndex !== currentPage) {
                    currentPage = pageIndex;
                    updateNavigation();
                }
            });
            
            // Initialize
            updateNavigation();
        }

        // Generate love book preview
        function generateLoveBookPreview() {
            const $container = $('#book-preview-container');
            $container.empty();
            
            if (!window.generatedStoryPages || window.generatedStoryPages.length === 0) {
                $container.html('<p style="text-align: center; color: #666; padding: 40px;">No preview available.</p>');
                return;
            }
            
            // Update the title to show 6 pages
            const previewTitle = `Your Love Story Preview (${window.generatedStoryPages.length} Pages)`;
            
            // Create book preview with navigation
            let html = `
                <div class="book-preview-wrapper">
                    <div class="book-preview-header">
                        <h3 style="color: #d32f2f; margin: 0;">${previewTitle}</h3>
                        <div class="page-counter">Click images to view full size</div>
                    </div>
                    
                    <div class="book-viewer">
                        <button class="nav-btn prev-btn" id="prev-page-btn" style="visibility: hidden;">
                            ← Previous
                        </button>
                        
                        <div class="book-pages" id="book-pages-container">
                            <div class="preview-page active" id="current-page">
                                <div class="page-content">
                                    <div class="page-header">
                                        <div class="page-number">Page 1</div>
                                        <div class="template-name">Love Story</div>
                                    </div>
                                    <div class="page-preview">
                                        <div class="background-container">
                                            <img src="${window.generatedStoryPages[0].image_url}" 
                                                 alt="Page 1" 
                                                 class="page-background" 
                                                 style="cursor: pointer; max-height: 300px; object-fit: contain;"
                                                 onclick="viewFullSizeImage('${window.generatedStoryPages[0].image_url}')">
                                        </div>
                                        <div class="page-text-preview">
                                            <div class="text-content">
                                                ${window.generatedStoryPages[0].prompt.substring(0, 80)}...
                                            </div>
                                        </div>
                                    </div>
                                    <div class="page-footer">
                                        <div class="text-summary">
                                            Click image to view full size • Page 1 of ${window.generatedStoryPages.length}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button class="nav-btn next-btn" id="next-page-btn">
                            Next →
                        </button>
                    </div>
                    
                    <div class="page-thumbnails" id="page-thumbnails">
            `;
            
            // Add thumbnails
            window.generatedStoryPages.forEach((page, index) => {
                html += `
                    <div class="thumbnail ${index === 0 ? 'active' : ''}" data-page="${index}">
                        <img src="${page.image_url}" alt="Page ${index + 1}" style="width: 100%; height: 100%; object-fit: cover;">
                        <span>${index + 1}</span>
                    </div>
                `;
            });
            
            html += `
                    </div>
                    
                    <div style="text-align: center; margin: 20px 0;">
                        <button onclick="downloadAllLovePagesAsZip()" style="
                            background: #4CAF50;
                            color: white;
                            border: none;
                            padding: 10px 20px;
                            border-radius: 5px;
                            cursor: pointer;
                            margin: 0 5px;
                        ">
                            💾 Download All Pages
                        </button>
                        <button onclick="printLoveAllPages()" style="
                            background: #2196F3;
                            color: white;
                            border: none;
                            padding: 10px 20px;
                            border-radius: 5px;
                            cursor: pointer;
                            margin: 0 5px;
                        ">
                            🖨️ Print Story
                        </button>
                    </div>
                </div>
            `;
            
            $container.html(html);
            
            // Initialize page navigation for love story
            initLovePageNavigation();
            
            // Enable all action buttons for love story
            enableLoveActionButtons();
            console.log('Love book preview generated and buttons enabled');
        }

        // Initialize page navigation for love story
        function initLovePageNavigation() {
            let currentPage = 0;
            const totalPages = window.generatedStoryPages.length;
            
            // Update navigation buttons
            function updateNavigation() {
                $('#prev-page-btn').css('visibility', currentPage === 0 ? 'hidden' : 'visible');
                $('#next-page-btn').css('visibility', currentPage === totalPages - 1 ? 'hidden' : 'visible');
                
                // Update thumbnails
                $('.thumbnail').removeClass('active');
                $(`.thumbnail[data-page="${currentPage}"]`).addClass('active');
                
                // Update current page display
                updateLoveCurrentPageDisplay(currentPage);
            }
            
            // Update current page display
            function updateLoveCurrentPageDisplay(pageIndex) {
                const page = window.generatedStoryPages[pageIndex];
                const $currentPage = $('#current-page');
                
                $currentPage.html(`
                    <div class="page-content">
                        <div class="page-header">
                            <div class="page-number">Page ${pageIndex + 1}</div>
                            <div class="template-name">Love Story</div>
                        </div>
                        <div class="page-preview">
                            <div class="background-container">
                                <img src="${page.image_url}" 
                                     alt="Page ${pageIndex + 1}" 
                                     class="page-background" 
                                     style="cursor: pointer; max-height: 300px; object-fit: contain;"
                                     onclick="viewFullSizeImage('${page.image_url}')">
                            </div>
                            <div class="page-text-preview">
                                <div class="text-content">
                                    ${page.prompt.substring(0, 80)}...
                                </div>
                            </div>
                        </div>
                        <div class="page-footer">
                            <div class="text-summary">
                                Page ${pageIndex + 1} of ${totalPages} • Click image to view full size
                            </div>
                        </div>
                    </div>
                `);
                
                // Add page turn animation
                $currentPage.addClass('page-turn');
                setTimeout(() => $currentPage.removeClass('page-turn'), 300);
            }
            
            // Next page button
            $('#next-page-btn').off('click').on('click', function() {
                if (currentPage < totalPages - 1) {
                    currentPage++;
                    updateNavigation();
                }
            });
            
            // Previous page button
            $('#prev-page-btn').off('click').on('click', function() {
                if (currentPage > 0) {
                    currentPage--;
                    updateNavigation();
                }
            });
            
            // Thumbnail clicks
            $('.thumbnail').off('click').on('click', function() {
                const pageIndex = parseInt($(this).data('page'));
                if (pageIndex !== currentPage) {
                    currentPage = pageIndex;
                    updateNavigation();
                }
            });
            
            // Initialize
            updateNavigation();
        }

        // Global functions for love story
        window.downloadAllLovePagesAsZip = function() {
            if (!window.generatedStoryPages || window.generatedStoryPages.length === 0) {
                alert('No pages available to download.');
                return;
            }
            
            alert('To download all pages, please:\n\n1. Click each thumbnail to view the full image\n2. Use the "Download This Page" button on each image\n\nWe\'re working on ZIP download functionality!');
        };

        window.printLoveAllPages = function() {
            if (!window.generatedStoryPages || window.generatedStoryPages.length === 0) {
                alert('No pages available to print.');
                return;
            }
            
            // Create a print-friendly version
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>My Love Story Book - ${window.generatedStoryPages.length} Pages</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                        .page { break-inside: avoid; margin-bottom: 30px; text-align: center; page-break-inside: avoid; }
                        .page img { max-width: 100%; height: auto; max-height: 500px; }
                        .page-number { color: #d32f2f; font-weight: bold; margin: 10px 0; font-size: 18px; }
                        .page-prompt { color: #666; font-size: 14px; margin-top: 10px; font-style: italic; }
                        @media print {
                            .no-print { display: none; }
                            .page { page-break-inside: avoid; }
                        }
                        .header { text-align: center; margin-bottom: 30px; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1 style="color: #d32f2f;">My Love Story Book</h1>
                        <p style="color: #666;">${window.generatedStoryPages.length} pages • Created ${new Date().toLocaleDateString()}</p>
                    </div>
                    
                    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
                        <button onclick="window.print()" style="
                            background: #d32f2f;
                            color: white;
                            border: none;
                            padding: 10px 20px;
                            border-radius: 5px;
                            cursor: pointer;
                        ">🖨️ Print This Story</button>
                        <button onclick="window.close()" style="
                            background: #666;
                            color: white;
                            border: none;
                            padding: 10px 20px;
                            border-radius: 5px;
                            cursor: pointer;
                            margin-left: 10px;
                        ">Close Window</button>
                    </div>
            `);
            
            // Add all pages
            window.generatedStoryPages.forEach((page, index) => {
                printWindow.document.write(`
                    <div class="page">
                        <div class="page-number">Page ${index + 1}</div>
                        <img src="${page.image_url}" alt="Page ${index + 1}">
                        <div class="page-prompt">${page.prompt}</div>
                    </div>
                `);
            });
            
            printWindow.document.write('</body></html>');
            printWindow.document.close();
        };

        // Enable kids action buttons
        function enableKidsActionButtons() {
            // View Full Story button
            $('#view-full-kids-story').off('click').on('click', function() {
                // Save current state before showing full view
                window.originalKidsPreviewState = $('#kids-book-preview-container').html();
                window.isInFullKidsPreview = true;
                showFullKidsStoryViewer();
            });
            
            // Download PDF button
            $('#download-kids-story-pdf').off('click').on('click', function() {
                downloadKidsStoryPDF();
            });
            
            // Enable both buttons
            $('#view-full-kids-story, #download-kids-story-pdf').prop('disabled', false).css('opacity', 1);
        }

        // Enable love action buttons
        function enableLoveActionButtons() {
            // View Full Story button
            $('#view-full-story').off('click.loveFullView').on('click.loveFullView', function() {
                console.log('View Full Love Story clicked');
                showFullLoveStoryViewer();
            });
            
            // Download PDF button
            $('#download-story-pdf').off('click.lovePDF').on('click.lovePDF', function() {
                downloadLoveStoryPDF();
            });
            
            // Enable both buttons
            $('#view-full-story, #download-story-pdf').prop('disabled', false).css('opacity', 1);
        }

        // Show full love story viewer
        function showFullLoveStoryViewer() {
            if (!window.generatedStoryPages || window.generatedStoryPages.length === 0) {
                alert('No story pages available. Please generate the story first.');
                return;
            }
            
            console.log('=== SHOWING FULL LOVE STORY VIEWER ===');
            console.log('Generated pages:', window.generatedStoryPages.length);
            
            // Get the current preview HTML BEFORE modifying it
            const $container = $('#book-preview-container');
            const currentHtml = $container.html();
            
            console.log('Current container HTML length:', currentHtml.length);
            console.log('Container found?', $container.length);
            
            // Save the original state
            window.originalLovePreviewState = currentHtml;
            window.isInFullLovePreview = true;
            
            console.log('Original state saved:', window.originalLovePreviewState ? 'YES' : 'NO');
            
            if (!window.originalLovePreviewState || window.originalLovePreviewState.trim() === '') {
                console.log('WARNING: No preview state to save! Regenerating preview first...');
                generateLoveBookPreview();
                window.originalLovePreviewState = $('#book-preview-container').html();
            }
            
            // Create full view HTML
            const fullViewHtml = `
                <div style="text-align: center; margin-bottom: 30px;">
                    <h3 style="color: #d32f2f;">All ${window.generatedStoryPages.length} Pages</h3>
                    <p style="color: #666;">Click any image to view full size</p>
                </div>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                    ${window.generatedStoryPages.map((page, index) => `
                        <div style="background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                            <div style="background: #d32f2f; color: white; padding: 10px; text-align: center; font-weight: bold;">
                                Page ${index + 1}
                            </div>
                            <img src="${page.image_url}" 
                                 alt="Page ${index + 1}" 
                                 style="width: 100%; height: 200px; object-fit: cover; cursor: pointer;"
                                 onclick="viewFullSizeImage('${page.image_url}')">
                            <div style="padding: 15px;">
                                <p style="color: #666; font-size: 14px; margin: 0 0 10px 0;">
                                    ${page.prompt ? page.prompt.substring(0, 80) : 'Love story page'}...
                                </p>
                                <div style="display: flex; gap: 10px;">
                                    <a href="${page.image_url}" 
                                       download="love-story-page-${index + 1}.png" 
                                       style="
                                           background: #4CAF50;
                                           color: white;
                                           padding: 5px 10px;
                                           border-radius: 3px;
                                           text-decoration: none;
                                           font-size: 12px;
                                           display: inline-block;
                                       ">💾 Download</a>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
                <div style="text-align: center; margin-top: 30px;">
                    <button id="back-to-love-preview-btn" style="
                        background: #d32f2f;
                        color: white;
                        border: none;
                        padding: 12px 24px;
                        border-radius: 5px;
                        cursor: pointer;
                        font-weight: bold;
                        font-size: 16px;
                        transition: all 0.3s ease;
                    ">
                        ← Back to Preview
                    </button>
                </div>
            `;
            
            // Update the container
            $container.html(fullViewHtml);
            
            console.log('Full view generated. Setting up back button handler...');
            
            // Add click handler for back button
            $('#back-to-love-preview-btn').off('click.loveBack').on('click.loveBack', function() {
                console.log('Back to preview button clicked');
                if (typeof restoreLovePreview === 'function') {
                    restoreLovePreview();
                } else {
                    console.error('restoreLovePreview function not found!');
                    // Fallback: regenerate the preview
                    generateLoveBookPreview();
                }
            });
            
            console.log('=== FULL VIEW SETUP COMPLETE ===');
        }

        // Show full kids story viewer
        function showFullKidsStoryViewer() {
            if (!window.generatedKidsPages || window.generatedKidsPages.length === 0) {
                alert('No story pages available. Please generate the story first.');
                return;
            }
            
            // Save current state before showing full view
            window.originalKidsPreviewState = $('#kids-book-preview-container').html();
            window.isInFullKidsPreview = true;
            
            // Create simple grid viewer
            const $container = $('#kids-book-preview-container');
            const fullViewHtml = `
                <div style="text-align: center; margin-bottom: 30px;">
                    <h3 style="color: #1976d2;">All ${window.generatedKidsPages.length} Pages</h3>
                    <p style="color: #666;">Click any image to view full size</p>
                </div>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                    ${window.generatedKidsPages.map((page, index) => `
                        <div style="background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                            <div style="background: #1976d2; color: white; padding: 10px; text-align: center; font-weight: bold;">
                                Page ${index + 1}
                            </div>
                            <img src="${page.image_url}" 
                                 alt="Page ${index + 1}" 
                                 style="width: 100%; height: 200px; object-fit: cover; cursor: pointer;"
                                 onclick="viewFullSizeImage('${page.image_url}')">
                            <div style="padding: 15px;">
                                <p style="color: #666; font-size: 14px; margin: 0 0 10px 0;">
                                    ${page.text.substring(0, 100)}...
                                </p>
                                <a href="${page.image_url}" 
                                   download="${characterDetails.kidName}-story-page-${index + 1}.png" 
                                   style="
                                       background: #4CAF50;
                                       color: white;
                                       padding: 5px 10px;
                                       border-radius: 3px;
                                       text-decoration: none;
                                       font-size: 12px;
                                       display: inline-block;
                                   ">💾 Download</a>
                            </div>
                        </div>
                    `).join('')}
                </div>
                <div style="text-align: center; margin-top: 30px;">
                    <button id="back-to-kids-preview-btn" style="
                        background: #1976d2;
                        color: white;
                        border: none;
                        padding: 10px 20px;
                        border-radius: 5px;
                        cursor: pointer;
                    ">← Back to Preview</button>
                </div>
            `;
            
            $container.html(fullViewHtml);
            
            // Add click handler for back button
            $('#back-to-kids-preview-btn').off('click').on('click', function() {
                if (typeof restoreKidsPreview === 'function') {
                    restoreKidsPreview();
                }
            });
        }

        // Download kids story as PDF
        function downloadKidsStoryPDF() {
            alert('For now, you can print the story and save as PDF:\n\n1. Click "Print Story" button\n2. In the print dialog, choose "Save as PDF"\n\nThis will create a PDF of all ' + window.generatedKidsPages.length + ' pages!');
        }

        // Download love story as PDF
        function downloadLoveStoryPDF() {
            alert('For now, you can print the story and save as PDF:\n\n1. Click "Print Story" button\n2. In the print dialog, choose "Save as PDF"\n\nThis will create a PDF of all ' + window.generatedStoryPages.length + ' pages!');
        }

        // Global functions for kids story
        window.downloadAllKidsPagesAsZip = function() {
            if (!window.generatedKidsPages || window.generatedKidsPages.length === 0) {
                alert('No pages available to download.');
                return;
            }
            
            // Create a temporary zip download
            alert('To download all pages, please:\n\n1. Click each thumbnail to view the full image\n2. Use the "Download This Page" button on each image\n\nWe\'re working on ZIP download functionality!');
        };

        window.printKidsAllPages = function() {
            if (!window.generatedKidsPages || window.generatedKidsPages.length === 0) {
                alert('No pages available to print.');
                return;
            }
            
            // Create a print-friendly version
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>${characterDetails.kidName}'s Story Book - ${window.generatedKidsPages.length} Pages</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                        .page { break-inside: avoid; margin-bottom: 30px; text-align: center; page-break-inside: avoid; }
                        .page img { max-width: 100%; height: auto; max-height: 500px; }
                        .page-number { color: #1976d2; font-weight: bold; margin: 10px 0; font-size: 18px; }
                        .page-text { color: #333; font-size: 16px; margin-top: 10px; padding: 0 20px; }
                        @media print {
                            .no-print { display: none; }
                            .page { page-break-inside: avoid; }
                        }
                        .header { text-align: center; margin-bottom: 30px; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1 style="color: #1976d2;">${characterDetails.kidName}'s Story Book</h1>
                        <p style="color: #666;">${window.generatedKidsPages.length} pages • Created ${new Date().toLocaleDateString()}</p>
                    </div>
                    
                    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
                        <button onclick="window.print()" style="
                            background: #1976d2;
                            color: white;
                            border: none;
                            padding: 10px 20px;
                            border-radius: 5px;
                            cursor: pointer;
                        ">🖨️ Print This Story</button>
                        <button onclick="window.close()" style="
                            background: #666;
                            color: white;
                            border: none;
                            padding: 10px 20px;
                            border-radius: 5px;
                            cursor: pointer;
                            margin-left: 10px;
                        ">Close Window</button>
                    </div>
            `);
            
            // Add all pages
            window.generatedKidsPages.forEach((page, index) => {
                printWindow.document.write(`
                    <div class="page">
                        <div class="page-number">Page ${index + 1}</div>
                        <img src="${page.image_url}" alt="Page ${index + 1}">
                        <div class="page-text">${page.text}</div>
                    </div>
                `);
            });
            
            printWindow.document.write('</body></html>');
            printWindow.document.close();
        };

        // Initialize ReadyPlayerMe for kids
        function initRPMKids() {
            console.log('Initializing ReadyPlayerMe for Kids with custom subdomain');
            
            // Clear any existing listeners
            $(window).off('message.rpmKids');
            
            // Listen for messages from ReadyPlayerMe iframe
            $(window).on('message.rpmKids', function(event) {
                console.log('Message from iframe:', event.originalEvent.data);
                
                // Check if it's from ReadyPlayerMe (including your custom subdomain)
                if (event.originalEvent.origin !== 'https://lovebookstory.readyplayer.me' && 
                    event.originalEvent.origin !== 'https://models.readyplayer.me') {
                    return;
                }
                
                const data = event.originalEvent.data;
                console.log('Data received:', data, 'Type:', typeof data);
                
                // Handle different message formats
                if (typeof data === 'string') {
                    // Check if it's a URL string (new format for custom subdomain)
                    if (data.includes('lovebookstory.readyplayer.me') || 
                        data.includes('readyplayer.me') || 
                        data.includes('models.readyplayer.me')) {
                        console.log('Got URL string:', data);
                        $('#avatar-url-input-kids').val(data);
                        $('#rpm-creation-kids').hide();
                        $('#rpm-url-section-kids').show();
                        
                        // AUTO-SAVE for custom subdomain
                        autoSaveAvatar(data, 'kids');
                    }
                } else if (typeof data === 'object') {
                    // Frame API format
                    const avatarUrl = data.url || (data.data && data.data.url);
                    
                    // Also check for base64 data (might be in data field)
                    if (avatarUrl) {
                        console.log('Got avatar URL from frame API:', avatarUrl);
                        $('#avatar-url-input-kids').val(avatarUrl);
                        $('#rpm-creation-kids').hide();
                        $('#rpm-url-section-kids').show();
                        
                        // AUTO-SAVE for frame API
                        autoSaveAvatar(avatarUrl, 'kids');
                    }
                }
            });
            
            // Save button handler with manual fallback
            $('#save-avatar-url-kids').off('click.rpmKids').on('click.rpmKids', function() {
                let avatarUrl = $('#avatar-url-input-kids').val().trim();
                
                if (!avatarUrl) {
                    alert('Please paste your ReadyPlayerMe URL first.');
                    return;
                }
                
                // Convert GLB to PNG if needed
                if (avatarUrl.includes('.glb')) {
                    avatarUrl = avatarUrl.replace('.glb', '.png');
                }
                
                console.log('Manual saving avatar URL:', avatarUrl);
                $('#avatar-kids-url').val(avatarUrl);
                
                $(this).html('✓ Saved!').css('background', '#4CAF50').prop('disabled', true);
                $('#avatar-url-input-kids').prop('disabled', true);
                
                // Show success message
                alert('✅ Child\'s avatar saved successfully! Click "Next" to choose a story.');
                
                // Enable Next button in Step 3
                $('.wizard-step[data-step="3"] .wizard-next').prop('disabled', false);
                
                // Force save to hidden field
                $('#avatar-kids-url').val(avatarUrl).trigger('change');
            });
            
            // Add direct iframe communication with custom subdomain
            const iframe = document.getElementById('readyplayerme-iframe-kids');
            if (iframe && iframe.contentWindow) {
                try {
                    // Try multiple subscription formats for compatibility
                    iframe.contentWindow.postMessage({ 
                        type: 'subscribe', 
                        events: ['v1.avatar.exported', 'avatarExported', 'avatarSaved'] 
                    }, '*');
                    
                    // Also try simpler format
                    iframe.contentWindow.postMessage('getAvatar', '*');
                } catch (e) {
                    console.log('Could not send message to iframe:', e);
                }
            }
        }

        // Initialize ReadyPlayerMe for love story
        function initRPMLove() {
            console.log('Initializing ReadyPlayerMe for Love Story with custom subdomain');
            window.firstAvatarSaved = false;
            
            // Clear any existing listeners
            $(window).off('message.rpmLove');
            
            // Initially hide the second avatar creation section
            $('#rpm-creation-love2').hide();
            $('#love2-avatar-section').hide();
            
            // Listen for messages from ReadyPlayerMe iframe
            $(window).on('message.rpmLove', function(event) {
                console.log('Love story message:', event.originalEvent.data);
                
                // Check if it's from ReadyPlayerMe (including your custom subdomain)
                if (event.originalEvent.origin !== 'https://lovebookstory.readyplayer.me' && 
                    event.originalEvent.origin !== 'https://models.readyplayer.me') {
                    return;
                }
                
                const data = event.originalEvent.data;
                console.log('Love - Data received:', data, 'Type:', typeof data);
                
                // Handle different message formats
                if (typeof data === 'string') {
                    // Check if it's a URL string
                    if (data.includes('lovebookstory.readyplayer.me') || 
                        data.includes('readyplayer.me') || 
                        data.includes('models.readyplayer.me')) {
                        console.log('Got URL string:', data);
                        
                        if (!window.firstAvatarSaved) {
                            $('#avatar-url-input-love1').val(data);
                            $('#rpm-creation-love1').hide();
                            $('#rpm-url-section-love1').show();
                            
                            // AUTO-SAVE first avatar
                            autoSaveAvatar(data, 'love1');
                        } else {
                            $('#avatar-url-input-love2').val(data);
                            $('#rpm-creation-love2').hide();
                            $('#rpm-url-section-love2').show();
                            
                            // AUTO-SAVE second avatar
                            autoSaveAvatar(data, 'love2');
                        }
                    }
                } else if (typeof data === 'object') {
                    // Frame API format
                    const avatarUrl = data.url || (data.data && data.data.url);
                    if (avatarUrl) {
                        console.log('Got avatar URL from frame API:', avatarUrl);
                        
                        if (!window.firstAvatarSaved) {
                            $('#avatar-url-input-love1').val(avatarUrl);
                            $('#rpm-creation-love1').hide();
                            $('#rpm-url-section-love1').show();
                            
                            // AUTO-SAVE first avatar
                            autoSaveAvatar(avatarUrl, 'love1');
                        } else {
                            $('#avatar-url-input-love2').val(avatarUrl);
                            $('#rpm-creation-love2').hide();
                            $('#rpm-url-section-love2').show();
                            
                            // AUTO-SAVE second avatar
                            autoSaveAvatar(avatarUrl, 'love2');
                        }
                    }
                }
            });
            
            // First avatar save button
            $('#save-avatar-url-love1').off('click.rpmLove1').on('click.rpmLove1', function() {
                let avatarUrl = $('#avatar-url-input-love1').val().trim();
                
                if (!avatarUrl) {
                    alert('Please paste your ReadyPlayerMe URL first.');
                    return;
                }
                
                // Convert GLB to PNG if needed
                if (avatarUrl.includes('.glb')) {
                    avatarUrl = avatarUrl.replace('.glb', '.png');
                }
                
                console.log('Manual saving first avatar URL:', avatarUrl);
                $('#avatar-love1-url').val(avatarUrl);
                
                $(this).html('✓ Saved!').css('background', '#4CAF50').prop('disabled', true);
                $('#avatar-url-input-love1').prop('disabled', true);
                
                alert('✅ Your avatar saved! Now create your partner\'s avatar.');
                window.firstAvatarSaved = true;
                
                // Show the second avatar creation section
                $('#love2-avatar-section').show(500, function() {
                    // Scroll to the second avatar section
                    $('html, body').animate({
                        scrollTop: $('#love2-avatar-section').offset().top - 50
                    }, 500);
                });
                $('#rpm-creation-love2').show();
                
                // Force save to hidden field
                $('#avatar-love1-url').trigger('change');
            });
            
            // Second avatar save button
            $('#save-avatar-url-love2').off('click.rpmLove2').on('click.rpmLove2', function() {
                let avatarUrl = $('#avatar-url-input-love2').val().trim();
                
                if (!avatarUrl) {
                    alert('Please paste your partner\'s ReadyPlayerMe URL first.');
                    return;
                }
                
                // Convert GLB to PNG if needed
                if (avatarUrl.includes('.glb')) {
                    avatarUrl = avatarUrl.replace('.glb', '.png');
                }
                
                console.log('Manual saving second avatar URL:', avatarUrl);
                $('#avatar-love2-url').val(avatarUrl);
                
                $(this).html('✓ Saved!').css('background', '#4CAF50').prop('disabled', true);
                $('#avatar-url-input-love2').prop('disabled', true);
                
                alert('✅ Partner\'s avatar saved successfully! Click "Next" to create AI scene.');
                
                // Enable Next button in Step 3
                $('.wizard-step[data-step="3"] .wizard-next').prop('disabled', false);
                
                // Force save to hidden field
                $('#avatar-love2-url').trigger('change');
            });
            
            // Add direct iframe communication for first iframe
            const iframe1 = document.getElementById('readyplayerme-iframe-love1');
            if (iframe1 && iframe1.contentWindow) {
                try {
                    iframe1.contentWindow.postMessage({ 
                        type: 'subscribe', 
                        events: ['v1.avatar.exported', 'avatarExported', 'avatarSaved'] 
                    }, '*');
                } catch (e) {
                    console.log('Could not send message to iframe 1:', e);
                }
            }
            
            // Add direct iframe communication for second iframe
            const iframe2 = document.getElementById('readyplayerme-iframe-love2');
            if (iframe2 && iframe2.contentWindow) {
                try {
                    iframe2.contentWindow.postMessage({ 
                        type: 'subscribe', 
                        events: ['v1.avatar.exported', 'avatarExported', 'avatarSaved'] 
                    }, '*');
                } catch (e) {
                    console.log('Could not send message to iframe 2:', e);
                }
            }
        }

        // Convert image URL to base64
        function getBase64FromImageUrl(url) {
            return new Promise((resolve, reject) => {
                const img = new Image();
                img.crossOrigin = 'Anonymous';
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    canvas.width = this.naturalWidth;
                    canvas.height = this.naturalHeight;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(this, 0, 0);
                    const dataUrl = canvas.toDataURL('image/png');
                    resolve(dataUrl.split(',')[1]); // Get base64 without data:image/png;base64, prefix
                };
                img.onerror = reject;
                img.src = url;
            });
        }

        // Helper function for delay
        function delay(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

        // Initialize button states
        function initializeButtonStates() {
            console.log('Initializing button states');
            
            // Disable Next button in Step 1 until story type is selected
            $('.wizard-step[data-step="1"] .wizard-next').prop('disabled', true);
            
            // Disable Next button in Step 3 until avatar is saved
            $('.wizard-step[data-step="3"] .wizard-next').prop('disabled', true);
            
            // Disable Add to Cart button initially
            $('.add-to-cart-btn').prop('disabled', true).css('opacity', 0.7);
            
            // Disable Generate buttons
            $('#generate-scene-kids').prop('disabled', true);
            $('#generate-scene-love').prop('disabled', true);
            $('#generate-love-story').prop('disabled', true);
            $('#generate-kids-story').prop('disabled', true);
        }

        // Initialize button states
        initializeButtonStates();
        
        console.log('=== WIZARD INITIALIZATION COMPLETE ===');
    }
    
    // ============================================
    // COMMON FUNCTIONS (used on all pages)
    // ============================================
    function initCommonFunctions() {
        // Only initialize what's needed on all pages
        console.log('Initializing common functions...');
        
        // Make any data-image URLs clickable
        $(document).on('click', 'img[src*="data:image"]', function() {
            if (typeof window.viewFullSizeImage === 'function') {
                window.viewFullSizeImage($(this).attr('src'));
            }
        });
    }
    
    // ============================================
    // EVENT HANDLERS (for all pages)
    // ============================================
    
    // Handle purchase option selection
    $(document).on('click', '.purchase-option', function() {
        const selectedProductId = $(this).data('product-id');
        const purchaseOption = $(this).data('option');
        const price = $(this).data('price');
        const isKids = $(this).hasClass('kids-purchase');
        
        console.log(`Purchase option clicked: ${purchaseOption}, Product ID: ${selectedProductId}, Price: ${price} DH`);
        
        $('#purchase-product-id').val(selectedProductId);
        $('#purchase-option').val(purchaseOption);
        
        if (isKids) {
            $('#step4-content-kids .add-to-cart-btn')
                .html(`Add to Cart - ${price} DH`)
                .prop('disabled', false)
                .css('opacity', 1);
            
            $('.kids-purchase').css({
                'border-color': '#ddd',
                'transform': 'scale(1)'
            });
            $(this).css({
                'border-color': '#1976d2',
                'transform': 'scale(1.05)'
            });
        } else {
            $('#step4-content-love .add-to-cart-btn')
                .html(`Add to Cart - ${price} DH`)
                .prop('disabled', false)
                .css('opacity', 1);
            
            $('.purchase-option:not(.kids-purchase)').css({
                'border-color': '#ddd',
                'transform': 'scale(1)'
            });
            $(this).css({
                'border-color': '#d32f2f',
                'transform': 'scale(1.05)'
            });
        }
        
        console.log(`✅ Purchase option selected: ${purchaseOption} - Add to Cart button ENABLED`);
    });
    
    // Add to Cart handler
    $(document).on('click', '.add-to-cart-btn:not(:disabled)', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('=== ADD TO CART CLICKED (WORKING VERSION) ===');
        
        const storyType = $('#story-type').val();
        const characterData = $('#character-details').val();
        const purchaseProductId = $('#purchase-product-id').val();
        const purchaseOption = $('#purchase-option').val();
        
        console.log('Story type:', storyType);
        console.log('Selected Product ID:', purchaseProductId);
        console.log('Purchase Option:', purchaseOption);
        
        // Check if purchase option is selected
        if (!purchaseProductId || !purchaseOption) {
            alert('⚠️ Please select Digital Only (49DH) or Digital + Physical (289DH) first!');
            return;
        }
        
        // Check if preview was generated
        if (!window.previewImage) {
            alert('⚠️ Please generate the preview first by clicking "Generate Preview (1 Page)"');
            return;
        }
        
        // Simple validation
        if (storyType === 'love') {
            const avatar1 = $('#avatar-love1-url').val();
            const avatar2 = $('#avatar-love2-url').val();
            if (!avatar1 || !avatar2) {
                alert('⚠️ Please create both avatars first');
                return;
            }
        } else if (storyType === 'kids') {
            const avatar = $('#avatar-kids-url').val();
            if (!avatar) {
                alert('⚠️ Please create your child\'s avatar first');
                return;
            }
        }
        
        // Show loading
        const $button = $(this);
        const originalText = $button.html();
        $button.html('🔄 Adding to Cart...').prop('disabled', true);
        
        // Prepare data
        let data = {
            action: 'my_book_add_to_cart',
            product_id: purchaseProductId,
            quantity: 1,
            purchase_option: purchaseOption,
            story_type: storyType,
            character_details: characterData,
            preview_image: window.previewImage || ''
        };
        
        // Add avatars based on story type
        if (storyType === 'kids') {
            data.avatar = $('#avatar-kids-url').val();
        } else if (storyType === 'love') {
            data.avatar = $('#avatar-love1-url').val();
            data.avatar2 = $('#avatar-love2-url').val();
        }
        
        console.log('Sending data to AJAX:', data);
        
        // AJAX request
        $.ajax({
            type: 'POST',
            url: book_wizard_params.ajax_url,
            data: data,
            success: function(response) {
                console.log('✅ AJAX Success:', response);
                
                if (response.success) {
                    alert('✅ Added to cart successfully!');
                    window.location.href = response.data.redirect;
                } else {
                    alert('❌ Error: ' + (response.data || 'Unknown error'));
                    $button.html(originalText).prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ AJAX Error:', error);
                alert('❌ AJAX Error: ' + error + '\nCheck browser console for details.');
                $button.html(originalText).prop('disabled', false);
            }
        });
    });
    
    // ============================================
    // DOCUMENT READY
    // ============================================
    $(document).ready(function() {
        console.log('Document ready - initializing page...');
        initPage();
    });
    
})(jQuery);