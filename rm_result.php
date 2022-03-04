<?php

echo '<link rel="stylesheet" href="style.css?v=0.0.5">' . "\n";

$dirs = array(
	__DIR__ . '/data/upload',
	__DIR__ . '/data/invoice_letter',
	__DIR__ . '/data/payment_notice_letter',
	__DIR__ . '/result',
	__DIR__ . '/log'
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
