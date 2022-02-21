<?php


namespace Service\Routing;


use Run\RequestRouter\TextRouterInterface;
use Verse\Di\Env;
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
//            $originalResource = $request->getResource();
//            if (strlen($originalResource) > 1) {
//                $originalText = mb_substr($originalResource, 1);
//            } else {
                return null;
//            }
        }

        $text = mb_strtolower($originalText);

        $resource = null;

        if ($text === 'хватит') {
            $resource = '/done';
        }

        if ($text === 'покажи') {
            $resource = '/item_all';
        }

        if ($text === 'list') {
            if ($request->getChannelState()->get('edit_mode') === 1) {
                $resource = '/item_edit_mode';
            } else {
                $resource = '/item_all';
            }
        }

        if ($text === 'rename') {
            $resource = '/wishlist_name';
        }

        if (($result = mb_eregi_replace( 'хочу', ' ', $originalText)) !== $originalText) {
            Env::getContainer()->bootstrap('logger')->debug('EREGI', ['text' => $text, 'result' => $result,  ]);
            $resource = '/item_draft';
            $data['text'] = $this->mb_ucfirst(trim(preg_replace('/\s+/', ' ', $result)));
        }

        return [$resource, $data];
    }

    private function mb_ucfirst($string)
    {
        return mb_strtoupper(mb_substr($string, 0, 1)).mb_substr($string, 1);
    }
}