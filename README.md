# sklad
Целью проекта было создание веб-поиска по артикулу автозапчастей по складу или нескольким подключенным прайсам поставщиков по аналогии с поиском интернет-магазинов автозапчастей.

Проект разрабатывался с июня по август 2017 года.
Цель была достигнута в отношении поиска через артикулы неоригинальных производителей запчастей. 
Реализовано на примере производителя PROFIT (ссылка в меню "Поиск по неоригинальным кроссам TecDoc"). (Тестировалось на бОльшем количестве производителей). Можно использовать артикул 1540-2622, 1512-0706, 1014-2075 или любой другой этой фирмы. Допускается любое количество случайно поставленных (или не поставленных вообще) тире, пробелов и других знаков. Также было протестировано поведение на примере "множественности" фирм, когда один и тот же артикул используется разными производителями (артикул C809).

Входом на сайт является URL путь sklad/public/ или sklad/public/index.php.
По ссылке "Log in" вход могут получить user (с паролем user), manager (с паролем manager), по пути в URL sklad/public/adm/login_admin.php доступ может получить администратор (логин admin, пароль admin).
У каждого уровня пользователей своя мера отображения информации о вбиваемом артикуле и служебной информации. (Правильное отображение цен для разных пользователей не прорабатывалось до конца и применялось только для демонстрации).

Поиск по оригинальным номерам реализован не был.
Перед заморозкой проекта больше всего работы велось над файлом sklad/public/adm/tecdoc_poisk_admin.php.
Проект был заморожен из-за недостатков, определенных в процессе работы:
1. Процедурный код. Для такого проекта, вероятно, нужен подход с ООП.
2. Вытекающая отсюда нарастающая сложность кода, повторяемость кода, сложность в развитии и дальнейшей поддержке всего веб-приложения.
3. Необходимость выделения на хостинге под такой проект (если делать его всесторонне по всем автопроизводителям и с возможностью подключения многих поставщиков) большого места под базу данных: ориентировочно около 20 ГБ или более.
4. Нехватка знаний.

Тем не менее, к общим плюсам можно отнести (особенно для такого новичка, как я):
1. Создание поиска с перекрёстными кросс-номерами по аналогии с поиском в интернет-магазинах автозапчастей.
2. Различные хорошие явные и скрытые возможности поиска: 
	-	возможность ошибаться при вставке артикула, указывая лишние тире, пробелы, случайные знаки, или не проставляя никаких дополнительных знаков, оставляя артикул без пробелов и тире - результат будет один и тот же.
	-	защищенность поиска (htmlentities, htmlspecialchars, mysqli_real_escape_string).
3. Относительно простая база данных и простые запросы к базе данных.