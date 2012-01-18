<?php

add_action('init', 'video_slider');

function video_slider()
{

    $eventlabels = array(
        'name' => 'video_slider',
        'singular_name' => 'video_slider',
        'add_new' => 'Добавить видео',
        'add_new_item' => 'Новое видео',
        'edit_item' => 'Добавить новое видео',
        'new_item' => 'Новое видео',
        'view_item' => 'Показать видео',
        'search_items' => 'Поиск видео',
        'not_found' => 'Видео не найдены',
        'not_found_in_trash' => 'Видео в корзине не найдены',
        'parent_item_colon' => '',
        'menu_name' => 'Видео',
        'all_items' => 'Все видео'
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
        'supports' => array('title'));

    register_post_type('video_slider', $eventargs);
}