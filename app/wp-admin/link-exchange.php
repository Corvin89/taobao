<?php
require_once('admin.php'); 
$title = __('Каталог');
$parent_file = 'link-exchange.php';
$today = current_time('mysql', 1); 
require_once('admin-header.php');
include_once('magpierss/rss_fetch.inc');
?>
<div class="wrap">


 
<h2>WP Link Directory</h2>
<p>Один из немногих плагинов, для создания полноценного каталога ссылок на базе WordPress. Это один из самых мощных и удобных плагинов. Вы можете создать не только белый каталог, но и обменный каталог ссылок.
</p>
<h2>Возможности</h2>
<ul><li>Создание категорий и подкатегорий. Добавление мета-тегов и описаний для каждой в отдельности.
</li><li>Простой механизм добавления ссылок. Из панели администратора или прямо из каталога Вашими посетителями
</li><li>Легкость поиска по базе ссылок обеспечивается встроенным поиском
</li><li>Автоматическая интеграция в Вашу текущую тему (<i>не всегда корректно - ред.</i>)
</li><li>Проверка наличия обратной ссылки в автоматическом режиме
</li><li>Добавления ссылок как с обратной ссылкой, так и без нее. При последующих проверках, ссылки, добавленые как без обратной ссылки не будут представляться к удалению
</li><li>Шаблоны e-mail уведомлений
</li><li>Наличие ЧПУ. <b>Названия категорий/подкатегорий автоматически транслитеруются</b> (спасибо <a href="http://pocrasheno.ru" class='external text' title="http://pocrasheno.ru" rel="nofollow">Crash'y</a>)
</li></ul>
<h2>Установка</h2>
<ol><li> Загрузите все файлы на Ваш сервер.
</li><li> Установите права доступа "Chmod 777" для "directory/temp", "directory/temp/*all file*"
</li><li> Зайди в АдминПанель -> Плагины и активируйте плагин.
</li><li> АдминПанель -> Каталог ссылок.
</li><li> Ваш каталог готов! См. адрес блога/directory/
</li></ol>
<h2>Скачать</h2>
<p>C нашего файлового архива - <a href="http://wpwiki.ru/forum/index.php?automodule=downloads&amp;showfile=3" class='external text' title="http://wpwiki.ru/forum/index.php?automodule=downloads&amp;showfile=3" rel="nofollow">СКАЧАТЬ</a>
</p><p>Страница автора перевода -<a href="http://pocrasheno.ru/wordpress/51-wp-link-directory.html" class='external free' title="http://pocrasheno.ru/wordpress/51-wp-link-directory.html" rel="nofollow">http://pocrasheno.ru/wordpress/51-wp-link-directory.html</a>
</p><p>Автор (eng) - <a href="http://www.ebrandmarketing.com.au/wordpress-link-directory/" class='external free' title="http://www.ebrandmarketing.com.au/wordpress-link-directory/" rel="nofollow">http://www.ebrandmarketing.com.au/wordpress-link-directory/</a>
</p>
</div>
<?php
require('./admin-footer.php');
?>
