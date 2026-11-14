<?php

/**
 * @author AIZAWA Hina <hina@fetus.jp>
 * @copyright 2015 by AIZAWA Hina <hina@fetus.jp>
 * @license https://github.com/fetus-hina/yii2-jp-phone-validator/blob/master/LICENSE MIT
 * @since 1.0.0
 */

declare(strict_types=1);

namespace jp3cki\yii2\jpphone;

use Override;
use Yii;
use jp3cki\yii2\jpphone\internal\impl\FreeAccess;
use jp3cki\yii2\jpphone\internal\impl\FreeDial;
use jp3cki\yii2\jpphone\internal\impl\Ip;
use jp3cki\yii2\jpphone\internal\impl\Landline;
use jp3cki\yii2\jpphone\internal\impl\M2m;
use jp3cki\yii2\jpphone\internal\impl\Mobi;
use jp3cki\yii2\jpphone\internal\impl\NavDial;
use yii\base\InvalidConfigException;
use yii\validators\Validator;

use function array_any;
use function is_string;
use function sprintf;

/**
 * Validate Phone number (JAPAN spec)
 */
class JpPhoneNumberValidator extends Validator
{
    /** 固定電話 */
    public const int FLAG_LANDLINE = 0x0001;
    /** 携帯電話 */
    public const int FLAG_MOBILE = 0x0002;
    /** IP電話(050) */
    public const int FLAG_IP_PHONE = 0x0004;
    /** フリーダイヤル(0120) */
    public const int FLAG_FREE_DIAL = 0x0008;
    /** フリーアクセス(0800) */
    public const int FLAG_FREE_ACCESS = 0x0010;
    /** ナビダイヤル(0570) */
    public const int FLAG_NAV_DIAL = 0x0020;
    // 0x0040, 0x0080 は v4 以前に別用途で使用していた実績があるため、事故防止のため欠番とする
    /** M2M・データ伝送携帯電話番号(020) */
    public const int FLAG_M2M = 0x0100;

    /** 一般的な番号の組み合わせ */
    public const int FLAG_CONSUMERS = 0x0007;

    /** すべての組み合わせ */
    public const int FLAG_ALL = 0x013f;

    /** @var int-mask-of<self::FLAG_*> validとみなす電話番号の種類(FLAG_*の組み合わせ) */
    public int $types = self::FLAG_CONSUMERS;

    /**
     * ハイフンの許可
     *
     * @var HyphenMode|bool|null HyphenMode または null=気にしない, true=要求, false=許可しない
     */
    public HyphenMode|bool|null $hyphen = null;

    /**
     * @inheritdoc
     */
    #[Override]
    public function init()
    {
        parent::init();
        $this->assertValidTypes();
        if ($this->message === null) {
            $this->message = Yii::t('jp3ckiJpPhone', '{attribute} is not a valid phone number.');
        }
    }

    /**
     * @inheritdoc
     */
    #[Override]
    protected function validateValue($value)
    {
        if (!$this->isValid($value)) {
            return [$this->message, []];
        }

        return null;
    }

    /**
     * $types が FLAG_* の組み合わせとして正しいかを検査する
     *
     * @throws InvalidConfigException
     */
    private function assertValidTypes(): void
    {
        if ($this->types === 0 || ($this->types & ~self::FLAG_ALL) !== 0) {
            throw new InvalidConfigException(
                sprintf('The "types" property must be a combination of FLAG_* constants, 0x%04x given.', $this->types),
            );
        }
    }

    /**
     * @throws InvalidConfigException
     */
    private function isValid(mixed $number): bool
    {
        // init() 後に $types が変更されている可能性があるため、検証時にも検査する
        $this->assertValidTypes();

        // 電話番号は先頭の 0 を含むため、文字列以外は常に不正とみなす
        if (!is_string($number)) {
            return false;
        }

        $classMap = [
            self::FLAG_MOBILE => Mobi::class,
            self::FLAG_IP_PHONE => Ip::class,
            self::FLAG_FREE_DIAL => FreeDial::class,
            self::FLAG_FREE_ACCESS => FreeAccess::class,
            self::FLAG_NAV_DIAL => NavDial::class,
            self::FLAG_M2M => M2m::class,
            // 固定電話はコストが高いので最後に検査する
            self::FLAG_LANDLINE => Landline::class,
        ];
        $hyphen = $this->getHyphenMode();
        return array_any(
            $classMap,
            fn (string $className, int $classFlag): bool => ($this->types & $classFlag) === $classFlag &&
                (new $className(['hyphen' => $hyphen]))->validate($number),
        );
    }

    private function getHyphenMode(): HyphenMode
    {
        return match ($this->hyphen) {
            null => HyphenMode::Optional,
            true => HyphenMode::Required,
            false => HyphenMode::Forbidden,
            default => $this->hyphen,
        };
    }
}
