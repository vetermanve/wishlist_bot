<?php


namespace Run\Controller;


use Verse\Telegram\Run\Controller\TelegramRunController;

class TelegramExtendedController extends TelegramRunController
{
    public function getUserId ()  {
        $form = $this->requestWrapper->getParam('from');

        if (isset($form, $form['id'])) {
            return  $form['id'];
        }

        return null;
    }
}