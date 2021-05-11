<?php

declare(strict_types=1);

use Curl\Curl;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

require_once(__DIR__ . '/../vendor/autoload.php');

define('PUT_BASE_DIR', __DIR__ . '/../data/phone/others');

// 総務省の電話番号リストの在りか
$excels = [
    'https://www.soumu.go.jp/main_content/000697563.xlsx',  // 070
    'https://www.soumu.go.jp/main_content/000697565.xlsx',  // 080
    'https://www.soumu.go.jp/main_content/000697567.xlsx',  // 090
];

foreach ($excels as $url) {
    $spreadsheet = parseExcel(downloadExcel($url));
    list($start, $data) = convertSheet($spreadsheet->getActiveSheet());
    saveData($start, $data);
}

function downloadExcel(string $url): string
{
    echo "Downloading $url ...\n";
    $curl = new Curl();
    $curl->get($url);
    if ($curl->error) {
        throw new Exception('Could not download ' . $url);
    }
    return $curl->rawResponse;
}

function parseExcel(string $binary): Spreadsheet
{
    echo "Parsing Excel...\n";
    $tmppath = tempnam(sys_get_temp_dir(), 'xls-');
    try {
        file_put_contents($tmppath, $binary);
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($tmppath);
        @unlink($tmppath);
        return $spreadsheet;
    } catch (Exception $e) {
        @unlink($tmppath);
        throw $e;
    }
}

function convertSheet(Worksheet $sheet): array
{
    echo "Converting...\n";
    $key = null;
    $ret = [];
    $rowCount = (int)$sheet->getHighestRow();

    // skip headers
    for ($y = 1; $y <= $rowCount; ++$y) {
        if (preg_match('/^0\d+$/', (string)$sheet->getCell("A{$y}")->getValue())) {
            break;
        }
    }

    // data
    for (; $y <= $rowCount; ++$y) {
        $prefix = trim((string)$sheet->getCell("A{$y}")->getValue());
        for ($x = 0; $x <= 9; ++$x) {
            $cell = chr(ord('B') + $x) . $y;
            if (trim((string)$sheet->getCell($cell)->getValue()) !== '') {
                $number = $prefix . (string)$x;
                if ($key === null) {
                    $key = substr($number, 0, 3); // 070, 080, 090
                }
                for ($z = 0; $z <= 9; ++$z) {
                    $ret[] = substr($number, 3) . $z;
                }
            }
        }
    }
    return [$key, $ret];
}

function saveData(string $start3digit, array $data): void
{
    $filepath = PUT_BASE_DIR . '/' . $start3digit . '.json.gz';
    if (!file_exists(dirname($filepath))) {
        mkdir(dirname($filepath), 0755, true);
    }
    sort($data);
    $json = json_encode($data);
    file_put_contents($filepath, gzencode($json, 9, FORCE_GZIP));
}
