<?php


namespace Service\Routing;


use App\Done\Controller\Done;
use App\Item\Controller\AllItems;
use App\Item\Controller\Draft;
use App\Item\Controller\EditMode;
use App\Wishlist\Controller\Name;
use Verse\Telegram\Run\RequestRouter\ResourceCompiler;
use Verse\Telegram\Run\RequestRouter\TextRouterInterface;
use Verse\Run\RunRequest;

class TextRouting implements TextRouterInterface
{

    /**
     * @inheritDoc
     */
    public function getClassAndData(RunRequest $request)
    {
        $data = [];
        $originalText = $request->getParamOrData('text');
        if (!$originalText) {
            return null;
        }

        $text = mb_strtolower($originalText);

        $resource = null;

        if ($text === 'хватит') {
            $resource = ResourceCompiler::fromClassName(Done::class);
        }

        if ($text === 'покажи') {
            $resource = ResourceCompiler::fromClassName(AllItems::class);
        }

        if ($text === 'list') {
            if ($request->getChannelState()->get('edit_mode') === 1) {
                $resource = ResourceCompiler::fromClassName(EditMode::class);
            } else {
                $resource = ResourceCompiler::fromClassName(AllItems::class);
            }
        }

        if ($text === 'rename') {
            $resource = ResourceCompiler::fromClassName(Name::class);
        }

        if (($result = mb_eregi_replace( 'хочу', ' ', $originalText)) !== $originalText) {
//            Env::getContainer()->bootstrap('logger')->debug('EREGI', ['text' => $text, 'result' => $result,  ]);
            $resource = ResourceCompiler::fromClassName(Draft::class);
            $data['text'] = $this->mb_ucfirst(trim(preg_replace('/\s+/', ' ', $result)));
        }

        return [$resource, $data];
    }

    private function mb_ucfirst($string)
    {
        return mb_strtoupper(mb_substr($string, 0, 1)).mb_substr($string, 1);
    }
}