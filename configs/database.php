<?php
define('DB_HOST', 'db');         // <-- dùng tên service trong docker-compose
define('DB_USER', 'root');       // hoặc "myuser" nếu muốn dùng user thường
define('DB_PASSWORD', 'root123'); // trùng với MYSQL_ROOT_PASSWORD trong yml
define('DB_PORT', 3306);
define('DB_NAME', 'app_web1');   // có thể đổi theo MYSQL_DATABASE trong yml

