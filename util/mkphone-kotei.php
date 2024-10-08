<?php

declare(strict_types=1);

use Curl\Curl;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

require_once(__DIR__ . '/../vendor/autoload.php');

ini_set('memory_limit', '512M');

define('PUT_BASE_DIR', __DIR__ . '/../data/phone/landline');

// 総務省の電話番号リストの在りか
$excels = [
    'https://www.soumu.go.jp/main_content/000697543.xls',   // 01
    'https://www.soumu.go.jp/main_content/000697544.xls',   // 02
    'https://www.soumu.go.jp/main_content/000697545.xls',   // 03
    'https://www.soumu.go.jp/main_content/000697546.xls',   // 04
    'https://www.soumu.go.jp/main_content/000697548.xls',   // 05
    'https://www.soumu.go.jp/main_content/000697549.xls',   // 06
    'https://www.soumu.go.jp/main_content/000697550.xls',   // 07
    'https://www.soumu.go.jp/main_content/000697551.xls',   // 08
    'https://www.soumu.go.jp/main_content/000697552.xls',   // 09
];

foreach ($excels as $url) {
    $spreadsheet = parseExcel(downloadExcel($url));
    [$start, $data] = convertSheet($spreadsheet->getActiveSheet());
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
        $reader = IOFactory::createReader('Xls');
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
    $rowCount = $sheet->getHighestRow();
    for ($y = 1; $y <= $rowCount; ++$y) {
        if ($sheet->getCell("F{$y}")->getValue() === '使用中') {
            $shigai = $sheet->getCell("C{$y}")->getValue();
            $shinai = $sheet->getCell("D{$y}")->getValue();
            if ($key === null) {
                $key = substr($shigai, 0, 2);
            }
            if (!isset($ret["_{$shigai}"])) {
                echo "  市外局番: {$shigai}\n";
                $ret["_{$shigai}"] = [];
            }
            $ret["_{$shigai}"][] = $shinai;
        }
    }
    return [$key, $ret];
}

function saveData(string $start2digit, array $data): void
{
    $filepath1 = PUT_BASE_DIR . '/' . $start2digit . '.json.gz';
    if (!file_exists(dirname($filepath1))) {
        mkdir(dirname($filepath1), 0755, true);
    }
    $json = json_encode(array_map(
        function (string $shigai): string {
            return ltrim($shigai, '_');
        },
        array_keys($data)
    ));
    file_put_contents($filepath1, gzencode($json, 9, FORCE_GZIP));
    foreach ($data as $shigai_ => $shinaiList) {
        $shigai = ltrim($shigai_, '_');
        $filepath2 = PUT_BASE_DIR . '/' . $start2digit . '/' . $shigai . '.json.gz';
        if (!file_exists(dirname($filepath2))) {
            mkdir(dirname($filepath2), 0755, true);
        }
        sort($shinaiList);
        $json = json_encode($shinaiList);
        file_put_contents($filepath2, gzencode($json, 9, FORCE_GZIP));
    }
}
