<?php

add_filter( 'show_admin_bar', '__return_false' );

add_theme_support( 'menu' );

register_nav_menus(array(
    'top' => 'Верхнее меню',

));
add_action('init', 'messages_slider');

function messages_slider()
{

    $eventlabels = array(
        'name' => 'Показать сообщения',
        'singular_name' => 'messages_slider',
        'add_new' => 'Добавить сообщение',
        'add_new_item' => 'Новое сообщение',
        'edit_item' => 'Добавить новаое сообщение',
        'new_item' => 'Новое сообщение',
        'view_item' => 'Показать сообщение',
        'search_items' => 'Search Clients',
        'not_found' =>  'No Clients Found',
        'not_found_in_trash' => 'No Clients Found in Trash',
        'parent_item_colon' => '',
        'menu_name' => 'Срочные сообщения'

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
        'supports' => array('title', 'editor', 'thumbnail'));

    register_post_type('clients',$eventargs);
}
