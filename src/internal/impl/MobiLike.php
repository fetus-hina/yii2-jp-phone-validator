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
use function preg_replace;
use function substr;

/**
 * Mobile phone (090-abcd-efgh) like
 */
abstract class MobiLike extends Base
{
    #[Override]
    protected function isValidFormat(string $number): bool
    {
        $firstPart = $this->getFirstPart();
        return !!preg_match('/^(?:' . implode('|', $firstPart) . ')(?:(?:-\d{4}-\d{4})|\d{8})$/', $number);
    }

    #[Override]
    protected function isAssignedNumber(string $number): bool
    {
        $number = preg_replace('/[^0-9]+/', '', $number);
        $firstPart = substr($number, 0, 3);
        $prefixList = $this->loadDataFile('others/' . $firstPart . '.json.gz');
        return !!in_array(substr($number, 3, 4), $prefixList, true);
    }

    /**
     * @return list<string>
     */
    abstract protected function getFirstPart(): array;
}
