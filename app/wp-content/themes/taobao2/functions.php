<?php

ini_set("display_errors","0");

ini_set("display_startup_errors","0");

error_reporting(E_ALL);

include_once "functions-baner.php";
include_once "functions-video.php";
require_once "functions/functions-alexandr-kuciy.php";
require_once "functions/functions-sergey-korkyshko.php";
require_once "functions/functions-sergey-martyshko.php";
require_once "functions/functions-vladislav-fedorischev.php";
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

add_action('init', 'optionstext');

function optionstext()
{

    $eventlabels = array(
        'name' => 'Текст на главной',
        'singular_name' => 'optionstext',
        'add_new' => 'Добавить запись',
        'add_new_item' => 'Добавить запись',
        'edit_item' => 'Добавить новую запись',
        'new_item' => 'Новая запись',
        'view_item' => 'Показать',
        'search_items' => '',
        'not_found' =>  '',
        'not_found_in_trash' => '',
        'parent_item_colon' => '',
        'menu_name' => 'Настройки в главной',
        'all_items' => 'Все записи'

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

    register_post_type('optionstext',$eventargs);
}

// Load main options panel file
require_once (TEMPLATEPATH . '/functions/admin-menu.php');

 
function new_excerpt_length($length) {
	return 15;
}
add_filter('excerpt_length', 'new_excerpt_length'); 

function new_excerpt_more($more) {
return '<span class="block"><a href="'. get_permalink($post->ID) . '">' . ' Читать далее ..' . '</a></span>';
}
add_filter('excerpt_more', 'new_excerpt_more');



