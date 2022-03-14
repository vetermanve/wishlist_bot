<?php


namespace App\Link\Service;


class Links
{
    public static function getLink($id) {
        return 'https://t.me/'.(getenv('TELEGRAM_BOT_NAME') ?? '' ).'?start='.$id;
    }
}