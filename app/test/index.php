<?php
$url = "http://api.jde.ru/calculator_ifframe.php";
$ch = curl_init(); // инициализируем сессию curl
curl_setopt($ch, CURLOPT_URL,$url); // указываем URL, куда отправлять POST-запрос
curl_setopt($ch, CURLOPT_FAILONERROR, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);// разрешаем перенаправление
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // указываем, что результат запроса следует передать в переменную, а не вывести на экран
curl_setopt($ch, CURLOPT_TIMEOUT, 3); // таймаут соединения
curl_setopt($ch, CURLOPT_POST, 1); // указываем, что данные надо передать именно методом POST
curl_setopt($ch, CURLOPT_POSTFIELDS, "Branch_From=13&Branch_To=109&Type=0&Weight=222&Volume=&Oversize_Weight=&Oversize_Volume=&Lathing_Volume=&Lathing_Ratio=1&add=1"); // добавляем данные POST-запроса
$result = curl_exec($ch); // выполняем запрос
curl_close($ch); // завершаем сессию
$result = mb_convert_encoding ($result ,"UTF-8" , "Windows-1251" ); //преобразуем результат в юникод



$result = explode("Полная стоимость услуг доставки - <b>", $result); //вычленяем стоимость
$st = explode(" руб</b>.</p>", $result[1]);

$stoimost = $st[0]; //записываем стоимость в переменную
$stoimost = preg_replace("/ +/i", "", $stoimost);; //удаляем лишние пробелы












?>