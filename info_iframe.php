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
		font-size: 1.6rem;
		display: flex;
		justify-content: flex-start;
	}

	main dl + dl {
		margin-top: 1rem;
	}

	main dl dt {
		width: 130px;
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

	main dl dd span {
		display: inline-block;
		/* width: 100px; */
		margin-right: .5em;
		font-weight: 500;
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

$cmd = "ps -ax | grep split_pdf_for_commandline.php | grep -v grep";
exec("export LANG=ja_JP.UTF-8; " . $cmd, $output, $result);

// $output = array(
// 	"4510 ??         0:00.12 /usr/local/bin/php ./split_pdf_for_commandline.php /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_0_1-1114.pdf /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_0_1-1114.csv invoice-letter cvs 2022 01",
// 	"4512 ??         0:00.12 /usr/local/bin/php ./split_pdf_for_commandline.php /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_1_1115-2208.pdf /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_1_1115-2208.csv invoice-letter cvs 2022 01",
// 	"4514 ??         0:00.12 /usr/local/bin/php ./split_pdf_for_commandline.php /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_2_2209-3309.pdf /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_2_2209-3309.csv invoice-letter cvs 2022 01",
// 	"4516 ??         0:00.12 /usr/local/bin/php ./split_pdf_for_commandline.php /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_3_3310-4394.pdf /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_3_3310-4394.csv invoice-letter cvs 2022 01",
// 	"4518 ??         0:00.12 /usr/local/bin/php ./split_pdf_for_commandline.php /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_4_4395-5491.pdf /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_4_4395-5491.csv invoice-letter cvs 2022 01",
// 	"4520 ??         0:00.12 /usr/local/bin/php ./split_pdf_for_commandline.php /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_5_5492-6589.pdf /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_5_5492-6589.csv invoice-letter cvs 2022 01",
// 	"4522 ??         0:00.12 /usr/local/bin/php ./split_pdf_for_commandline.php /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_6_6590-7701.pdf /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_6_6590-7701.csv invoice-letter cvs 2022 01",
// 	"4524 ??         0:00.12 /usr/local/bin/php ./split_pdf_for_commandline.php /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_7_7702-8815.pdf /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_7_7702-8815.csv invoice-letter cvs 2022 01",
// 	"4526 ??         0:00.14 /usr/local/bin/php ./split_pdf_for_commandline.php /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_8_8816-9699.pdf /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_8_8816-9699.csv invoice-letter cvs 2022 01",
// 	"6090 ??         0:00.06 /usr/local/bin/php ./split_pdf_for_commandline.php /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_0_1-2258.pdf /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_0_1-2258.csv payment-notice-letter cvs 2022 01",
// 	"6093 ??         0:00.06 /usr/local/bin/php ./split_pdf_for_commandline.php /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_1_2259-4427.pdf /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_1_2259-4427.csv payment-notice-letter cvs 2022 01",
// 	"6096 ??         0:00.06 /usr/local/bin/php ./split_pdf_for_commandline.php /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_2_4428-6546.pdf /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_2_4428-6546.csv payment-notice-letter cvs 2022 01",
// 	"6098 ??         0:00.07 /usr/local/bin/php ./split_pdf_for_commandline.php /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_3_6547-8584.pdf /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_3_6547-8584.csv payment-notice-letter cvs 2022 01",
// 	"6100 ??         0:00.07 /usr/local/bin/php ./split_pdf_for_commandline.php /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_4_8585-10615.pdf /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_4_8585-10615.csv payment-notice-letter cvs 2022 01",
// 	"6102 ??         0:00.09 /usr/local/bin/php ./split_pdf_for_commandline.php /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_5_10616-11804.pdf /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_5_10616-11804.csv payment-notice-letter cvs 2022 01",
// 	"4510 ??         0:00.12 /usr/local/bin/php ./split_pdf_for_commandline.php /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_0_1-1114.pdf /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_0_1-1114.csv invoice-letter cvs 2022 02",
// 	"4512 ??         0:00.12 /usr/local/bin/php ./split_pdf_for_commandline.php /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_1_1115-2208.pdf /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_1_1115-2208.csv invoice-letter cvs 2022 02",
// 	"4514 ??         0:00.12 /usr/local/bin/php ./split_pdf_for_commandline.php /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_2_2209-3309.pdf /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_2_2209-3309.csv invoice-letter cvs 2022 02",
// 	"4516 ??         0:00.12 /usr/local/bin/php ./split_pdf_for_commandline.php /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_3_3310-4394.pdf /Users/jun/Sites/TTC/split_php/data/upload/2022/01/tmp_3_3310-4394.csv invoice-letter cvs 2022 02",
// );

if (count($output) > 0) {
	echo "<h1>実行中のプロセス</h1>\n";

	$proc_array = array();

	foreach ($output as $key => $ps) {
		// echo "<p>{$ps}</p>\n";

		$ps_array = explode(' ', trim($ps));
		// print_r($ps_array);
		// echo "<br>\n";
		
		$proc_id 	= $ps_array[0];
		$proc_time	= $ps_array[10];
		$proc_type 	= $ps_array[15];
		$month		= $ps_array[17] . '_' . $ps_array[18];

		// $proc_id 	= $ps_array[0];
		// $proc_time	= $ps_array[10];
		// $proc_type 	= $ps_array[20];
		// $month		= $ps_array[22] . '_' . $ps_array[23];

		// echo "<p>{$year}/{$month}分 {$proc_type} : {$proc_time}経過 (ID : {$proc_id})</p>\n";

		if (empty($proc_array[$month])) $proc_array[$month] = array();
		if (empty($proc_array[$month][$proc_type])) $proc_array[$month][$proc_type] = 1;
		else $proc_array[$month][$proc_type]++;
	}

	foreach($proc_array as $key => $month_data) {
		$split_key = explode('_', $key);
		$label_month = $split_key[0] . '年' . $split_key[1] . '月分';
		$count = 0;

		foreach($month_data as $type => $value) {
			$count++;

			$label_type =  $type == 'invoice-letter' 			? '請求書'
						: ($type == 'payment-notice-letter'
																? '支払い通知書'
																: '3個目の書類');
			echo '<dl><dt>' . ($count == 1 ? $label_month : '') . "</dt><dd><span>{$label_type}のPDF分割</span>を処理中です。</dd></dl>\n";
		}
	}

	echo "<br><br>\n";

	echo "<script>\n";
	echo "setTimeout(function () {\n";
	echo "	location.reload();\n";
	echo "}, 3000);\n";
	echo "</script>\n";
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