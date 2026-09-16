<?php

declare(strict_types=1);

use Curl\Curl;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

require_once __DIR__ . '/../vendor/autoload.php';

define('PUT_BASE_DIR', __DIR__ . '/../data/phone/others');

// 総務省の電話番号リストの在りか
$excels = [
    // 020 は「Ｍ２Ｍ等専用番号（データ伝送携帯電話番号）」の 11桁 (020-CDE-FGHJK)。
    // 総務省データは 020-CDE 単位だが、070/080/090 と同様に 020-CDEF 単位で扱うため、
    // convertSheet の 0〜9 付与により 4桁ブロック (others/020.json.gz) として出力される。
    // (14桁 0200-DEFGH-JKLMN は別区分・別スクリプト mkphone-m2m.php で生成する)
    'https://www.soumu.go.jp/main_content/001055867.xlsx', // 020
    'https://www.soumu.go.jp/main_content/001019693.xlsx', // 060
    'https://www.soumu.go.jp/main_content/001090878.xlsx', // 070
    'https://www.soumu.go.jp/main_content/000697565.xlsx', // 080
    'https://www.soumu.go.jp/main_content/000697567.xlsx', // 090
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
        if (!$curl->error && $curl->httpStatusCode === 200) {
            return $curl->rawResponse;
        }

        $lastError = sprintf(
            'HTTP %d%s',
            (int)$curl->httpStatusCode,
            $curl->errorMessage ? ' (' . $curl->errorMessage . ')' : '',
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
