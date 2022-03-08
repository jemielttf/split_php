<?php

require_once './setting.php';

echo '<link rel="stylesheet" href="style.css?v=0.0.6">' . "\n";

$dirs = array(
	FILES_DIR . '/invoice_letter',
	FILES_DIR . '/payment_notice_letter',
	CURRENT_DIR . '/log'
);


foreach($dirs as $dir) {
	if (!file_exists($dir)) {
		echo "{$dir} はありません。<br>\n";
	} else {
		exec("rm -rf {$dir}");
		echo "{$dir} を削除しました。<br>\n";
	}
}

echo "<script>\n";
echo "setTimeout(function () {\n";
echo "	location.href = './info_iframe.php';\n";
echo "}, 5000);\n";
echo "</script>\n";
