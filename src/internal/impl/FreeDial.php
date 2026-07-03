<?php

/**
 * @author AIZAWA Hina <hina@fetus.jp>
 * @copyright 2015 by AIZAWA Hina <hina@fetus.jp>
 * @license https://github.com/fetus-hina/yii2-jp-phone-validator/blob/master/LICENSE MIT
 * @since 1.2.0
 */

declare(strict_types=1);

namespace jp3cki\yii2\jpphone\internal\impl;

use Override;

/**
 * FreeDial(0120-abc-def OR 0120-ab-cdef)
 */
final class FreeDial extends FreeDialLike
{
    #[Override]
    protected function getFirstPart(): string
    {
        return '0120';
    }
}
