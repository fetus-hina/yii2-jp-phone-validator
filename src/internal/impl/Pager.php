<?php

/**
 * @author AIZAWA Hina <hina@fetus.jp>
 * @copyright 2015 by AIZAWA Hina <hina@fetus.jp>
 * @license https://github.com/fetus-hina/yii2-jp-phone-validator/blob/master/LICENSE MIT
 * @since 1.0.0
 */

namespace jp3cki\yii2\jpphone\internal\impl;

/**
 * Pager (pocket-bell) 020-xxxx-xxxx
 */
class Pager extends MobiLike
{
    protected function getFirstPart()
    {
        return ['020'];
    }
}
