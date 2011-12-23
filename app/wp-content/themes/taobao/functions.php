<?php

add_filter( 'show_admin_bar', '__return_false' );

add_theme_support( 'menu' );

register_nav_menu(array(
    'top' => 'Верхнее меню',

));
?>