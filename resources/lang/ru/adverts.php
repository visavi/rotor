<?php

return [
    'title'         => 'Реклама на сайте',
    'create_advert' => 'Размещение рекламы',
    'expires'       => 'Истекает',
    'color'         => 'Цвет',
    'bold'          => 'Жирный текст',
    'total_links'   => 'Всего ссылок',
    'empty_links'   => 'В данный момент рекламных ссылок еще нет!',
    'link'          => 'Адрес сайта',
    'name'          => 'Название',
    'buy_for'       => 'Купить за',
    'rules_text'    => '
    Стоимость размещения ссылки ' . plural(setting('rekuserprice'), setting('moneyname')) . ' за ' . setting('rekusertime') . ' часов<br>
    Цвет и жирный текст опционально, стоимость каждой опции ' . plural(setting('rekuseroptprice'), setting('moneyname')) . '<br>
    Ссылка прокручивается на всех страницах сайта с другими ссылками пользователей<br>
    В названии ссылки запрещено использовать любые ненормативные и матные слова<br>
    Адрес ссылки не должен направлять на прямое скачивание какого-либо контента<br>
    Запрещены ссылки на сайты с алярмами и порно<br>
    За нарушение правил предусмотрено наказание в виде бана<br>',

    'user_advert' => 'Пользовательская реклама',
    'edit_advert' => 'Редактирование ссылки',
];