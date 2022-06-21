<?php

namespace jp3cki\yii2\jpphone\unittest;

use Yii;
use jp3cki\yii2\jpphone\JpPhoneNumberValidator as Target;
use jp3cki\yii2\jpphone\test\TestCase;
use yii\base\DynamicModel;

class JpPhoneTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    /**
     * @dataProvider numberProvider
     */
    public function testAdHoc($expect, $types, $hyphen, $value)
    {
        $o = new Target();
        $o->types = $types;
        $o->hyphen = $hyphen;
        $this->assertEquals($expect, $o->validate($value));
    }

    /**
     * @dataProvider numberProvider
     */
    public function testModel($expect, $types, $hyphen, $value)
    {
        $o = DynamicModel::validateData(
            ['value' => $value],
            [[['value'], Target::className(), 'types' => $types, 'hyphen' => $hyphen]]
        );
        $this->assertEquals($expect, !$o->hasErrors());
    }

    /**
     * @dataProvider flagProvider
     */
    public function testFlagAdHoc($type, $value)
    {
        $o = new Target();
        $o->types = $type;
        $o->hyphen = null;
        $this->assertTrue($o->validate($value));

        $o = new Target();
        $o->types = Target::FLAG_ALL & (~$type);
        $o->hyphen = null;
        $this->assertFalse($o->validate($value));
    }

    /**
     * @dataProvider flagProvider
     */
    public function testFlagModel($type, $value)
    {
        $o = DynamicModel::validateData(
            ['value' => $value],
            [[['value'], Target::className(), 'types' => $type, 'hyphen' => null]]
        );
        $this->assertFalse($o->hasErrors());

        $o = DynamicModel::validateData(
            ['value' => $value],
            [[['value'], Target::className(), 'types' => Target::FLAG_ALL & (~$type), 'hyphen' => null]]
        );
        $this->assertTrue($o->hasErrors());
    }

    public function numberProvider()
    {
        return [
            [true,  Target::FLAG_FREE_DIAL, null,  '0120123456'],
            [true,  Target::FLAG_FREE_DIAL, null,  '0120-123-456'],
            [true,  Target::FLAG_FREE_DIAL, null,  '0120-12-3456'],
            [false, Target::FLAG_FREE_DIAL, null,  '0120-1-23456'],
            [false, Target::FLAG_FREE_DIAL, null,  '0120-123456'],
            [false, Target::FLAG_FREE_DIAL, true,  '0120123456'],
            [true,  Target::FLAG_FREE_DIAL, true,  '0120-123-456'],
            [true,  Target::FLAG_FREE_DIAL, true,  '0120-12-3456'],
            [true,  Target::FLAG_FREE_DIAL, false, '0120123456'],
            [false, Target::FLAG_FREE_DIAL, false, '0120-123-456'],
            [false, Target::FLAG_FREE_DIAL, false, '0120-12-3456'],

            [true,  Target::FLAG_FREE_ACCESS, null,  '08009876543'],
            [true,  Target::FLAG_FREE_ACCESS, null,  '0800-987-6543'],
            [false, Target::FLAG_FREE_ACCESS, null,  '0800-9876543'],
            [false, Target::FLAG_FREE_ACCESS, true,  '08009876543'],
            [true,  Target::FLAG_FREE_ACCESS, true,  '0800-987-6543'],
            [true,  Target::FLAG_FREE_ACCESS, false, '08009876543'],
            [false, Target::FLAG_FREE_ACCESS, false, '0800-987-6543'],

            [true,  Target::FLAG_IP_PHONE, null, '05010091234'],
            [true,  Target::FLAG_IP_PHONE, null, '050-1009-1234'],
            [false, Target::FLAG_IP_PHONE, null, '050-10091-234'],
            [false, Target::FLAG_IP_PHONE, true, '05010091234'],
            [true,  Target::FLAG_IP_PHONE, true, '050-1009-1234'],
            [true,  Target::FLAG_IP_PHONE, false, '05010091234'],
            [false, Target::FLAG_IP_PHONE, false, '050-1009-1234'],

            // 正しい固定電話番号
            [true, Target::FLAG_LANDLINE, null, '0352535111'],
            [true, Target::FLAG_LANDLINE, null, '0112001234'],
            [true, Target::FLAG_LANDLINE, null, '0123201234'],
            [true, Target::FLAG_LANDLINE, null, '0126721234'],
            [true, Target::FLAG_LANDLINE, null, '03-5253-5111'],
            [true, Target::FLAG_LANDLINE, null, '011-200-1234'],
            [true, Target::FLAG_LANDLINE, null, '0123-20-1234'],
            [true, Target::FLAG_LANDLINE, null, '01267-2-1234'],
            [false, Target::FLAG_LANDLINE, true, '0352535111'],
            [false, Target::FLAG_LANDLINE, true, '0112001234'],
            [false, Target::FLAG_LANDLINE, true, '0123201234'],
            [false, Target::FLAG_LANDLINE, true, '0126721234'],
            [true, Target::FLAG_LANDLINE, true, '03-5253-5111'],
            [true, Target::FLAG_LANDLINE, true, '011-200-1234'],
            [true, Target::FLAG_LANDLINE, true, '0123-20-1234'],
            [true, Target::FLAG_LANDLINE, true, '01267-2-1234'],
            [true, Target::FLAG_LANDLINE, false, '0352535111'],
            [true, Target::FLAG_LANDLINE, false, '0112001234'],
            [true, Target::FLAG_LANDLINE, false, '0123201234'],
            [true, Target::FLAG_LANDLINE, false, '0126721234'],
            [false, Target::FLAG_LANDLINE, false, '03-5253-5111'],
            [false, Target::FLAG_LANDLINE, false, '011-200-1234'],
            [false, Target::FLAG_LANDLINE, false, '0123-20-1234'],
            [false, Target::FLAG_LANDLINE, false, '01267-2-1234'],
            // ハイフン区切りが正しくない
            [false, Target::FLAG_LANDLINE, true, '0112-00-1234'],

            // 090
            [true,  Target::FLAG_MOBILE, null, '09010091234'],
            [true,  Target::FLAG_MOBILE, null, '090-1009-1234'],
            [false, Target::FLAG_MOBILE, null, '090-10091234'],
            [false, Target::FLAG_MOBILE, null, '090-100-91234'],
            [false, Target::FLAG_MOBILE, true, '09010091234'],
            [true,  Target::FLAG_MOBILE, true, '090-1009-1234'],
            [true,  Target::FLAG_MOBILE, false, '09010091234'],
            [false, Target::FLAG_MOBILE, false, '090-1009-1234'],
            // 080
            [true,  Target::FLAG_MOBILE, null, '08010091234'],
            [true,  Target::FLAG_MOBILE, null, '080-1009-1234'],
            [false, Target::FLAG_MOBILE, null, '080-10091234'],
            [false, Target::FLAG_MOBILE, null, '080-100-91234'],
            [false, Target::FLAG_MOBILE, true, '08010091234'],
            [true,  Target::FLAG_MOBILE, true, '080-1009-1234'],
            [true,  Target::FLAG_MOBILE, false, '08010091234'],
            [false, Target::FLAG_MOBILE, false, '080-1009-1234'],
            // 080 は 0800 と紛らわしい
            [false, Target::FLAG_MOBILE, null, '08009876543'],
            // 070
            [true,  Target::FLAG_MOBILE, null, '07050191234'],
            [true,  Target::FLAG_MOBILE, null, '070-5019-1234'],
            [false, Target::FLAG_MOBILE, null, '070-50191234'],
            [false, Target::FLAG_MOBILE, null, '070-100-91234'],
            [false, Target::FLAG_MOBILE, true, '07050191234'],
            [true,  Target::FLAG_MOBILE, true, '070-5019-1234'],
            [true,  Target::FLAG_MOBILE, false, '07050191234'],
            [false, Target::FLAG_MOBILE, false, '070-5019-1234'],

            // ナビダイヤル
            [true,  Target::FLAG_NAV_DIAL, null, '0570000123'],
            [true,  Target::FLAG_NAV_DIAL, null, '0570-000-123'],
            [true,  Target::FLAG_NAV_DIAL, null, '0570-00-0123'],
            [false, Target::FLAG_NAV_DIAL, null, '0570-0001-23'],
            [false, Target::FLAG_NAV_DIAL, null, '0120-000123'],
            [false, Target::FLAG_NAV_DIAL, true, '0570000123'],
            [true,  Target::FLAG_NAV_DIAL, true, '0570-000-123'],
            [true,  Target::FLAG_NAV_DIAL, true, '0570-00-0123'],
            [true,  Target::FLAG_NAV_DIAL, false, '0570000123'],
            [false, Target::FLAG_NAV_DIAL, false, '0570-000-123'],
            [false, Target::FLAG_NAV_DIAL, false, '0570-00-0123'],

            // ポケベル(死亡)
            [false, Target::FLAG_PAGER, null, '02046201234'],
            [false, Target::FLAG_PAGER, null, '020-4620-1234'],
            [false, Target::FLAG_PAGER, null, '020-46201234'],
            [false, Target::FLAG_PAGER, true, '02046201234'],
            [false, Target::FLAG_PAGER, true, '020-4620-1234'],
            [false, Target::FLAG_PAGER, false, '02046201234'],
            [false, Target::FLAG_PAGER, false, '020-4620-1234'],

            // Q2(死亡)
            [false, Target::FLAG_DIAL_Q2, null, '0990504123'],
            [false, Target::FLAG_DIAL_Q2, null, '0990-504-123'],
        ];
    }

    public function flagProvider()
    {
        return [
            [Target::FLAG_FREE_DIAL, '0120123456'],
            [Target::FLAG_FREE_ACCESS, '08009876543'],
            [Target::FLAG_IP_PHONE, '05010091234'],
            [Target::FLAG_LANDLINE, '03-5253-5111'],
            [Target::FLAG_MOBILE, '09010091234'],
            [Target::FLAG_MOBILE, '08010091234'],
            [Target::FLAG_MOBILE, '07050191234'],
            [Target::FLAG_NAV_DIAL, '0570000123'],
        ];
    }
}
