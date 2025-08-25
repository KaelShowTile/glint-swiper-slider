<?php
add_action('admin_enqueue_scripts', 'glint_swiper_admin_scripts');
function glint_swiper_admin_scripts($hook) {
    global $post_type;
    
    if (($hook === 'post-new.php' || $hook === 'post.php') && $post_type === 'glint-swiper-slider') {
        wp_enqueue_media();
        wp_enqueue_script(
            'glint-swiper-admin',
            GLINT_SWIPER_URL . 'assets/js/admin.js',
            ['jquery', 'jquery-ui-autocomplete'],
            '1.0',
            true
        );
        
        wp_localize_script('glint-swiper-admin', 'glint_swiper', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('glint_swiper_nonce')
        ]);
        
        wp_enqueue_style(
            'glint-swiper-admin',
            GLINT_SWIPER_URL . 'assets/css/admin.css',
            [],
            '1.0'
        );
    }
}

// Add AJAX handlers for autocomplete
add_action('wp_ajax_glint_search_product_terms', 'glint_swiper_search_product_terms');
function glint_swiper_search_product_terms() {
    check_ajax_referer('glint_swiper_nonce', 'nonce');
    
    $term = sanitize_text_field($_GET['term']);
    $type = sanitize_text_field($_GET['type']);
    
    if ($type === 'category') {
        $taxonomy = 'product_cat';
    } elseif ($type === 'attribute') {
        $taxonomy = wc_get_attribute_taxonomy_names();
    } elseif($type === 'tag'){
        $taxonomy = 'product_tag';
    }else {
        wp_send_json_error();
    }
    
    $terms = get_terms([
        'taxonomy' => $taxonomy,
        'search' => $term,  // Use 'search' instead of 'name__like'
        'hide_empty' => false,
        'number' => 20
    ]);
    
    $results = [];
    foreach ($terms as $term) {
        $results[] = [
            'label' => $term->name,
            'value' => $term->name
        ];
    }
    
    wp_send_json($results);
}