<?php

add_theme_support('post-thumbnails');

if ( function_exists('add_theme_support') ) {
    add_theme_support('post-thumbnails');
}


add_action('init', 'baner_slider');

function baner_slider()
{

    $eventlabels = array(
        'name' => 'baner_slider',
        'singular_name' => 'baner_slider',
        'add_new' => 'Добавить банер',
        'add_new_item' => 'Новый банер',
        'edit_item' => 'Добавить новый банер',
        'new_item' => 'Новый банер',
        'view_item' => 'Показать банер',
        'search_items' => 'Поиск банеров',
        'not_found' => 'Банеры не найдены',
        'not_found_in_trash' => 'Банеры в корзине не найдены',
        'parent_item_colon' => '',
        'menu_name' => 'Банеры',
        'all_items' => 'Все банеры'
    );
    $eventargs = array(
        'labels' => $eventlabels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => true,
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array('thumbnail'));

    register_post_type('baner_slider', $eventargs);
}

function the_thumb($width=200, $height=0) {
    global $post;
    $w=''; if($width) $w='&w='.$width;
    $h=''; if($height) $w='&h='.$height;
    ?>
<img alt="" src="<?php echo site_url('/')?>thumb.php?zc=0<?php echo $w?><?php echo $h?>&src=<?php $picture = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full'); echo $picture[0]; ?>">
<?php
}