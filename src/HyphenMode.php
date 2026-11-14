<?php

/**
 * @author AIZAWA Hina <hina@fetus.jp>
 * @copyright 2026 by AIZAWA Hina <hina@fetus.jp>
 * @license https://github.com/fetus-hina/yii2-jp-phone-validator/blob/master/LICENSE MIT
 * @since 6.0.0
 */

declare(strict_types=1);

namespace jp3cki\yii2\jpphone;

/**
 * ハイフンの許可状況
 */
enum HyphenMode
{
    /** ハイフンの有無を問わない（ハイフンがある場合は正しい位置にある必要がある） */
    case Optional;

    /** ハイフンを必須とする（正しい位置にある必要がある） */
    case Required;

    /** ハイフンを許容しない（数字のみの羅列である必要がある） */
    case Forbidden;
}
