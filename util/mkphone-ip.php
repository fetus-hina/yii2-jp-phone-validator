<?php
require_once(__DIR__ . '/../vendor/autoload.php');

use Curl\Curl;

define('PUT_BASE_DIR', __DIR__ . '/../data/phone/others');

// 総務省の電話番号リストの在りか
$excels = [
    'http://www.soumu.go.jp/main_content/000124106.xls',    // 050
];

foreach ($excels as $url) {
    $spreadsheet = parseExcel(downloadExcel($url));
    list($start, $data) = convertSheet($spreadsheet->getActiveSheet());
    saveData($start, $data);
}

function downloadExcel($url)
{
    echo "Downloading $url ...\n";
    $curl = new Curl();
    $curl->get($url);
    if ($curl->error) {
        throw new Exception('Could not download ' . $url);
    }
    return $curl->rawResponse;
}

function parseExcel($binary)
{
    echo "Parsing Excel...\n";
    $tmppath = tempnam(sys_get_temp_dir(), 'xls-');
    try {
        file_put_contents($tmppath, $binary);
        $reader = PHPExcel_IOFactory::createReader('Excel5');
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($tmppath);
        @unlink($spreadsheet);
        return $spreadsheet;
    } catch (Exception $e) {
        @unlink($tmppath);
        throw $e;
    }
}

function convertSheet($sheet)
{
    echo "Converting...\n";
    $key = null;
    $ret = [];
    $rowCount = $sheet->getHighestRow();
    for ($y = 1; $y <= $rowCount; ++$y) {
        if (preg_match('/^0\d+$/', $sheet->getCell("A{$y}")->getValue())) {
            break;
        }
    }
    for (; $y <= $rowCount; ++$y) {
        $prefix = trim($sheet->getCell("A{$y}")->getValue());
        for ($x = 0; $x <= 9; ++$x) {
            $cell = chr(ord('B') + $x) . $y;
            if (trim($sheet->getCell($cell)->getValue()) !== '') {
                $number = $prefix . (string)$x;
                if ($key === null) {
                    $key = substr($number, 0, 3); // 050
                }
                $ret[] = substr($number, 3);
            }
        }
    }
    return [$key, $ret];
}

function saveData($start3digit, $data)
{
    $filepath = PUT_BASE_DIR . '/' . $start3digit . '.json.gz';
    if (!file_exists(dirname($filepath))) {
        mkdir(dirname($filepath), 0755, true);
    }
    $json = json_encode($data);
    file_put_contents($filepath, gzencode($json, 9, FORCE_GZIP));
}
