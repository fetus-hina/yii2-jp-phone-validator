<?php

declare(strict_types=1);

namespace jp3cki\yii2\jpphone\test;

use Override;
use Yii;
use jp3cki\yii2\jpphone\internal\LibBootstrap;
use yii\console\Application;
use yii\helpers\ArrayHelper;

use function file_exists;
use function gc_collect_cycles;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    #[Override]
    public static function setUpBeforeClass(): void
    {
        $vendorDir = __DIR__ . '/../../vendor';
        $vendorAutoload = $vendorDir . '/autoload.php';
        if (file_exists($vendorAutoload)) {
            require_once $vendorAutoload;
        } else {
            throw new NotSupportedException("Vendor autoload file '{$vendorAutoload}' is missing.");
        }
        require_once $vendorDir . '/yiisoft/yii2/Yii.php';
        Yii::setAlias('@vendor', $vendorDir);
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->destroyApplication();
        gc_collect_cycles();
    }

    // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingTraversableTypeHintSpecification
    protected function mockApplication(
        string $language = 'en-US',
        array $config = [],
        string $appClass = Application::class,
    ): void {
        new $appClass(ArrayHelper::merge(
            [
                'id' => 'testapp',
                'basePath' => __DIR__ . '/..',
                'vendorPath' => __DIR__ . '/../../vendor',
                'language' => $language,
                'bootstrap' => [
                    LibBootstrap::class,
                ],
            ],
            $config,
        ));
    }

    protected function destroyApplication(): void
    {
        if (Yii::$app !== null && Yii::$app->has('errorHandler')) {
            Yii::$app->getErrorHandler()->unregister();
        }
        Yii::$app = null;
    }
}
