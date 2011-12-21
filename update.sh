#!sh
git stash
git pull origin master
mysql -uroot -proot -e "drop database taobao;"
mysql -uroot -proot -e "create database taobao;"
mysql -uroot -proot taobao < backup/database.sql
mysql -uroot -proot -e "UPDATE wp_options SET option_value = 'http://localhost/taobao/app' WHERE option_name = 'home' OR option_name = 'siteurl';" taobao
git stash pop

