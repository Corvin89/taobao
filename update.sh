#!sh
git stash
git pull origin master
mysql -uroot -e "drop database taobao;"
mysql -uroot -e "create database taobao;"
mysql -uroot taobao < backup/database.sql
mysql -uroot -e "UPDATE wp_options SET option_value = 'http://taobao.dev/' WHERE option_name = 'home' OR option_name = 'siteurl';" taobao
git stash pop

