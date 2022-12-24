<?php


namespace App\Landing\Controller;


use App\Admin\Service\Commands;
use App\Base\Controller\WishlistBaseController;
use Verse\Telegram\Run\Controller\TelegramResponse;

class Landing extends WishlistBaseController
{
    public function text_message(): ?TelegramResponse
    {
        $this->setNextResource(null);
        $text = 'Не понял команды "' . $this->p('text').'"';

        $resp = $this->textResponse($text);

        foreach ((new Commands())->getAllCommands() as $link => $desc) {
            $resp->addKeyboardKey($desc, $link);
        }

        return $resp;
    }
}