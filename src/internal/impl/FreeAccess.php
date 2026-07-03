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
use function substr;

/**
 * FreeAccess(0800-abc-defg)
 */
final class FreeAccess extends Base
{
    #[Override]
    protected function isValidFormat(string $number): bool
    {
        return (bool)preg_match('/^0800(?:(?:-\d{3}-\d{4})|(?:\d{7}))$/', $number);
    }

    #[Override]
    protected function isAssignedNumber(string $number): bool
    {
        $number = preg_replace('/[^0-9]+/', '', $number);
        $prefixList = $this->loadDataFile('others/0800.json.gz');
        // 0800ABCDEFGのABC部分が割り当て済みかどうかを確認する
        return (bool)in_array(substr($number, 4, 3), $prefixList, true);
    }
}
