<?php


namespace Base\Schema;


use Verse\Renderer\Component\RenderSetupComponent;
use Verse\Telegram\Run\Scheme\TelegramPullExtendedScheme;

class WishlistSchema extends TelegramPullExtendedScheme
{
    public function configure()
    {
        $this->addComponent(new RenderSetupComponent());

        parent::configure();
    }

}