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

Windows
-------

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


Ubuntu
------

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

Запустить терминал;

Перейти в папку с проектом `cd /var/www/taobao/`;

Выполнить команду `sudo chmod -R 777 .`, что даст все права доступа к папке с проектом;

Зайти в админку на страницу `Параметры -> Постоянные ссылки` и нажать `Сохранить изменения` для того чтобы обновить `.htaccess`;

В терминале:
```
sodo a2enmod rewrite
sudo service apache2 restart
cd /etc/apache2/sites-enabled/
```
Открыть файл `000-default` любым редактором с правами `root`;

Внести изменения:
```
	...
5 |	<Directory />
6 |     	Options FollowSymLinks
7 |     	AllowOverride None 	`заменить на`	AllowOverride All
8 |     </Directory>
9 |     <Directory /var/www/>
10|     	Options Indexes FollowSymLinks MultiViews
11|     	AllowOverride None	`заменить на`	AllowOverride All
12|     	Order allow,deny
13|     	allow from all
14|     </Directory>
	...
```
Сохранить изменения и выйти;

Выполнить в терминале `sudo service apache2 restart`
