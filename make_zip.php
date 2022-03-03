<?php
ini_set('max_execution_time', 0);
date_default_timezone_set('Asia/Tokyo');

$year 	= $_POST['year'];
$month 	= $_POST['month'];
$type	= $_POST['type'];

class MakeZipArchive {
    protected string $year;
    protected string $month;
	protected string $type;
    
    public function __construct($year, $month, $type) {
		$this->year 	= $year;
		$this->month 	= $month;
		$this->type 	= $type;
        
        $this->init($this->year, $this->month, $this->type);
    }
    
    public function init($year, $month, $type) {
        $working_dir = "./result/{$year}/{$month}";
		$file_list = glob($working_dir . '/*');

		$pdf_list   = array();
		foreach($file_list as $filename) {
			if(preg_match("@_{$type}\.pdf$@", $filename, $m)) {
				$pdf_list[]  = $filename;
			}
		}

		print_r($pdf_list);
		echo "<br>\n";

		$zip_name = "{$type}_{$year}_{$month}.zip";
		$zip_path = $working_dir . '/' . $zip_name;

		$zip = new ZipArchive();
		// Zip ファイルをオープン
		$res = $zip->open($zip_path, ZipArchive::CREATE);

		if ($res === true) {
			foreach ($pdf_list as $value) {
				$new_name = str_replace($working_dir . '/', '', $value);
				$zip->addFile($value, $new_name);
			}
			// Zip ファイルをクローズ
			$zip->close();

			mb_http_output("pass");
			header("Content-Type: application/zip");
			header("Content-Transfer-Encoding: Binary");
			header("Content-Length: " . filesize($zip_path));
			header('Content-Disposition: attachment; filename*=UTF-8\'\'' . $zip_name);
			ob_end_clean();
			readfile($zip_path);
			// zipを削除
			unlink($zip_path);
		}
		
        exit();
    }
}

new MakeZipArchive($year, $month, $type);