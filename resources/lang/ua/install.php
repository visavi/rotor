<?php

return [
    'install'               => 'Установка',
    'update'                => 'Оновлення',
    'step1'                 => 'Крок 1 - перевірка вимог',
    'step2_install'         => 'Крок 2 - перевірка статусу БД (установка)',
    'step3_install'         => 'Крок 3 - виконання міграцій (установка)',
    'step4_install'         => 'Крок 4 - заповнення БД (установка)',
    'step5_install'         => 'Крок 5 - створення адміністратора (установка)',
    'step2_update'          => 'Крок 2 - перевірка статусу БД (оновлення)',
    'install_completed'     => 'Установка завершена',
    'update_completed'      => 'Оновлення завершено',
    'debug'                 => 'Якщо в процесі установки движка станеться якась помилка, щоб дізнатися причину помилки увімкніть виведення помилок, змініть значення APP_DEBUG на true',
    'env'                   => 'Для встановлення движка, вам необхідно прописати дані від БД у файл .env',
    'app_key'               => 'Не забудьте змінити значення APP_KEY, ці дані необхідні для хешування cookies та паролів у сесіях',
    'requirements'          => 'Мінімальна версія PHP необхідна для роботи движка PHP :php і MySQL :mysql, MariaDB :maria або Postgres :pgsql',
    'check_requirements'    => 'Перевірка вимог',
    'ffmpeg'                => 'Для обробки відео бажано встановити бібліотеку FFmpeg',
    'chmod_rights'          => 'Права доступу',
    'chmod_views'           => 'Додатково можете виставити права на директорії та файли з шаблонами всередині resources/views - це необхідно для редагування шаблонів сайту',
    'chmod'                 => 'Якщо якийсь пункт виділено червоним, необхідно зайти по FTP і виставити CHMOD роздільну здатність',
    'errors'                => 'Деякі настройки є рекомендованими для повної сумісності, однак скрипт може працювати навіть якщо рекомендовані налаштування не співпадають з поточними.',
    'continue'              => 'Ви можете продовжити встановлення двигуна!',
    'requirements_pass'     => 'Всі модулі та бібліотеки присутні, налаштування коректні, необхідні файли та папки доступні для запису',
    'requirements_not_pass' => 'Дані попередження не є критичними, але для повноцінної, стабільної та безпечної роботи двигуна бажано їх усунути',
    'continue_restrict'     => 'Ви можете продовжити встановлення скрипта, але немає жодних гарантій, що двигун працюватиме стабільно',
    'check_status'          => 'Перевірити статус БД',
    'requirements_warning'  => 'У вас є попередження!',
    'requirements_failed'   => 'Є критичні помилки!',
    'requirements_url'      => 'Адреса сайту у файлі env ":env_url" відрізняється від фактичного ":current_url"!',
    'resolve_errors'        => 'Ви не зможете приступити до встановлення, поки не усунете критичні помилки',
    'migrations'            => 'Виконати міграції',
    'seeds'                 => 'Заповнити БД',
    'create_admin'          => 'Створити адміністратора',
    'create_admin_info'     => 'Перш ніж перейти до адміністрування вашого сайту, необхідно створити обліковий запис адміністратора.',
    'create_admin_errors'   => 'Перед тим як натискати кнопку Створити, переконайтеся, що на попередній сторінці немає повідомлень про помилки, інакше процес не зможе бути завершений вдало',
    'delete_install'        => 'Після закінчення інсталяції необхідно видалити файл app/Http/Controllers/InstallController.php, пароль та інші дані ви зможете змінити у своєму профілі',
    'welcome'               => 'Ласкаво просимо!',
    'text_message'          => 'Привіт, :login! Вітаємо з успішною установкою нашого двигуна Rotor.
    Нові версії, апгрейди, а також безліч інших доповнень ви знайдете на нашому сайті [url=https://visavi.net]visavi.net[/url]',
    'text_news'             => 'Ласкаво просимо на демонстраційну сторінку движка Rotor
    Rotor – функціонально закінчена система керування контентом з відкритим кодом написана на PHP. Вона використовує базу даних MySQL для зберігання вмісту вашого сайту. Rotor є гнучкою, потужною та інтуїтивно зрозумілою системою з мінімальними вимогами до хостингу, високим рівнем захисту та є чудовим вибором для побудови сайту будь-якого ступеня складності.
    Головною особливістю Rotor є низьке навантаження на системні ресурси, навіть при дуже великій аудиторії сайту навантаження не сервер буде мінімальним, і ви не відчуватимете будь-яких проблем з відображенням інформації.
    Двигун Rotor ви можете завантажити на офіційному сайті [url=https://visavi.net]visavi.net[/url]',
    'success_install'       => 'Вітаємо, Rotor був успішно встановлений!',
    'success_update'        => 'Вітаємо, Rotor був успішно оновлений!',
    'main_page'             => 'Перейти на головну сторінку сайту',
];
