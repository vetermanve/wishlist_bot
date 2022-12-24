<?php


namespace App\Admin\Service;


use App\Admin\Controller\Restart;
use App\Admin\Controller\SetCommands;
use App\Start\Controller\Start;
use App\Wishlist\Controller\All;
use App\Wishlist\Controller\Wishlist;
use Verse\Telegram\Run\RequestRouter\ResourceCompiler;

class Commands
{
    public function getAllCommands($withAdmins = false)
    {
        $commands = [
            $this->r(Start::class) => 'В начало',
            $this->r(Wishlist::class) => Wishlist::$description,
            $this->r(All::class) => 'Все вишлистыы',
        ];

        if ($withAdmins) {
            $commands += [
                $this->r(Restart::class), 'description' => 'Перезапустить бота',
                $this->r(SetCommands::class), 'description' => 'Установит команды бота',
            ];
        }

        return $commands;
    }

    private function r($className) {
        return ResourceCompiler::fromClassName($className);
    }
}

