<?php

/**
 * @author AIZAWA Hina <hina@fetus.jp>
 * @copyright 2015 by AIZAWA Hina <hina@fetus.jp>
 * @license https://github.com/fetus-hina/yii2-jp-phone-validator/blob/master/LICENSE MIT
 * @since 1.0.0
 */

declare(strict_types=1);

namespace jp3cki\yii2\jpphone\internal;

use Override;
use Yii;
use yii\base\BootstrapInterface;
use yii\i18n\PhpMessageSource;

final class LibBootstrap implements BootstrapInterface
{
    /**
     * @inheritdoc
     */
    #[Override]
    public function bootstrap($app)
    {
        Yii::setAlias('@jp3ckiJpPhoneMessages', __DIR__ . '/../../messages');
        $i18n = $app->i18n;
        if (!isset($i18n->translations['jp3ckiJpPhone'])) {
            $i18n->translations['jp3ckiJpPhone'] = [
                'class' => PhpMessageSource::class,
                'sourceLanguage' => 'en-US',
                'basePath' => '@jp3ckiJpPhoneMessages',
            ];
        }
    }
}
