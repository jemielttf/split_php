<?php

require_once './setting.php';

$year 	= $argv[1];
$month 	= $argv[2];
$type	= $argv[3];

define('DATA_BASE', 	"/{$type}/{$year}_{$month}");
define('RESULT_DIR', 	FILES_DIR . DATA_BASE . '/members/');
define('ZIP_DIR', 	FILES_DIR . DATA_BASE . '/');
define('LOG_DIR', 		CURRENT_DIR . "/log/");

class MakeZipArchive {
    protected $year;
    protected $month;
	protected $type;
    
    public function __construct($year, $month, $type) {
		$this->year 	= $year;
		$this->month 	= $month;
		$this->type 	= $type;
        
        $this->init($this->year, $this->month, $this->type);
    }
    
    public function init($year, $month, $type) {
        $working_dir = RESULT_DIR;
		$file_list = glob($working_dir . '/*');

		$pdf_list   = array();
		foreach($file_list as $filename) {
			if(preg_match("@\.pdf$@", $filename, $m)) {
				$pdf_list[]  = $filename;
			}
		}

		$zip_name = "{$type}-{$year}_{$month}.zip";
		$zip_path = ZIP_DIR . $zip_name;

		echo 'PDF count : ' . count($pdf_list) . "\n";
		echo $zip_path."\n";
		echo "----------------------------\n";

		$zip = new ZipArchive();
		// Zip ファイルをオープン
		$res = $zip->open($zip_path, ZipArchive::CREATE);

		if ($res === true) {
			foreach ($pdf_list as $value) {
				$new_name = str_replace("{$working_dir}/", '', $value);
				$zip->addFile($value, $new_name);
			}
			// Zip ファイルをクローズ
			$zip->close();

			// mb_http_output("pass");
			// header("Content-Type: application/zip");
			// header("Content-Transfer-Encoding: Binary");
			// header("Content-Length: " . filesize($zip_path));
			// header('Content-Disposition: attachment; filename*=UTF-8\'\'' . $zip_name);
			// ob_end_clean();
			// readfile($zip_path);
			// // zipを削除
			// unlink($zip_path);
		}
		
        exit();
    }
}

new MakeZipArchive($year, $month, $type);