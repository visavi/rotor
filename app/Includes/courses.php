<?php

use Curl\Curl;

if (@filemtime(STORAGE."/temp/courses.dat") < time() - 3600 || @filesize(STORAGE."/temp/courses.dat") == 0) {

    $curl = new Curl();

    if ($xml = $curl->get('http://www.cbr.ru/scripts/XML_daily.asp')){

        $courses = [];
        $courses['Date'] = (string) $xml->attributes()->Date;
        foreach ($xml->Valute as $item) {

            $courses[(string) $item->CharCode] = [
                'name' => (string) $item->Name,
                'value' => (string) $item->Value,
                'nominal' => (string) $item->Nominal,
            ];
        }

        file_put_contents(STORAGE."/temp/courses.dat", json_encode($courses), LOCK_EX);
    }
}

$courses = @json_decode(file_get_contents(STORAGE."/temp/courses.dat"));

if (! empty($courses->USD)): ?>

    <b>Курсы валют</b> (<?= $courses->Date ?>)<br>
    <b><?= $courses->USD->nominal ?> <?= $courses->USD->name ?></b> - <?= $courses->USD->value ?><br>
    <b><?= $courses->EUR->nominal ?> <?= $courses->EUR->name ?></b> - <?= $courses->EUR->value ?><br>
    <b><?= $courses->UAH->nominal ?> <?= $courses->UAH->name ?></b> - <?= $courses->UAH->value ?><br>

<?php else: ?>
    <?php showError('Ошибка! Не удалось загрузить последние курсы валют!'); ?>
<?php endif; ?>
