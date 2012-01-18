<?php
register_taxonomy('video_slider-category',
    array(
        0 => 'video_slider',
    ),
    array('hierarchical' => true,
        'label' => 'Categories',
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'Slug'),
        'singular_label' => 'Category'
    )
);