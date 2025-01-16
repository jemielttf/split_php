<?php

ini_set('max_execution_time', 0);
date_default_timezone_set('Asia/Tokyo');

define('CURRENT_DIR', 	__DIR__);
define('FILES_DIR', 	__DIR__ . '/data');
// define('FILES_DIR', 	'/export/www/webapp/invoice_pdf');

define('PDFtk_PATH', 	'/usr/bin/pdftk');
define('PHP_PATH', 		'/usr/local/bin/php');
// define('PDFtk_PATH', 	'/usr/bin/pdftk');
// define('PHP_PATH', 		'/usr/bin/php');