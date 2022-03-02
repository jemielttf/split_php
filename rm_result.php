<?php

echo '<link rel="stylesheet" href="style.css?v=0.0.5">' . "\n";

$dir_d = __DIR__ . '/data/upload';
$dir_r = __DIR__ . '/result';
$dir_l = __DIR__ . '/log';

if (!file_exists($dir_d)) {
    echo "{$dir_d} はありません。<br>\n";
} else {
	exec("rm -rf {$dir_d}");
	echo "{$dir_d} を削除しました。<br>\n";
}

if (!file_exists($dir_r)) {
    echo "{$dir_r} はありません。<br>\n";
} else {
	exec("rm -rf {$dir_r}");
	echo "{$dir_r} を削除しました。<br>\n";
}

// if (!file_exists($dir_l)) {
//     echo "{$dir_l} はありません。<br>\n";
// } else {
// 	exec("rm -rf {$dir_l}");
// 	echo "{$dir_l} を削除しました。<br>\n";
// }

echo "<script>\n";
echo "setTimeout(function () {\n";
echo "	location.href = './info_iframe.php';\n";
echo "}, 5000);\n";
echo "</script>\n";
