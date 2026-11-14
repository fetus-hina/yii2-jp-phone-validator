<?php

/**
 * @author AIZAWA Hina <hina@fetus.jp>
 * @copyright 2015 by AIZAWA Hina <hina@fetus.jp>
 * @license https://github.com/fetus-hina/yii2-jp-phone-validator/blob/master/LICENSE MIT
 * @since 1.0.0
 */

declare(strict_types=1);

namespace jp3cki\yii2\jpphone\internal\impl;

use jp3cki\yii2\jpphone\HyphenMode;
use yii\base\BaseObject;

use function file_exists;
use function file_get_contents;
use function is_array;
use function json_decode;
use function str_contains;

abstract class Base extends BaseObject
{
    /**
     * Hyphen accept/require mode
     */
    public HyphenMode $hyphen = HyphenMode::Optional;

    /**
     * Validate phone number
     *
     * @params string $number Phone number
     */
    public function validate(string $number): bool
    {
        return $this->isValidFormat($number) &&
            $this->isValidHyphenStatus($number) &&
            $this->isAssignedNumber($number);
    }

    abstract protected function isValidFormat(string $number): bool;

    abstract protected function isAssignedNumber(string $number): bool;

    protected function isValidHyphenStatus(string $number): bool
    {
        return match ($this->hyphen) {
            HyphenMode::Optional => true,
            HyphenMode::Required => str_contains($number, '-'),
            HyphenMode::Forbidden => !str_contains($number, '-'),
        };
    }

    /**
     * @return list<string>
     */
    protected function loadDataFile(string $path): array
    {
        $realpath = __DIR__ . '/../../../data/phone/' . $path;
        if (!file_exists($realpath)) {
            return [];
        }

        $ret = @json_decode(file_get_contents('compress.zlib://' . $realpath));
        return is_array($ret) ? $ret : [];
    }
}
