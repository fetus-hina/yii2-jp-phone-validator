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

use function in_array;
use function preg_match;
use function preg_replace;
use function strlen;
use function substr;

/**
 * FreeDial(0120-abc-def OR 0120-ab-cdef) like
 */
abstract class FreeDialLike extends Base
{
    #[Override]
    protected function isValidFormat(string $number): bool
    {
        $firstPart = $this->getFirstPart();
        return (bool)preg_match('/^' . $firstPart . '(?:(?:-(?:\d{3}-\d{3}|\d{2}-\d{4}))|\d{6})$/', $number);
    }

    #[Override]
    protected function isAssignedNumber(string $number): bool
    {
        $number = preg_replace('/[^0-9]+/', '', $number);
        $firstPart = $this->getFirstPart();
        $prefixList = $this->loadDataFile('others/' . $firstPart . '.json.gz');
        // 0120ABCDEFのABC部分が割り当て済みかどうかを確認する
        return (bool)in_array(substr($number, strlen($firstPart), 3), $prefixList, true);
    }

    abstract protected function getFirstPart(): string;
}
