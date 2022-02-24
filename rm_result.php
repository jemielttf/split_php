<?php

$dir_d = __DIR__ . '/data/upload';
$dir_r = __DIR__ . '/result';

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
