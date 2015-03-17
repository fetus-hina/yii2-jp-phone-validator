<?php
/**
 * @author AIZAWA Hina <hina@bouhime.com>
 * @copyright 2015 by AIZAWA Hina <hina@bouhime.com>
 * @license https://github.com/fetus-hina/yii2-extra-validator/blob/master/LICENSE MIT
 * @since 1.0.0
 */

namespace jp3cki\yii2\jpphone\internal\impl;

/**
 * Mobile phones (090-xxxx-xxxx, 080-, 070-)
 */
class Mobi extends MobiLike
{
    protected function getFirstPart()
    {
        return ['090', '080', '070'];
    }
}
