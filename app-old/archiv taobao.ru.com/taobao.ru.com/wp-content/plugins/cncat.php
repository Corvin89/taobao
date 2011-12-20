<?php
/*
Plugin Name: CNCat Plugin.
Plugin URI: http://cn-software.com/
Description: CNCat new links and articles widgets.
Author: CN-Software Ltd.
Version: 1.0
Author URI: http://cn-software.com/
*/ 

function cncat_plugin_init() {
    wp_register_sidebar_widget('cncat_newlinks', 'CNCat new links', 'cncat_plugin_widget_newlinks', array());
    wp_register_sidebar_widget('cncat_newarticles', 'CNCat new articles', 'cncat_plugin_widget_newarticles', array());
    wp_register_sidebar_widget('cncat_search', 'CNCat search', 'cncat_plugin_widget_search', array());
    wp_register_sidebar_widget('cncat_menu', 'CNCat menu', 'cncat_plugin_widget_menu', array());
}

function cncat_plugin_widget_newlinks($attrs) {
    global $CNCAT;

    $newlinks = isset($CNCAT['page']['newlinks'])
        ? $CNCAT['page']['newlinks']
        : array();

    if (empty($newlinks)) {
        return;
    }
?>
    <?php print $attrs['before_title'] . $CNCAT['lang']['new_items'] . $attrs['after_title']?>
    <ul>
    <?php foreach ($newlinks as $link):?>
        <li><a href=""><?php print $link['item_title']?></a></li>
    <?php endforeach?>
    </ul>
<?php
}

function cncat_plugin_widget_newarticles($attrs) {
    global $CNCAT;

    $newarticles = isset($CNCAT['page']['newarticles'])
        ? $CNCAT['page']['newarticles']
        : array();

    if (empty($newarticles)) {
        return;
    }

?>
    <?php print $attrs['before_title'] . $CNCAT['lang']['new_articles'] . $attrs['after_title']?>
    <ul>
    <?php foreach ($newarticles as $article):?>
        <li><a href=""><?php print $article['item_title']?></a></li>
    <?php endforeach?>
    </ul>
<?php
}

function cncat_plugin_widget_search($attrs) {
    global $CNCAT;

    if (empty($GLOBALS['CNCAT'])) {
        return;
    }
?>
    <?php print $attrs['before_title'] . $CNCAT['lang']['wp_widget_search_title'] . $attrs['after_title']?>
    <form action="<?php print $CNCAT['wp_abs']?>wp-cncat.php?act=search" method="POST">
        <input type="hidden" name="act" value="search" />
        <p>
            <input type="text" name="q" size="15" value="<?php print htmlspecialchars($CNCAT['page']['search_query'])?>" />
            <input type="submit" value="<?php print $CNCAT["lang"]["search_submit"]?>" class="submit" />
        </p>
    </form>
<?php
}

function cncat_plugin_widget_menu($attrs) {
    global $CNCAT;

    if (empty($GLOBALS['CNCAT'])) {
        return;
    }
?>
    <?php print $attrs['before_title'] . $CNCAT['lang']['wp_widget_menu_title'] . $attrs['after_title']?>
    <ul>
        <li><a href="<?php print $CNCAT['wp_abs']?>wp-cncat.php"><?php print $CNCAT["lang"]["menu_main"]?></a></li>
        <?php if (!$CNCAT['config']['add_disable']) {?>
            <li><a href="<?php print $CNCAT['wp_abs']?>wp-cncat.php?act=add"><strong><?php print $CNCAT['lang']['menu_add_item']?></strong></a></li>
        <?php }?>
        <?php if (!$CNCAT['config']['add_article_disable']) {?>
            <li><a href="<?php print $CNCAT['wp_abs']?>wp-cncat.php?act=add_article"><strong><?php print $CNCAT['lang']['menu_add_article']?></strong></a></li>
        <?php }?>
        <li><a href="<?php print 'cncat/'.$CNCAT["system"]["dir_admin"]?>"><?php print $CNCAT['lang']['menu_admin']?></a></li>
        <li><a href="<?php print $CNCAT['wp_abs']?>wp-cncat.php?act=map"><?php print $CNCAT['lang']['menu_map']?></a></li>
    </ul>
<?php
}

add_action('setup_theme', 'cncat_plugin_init');
