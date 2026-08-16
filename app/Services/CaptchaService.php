<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Mobicms\Captcha\Image as MobicmsCaptcha;
use Visavi\Captcha\CaptchaBuilder as AnimatedCaptchaBuilder;
use Visavi\Captcha\PhraseBuilder as AnimatedPhraseBuilder;

/**
 * Защитная картинка
 *
 * На сайте фраза лежит в сессии, в API сессии нет — там она хранится в кэше
 * по ключу, который клиент получает вместе с картинкой и возвращает с формой
 */
class CaptchaService
{
    public const CACHE_PREFIX = 'apiCaptcha';

    /** Время жизни задания, за него нужно успеть заполнить форму */
    public const LIFETIME = 600;

    /**
     * Картинка и её разгадка
     *
     * @return array{phrase: string, image: string, mime: string}
     */
    public function build(): array
    {
        if (setting('captcha_type') === 'animated') {
            $builder = new AnimatedPhraseBuilder();
            $phrase = $builder->getPhrase(setting('captcha_maxlength'), setting('captcha_symbols'));

            $captcha = new AnimatedCaptchaBuilder($phrase);

            return ['phrase' => $phrase, 'image' => $captcha->render(), 'mime' => 'image/gif'];
        }

        $captcha = new MobicmsCaptcha();
        $captcha->imageWidth = 180;
        $captcha->imageHeight = 50;
        $captcha->lengthMax = setting('captcha_maxlength');
        $captcha->characterSet = (string) setting('captcha_symbols');

        return ['phrase' => $captcha->getCode(), 'image' => $captcha->build(), 'mime' => 'image/png'];
    }

    /**
     * Задание для клиента API
     *
     * Картинка отдаётся data-URI: отдельный запрос за ней клиенту неудобен,
     * а на форму регистрации она нужна ровно одна
     */
    public function challenge(): array
    {
        $type = (string) setting('captcha_type');

        if (in_array($type, ['recaptcha_v2', 'recaptcha_v3'], true)) {
            return ['type' => $type, 'site_key' => (string) setting('recaptcha_public')];
        }

        if (! in_array($type, ['graphical', 'animated'], true)) {
            return ['type' => 'none'];
        }

        $captcha = $this->build();
        $key = Str::random(32);

        Cache::put(self::CACHE_PREFIX . $key, $captcha['phrase'], self::LIFETIME);

        return [
            'type'       => $type,
            'key'        => $key,
            'image'      => 'data:' . $captcha['mime'] . ';base64,' . base64_encode($captcha['image']),
            'expires_in' => self::LIFETIME,
        ];
    }

    /**
     * Проверка ответа клиента API
     */
    public function verify(Request $request): bool
    {
        if (! in_array(setting('captcha_type'), ['graphical', 'animated'], true)) {
            // reCAPTCHA проверяется по токену от клиента, отключённая капча пропускает всех
            return captchaVerify();
        }

        $key = (string) $request->input('captcha_key');
        $code = (string) $request->input('protect');

        if ($key === '' || $code === '') {
            return false;
        }

        // Задание одноразовое: подобрать ответ ко второй попытке нельзя
        $phrase = Cache::pull(self::CACHE_PREFIX . $key);

        return $phrase !== null && strtolower($code) === strtolower((string) $phrase);
    }
}
