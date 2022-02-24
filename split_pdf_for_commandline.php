<?php
require_once 'vendor/autoload.php';

use setasign\Fpdi\Fpdi;

define("DATA_DIR", __DIR__ . '/data/');

$page_s = count($argv) == 3 ? $argv[1] : 1;
$page_e = count($argv) == 3 ? $argv[2] : 1;
$result = split_pdf(DATA_DIR . 'test_file.pdf', $page_s, $page_e, "split_file_{$page_s}-{$page_e}");

if ($result[0]) echo $result[1] . "\n";
else echo "ページ{$page_s}〜{$page_e}pのPDFの出力に成功しました。" . "\n";

function split_pdf($file_path, $start, $end, $save_file_name) {
	$pdf 			= new Fpdi();

	try {
		$total_pages 	= $pdf -> setSourceFile($file_path);			// PDFファイルを読み込む
	} catch (Exception $error) {
		return [1, $error -> getMessage()];
	}

	for ($i = $start; $i <= $end; $i++) {						
		try {
			$templateId = $pdf -> importPage($i);						// 該当ページをテンプレートとしてインポート
		} catch (Exception $error) {
			return [1, $error -> getMessage()];
		}

        $pdf -> AddPage();												// 出力用のページを一つ追加
        $pdf -> useTemplate($templateId, ['adjustPageSize' => true]);	// 出力用のページに、テンプレートを適用する
		// $pdf -> useTemplate($templateId, null, null, 0, 0, true);		// 旧バージョン用
	}

	/**
	 * I: send the file inline to the browser. The PDF viewer is used if available.
	 * D: send to the browser and force a file download with the name given by name.
	 * F: save to a local file with the name given by name (may include a path).
	 * S: return the document as a string.
	 */
	$pdf -> Output($save_file_name . '.pdf', 'F');

	return [0, ''];
}