<?php

/**
 * @author AIZAWA Hina <hina@fetus.jp>
 * @copyright 2026 by AIZAWA Hina <hina@fetus.jp>
 * @license https://github.com/fetus-hina/yii2-jp-phone-validator/blob/master/LICENSE MIT
 * @since 5.0.0
 */

declare(strict_types=1);

namespace jp3cki\yii2\jpphone\internal\impl;

use Override;

use function in_array;
use function preg_match;
use function preg_replace;
use function strlen;
use function substr;

/**
 * M2M/データ伝送携帯電話番号(020)
 *
 *   - 11桁: 020-CDEF-GHJK (020-0… を除く。総務省の割当は 020-CDE 単位だが慣例上 CDEF 単位で扱う)
 *   - 14桁: 0200-DEFGH-JKLMN
 *
 * 020-0… は 14桁 (0200) 側、020-1…〜020-9… は 11桁側で、番号空間は重複しない。
 */
final class M2m extends Base
{
    #[Override]
    protected function isValidFormat(string $number): bool
    {
        return (bool)preg_match(
            '/^(?:020(?:(?:-\d{4}-\d{4})|\d{8})|0200(?:(?:-\d{5}-\d{5})|\d{10}))$/',
            $number,
        );
    }

    #[Override]
    protected function isAssignedNumber(string $number): bool
    {
        $number = preg_replace('/[^0-9]+/', '', $number);

        // ハイフンを除いた桁数で 11桁 (020) と 14桁 (0200) を区別する
        if (strlen($number) === 14) {
            $prefixList = $this->loadDataFile('others/0200.json.gz');
            // 0200 に続く DEFGH の 5 桁が割り当て済みかどうかを確認する
            return (bool)in_array(substr($number, 4, 5), $prefixList, true);
        }

        $prefixList = $this->loadDataFile('others/020.json.gz');
        // 020 に続く CDEF の 4 桁が割り当て済みかどうかを確認する
        return (bool)in_array(substr($number, 3, 4), $prefixList, true);
    }
}
