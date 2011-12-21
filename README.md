taobao.ru.com
=============

![Скриншот главнй страницы](https://github.com/web4life/taobao/blob/master/design/home.png?raw=true)


Требования
-----------

* Apache 2.2.21
* MySQL 5.5.16
* Wordpress 3.2.1

Установка
----------

Создать базу данных `<имя_базы_данных>`

Импортировать базу из [backup/database.sql](https://github.com/web4life/taobao/blob/master/backup/database.sql)

Изменить url в базе данных командой `UPDATE wp_options SET option_value = '<урл_проекта>' WHERE option_name = 'home' OR option_name = 'siteurl';`

Скопировать [wp-config-sample.php](https://github.com/web4life/taobao/blob/master/app/wp-config-sample.php) в `wp-config.php`

Изменить в `wp-config.php` следующие строки:

```

/** Имя базы данных для WordPress */
define('DB_NAME', '<имя_базы_данных>');

/** Имя пользователя MySQL */
define('DB_USER', '<имя_пользователя>');

/** Пароль к базе данных MySQL */
define('DB_PASSWORD', '<пароль_пользователя>');

```

Зайти в админку на страницу `Параметры -> Постоянные ссылки` и нажать `Сохранить изменения` для того чтобы обновить `.htaccess`


[Настройки Apache для Wordpress](https://github.com/web4life/taobao/wiki/%D0%9D%D0%B0%D1%81%D1%82%D1%80%D0%BE%D0%B9%D0%BA%D0%B8-Apache-%D0%B4%D0%BB%D1%8F-Wordpress)
--------------------------------