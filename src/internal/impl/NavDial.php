<?php

/**
 * @author AIZAWA Hina <hina@fetus.jp>
 * @copyright 2015 by AIZAWA Hina <hina@fetus.jp>
 * @license https://github.com/fetus-hina/yii2-jp-phone-validator/blob/master/LICENSE MIT
 * @since 1.0.0
 */

namespace jp3cki\yii2\jpphone\internal\impl;

/**
 * NavDial(0570-abc-def OR 0570-ab-cdef)
 */
class NavDial extends FreeDialLike
{
    protected function getFirstPart()
    {
        return '0570';
    }
}
