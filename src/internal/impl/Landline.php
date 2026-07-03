<?php

/**
 * @author AIZAWA Hina <hina@fetus.jp>
 * @copyright 2015 by AIZAWA Hina <hina@fetus.jp>
 * @license https://github.com/fetus-hina/yii2-jp-phone-validator/blob/master/LICENSE MIT
 * @since 1.0.0
 */

declare(strict_types=1);

namespace jp3cki\yii2\jpphone\internal\impl;

use Override;

use function implode;
use function in_array;
use function preg_match;
use function strcmp;
use function strlen;
use function substr;
use function usort;

final class Landline extends Base
{
    #[Override]
    protected function isValidFormat(string $number): bool
    {
        // 固定電話は市外/市内局番の区別をつけるのが大変なので
        // この時点ではとりあえずあり得る組み合わせかを検査する
        $patterns = [
            /* 0[1-9] */ '(?:-\d{4}-\d{4})', // 03-1234-1234
            /* 0[1-9] */ '(?:\d-\d{3}-\d{4})', // 012-123-1234
            /* 0[1-9] */ '(?:\d{2}-\d{2}-\d{4})', // 0123-12-1234
            /* 0[1-9] */ '(?:\d{3}-\d-\d{4})', // 01234-1-1234
            /* 0[1-9] */ '(?:\d{8})', // 0121231234
        ];
        $regex = '/^0[1-9](?:' . implode('|', $patterns) . ')$/';
        return (bool)preg_match($regex, $number);
    }

    #[Override]
    protected function isAssignedNumber(string $number): bool
    {
        // ハイフンがあればその区切りで正しいか確認する
        if (preg_match('/^(\d+)-(\d+)-\d+$/', $number, $match)) {
            return $this->isValidShigaiShinai($match[1], $match[2]);
        }

        // ハイフンがなければ市外・市内の区切りを探す
        if (($shigai = $this->findShigai($number)) === false) {
            return false;
        }

        $shigaiLength = strlen($shigai);
        return $this->isValidShigaiShinai(
            substr($number, 0, $shigaiLength),
            substr($number, $shigaiLength, 6 - $shigaiLength),
        );
    }

    private function isValidShigaiShinai(string $shigai, string $shinai): bool
    {
        $shinaiList = $this->loadShinaiList($shigai);
        return (bool)in_array($shinai, $shinaiList, true);
    }

    private function findShigai(string $number): string|false
    {
        $shigaiList = $this->loadShigaiList(substr($number, 0, 2));
        foreach ($shigaiList as $shigai) {
            if ($shigai === substr($number, 0, strlen($shigai))) {
                return $shigai;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function loadShigaiList(string $prefix2): array
    {
        $list = $this->loadDataFile('landline/' . $prefix2 . '.json.gz');
        usort($list, function (string $lhs, string $rhs): int {
            if (($tmp = strlen($rhs) - strlen($lhs)) !== 0) { // 長い順
                return $tmp;
            }
            return strcmp($lhs, $rhs);
        });

        return $list;
    }

    /**
     * @return list<string>
     */
    private function loadShinaiList(string $shigai): array
    {
        $prefix2 = substr($shigai, 0, 2);
        return $this->loadDataFile('landline/' . $prefix2 . '/' . $shigai . '.json.gz');
    }
}
