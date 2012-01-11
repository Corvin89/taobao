<?php
include_once "functions-baner.php";

add_filter( 'show_admin_bar', '__return_false' );

add_theme_support( 'menu' );

register_nav_menus(array(
    'top' => 'Верхнее меню',

));



add_action('init', 'messages_slider');

function messages_slider()
{

    $eventlabels = array(
        'name' => 'Cрочные сообщения',
        'singular_name' => 'messages_slider',
        'add_new' => 'Добавить срочное сообщение',
        'add_new_item' => 'Добавить срочное сообщение',
        'edit_item' => 'Добавить новое срочное сообщение',
        'new_item' => 'Новое срочное сообщение',
        'view_item' => 'Показать',
        'search_items' => '',
        'not_found' =>  '',
        'not_found_in_trash' => '',
        'parent_item_colon' => '',
        'menu_name' => 'Cрочные сообщения',
        'all_items' => 'Все срочные сообщения'

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
        'supports' => array('title', 'editor'));

    register_post_type('messages_slider',$eventargs);
}