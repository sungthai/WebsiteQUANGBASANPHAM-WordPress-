<?php

if (!defined('ABSPATH')) {
    exit;
}

function quangba_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'gallery', 'caption', 'style', 'script'));
}
add_action('after_setup_theme', 'quangba_setup');

function quangba_register_products() {
    register_post_type('san_pham', array(
        'labels' => array(
            'name' => 'Sản phẩm',
            'singular_name' => 'Sản phẩm',
            'add_new_item' => 'Thêm sản phẩm',
            'edit_item' => 'Sửa sản phẩm',
        ),
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'san-pham'),
        'menu_icon' => 'dashicons-cart',
        'supports' => array('title', 'editor', 'excerpt', 'thumbnail'),
        'show_in_rest' => true,
    ));
}
add_action('init', 'quangba_register_products');

function quangba_register_price_box() {
    add_meta_box('quangba_product_price', 'Giá sản phẩm (VND)', 'quangba_render_price_box', 'san_pham', 'side');
}
add_action('add_meta_boxes', 'quangba_register_price_box');

function quangba_render_price_box($post) {
    wp_nonce_field('quangba_save_price', 'quangba_price_nonce');
    $price = get_post_meta($post->ID, '_quangba_price', true);
    echo '<label for="quangba_price">Nhập giá dạng số, ví dụ 1250000</label>';
    echo '<input id="quangba_price" name="quangba_price" type="number" min="0" step="1000" value="' . esc_attr($price) . '" style="width:100%;margin-top:8px">';
}

function quangba_save_price($post_id) {
    if (!isset($_POST['quangba_price_nonce'])) {
        return;
    }
    $nonce = wp_unslash($_POST['quangba_price_nonce']);
    if (!is_string($nonce) || !wp_verify_nonce(sanitize_text_field($nonce), 'quangba_save_price')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id) || get_post_type($post_id) !== 'san_pham' || !isset($_POST['quangba_price'])) {
        return;
    }

    $raw_price = wp_unslash($_POST['quangba_price']);
    if (!is_string($raw_price)) {
        return;
    }
    $price = sanitize_text_field($raw_price);
    if ($price === '') {
        delete_post_meta($post_id, '_quangba_price');
    } elseif (ctype_digit($price)) {
        update_post_meta($post_id, '_quangba_price', $price);
    }
}
add_action('save_post_san_pham', 'quangba_save_price');

function quangba_enqueue_styles() {
    wp_enqueue_style('quangba-style', get_stylesheet_uri(), array(), '1.0.0');
}
add_action('wp_enqueue_scripts', 'quangba_enqueue_styles');
