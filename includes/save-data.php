<?php
add_action('save_post_glint-swiper-slider', 'glint_swiper_save_post_data');
function glint_swiper_save_post_data($post_id) {
    if (!isset($_POST['glint_swiper_nonce']) || 
        !wp_verify_nonce($_POST['glint_swiper_nonce'], 'glint_swiper_save_data') ||
        defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ||
        !current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save slider type
    if (!empty($_POST['slider_type'])) {
        update_post_meta($post_id, '_slider_type', sanitize_text_field($_POST['slider_type']));
    }

    // Save slider height
    if (!empty($_POST['slider_height'])) {
        update_post_meta($post_id, '_slider_height', sanitize_text_field($_POST['slider_height']));
    } else {
        delete_post_meta($post_id, '_slider_height');
    }

    //Save custom css
    if (!empty($_POST['custom_css'])) {
        update_post_meta($post_id, '_custom_css', sanitize_text_field($_POST['custom_css']));
    } else {
        delete_post_meta($post_id, '_custom_css');
    }

    // Save image/text slider data
    if ($_POST['slider_type'] === 'image_text' && !empty($_POST['image_slide'])) {

        //Save caption style
        if (!empty($_POST['caption_style'])) {
            update_post_meta($post_id, '_caption_style', sanitize_text_field($_POST['caption_style']));
        } else {
            delete_post_meta($post_id, '_caption_style');
        }

        if (!empty($_POST['slide_gap'])) {
            update_post_meta($post_id, '_slide_gap', sanitize_text_field($_POST['slide_gap']));
        } else {
            delete_post_meta($post_id, '_slide_gap');
        }

        $slides = [];
        foreach ($_POST['image_slide'] as $slide) {
            $slides[] = [
                'title' => sanitize_text_field($slide['title']),
                'content' => wp_kses_post($slide['content']),
                'image_id' => absint($slide['image_id']),
                'link' => esc_url_raw($slide['link']),
                'image_size' => sanitize_text_field($slide['image_size'] ?? 'full')
            ];
        }
        update_post_meta($post_id, '_image_slides', $slides);
    }

    // Save review slider data
    if ($_POST['slider_type'] === 'review' && !empty($_POST['review_slide'])) {
        $reviews = [];
        foreach ($_POST['review_slide'] as $review) {
            $reviews[] = [
                'reviewer' => sanitize_text_field($review['reviewer']),
                'date' => sanitize_text_field($review['date']),
                'rating' => absint($review['rating']),
                'content' => wp_kses_post($review['content']),
                'review_image_id' => absint($review['review_image_id']),
                'review_image_size' => sanitize_text_field($review['review_image_size'] ?? 'full')
            ];
        }
        update_post_meta($post_id, '_review_slides', $reviews);
    }

    // Save product slider data
    if ($_POST['slider_type'] === 'product') {
        // Process categories
        $categories = [];
        if (!empty($_POST['product_categories'])) {
            $categories = array_map('sanitize_text_field', 
                explode(',', sanitize_text_field($_POST['product_categories'])));
            $categories = array_filter($categories);
        }
        
        // Process attributes
        $attributes = [];
        if (!empty($_POST['product_attributes'])) {
            $attributes = array_map('sanitize_text_field', 
                explode(',', sanitize_text_field($_POST['product_attributes'])));
            $attributes = array_filter($attributes);
        }

        // Process tags
        $tags = [];
        if (!empty($_POST['product_tags'])) {
            $tags = array_map('sanitize_text_field', 
                explode(',', sanitize_text_field($_POST['product_tags'])));
            $tags = array_filter($tags);
        }
        
        update_post_meta($post_id, '_product_categories', $categories);
        update_post_meta($post_id, '_product_attributes', $attributes);
        update_post_meta($post_id, '_product_tags', $tags);
        update_post_meta($post_id, '_product_amount', absint($_POST['product_amount']));
        update_post_meta($post_id, '_filter_rule', sanitize_text_field($_POST['filter_rule']));
        update_post_meta($post_id, '_order_by', sanitize_text_field($_POST['order_by']));
    }
}