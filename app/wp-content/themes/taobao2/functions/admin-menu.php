<?php
// Создать пользовательское меню
add_action('admin_menu', 'register_my_custom_submenu_page');


function register_my_custom_submenu_page() {
 add_submenu_page( 'themes.php', 'Настройка темы', 'Настройка темы', 'manage_options', 'my-custom-submenu-page', 'my_custom_submenu_page_callback' );
}

function my_custom_submenu_page_callback() {
    if(isset($_POST['settings'])) {
        unset($_POST['settings']);
        foreach($_POST as $option=>$value) {
            update_option($option, $value);
        }  
    }
	?>
    <div class="wrap">
        <h2>Настройка темы</h2>

        <form method="post" action="themes.php?page=my-custom-submenu-page">
            <input type="hidden" name="settings"> 
            <table class="form-table">

                <tr valign="top">
                    <th scope="row">Адрес в футере</th>
                    <td><textarea style="width:400px; height:100px;" name="omr_tracking_code"><?php echo get_option('omr_tracking_code');?></textarea>
                    </td>
                </tr>

            </table>

            <p class="submit">
                <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>"
                        />
            </p>


        </form>
    </div>
    <?php

}