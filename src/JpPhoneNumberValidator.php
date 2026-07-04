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
use yii\validators\Validator;

/**
 * Validate Phone number (JAPAN spec)
 */
class JpPhoneNumberValidator extends Validator
{
    /** 固定電話 */
    public const FLAG_LANDLINE = 0x0001;
    /** 携帯電話 */
    public const FLAG_MOBILE = 0x0002;
    /** IP電話(050) */
    public const FLAG_IP_PHONE = 0x0004;
    /** フリーダイヤル(0120) */
    public const FLAG_FREE_DIAL = 0x0008;
    /** フリーアクセス(0800) */
    public const FLAG_FREE_ACCESS = 0x0010;
    /** ナビダイヤル(0570) */
    public const FLAG_NAV_DIAL = 0x0020;
    // 0x0040, 0x0080 は v4 以前に別用途で使用していた実績があるため、事故防止のため欠番とする
    /** M2M・データ伝送携帯電話番号(020) */
    public const FLAG_M2M = 0x0100;

    /** 一般的な番号の組み合わせ */
    public const FLAG_CONSUMERS = 0x0007;

    /** すべての組み合わせ */
    public const FLAG_ALL = 0x013f;

    /** @var int validとみなす電話番号の種類(FLAG_*の組み合わせ) */
    public int $types = self::FLAG_CONSUMERS;

    /**
     * ハイフンの許可
     *
     * @var bool|null null=気にしない, true=要求, false=許可しない
     */
    public bool|null $hyphen = null;

    /**
     * @inheritdoc
     */
    #[Override]
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = Yii::t('jp3ckiJpPhone', '{attribute} is not a valid phone number.');
        }
    }

    /**
     * @inheritdoc
     */
    #[Override]
    public function validateAttribute($model, $attribute)
    {
        if (!$this->isValid($model->$attribute)) {
            $this->addError($model, $attribute, $this->message);
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

    private function isValid(mixed $number): bool
    {
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
        foreach ($classMap as $classFlag => $className) {
            if (($this->types & $classFlag) === $classFlag) {
                $impl = new $className();
                $impl->hyphen = $this->hyphen;
                if ($impl->validate($number)) {
                    return true;
                }
            }
        }

        return false;
    }
}
