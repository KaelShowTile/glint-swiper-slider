<?php
function glint_swiper_register_post_type() {
    $args = [
        'label'               => 'Swiper Sliders',
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'capability_type'     => 'post',
        'hierarchical'        => false,
        'supports'            => ['title'],
        'menu_icon'           => 'dashicons-slides',
        'rewrite'             => false,
        'show_in_rest'        => false,
    ];

    register_post_type('glint-swiper-slider', $args);
}
add_action('init', 'glint_swiper_register_post_type');

// Add shortcode column to admin list
add_filter('manage_glint-swiper-slider_posts_columns', 'glint_swiper_add_shortcode_column');
function glint_swiper_add_shortcode_column($columns) {
    $columns['shortcode'] = 'Shortcode';
    return $columns;
}

add_action('manage_glint-swiper-slider_posts_custom_column', 'glint_swiper_show_shortcode_column', 10, 2);
function glint_swiper_show_shortcode_column($column, $post_id) {
    if ($column === 'shortcode') {
        echo '<code>[cht_slider id="' . $post_id . '"]</code>';
    }
}