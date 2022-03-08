<?php

require_once './setting.php';

$zip_path = FILES_DIR . '/' . $_POST['zip_path'];
$zip_name = $_POST['zip_name'];

$stream_size = 1024 * 5;

mb_http_output("pass");
header("Content-Type: application/zip");
header("Content-Transfer-Encoding: Binary");
header("Content-Length: " . filesize($zip_path));
header('Content-Disposition: attachment; filename*=UTF-8\'\'' . $zip_name);
header('Connection: close');

// out of memoryエラーが出る場合に出力バッファリングを無効
while (ob_get_level() > 0) {
	ob_end_clean();
}


ob_start();

// ファイル出力
if ($file = fopen($zip_path, 'rb')) {
	while (!feof($file) and (connection_status() == 0)) {
		echo fread($file, $stream_size); //指定したバイト数ずつ出力
		ob_flush();
	}
	ob_flush();
	fclose($file);
}

ob_end_clean();

// readfile($zip_path);

exit;