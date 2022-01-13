<?php


namespace Service\Emoji;


use Carbon\Exceptions\ParseErrorException;
use Spatie\Emoji\Emoji;

class CachedEmoji extends Emoji
{
    static private $emojiCached = [];
    static private $emojiCount = 0;

    public static function allValues(): array
    {
        self::checkAndFillCache();
        return self::$emojiCached;
    }

    public static function getRandomEmoji($count = 5) {
        self::checkAndFillCache();

        $result = [];
        while ($count-- > 0) {
            $result[] = self::$emojiCached[mt_rand(0, self::$emojiCount)];
        }

        return $result;
    }

    private static function checkAndFillCache() {
        if (empty(self::$emojiCached)) {
            self::$emojiCached = array_values(parent::all());
            self::$emojiCount = count(self::$emojiCached);
        }
    }
}