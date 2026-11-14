<?php

declare(strict_types=1);

use Curl\Curl;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

require_once __DIR__ . '/../vendor/autoload.php';

define('PUT_BASE_DIR', __DIR__ . '/../data/phone/others');

// 総務省の電話番号リストの在りか
$excels = [
    'https://www.soumu.go.jp/main_content/000697577.xlsx', // 0120
    'https://www.soumu.go.jp/main_content/001045206.xlsx', // 0800
    'https://www.soumu.go.jp/main_content/001076964.xlsx', // 0570
];

foreach ($excels as $url) {
    $spreadsheet = parseExcel(downloadExcel($url));
    [$start, $data] = convertSheet($spreadsheet->getActiveSheet());
    saveData($start, $data);
}

function downloadExcel(string $url): string
{
    echo "Downloading $url ...\n";

    // 総務省のサーバ (CDN + openresty) は一時的に 502 等を返すことがあるため、
    // 指数バックオフでリトライする。
    $maxAttempts = 15;
    $lastError = null;
    for ($attempt = 1; $attempt <= $maxAttempts; ++$attempt) {
        $curl = new Curl();
        $curl->setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36');
        $curl->get($url);
        // php-curl-class のプロパティは型宣言がないため、型を確認してから使う
        $statusCode = $curl->httpStatusCode;
        $response = $curl->rawResponse;
        if (!$curl->error && $statusCode === 200 && is_string($response)) {
            return $response;
        }

        $errorMessage = $curl->errorMessage;
        $lastError = sprintf(
            'HTTP %d%s',
            is_int($statusCode) ? $statusCode : 0,
            is_string($errorMessage) && $errorMessage !== '' ? ' (' . $errorMessage . ')' : '',
        );
        $curl->close();

        if ($attempt < $maxAttempts) {
            $wait = min(2 ** ($attempt - 1), 15); // 1, 2, 4, 8, 15, 15, ... 秒 (上限15秒)
            echo "  Attempt {$attempt} failed: {$lastError}. Retrying in {$wait}s...\n";
            sleep($wait);
        }
    }

    throw new Exception("Could not download {$url}: {$lastError}");
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
    } catch (Throwable $e) {
        @unlink($tmppath);
        throw $e;
    }
}

/**
 * @return array{string, list<string>}
 */
function convertSheet(Worksheet $sheet): array
{
    echo "Converting...\n";
    $key = null;
    $ret = [];
    $rowCount = $sheet->getHighestRow();

    // skip headers
    for ($y = 1; $y <= $rowCount; ++$y) {
        if (preg_match('/^0\d+$/', $sheet->getCell("A{$y}")->getValueString())) {
            break;
        }
    }

    // data
    for (; $y <= $rowCount; ++$y) {
        $prefix = trim($sheet->getCell("A{$y}")->getValueString());
        for ($x = 0; $x <= 9; ++$x) {
            // B 列 (2) から K 列 (11) に 0〜9 が並ぶ
            $cell = Coordinate::stringFromColumnIndex(2 + $x) . $y;
            if (trim($sheet->getCell($cell)->getValueString()) !== '') {
                $number = $prefix . (string)$x;
                if ($key === null) {
                    $key = substr($number, 0, 4);
                }
                $ret[] = substr($number, 4);
            }
        }
    }
    if ($key === null) {
        throw new Exception('No data found in the sheet');
    }

    return [$key, $ret];
}

/**
 * @param list<string> $data
 */
function saveData(string $startdigit, array $data): void
{
    $filepath = PUT_BASE_DIR . '/' . $startdigit . '.json.gz';
    if (!file_exists(dirname($filepath))) {
        mkdir(dirname($filepath), 0755, true);
    }
    sort($data);
    $json = json_encode($data, JSON_THROW_ON_ERROR);
    file_put_contents($filepath, gzencode($json, 9, FORCE_GZIP));
}
