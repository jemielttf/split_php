<!DOCTYPE html>

<html lang="ja">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, shrink-to-fit=no">
<meta name="description" content="">

<!-- <meta name="format-detection" content="telephone=no">
<link rel="shortcut icon" href="/favicon.ico">
<link rel="apple-touch-icon" href="/apple-touch-icon.svg"> -->
<title>使い方</title>

<style>
	html {
		font-size: 10px;
	}

	body {
		margin: 0;
		padding: 1rem;
		font-size: 1.4rem;
		font-family: -apple-system,
			BlinkMacSystemFont,
			'Segoe UI',
			'Hiragino Kaku Gothic ProN',
			'Hiragino Sans',
			'BIZ UDPGothic',
			Meiryo,
			Roboto,
			sans-serif;
	}

	main h1 {
		font-size: 1.8rem;
		font-weight: 600;
		padding: 0.5em .8em;
		margin: 0;
		background-color: #e7e7e7;
	}

	main h2 {
		font-size: 1.5rem;
		font-weight: 600;
		padding: 1em 0;
		margin: 0;
		border-bottom: 1px solid #e3e3e3;
	}

	main p {
		font-size: 1.4rem;
		font-weight: 300;
		line-height: 1.8;
	}

	main dl {
		margin: 0;
		margin-top: 2.5rem;
		font-size: 1.4rem;
		display: flex;
		justify-content: flex-start;
	}

	main dl + dl {
		margin-top: 1rem;
	}

	main dl dt {
		width: 7.3em;
		font-weight: 500;
		margin: 0;
		position: relative;
		padding-right: 1em;
		box-sizing: border-box;
	}

	main dl dt::after {
		content: ':';
		display: block;
		position: absolute;
		top: 0;
		right: 0.5em;
	}

	main dl dd {
		margin: 0;
	}

	main dl dd span:nth-of-type(1) {
		display: inline-block;
		/* width: 100px; */
		margin-right: .3em;
		font-weight: 500;
	}

	main dl dd span:nth-of-type(n+2) {
		display: inline-block;
		/* width: 100px; */
		margin-left: .5em;
		font-weight: 300;
		font-size: 0.8em;
	}

	main dl dd form {
		display: inline-block;
	}

	main dl dd a.download,
	main dl dd form input[type=submit] {
		-webkit-appearance: none;
		font-size: 1.1rem;
		font-weight: 300;
		text-decoration: none;
		color: #fff;
		padding: 0.2em 1.0em;
		background-color: #0363de;
		border: none;
		border-radius: 3px;
		cursor: pointer;
		margin-left: 1em;
		transition: background .4s ease-in-out 0s;
	}

	main dl dd a.download:hover,
	main dl dd form input[type=submit]:hover {
		background-color: #0d4082;
		transition: background .2s ease-out 0s;
	}

	main dl dd a.download,
	main dl dd form input[type=submit],
	main dl dd a.download:hover,
	main dl dd form input[type=submit]:hover {
		background: #00bfff;
		text-shadow: 0 1px 2px rgba(0, 0, 0, 0.27);
		border: 1px solid rgba(255, 255, 255, 0.7);
		transition: none;
	}

	main .pre {
		display: inline-block;
		padding: 1.4rem 2rem;
		border-radius: 1rem;
		border: 1px solid #e7e7e7;
		background-color: #efefef;
		vertical-align: top;
		width: 250px;
	}

	main .pre + .pre {
		margin-left: 2rem;
	}

	main .pre h2 {
		font-size: 1.3rem;
		font-weight: 600;
		line-height: 1;
		margin: 0;
		margin-bottom: .8em;
	}

	a {
		display: inline-block;
		font-size: .85em;
		font-weight: 300;
		color: rgb(27, 92, 166);
	}

	main .pre pre {
		font-family: monospace;
		font-size: 1.1rem;
		line-height: 1.6;
		margin: 0;
		padding: 0;
		color: #555;
	}
</style>

</head>


<body>

<main>

<?php

require_once './setting.php';
require_once './utils.php';

// $cmd = "ps ax | grep split_pdf_for_commandline.php | grep -v grep";
// exec("export LANG=ja_JP.UTF-8; " . $cmd, $output, $result);

$log_dirs = glob('./log/*', GLOB_ONLYDIR);
$log_list   = array();

foreach($log_dirs as $dir) {
	$files = get_newest_logs($dir);
	$log_list = array_merge($log_list, $files);
}

if (count($log_list) > 0) {
	echo "<h1>実行結果</h1>\n";

	$task_array = array();

	foreach($log_list as $file_path) {
		$proc_status 	= load_csv_data($file_path, 'csv', false);
		$proc_status 	= $proc_status[0];

		$time			= $proc_status[0];
		$type			= $proc_status[1];
		$year 			= $proc_status[2];
		$month			= $proc_status[3];
		$status			= $proc_status[4];
		
		$month_str 		= "{$year}_{$month}";

		if (empty($task_array[$type])) $task_array[$type] = array();
		if (empty($task_array[$type][$month_str])) $task_array[$type][$month_str] = array();
		
		$task_array[$type][$month_str][] = array(
			'time' 		=> $time,
			'status' 	=> $status
		);
	}

	// print_r($task_array);
	// echo "<br>\n";

	foreach($task_array as $type => $month_array) {
		$label_type	 =     $type == 'invoice_letter' 			? '請求書'
						: ($type == 'payment_notice_letter'		? '支払い通知書'
																: '3個目の書類');
		$count 			= 0;
		$is_finish 		= false;
		$time_stamps 	= array();
		$month_label	= null;

		foreach($month_array as $month_str => $month_data) {
			$count++;

			if (empty($month_label)) {
				$month_label = explode('_', $month_str);
			}

			$fin_count = 0;
			foreach($month_data as $params) {
				if ($params['status'] == 'fin') $fin_count++;
				$time_stamps[] = new DateTime($params['time']);
			}

			$is_finish = $fin_count == count($month_data) ? true : false;
		}

		

		$status_time = '';
		foreach($time_stamps as $time) {
			if (empty($status_time)) $status_time = $time;
			else $status_time = $status_time < $time ? $time : $status_time;
		}
		$status_time = $is_finish ? $status_time : new DateTime();
		$time_str = $status_time->format('Y.m.d H:i:s');

		echo 	'<dl><dt>' . 
				($count == 1 ? $label_type : '') . 
				"</dt><dd><span>{$month_label[0]}年{$month_label[1]}月分</span>のPDF分割" . 
				($is_finish ? 'は完了しました。' : 'を処理中です。') .
				"<span>({$time_str})</span>";

		if ($is_finish) {
			$working_dir 	= FILES_DIR . "/{$type}/{$month_label[0]}_{$month_label[1]}/";
			$zip_name 		= "{$type}-{$month_label[0]}_{$month_label[1]}.zip";
			$zip_path 		= $working_dir . $zip_name;
			$zip_param_path = explode(FILES_DIR, $zip_path);
			$zip_param_path = $zip_param_path[1];

			$zip_list = glob($zip_path . '*');
			if (empty($zip_list)) {
				$php_path		= PHP_PATH;
				$cmd = "nohup {$php_path} ./make_zip.php '{$month_label[0]}' '{$month_label[1]}' '{$type}' >> ./log/make_zip.log 2>&1 &";
				exec("export LANG=ja_JP.UTF-8; " . $cmd, $output, $result);
				echo "<span> (zipファイルを作成中です。)</span>";

				@ob_flush();
				@flush();
			} else {
				if (file_exists($zip_path) ) {
					$form_str  = 	"<form method='POST' action='./download_zip.php'>";
					$form_str .= 	"<input type='hidden' name='zip_path' value='$zip_param_path'>";
					$form_str .= 	"<input type='hidden' name='zip_name' value='{$zip_name}'>";
					$form_str .= 	"<input type='submit' value='ZIPダウンロード'>";
					$form_str .= 	"</form>";

					echo $form_str;
				} else {
					echo "<span> (zipファイルを作成中です。)</span>";
				}
			}

			if (
				file_exists('./LedgerSheetFileList.php') &&
				!file_exists($working_dir . '.db_registered')
			) {
				$split_type = explode('_', $type);
				array_splice($split_type, count($split_type) - 1, 1);

				$sheet_type = implode('_', $split_type);
				// echo "call : new LedgerSheetFileList('{$sheet_type}');\n";

				// require_once './LedgerSheetFileList.php';
				// new LedgerSheetFileList($sheet_type);
				file_put_contents($working_dir . '.db_registered', "[{$time_str}] {$type}-{$month_label[0]}_{$month_label[1]}", LOCK_EX);
			}
		}

		echo 	"</dd></dl>\n";
	}

	echo "<br><br>\n";

	echo "<script>\n";
	echo "setTimeout(function () {\n";
	echo "	location.reload();\n";
	echo "}, 30000);\n";
	echo "</script>\n";
}


function get_newest_logs($dir) {        
	$files = glob($dir . '/*_status.log');

	if (empty($files)) return array();

	$newest_file_date = null;
	$newest_file = null;
	foreach($files as $file) {
		$file_date = filemtime($file);
		
		if (empty($newest_file_date)) {
			$newest_file_date = $file_date;
			$newest_file = $file;
		} else {
			if ($newest_file_date < $file_date) {
				$newest_file_date = $file_date;
				$newest_file = $file;
			}
		}
	}

	// echo date('Y-m-d H:i:s', $newest_file_date) . " ({$newest_file})<br>\n";

	$file_proc_date = explode('-', $newest_file);
	$file_proc_date = explode('_', $file_proc_date[1]);
	$file_proc_date = "{$file_proc_date[0]}_{$file_proc_date[1]}";

	$proc_files = array();
	foreach($files as $file) {
		if (preg_match("@{$file_proc_date}@", $file)) $proc_files[] = $file;
	}

	return $proc_files;
}

function get_large_num_dir($dirs) {
	$dir_num = 0;

	foreach($dirs as $dirname) {
		preg_match('@(\d+)$@', $dirname, $m);
		$dir_num       = (int)$m[1] > (int)$dir_num ? $m[1] : $dir_num;
	}
	return $dir_num;
}

?>

<!--
	<h1>使い方</h1>
	
	<p>複数ページのPDFとページ分割を指定したCSV又はTSVをアップロードし<br>
	分割データの保存先を年月を入力してください。</p>

	<h2>実行結果の表示について</h2>

	<p>「請求書」又は「支払い通知書」それぞれについて最後に実行された月の結果を表示します。<br>
	また分割処理が完了している場合、zipファイルを作成しダウンロード出来るようになっています。
	</p>

	<h2>サンプルデータついて</h2>

	<p>テストで使用しているPDF <a href="./data/test_file.pdf" target="_blank">（ダウンロード）</a></p>
	
	<p><s>テストで使用しているCSVのフォーマットは「会員番号, 分割開始ページ, 分割終了ページ」になります。</s><br>
	TTCさん側で出力されたCSVがそのまま使用出来ます。</p>

	<article class="pre">
		<h2>sample_split_data.csv <a href="./data/sample_split_data.csv" target="_blank">（ダウンロード）</a></h2>

<pre>MemberCd,start,end,pages,
00001,1,4,4,
00002,5,6,2,
00003,7,9,3,
00004,10,13,4,
00005,14,16,3,
00006,17,20,4,
00007,21,22,2,
00008,23,24,2,
00009,25,28,4,
00010,29,31,3,
00011,32,33,2,
00012,34,35,2,
00013,36,37,2,
00014,38,40,3,
00015,41,44,4,
00016,45,46,2,
00017,47,48,2,
00018,49,50,2,</pre>			
		</article>

		<article class="pre">
			<h2>sample_split_data.tsv <a href="./data/sample_split_data.tsv" target="_blank">（ダウンロード）</a></h2>
	
<pre>MemberCd	start	end	pages	
00001	1	1	1	
00002	2	4	3	
00003	5	7	3	
00004	8	10	3	
00005	11	14	4	
00006	15	16	2	
00007	17	18	2	
00008	19	21	3	
00009	22	24	3	
00010	25	28	4	
00011	29	31	3	
00012	32	33	2	
00013	34	35	2	
00014	36	37	2	
00015	38	40	3	
00016	41	42	2	
00017	43	44	2	
00018	45	47	3	
00019	48	49	2	
00020	50	50	1	</pre>			
			</article>
-->

</main>

</body>

</html>