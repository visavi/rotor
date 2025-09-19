<?php

namespace App\Traits;

use Illuminate\Support\HtmlString;

trait ShortTextTrait
{
    /**
     * Возвращает сокращённый текст
     */
    public function shortText(): HtmlString
    {
        $params = $this->getParams();
        $text = $params['handler']($this->text);

        $more = view('app/_more', [
            'url'  => $params['url'],
            'text' => $params['text'],
        ]);

        if ($params['cut'] && str_contains($text, '[cut]')) {
            $result = current(explode('[cut]', $text)) . $more;
        } elseif (wordCount($text) > $params['words']) {
            $result = bbCodeTruncate($text, $params['words']) . $more;
        } else {
            $result = bbCode($text);
        }

        return new HtmlString($result);
    }

    /**
     * Get default params
     */
    protected function getParams(): array
    {
        return array_merge([
            'words'   => 100,
            'cut'     => false,
            'text'    => __('main.read_more'),
            'url'     => null,
            'handler' => fn ($text) => $text,
        ], $this->setShortText());
    }

    /**
     * Set short text
     */
    abstract protected function setShortText(): array;
}
