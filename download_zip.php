<?php

require_once './setting.php';

$zip_path = FILES_DIR . '/' . $_POST['zip_path'];
$zip_name = $_POST['zip_name'];

mb_http_output("pass");
header("Content-Type: application/zip");
header("Content-Transfer-Encoding: Binary");
header("Content-Length: " . filesize($zip_path));
header('Content-Disposition: attachment; filename*=UTF-8\'\'' . $zip_name);
ob_end_clean();
readfile($zip_path);
