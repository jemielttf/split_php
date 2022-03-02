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
		width: 8em;
		margin: 0;
		position: relative;
		padding-right: 1.5em;
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
		margin-right: .5em;
		font-weight: 500;
	}

	main dl dd span:nth-of-type(2) {
		display: inline-block;
		/* width: 100px; */
		margin-left: .5em;
		font-weight: 300;
		font-size: 0.8em;
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
date_default_timezone_set('Asia/Tokyo');

$cmd = "ps -ax | grep split_pdf_for_commandline.php | grep -v grep";
exec("export LANG=ja_JP.UTF-8; " . $cmd, $output, $result);


$filelist = get_newest_dir_logs('./log');

$log_list   = array();
foreach($filelist as $filename) {
	if(preg_match('@_status\.log$@', $filename, $m)) {
		$log_list[]  = $filename;
	}
}

if (count($log_list) > 0) {
	echo "<h1>実行中のプロセス</h1>\n";

	$task_array = array();

	foreach($log_list as $file_path) {
		$split_file_path 	= explode('/', $file_path);
		$length 			= count($split_file_path);

		$month	= $split_file_path[$length - 3] . '_' . $split_file_path[$length - 2];
		$name	= $split_file_path[$length - 1];
		$type	= explode('_', $name);
		$type	= $type[0];

		if (empty($task_array[$month])) $task_array[$month] = array();
		if (empty($task_array[$month][$type])) $task_array[$month][$type] = array();
		
		$task_array[$month][$type][] = $file_path;
	}

	foreach($task_array as $key => $month_data) {
		$split_key = explode('_', $key);
		$label_month = $split_key[0] . '年' . $split_key[1] . '月分';
		$count = 0;

		foreach($month_data as $type => $log_list) {
			$count++;

			$label_type =  $type == 'invoice-letter' 			? '請求書'
						: ($type == 'payment-notice-letter'
																? '支払い通知書'
																: '3個目の書類');

			
			$fin_count = 0;
			$is_finish = false;
			$time_stamps = array();

			foreach($log_list as $path) {
				$proc_status = load_csv_data($path);
				$proc_status = $proc_status[0];
				if ($proc_status[1] == 'fin') $fin_count++;
				$time_stamps[] = new DateTime($proc_status[0]);
			}
			$is_finish = $fin_count == count($log_list) ? true : false;

			$status_time = '';
			foreach($time_stamps as $time) {
				if (empty($status_time)) $status_time = $time;
				else $status_time = $status_time < $time ? $time : $status_time;
			}
			$status_time = $is_finish ? $status_time : new DateTime();
			$time_str = $status_time -> format('Y.m.d H:i:s');

			echo 	'<dl><dt>' . 
					($count == 1 ? $label_month : '') . 
					"</dt><dd><span>{$label_type}のPDF分割</span>" . 
					($is_finish ? 'は完了しました。' : 'を処理中です。') .
					"<span>({$time_str})</span></dd></dl>\n";
		}
	}

	echo "<br><br>\n";

	// echo "<script>\n";
	// echo "setTimeout(function () {\n";
	// echo "	location.reload();\n";
	// echo "}, 3000);\n";
	// echo "</script>\n";
}


function get_newest_dir_logs($dir) {        
	$filenames          = glob($dir . '/*', GLOB_ONLYDIR);

	$target_dirnames    = array();
	$last_dirname       = null;

	foreach($filenames as $filename) {
		$target_dirnames[]  = $filename;
	}

	// 最大インデックス付きの親ディレクトリを特定
	$dir_num_y = get_large_num_dir($target_dirnames);
	$year_dirname = $dir . '/' . $dir_num_y;

	$filenames = glob($year_dirname . '/*', GLOB_ONLYDIR);
	$target_dirnames    = array();

	foreach($filenames as $filename) {
		if(strpos($filename, $year_dirname) === 0) {
			$target_dirnames[]  = $filename;
		}
	}

	// 最大インデックス付きの親ディレクトリを特定
	$dir_num_m = get_large_num_dir($target_dirnames);
	$last_dirname   = $year_dirname . '/' . $dir_num_m;

	// 最大インデックス付きの親ディレクトリの中のファイルリスト
	$file_list      = glob($last_dirname . '/*');

	return $file_list;
}

function get_large_num_dir($dirs) {
	$dir_num = 0;

	foreach($dirs as $dirname) {
		preg_match('@(\d+)$@', $dirname, $m);
		$dir_num       = (int)$m[1] > (int)$dir_num ? $m[1] : $dir_num;
	}
	return $dir_num;
}

function load_csv_data($file_xsv, $type = 'csv') {
	$file = new SplFileObject($file_xsv, 'r');
	$file -> setFlags(SplFileObject::READ_CSV);
	if ($type == 'tsv') $file -> setCsvControl("\t");

	$array = array();

	$count = 0;
	foreach ($file as $row) {
		$count++;
		if (!is_null($row[0])) array_push($array, $row);
	}

	return $array;
}

?>


	<h1>使い方</h1>
	
	<p>複数ページのPDFとページ分割を指定したCSV又はTSVをアップロードし<br>
	分割データの保存先を年月を入力してください。</p>

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
	

</main>

</body>

</html>