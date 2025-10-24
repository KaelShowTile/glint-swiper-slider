<?php
// Register shortcode
add_shortcode('glint_swiper', 'glint_swiper_shortcode_handler');

function glint_swiper_shortcode_handler($atts) {
    $atts = shortcode_atts(['id' => 0], $atts);
    $slider_id = absint($atts['id']);
    
    if (!$slider_id || 'glint-swiper-slider' !== get_post_type($slider_id)) {
        return '';
    }
    
    // Get slider data
    $slider_type = get_post_meta($slider_id, '_slider_type', true);
    $slider_height = get_post_meta($slider_id, '_slider_height', true);
    $style = $slider_height ? 'style="height: ' . esc_attr($slider_height) . '"' : '';
    $extra_css = get_post_meta($slider_id, '_custom_css', true);
    $output = '';
    
    // Enqueue assets
    wp_enqueue_style('glint-swiper-frontend');
    wp_enqueue_script('glint-swiper-frontend');
    wp_add_inline_script('glint-swiper-frontend', 'glint_swiper_init();');
    
    // Generate slider HTML
    
    switch ($slider_type) {
        case 'image_text':
            $caption_style = get_post_meta($slider_id, '_caption_style', true);
            $gap_size = get_post_meta($slider_id, '_slide_gap', true) ?: 0;
            $output .= '<div class="swiper glint-swiper-' . $slider_id . ' ' . $slider_type . '-slider ' . $extra_css . ' ' . $caption_style . '" ' . $style . ' data-gap="' . esc_attr($gap_size) . '">';
            $output .= '<div class="swiper-wrapper">';
            $slides = get_post_meta($slider_id, '_image_slides', true);
            if ($slides) {
                foreach ($slides as $slide) {
                    $image_size = $slide['image_size'] ?? 'full';
                    $image_url = wp_get_attachment_image_url($slide['image_id'], $image_size);
                    $output .= '<div class="swiper-slide">';
                    
                    // Wrap content in link if exists
                    if (!empty($slide['link'])) {
                        $output .= '<a href="' . esc_url($slide['link']) . '" class="slide-link">';
                    }else{
                        $output .= '<a href="' . esc_url($image_url) . '" class="slide-link">';
                    }
                    
                    $output .= $image_url ? '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($slide['title']) . '">' : '';
                    $output .= '<div class="slide-content">';
                    $output .= $slide['title'] ? '<h3>' . esc_html($slide['title']) . '</h3>' : '';
                    $output .= $slide['content'] ? '<p class="slide-text">' . wp_kses_post($slide['content']) . '</p>' : '';
                    $output .= '</div>';
                    
                    if (!empty($slide['link'])) {
                        $output .= '</a>';
                    }
                    
                    $output .= '</div>';
                }
            }
            break;
            
        case 'review':
            $output .= '<div class="swiper glint-swiper-' . $slider_id . ' ' . $slider_type . '-slider ' . $extra_css . ' image_text-slider below_image" ' . $style . '>';
            $output .= '<div class="swiper-wrapper">';
            $reviews = get_post_meta($slider_id, '_review_slides', true);
            if ($reviews) {
                foreach ($reviews as $review) {
                    $review_image_size = $review['review_image_size'] ?? 'full';
                    $review_image_url = wp_get_attachment_image_url($review['review_image_id'], $review_image_size);
                    $stars = str_repeat('<span class="star">★</span>', (int)$review['rating']);

                    $output .= '<div class="swiper-slide">';
                    if($review['content']){
                        $output .= $review_image_url ? '<img src="' . esc_url($review_image_url) . '" alt="' . esc_attr($review['content']) . '">' : '';
                    }else{
                        $output .= $review_image_url ? '<img src="' . esc_url($review_image_url) . '">' : '';
                    }                   
                    $output .= '<div class="slide-content">';
                    $output .= '<div class="review-meta">';
                    $output .= $review['reviewer'] ? '<span class="reviewer">' . esc_html($review['reviewer']) . '</span>' : '';
                    $output .= $review['date'] ? '<span class="review-date">' . esc_html($review['date']) . '</span>' : '';
                    $output .= $review['rating'] ? '<div class="review-rating">' . $stars . '</div>' : '';
                    $output .= '</div>';
                    $output .= $review['content'] ? '<p class="review-content">' . wp_kses_post($review['content']) . '</p>' : '';
                    $output .= '</div></div>';
                }
            }
            break;
            
        case 'product':
            $output .= '<div class="swiper glint-swiper-' . $slider_id . ' ' . $slider_type . '-slider ' . $extra_css . '" ' . $style . '>';
            $output .= '<div class="swiper-wrapper">';
            // Get product settings
            $categories = get_post_meta($slider_id, '_product_categories', true);
            $attributes = get_post_meta($slider_id, '_product_attributes', true);
            $tags = get_post_meta($slider_id, '_product_tags', true);
            $amount = get_post_meta($slider_id, '_product_amount', true) ?: 12;
            $filter_rule = get_post_meta($slider_id, '_filter_rule', true);
            $order_by = get_post_meta($slider_id, '_order_by', true);
            
            // Build product query
            $tax_query = ['relation' => $filter_rule];
            
            if (!empty($categories)) {
                $tax_query[] = [
                    'taxonomy' => 'product_cat',
                    'field'    => 'name',
                    'terms'    => $categories,
                ];
            }
            
            if (!empty($attributes)) {
                $tax_query[] = [
                    'taxonomy' => 'pa_' . sanitize_title($attributes[0]),
                    'field'    => 'name',
                    'terms'    => $attributes,
                ];
            }

            if (!empty($tags)) {
                $tax_query[] = [
                    'taxonomy' => 'product_tag',
                    'field'    => 'name',
                    'terms'    => $tags,
                ];
            }
            
            $orderby = $order_by === 'price' ? 'meta_value_num' : 'date';
            $meta_key = $order_by === 'price' ? '_price' : '';
            
            $args = [
                'post_type'      => 'product',
                'posts_per_page' => $amount,
                'tax_query'      => $tax_query,
                'orderby'        => $orderby,
                'meta_key'       => $meta_key,
                'order'          => $order_by === 'price' ? 'ASC' : 'DESC',
                'meta_query'     => [
                    [
                        'key'     => '_stock_status',
                        'value'   => 'instock',
                        'compare' => '=',
                    ]
                ]
            ];
            
            $products = new WP_Query($args);
            
            if ($products->have_posts()) {
                while ($products->have_posts()) {
                    $products->the_post();
                    global $product;

                    //for cht only
                    $product_id = $product->get_id();
                    $product_suffix = get_product_qty_suffix($product_id);
                    $product_display_suffix = "";
                    $step_value = round(get_product_qty_data($product_id), 2);
                    $regular_price = $product->get_regular_price();
                    $sale_price = "";
                    $full_title = get_the_title();

                    if ( $product->is_on_sale() ){
                        $sale_price = $product->get_sale_price();
                    }

                    if($product_suffix)
                    {
                        if($product_suffix == "m2")
                        {
                            if($regular_price > 0 && $step_value > 0){
                                $regular_price = round(($regular_price/$step_value), 2);
                            }
                            if($sale_price != "" && $step_value > 0){
                                $sale_price = round(($sale_price/$step_value), 2);
                            }
                            $product_display_suffix = "/m<sup>2</sup>";
                        }else{
                            $product_display_suffix = "/" . $product_suffix;
                        }
                    }
                    
                    $output .= '<div class="swiper-slide product-slide">';
                    
                    

                    //for cht only
                    $sticker_url = get_field('product_icon_image', $product_id);

                    if($sticker_url){
                        $output .= '<span class="product-sticker"><img src=' . $sticker_url . '></span>';
                    }
                    elseif ($product->is_on_sale()){
                        $output .= '<span class="product-discount-rate">-' . round( (1-($sale_price/$regular_price)), 2)*100 . '%</span>';
                    }
                    
                    $output .= '<a href="' . get_permalink() . '">';
                    $output .= get_the_post_thumbnail(null, 'large');
                    $output .= '<h3>' . get_the_title() . '</h3>';
                    
                    if ( $product->is_on_sale() ){
                        $output .= '<div class="price">';
                        $output .= '<del aria-hidden="true"><span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>' . $regular_price . ' </bdi></span></del>';
                        $output .= '<ins aria-hidden="true"><span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>' . $sale_price . $product_display_suffix .'</bdi></span></ins>';
                        $output .= '</div>';
                    }else{
                        $output .= '<div class="price">' . $product->get_price_html() . $product_id . '</div>';
                    }
                    $output .= '<button id="cht-add-cart-btn" data-product_id="' . $product_id . '" data-product_name="' . $full_title . '">Add to cart</button>';
                    $output .= '</a></div>';
                }
                wp_reset_postdata();
            }
            break;
    }
    
    $output .= '</div>'; // .swiper-wrapper
    $output .= '<div class="swiper-button-next"></div><div class="swiper-button-prev"></div>';
    $output .= '<div class="swiper-scrollbar"></div>';
    $output .= '</div>'; // .swiper
    
    return $output;
}

// Enqueue frontend assets
add_action('wp_enqueue_scripts', 'glint_swiper_frontend_assets');
function glint_swiper_frontend_assets() {
    // Only load assets when shortcode is used
    global $post;
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'glint_swiper')) {
        // Swiper CSS
        wp_enqueue_style(
            'swiper-bundle',
            'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
            [],
            '11.0.0'
        );
        
        // Swiper JS
        wp_enqueue_script(
            'swiper-bundle',
            'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
            [],
            '11.0.0',
            true
        );
        
        // Plugin frontend CSS
        wp_enqueue_style(
            'glint-swiper-frontend',
            GLINT_SWIPER_URL . 'assets/css/frontend.css',
            ['swiper-bundle'],
            '1.0'
        );
        
        // Plugin frontend JS
        wp_enqueue_script(
            'glint-swiper-frontend',
            GLINT_SWIPER_URL . 'assets/js/frontend.js',
            ['jquery', 'swiper-bundle'],
            '1.0',
            true
        );
    }
}