<?php

namespace App\Components;


use Phalcon\Di\Injectable;
use Phalcon\Translate\Adapter\NativeArray;
use Phalcon\Translate\InterpolatorFactory;
use Phalcon\Translate\TranslateFactory;

class Locale extends Injectable
{
    /**
     * @return NativeArray
     */
    public function getTranslator(): NativeArray
    {

        $language = $this->request->get('locale')??'en';

        $messages = [];
        $translationFile = '../app/messages/' . $language . '.php';

        if (true !== file_exists($translationFile)) {
            $translationFile = '../app/messages/en.php';
        }
        require $translationFile;

        $interpolator = new InterpolatorFactory();
        $factory      = new TranslateFactory($interpolator);

        if ($this->cache->has($language)) {
            echo "cache";
            return $factory->newInstance(
                'array',
                [
                    'content' => ((array)$this->cache->get($language))
                ]
            );
        }
        echo "file";

        $this->cache->set($language, $messages);
        return $factory->newInstance(
            'array',
            [
                'content' => $messages,
            ]
        ); 
    }
}
