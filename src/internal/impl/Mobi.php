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

/**
 * Mobile phones (090-xxxx-xxxx, 080-, 070-, 060-)
 */
final class Mobi extends MobiLike
{
    /**
     * @return list<string>
     */
    #[Override]
    protected function getFirstPart(): array
    {
        return ['090', '080', '070', '060'];
    }
}
