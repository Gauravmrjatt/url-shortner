<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'sql_osmarmy_com');
define('DB_USER', 'sql_osmarmy_com');
define('DB_PASS', '6858ce741b616');

define('BASE_URL', 'https://osmarmy.com');
define('SHORT_CODE_LENGTH', 6);

define('ENABLE_API_KEY', false);
define('RATE_LIMIT', 100);

define('ADMIN_PASSWORD', 'Ahfhfhfwjejbf');

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_log('/tmp/urlshortner_errors.log');

date_default_timezone_set('UTC');
