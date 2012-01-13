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


$prefix = "video_slider_";

$info_box = array(
    'id' => 'video_slider-meta-box',
    'title' => 'Ссылка на видео:',
    'page' => 'video_slider',
    'context' => 'normal',
    'priority' => 'high',
    'fields' => array(
        array(
            'name' => 'Youtube ',
            'desc' => '',
            'id' => 'Youtube',
            'type' => 'text',
            'std' => ''
        )

//        array(
//            'name' => 'Vimeo ',
//            'desc' => '',
//            'id' => 'Vimeo',
//            'type' => 'text',
//            'std' => ''
//        )
    )
);



add_action('admin_menu', 'event_add_box');

// Add meta box
function event_add_box() {
    global $info_box;



    $meta_box = $info_box;
    add_meta_box($meta_box['id'], $meta_box['title'], 'event_show_box_info', $meta_box['page'], $meta_box['context'], $meta_box['priority']);


}




function event_show_box_info() {
    global $info_box;
    event_show_box($info_box, 'event_meta_box_info_nonce');
}

// Callback function to show fields in meta box
function event_show_box($meta_box, $nonce) {
    global $post;

    // Use nonce for verification
    echo '<input type="hidden" name="'.$nonce.'" value="', wp_create_nonce(basename(__FILE__)), '" />';

    echo '<table class="form-table">';

    foreach ($meta_box['fields'] as $field) {
        // get current post meta data
        $meta = get_post_meta($post->ID, $field['id'], true);

        echo '<tr>',
        '<th style="width:20%"><label for="', $field['id'], '">', $field['name'], '</label></th>',
        '<td>';
        switch ($field['type']) {
            case 'text':
                echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:97%" />',
                '<br />', $field['desc'];
                break;
            case 'textarea':
                echo '<textarea name="', $field['id'], '" id="', $field['id'], '" cols="60" rows="4" style="width:97%">', $meta ? $meta : $field['std'], '</textarea>',
                '<br />', $field['desc'];
                break;
            case 'select':
                echo '<select name="', $field['id'], '" id="', $field['id'], '">';
                foreach ($field['options'] as $option) {
                    echo '<option', $meta == $option ? ' selected="selected"' : '', '>', $option, '</option>';
                }
                echo '</select>';
                break;
            case 'radio':
                foreach ($field['options'] as $option) {
                    echo '<input type="radio" name="', $field['id'], '" value="', $option['value'], '"', $meta == $option['value'] ? ' checked="checked"' : '', ' />', $option['name'];
                }
                break;
            case 'checkbox':
                echo '<input type="checkbox" name="', $field['id'], '" id="', $field['id'], '"', $meta ? ' checked="checked"' : '', ' />';
                break;
            case 'datepicker':
                echo '<input type="text" class="datepicker" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:97%" />',
                '<br />', $field['desc'];
                break;
        }
        echo 	'<td>',
        '</tr>';
    }

    echo '</table>';
}

add_action('save_post', 'event_save_data');

function event_save_data($post_id) {
    global $info_box, $prefix;

    event_save_data_type($post_id, $info_box, 'event_meta_box_info_nonce');
    event_save_data_type($post_id, null, 'event_meta_box_video_nonce');
}

// Save data from meta box
function event_save_data_type($post_id, $meta_box, $nonce) {
    global $prefix;
    // verify nonce
    if (!wp_verify_nonce($_POST[$nonce], basename(__FILE__))) {
        return $post_id;
    }

    // check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    // check permissions
    if ('page' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id)) {
            return $post_id;
        }
    } elseif (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }
    if($nonce == 'event_meta_box_video_nonce') {
        $count = 1;

        if(isset($_POST[$prefix . 'event_video_count']))
            $count = $_POST[$prefix . 'event_video_count'];

        $number = 1;
        $delta = 0;
        $values = array();
        do {
            $old = get_post_meta($post_id, $prefix . 'event_video_'.$number, true);
            if(isset($_POST[$prefix . 'event_video_'.$number])) {
                $new = $_POST[$prefix . 'event_video_'.$number];
                $new = str_replace('http://vimeo.com/', '', $new);
            } else
                $new = '';

            if ($new && !in_array($new, $values)) {
                $values[] = $new;
                update_post_meta($post_id, $prefix . 'event_video_'.($number-$delta), $new);
                update_post_meta($post_id, 'thumb-'.$prefix . 'event_video_'.($number-$delta), $_POST['thumb-'.$prefix . 'event_video_'.$number]);
                if($delta) {
                    delete_post_meta($post_id, $prefix . 'event_video_'.$number, $old);
                    delete_post_meta($post_id, 'thumb-'.$prefix . 'event_video_'.$number);
                }
            } else {
                delete_post_meta($post_id, $prefix . 'event_video_'.$number, $old);
                delete_post_meta($post_id, 'thumb-'.$prefix . 'event_video_'.$number);
                $delta++;
            }

            $number++;
        } while($number <= $count);

        update_post_meta($post_id, $prefix . 'event_video_count', $number-$delta-1);

    } else {
        foreach ($meta_box['fields'] as $field) {
            $old = get_post_meta($post_id, $field['id'], true);
            $new = $_POST[$field['id']];

            if ($new && $new != $old) {
                update_post_meta($post_id, $field['id'], $new);
            } elseif ('' == $new && $old) {
                delete_post_meta($post_id, $field['id'], $old);
            }
        }
    }
}