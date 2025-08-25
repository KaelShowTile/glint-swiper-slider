<?php
add_action('add_meta_boxes', 'glint_swiper_add_meta_boxes');
function glint_swiper_add_meta_boxes() {
    add_meta_box(
        'glint_swiper_settings',
        'Slider Settings',
        'glint_swiper_settings_callback',
        'glint-swiper-slider',
        'normal',
        'high'
    );

    add_meta_box(
        'glint_swiper_shortcode',
        'Slider Shortcode',
        'glint_swiper_shortcode_callback',
        'glint-swiper-slider',
        'side',
        'low'
    );
}

function glint_swiper_settings_callback($post) {
    wp_nonce_field('glint_swiper_save_data', 'glint_swiper_nonce');
    $slider_type = get_post_meta($post->ID, '_slider_type', true);
    
    // Slider Type Selector
    echo '<div class="glint-meta-field half-col">';
    echo '<label for="slider_type"><strong>Slider Type:</strong></label><br>';
    echo '<select id="slider_type" name="slider_type" class="glint-switcher">';
    echo '<option value="">Select Type</option>';
    echo '<option value="image_text"' . selected($slider_type, 'image_text', false) . '>Image/Text Slider</option>';
    echo '<option value="product"' . selected($slider_type, 'product', false) . '>Product Slider</option>';
    echo '<option value="review"' . selected($slider_type, 'review', false) . '>Review Slider</option>';
    echo '</select>';
    echo '</div>';

    echo '<div class="glint-meta-field half-col last">';
    echo '</div>';

    //Slider height
    echo '<div class="glint-meta-field half-col">';
    echo '<label for="slider_height"><strong>Slider Height:</strong></label><br>';
    echo '<input type="text" id="slider_height" name="slider_height" value="' . esc_attr(get_post_meta($post->ID, '_slider_height', true)) . '" class="widefat">';
    echo '<p class="description">Enter height value with unit (e.g., 500px, 50vh, 30rem, please put "auto" for product slider)</p>';
    echo '</div>';

    //custom css class
    echo '<div class="glint-meta-field half-col last">';
    echo '<label for="custom_css"><strong>Custom CSS Class:</strong></label><br>';
    echo '<input type="text" id="custom_css" name="custom_css" value="' . esc_attr(get_post_meta($post->ID, '_custom_css', true)) . '" class="widefat">';
    echo '<p class="description">Add extra CSS Class</p>';
    echo '</div>';

    // Image/Text Slider Fields
    echo '<div class="glint-slider-type image_text">';
    echo '<h3>Image/Text Slides</h3>';
    $image_slides = get_post_meta($post->ID, '_image_slides', true);
    $caption_style = get_post_meta($post->ID, '_caption_style', true);
    $slide_count = $image_slides ? count($image_slides) : 1;
    $image_sizes = get_intermediate_image_sizes();
    $image_sizes[] = 'full';
    
    //Caption style
    echo '<div class="glint-meta-field half-col">';
    echo '<label for="caption_style"><strong>Caption Style</strong></label><br>';
    echo '<select id="caption_style" name="caption_style" class="caption-switcher">';
    echo '<option value="bottom_transparent"' . selected($caption_style, 'bottom_transparent', false) . '>Bottom transparent background</option>';
    echo '<option value="bottom_white"' . selected($caption_style, 'bottom_white', false) . '>Bottom white background</option>';
    echo '<option value="below_image"' . selected($caption_style, 'below_image', false) . '>Below image with fixed hight</option>';
    echo '</select>';
    echo '</div>';

    //gap setting
    echo '<div class="glint-meta-field half-col last">';
    echo '<label for="slide_gap"><strong>Gap Size(e.g. 0, 5, 10, do not put "px"):</strong></label><br>';
    echo '<input type="text" id="slide_gap" name="slide_gap" value="' . esc_attr(get_post_meta($post->ID, '_slide_gap', true)) . '" class="widefat">';
    echo '</div>';
    
    echo '<div class="glint-repeater">';
    for ($i = 0; $i < $slide_count; $i++) {
        $title = $image_slides[$i]['title'] ?? '';
        $content = $image_slides[$i]['content'] ?? '';
        $image_id = $image_slides[$i]['image_id'] ?? '';
        $image_link = $image_slides[$i]['link'] ?? '';
        $image_url = wp_get_attachment_url($image_id);
        $get_image_size = $image_slides[$i]['image_size'] ?? '';
        
        echo '<div class="glint-repeater-item">';
        echo '<div class="glint-repeater-left">';
        echo '<h4>Slide #' . ($i + 1) . '</h4>';
        echo '</div>';
        echo '<div class="glint-repeater-right">';
        echo '<button class="remove-slide button" style="margin-left:10px;color:#dc3232;">Remove Slide</button>';
        echo '</div>';

        echo '<div class="glint-repeater-left">';
        echo '<label>Image:</label>';
        echo '<div class="image-preview">';
        if ($image_url) echo '<img src="' . esc_url($image_url) . '" style="max-width:200px;display:block;">';
        echo '</div>';
        echo '<input type="hidden" name="image_slide[' . $i . '][image_id]" value="' . esc_attr($image_id) . '" class="image-id">';
        echo '<button class="upload-image button">Select Image</button>';
        echo '<div class="glint-meta-field one-col">';
        echo '<label>Image Size:</label>';
        echo '<select name="image_slide[' . $i . '][image_size]" class="widefat">';
        foreach ($image_sizes as $size) {
            $selected = selected(($get_image_size ?? 'full'), $size, false);
            $label = ucwords(str_replace(['-', '_'], ' ', $size));
            $dimensions = "";
            
            if ($size === 'full') {
                $dimensions = '(Original Size)';
            } else {
                $dimensions = '';
                $size_info = wp_get_registered_image_subsizes()[$size] ?? null;
                
                if (!$size_info) {
                    global $_wp_additional_image_sizes;
                    if (isset($_wp_additional_image_sizes[$size])) {
                        $size_info = [
                            'width' => $_wp_additional_image_sizes[$size]['width'],
                            'height' => $_wp_additional_image_sizes[$size]['height']
                        ];
                    }
                }
                
                if ($size_info) {
                    $width = $size_info['width'] ?? '';
                    $height = $size_info['height'] ?? '';
                    $dimensions = $width && $height ? "({$width}×{$height})" : '';
                }
            }

            echo '<option value="' . esc_attr($size) . '" ' . $selected . '>'  . esc_html($label) . ($dimensions ? ' ' . $dimensions : '') . '</option>';
        }
        echo '</select>';
        echo '</div></div>';

        echo '<div class="glint-repeater-right">';
        echo '<label>Title:</label>';
        echo '<input type="text" name="image_slide[' . $i . '][title]" value="' . esc_attr($title) . '" class="widefat">';
        echo '<label>Content:</label>';
        echo '<textarea name="image_slide[' . $i . '][content]" class="widefat" rows="3">' . esc_textarea($content) . '</textarea>';
        echo '<label>Link URL:</label>';
        echo '<input type="url" name="image_slide[' . $i . '][link]" value="' . esc_url($image_link ?? '') . '" class="widefat" placeholder="https://example.com">';
        echo '</div></div><hr>';
    }
    echo '<button class="add-slide button">Add New Slide</button>';
    echo '</div></div>';

    // Review Slider Fields
    echo '<div class="glint-slider-type review">';
    echo '<h3>Review Slides</h3>';
    $review_slides = get_post_meta($post->ID, '_review_slides', true);
    $review_count = $review_slides ? count($review_slides) : 1;
    
    echo '<div class="glint-repeater">';
    for ($i = 0; $i < $review_count; $i++) {
        $reviewer = $review_slides[$i]['reviewer'] ?? '';
        $date = $review_slides[$i]['date'] ?? '';
        $rating = $review_slides[$i]['rating'] ?? '';
        $review_content = $review_slides[$i]['content'] ?? '';
        $review_image_id = $review_slides[$i]['review_image_id'] ?? '';
        $review_image_url = wp_get_attachment_url($review_image_id);
        $review_image_size = $review_slides[$i]['review_image_size'] ?? '';
        
        echo '<div class="glint-repeater-item">';

        echo '<div class="glint-repeater-left">';
        echo '<h4>Review #' . ($i + 1) . '</h4>';
        echo '</div>';
        echo '<div class="glint-repeater-right">';
        echo '<button class="remove-slide button" style="color:#dc3232;">Remove Review</button>';
        echo '</div>';

        echo '<div class="glint-repeater-left">';
        echo '<label>Image:</label>';
        echo '<div class="image-preview">';
        if ($review_image_url) echo '<img src="' . esc_url($review_image_url) . '" style="max-width:200px;display:block;">';
        echo '</div>';
        echo '<input type="hidden" name="review_slide[' . $i . '][review_image_id]" value="' . esc_attr($review_image_id) . '" class="image-id">';
        echo '<button class="upload-image button">Select Image</button>';
        echo '<div class="glint-meta-field one-col">';
        echo '<label>Image Size:</label>';
        echo '<select name="review_slide[' . $i . '][review_image_size]" class="widefat">';
        foreach ($image_sizes as $size) {
            $selected = selected(($review_image_size ?? 'full'), $size, false);
            $label = ucwords(str_replace(['-', '_'], ' ', $size));
            $dimensions = "";
            
            if ($size === 'full') {
                $dimensions = '(Original Size)';
            } else {
                $dimensions = '';
                $size_info = wp_get_registered_image_subsizes()[$size] ?? null;
                
                if (!$size_info) {
                    global $_wp_additional_image_sizes;
                    if (isset($_wp_additional_image_sizes[$size])) {
                        $size_info = [
                            'width' => $_wp_additional_image_sizes[$size]['width'],
                            'height' => $_wp_additional_image_sizes[$size]['height']
                        ];
                    }
                }
                
                if ($size_info) {
                    $width = $size_info['width'] ?? '';
                    $height = $size_info['height'] ?? '';
                    $dimensions = $width && $height ? "({$width}×{$height})" : '';
                }
            }

            echo '<option value="' . esc_attr($size) . '" ' . $selected . '>'  . esc_html($label) . ($dimensions ? ' ' . $dimensions : '') . '</option>';
        }
        echo '</select>';
        echo '</div></div>';

        echo '<div class="glint-repeater-right">';
        echo '<label>Reviewer Name:</label>';
        echo '<input type="text" name="review_slide[' . $i . '][reviewer]" value="' . esc_attr($reviewer) . '" class="widefat">';
        echo '<label>Date:</label>';
        echo '<input type="date" name="review_slide[' . $i . '][date]" value="' . esc_attr($date) . '" class="widefat">';
        echo '<label>Rating:</label>';
        echo '<select name="review_slide[' . $i . '][rating]" class="widefat">';
        for ($r = 1; $r <= 5; $r++) {
            echo '<option value="' . $r . '"' . selected($rating, $r, false) . '>' . $r . ' Star' . ($r > 1 ? 's' : '') . '</option>';
        }
        echo '</select>';
        echo '<label>Review Content:</label>';
        echo '<textarea name="review_slide[' . $i . '][content]" class="widefat" rows="3">' . esc_textarea($review_content) . '</textarea>';
        echo '</div></div><hr>';
    }
    echo '<button class="add-slide button">Add New Review</button>';
    echo '</div></div>';

    // Product Slider Fields
    echo '<div class="glint-slider-type product">';
    echo '<h3>Product Slider Settings</h3>';
    $categories = get_post_meta($post->ID, '_product_categories', true);
    $attributes = get_post_meta($post->ID, '_product_attributes', true);
    $tags = get_post_meta($post->ID, '_product_tags', true);
    $amount = get_post_meta($post->ID, '_product_amount', true) ?: 12;
    $filter_rule = get_post_meta($post->ID, '_filter_rule', true);
    $order_by = get_post_meta($post->ID, '_order_by', true);

    echo '<label>Product Categories (type to search):</label>';
    echo '<input type="text" name="product_categories" value="' . esc_attr(is_array($categories) ? implode(', ', $categories) : '') . '" class="glint-autocomplete widefat" data-type="category" placeholder="Start typing...">';

    echo '<label>Product Attributes (type to search):</label>';
    echo '<input type="text" name="product_attributes" value="' . esc_attr(is_array($attributes) ? implode(', ', $attributes) : '') . '" class="glint-autocomplete widefat" data-type="attribute" placeholder="Start typing...">';

    echo '<label>Product Tags (type to search):</label>';
    echo '<input type="text" name="product_tags" value="' . esc_attr(is_array($tags) ? implode(', ', $tags) : '') . '" class="glint-autocomplete widefat" data-type="tag" placeholder="Start typing...">';

    echo '<label>Products Amount:</label>';
    echo '<input type="number" name="product_amount" value="' . esc_attr($amount) . '" min="1" max="50" class="widefat">';

    echo '<label>Filter Rule:</label>';
    echo '<select name="filter_rule" class="widefat">';
    echo '<option value="and"' . selected($filter_rule, 'and', false) . '>AND (All conditions must match)</option>';
    echo '<option value="or"' . selected($filter_rule, 'or', false) . '>OR (Any condition can match)</option>';
    echo '</select>';

    echo '<label>Order By:</label>';
    echo '<select name="order_by" class="widefat">';
    echo '<option value="date"' . selected($order_by, 'date', false) . '>Date (Newest to Oldest)</option>';
    echo '<option value="price"' . selected($order_by, 'price', false) . '>Price (Low to High)</option>';
    echo '</select>';
    echo '</div>';
}

function glint_swiper_shortcode_callback($post) {
    if ($post->post_status === 'publish') {
        echo '<p>Use this shortcode to display the slider:</p>';
        echo '<code>[glint_swiper id="' . $post->ID . '"]</code>';
        echo '<p>Copy and paste this code into your page or post content.</p>';
    } else {
        echo '<p>Save or publish the slider to generate the shortcode.</p>';
    }
}