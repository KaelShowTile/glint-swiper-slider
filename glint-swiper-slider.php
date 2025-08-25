<?php
/*
Plugin Name: CHT Swiper Slider
Description: Custom Swiper Slider for WordPress
Version: 1.1.0
Author: Kael
*/

defined('ABSPATH') or die('!');

// Plugin constants
define('GLINT_SWIPER_PATH', plugin_dir_path(__FILE__));
define('GLINT_SWIPER_URL', plugin_dir_url(__FILE__));

// Include required files
require_once GLINT_SWIPER_PATH . 'includes/post-type.php';
require_once GLINT_SWIPER_PATH . 'includes/meta-boxes.php';
require_once GLINT_SWIPER_PATH . 'includes/save-data.php';
require_once GLINT_SWIPER_PATH . 'includes/enqueue.php';
require_once GLINT_SWIPER_PATH . 'includes/frontend.php';

// Register activation hook to flush rewrite rules
register_activation_hook(__FILE__, 'glint_swiper_flush_rewrites');
function glint_swiper_flush_rewrites() {
    glint_swiper_register_post_type();
    flush_rewrite_rules();
}