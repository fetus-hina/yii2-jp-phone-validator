<?php

/**
 * @author AIZAWA Hina <hina@fetus.jp>
 * @copyright 2015 by AIZAWA Hina <hina@fetus.jp>
 * @license https://github.com/fetus-hina/yii2-jp-phone-validator/blob/master/LICENSE MIT
 * @since 1.0.0
 */

namespace jp3cki\yii2\jpphone;

use Yii;
use yii\validators\Validator;

/**
 * Validate Phone number (JAPAN spec)
 */
class JpPhoneNumberValidator extends Validator
{
    /** 固定電話 */
    public const FLAG_LANDLINE     = 0x0001;
    /** 携帯電話 */
    public const FLAG_MOBILE       = 0x0002;
    /** IP電話(050) */
    public const FLAG_IP_PHONE     = 0x0004;
    /** フリーダイヤル(0120) */
    public const FLAG_FREE_DIAL    = 0x0008;
    /** フリーアクセス(0800) */
    public const FLAG_FREE_ACCESS  = 0x0010;
    /** ナビダイヤル(0570) */
    public const FLAG_NAV_DIAL     = 0x0020;
    /**
     * ダイヤルQ2(0990)
     * @deprecated
     */
    public const FLAG_DIAL_Q2      = 0x0040;
    /**
     * ポケベル(020-4)
     * @deprecated
     */
    public const FLAG_PAGER        = 0x0080;

    /** 一般的な番号の組み合わせ */
    public const FLAG_CONSUMERS    = 0x0007;
    /** すべての組み合わせ */
    public const FLAG_ALL          = 0x00ff;

    /** @var int validとみなす電話番号の種類(FLAG_*の組み合わせ) */
    public $types = self::FLAG_CONSUMERS;

    /**
     * ハイフンの許可
     *
     * @var bool|null null=気にしない, true=要求, false=許可しない
     */
    public $hyphen = null;

    /** @inheritdoc */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = Yii::t('jp3ckiJpPhone', '{attribute} is not a valid phone number.');
        }
    }

    /** @inheritdoc */
    public function validateAttribute($model, $attribute)
    {
        if (!$this->isValid($model->$attribute)) {
            $this->addError($model, $attribute, $this->message);
        }
    }

    /** @inheritdoc */
    protected function validateValue($value)
    {
        if (!$this->isValid($value)) {
            return [$this->message, []];
        }
        return null;
    }

    private function isValid($number)
    {
        $classMap = [
            self::FLAG_MOBILE       => 'jp3cki\yii2\jpphone\internal\impl\Mobi',
            self::FLAG_IP_PHONE     => 'jp3cki\yii2\jpphone\internal\impl\Ip',
            self::FLAG_FREE_DIAL    => 'jp3cki\yii2\jpphone\internal\impl\FreeDial',
            self::FLAG_FREE_ACCESS  => 'jp3cki\yii2\jpphone\internal\impl\FreeAccess',
            self::FLAG_NAV_DIAL     => 'jp3cki\yii2\jpphone\internal\impl\NavDial',
            // 固定電話はコストが高いので最後に検査する
            self::FLAG_LANDLINE     => 'jp3cki\yii2\jpphone\internal\impl\Landline',
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
