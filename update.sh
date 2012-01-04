#!sh
mysql -uadmin -padmin -e "drop database taobao;"
mysql -uadmin -padmin -e "create database taobao;"
mysql -uadmin -padmin taobao < backup/database.sql
mysql -uadmin -padmin -e "UPDATE wp_options SET option_value = 'http://taobao.dev/' WHERE option_name = 'home' OR option_name = 'siteurl';" taobao
#git stash pop

