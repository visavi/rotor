<?php

return [
    'antimat' => [
        'text' => '
        Усі слова зі списку замінятимуться на ***<br>
        Щоб видалити слово, натисніть на нього, додати слово можна у формі нижче<br>',
        'words' => 'Список слів',
        'total_words' => 'Усього слів',
        'confirm_clear' => 'Ви впевнені, що хочете видалити всі слова?',
        'empty_words' => 'Слов ще немає!',
        'enter_word' => 'Введіть слово',

        'not_enter_word' => 'Ви не ввели слово для занесення до списку!',
        'word_listed' => 'Введене слово вже є у списку!',
    ],

    'backup' => [
        'create_backup' => 'Створити бекап',
        'total_backups' => 'Всього бекапів',
        'empty_backups' => 'Бекапів ще немає!',
        'total_tables' => 'Усього таблиць',
        'records' => 'Записів',
        'size' => 'Розмір',
        'compress_method' => 'Метод стиснення',
        'not_compress' => 'Не стискати',
        'compress_ratio' => 'Ступінь стиснення',
        'empty_tables' => 'Немає таблиць для бекапу!',

        'no_tables_save' => 'Не вибрані таблиці для збереження!',
        'wrong_compression_method' => 'Неправильний метод стиснення!',
        'wrong_compression_ratio' => 'Неправильний ступінь стиснення!',
        'database_success_saved' => 'База даних успішно оброблена та збережена!',
        'backup_not_indicated' => 'Не вказано назву бекапу для видалення!',
        'invalid_backup_name' => 'Неприпустима назва бекапу!',
        'backup_not_exist' => 'Файлу видалення не існує!',
        'backup_success_deleted' => 'Бекап успішно видалено!',
    ],

    'banhists' => [
        'history' => 'Історія',
        'search_user' => 'Пошук користувача',
        'empty_history' => 'Історії банів ще немає!',
        'view_history' => 'Перегляд історії',
    ],

    'bans' => [
        'login_hint' => 'Введіть логін користувача, який необхідно змінити',
        'user_ban' => 'Бан користувача',
        'change_ban' => 'Зміна бана',
        'time_ban' => 'Час бана',
        'banned' => 'Забанити',
        'ban_hint' => 'Увага! Постарайтеся якнайдокладніше описати причину бана',
        'confirm_unban' => 'Ви дійсно бажаєте розбанити користувача?',

        'forbidden_ban' => 'Заборонено банити адміністрацію сайту!',
        'user_banned' => 'Цей користувач вже заблокований!',
        'user_not_banned' => 'Цей користувач не забанен!',
        'time_not_indicated' => 'Ви не вказали час бана!',
        'time_not_selected' => 'Не вибрано час бана!',
        'time_empty' => 'Занадто маленький час бана!',
        'success_banned' => 'Користувач успішно забанен!',
        'success_unbanned' => 'Користувач успішно розбанений!',
    ],

    'blacklists' => [
        'email' => 'Email',
        'logins' => 'Логини',
        'domains' => 'Домени',
        'empty_list' => 'Список ще порожній!',

        'type_not_found' => 'Вказаний тип не знайдено!',
        'invalid_login' => 'Неприпустимі символи в логіні!',
    ],

    'caches' => [
        'files' => 'Файли',
        'images' => 'Зображення',
        'views' => 'Шаблони',
        'clear' => 'Очистити кеш',
        'empty_files' => 'Файлів ще немає!',
        'success_cleared' => 'Кеш успішно очищений!',
        'only_file_cache' => 'Відображається лише файловий кеш',
    ],

    'chat' => [
        'clear' => 'Очистити чат',
        'confirm_clear' => 'Ви дійсно хочете очистити адмін-чат?',
        'edit_message' => 'Редагування повідомлення',
        'post_added_after' => 'Додано через :sec сек.',
        'success_cleared' => 'Адмін-чат успішно очищений!',
    ],

    'checkers' => [
        'new_files' => 'Нові файли та нові параметри файлів',
        'old_files' => 'Видалені файли та старі параметри файлів',
        'empty_changes' => 'Немає змін!',
        'initial_scan' => 'Необхідно провести початкове сканування!',
        'information_scan' => 'Сканування системи дозволяє дізнатися які файли або папки змінювалися протягом певного часу',
        'invalid_extensions' => 'Увага, сервіс не враховує деякі розширення файлів',
        'scan' => 'Сканувати',
        'success_crawled' => 'Сайт успішно проскановано!',
    ],

    'delivery' => [
        'online' => 'В онлайні',
        'active' => 'Активним',
        'admins' => 'Адміністрація',
        'users' => 'Всім користувачам',
        'not_recipients_selected' => 'Ви не вибрали одержувачів розсилки!',
        'not_recipients' => 'Відсутні одержувачі розсилки!',
        'success_sent' => 'Повідомлення успішно надіслано!',
    ],

    'delusers' => [
        'condition' => 'Видалити користувачів, які не відвідували сайт',
        'minimum_asset' => 'Мінімум активу',
        'deleted_condition' => 'Будуть видалені користувачі, які не відвідували сайт більше',
        'asset_condition' => 'І що мають у своєму активі не більше',
        'deleted_users' => 'Буде видалено користувачів',
        'delete_users' => 'Видалити користувачів',
        'invalid_period' => 'Вказано неприпустимий час для видалення!',
        'users_not_found' => 'Відсутні користувачі для видалення!',
        'success_deleted' => 'Користувачі успішно видалені!',
    ],

    'errors' => [
        'hint' => 'Увага! Запис логів вимкнено в налаштуваннях!',
        'errors' => 'Помилки',
        'autobans' => 'Автобани',
        'logs_not_exist' => 'Зазначені логи не існують!',
        'success_cleared' => 'Логи успішно очищені!',
    ],

    'files' => [
        'confirm_delete_dir' => 'Ви дійсно хочете видалити цю директорію?',
        'confirm_delete_file' => 'Ви дійсно хочете видалити цей файл?',
        'objects' => 'Об\'єктів',
        'lines' => 'Рядок',
        'changed' => 'Змінено',
        'empty_objects' => 'Об\'єктів немає!',
        'create_object' => 'Створення нового об\'єкта',
        'directory_name' => 'Назва директорії',
        'create_directory' => 'Створити директорію',
        'file_name' => 'Назва файлу (без розширення)',
        'create_file' => 'Створити файл',
        'create_hint' => 'Дозволено латинські символи та цифри, а також знаки дефіс та нижнє підкреслення',
        'file_editing' => 'Редагування файлу',
        'edit_hint' => 'Натисніть Ctrl+Enter для перекладу рядка',
        'writable' => 'Увага! Файл недоступний для запису!',
        'file_not_exist' => 'Цей файл не існує!',
        'directory_not_exist' => 'Даної директорії не існує!',
        'directory_not_writable' => 'Директорія :dir недоступна для запису!',
        'file_required' => 'Необхідно ввести назву файлу!',
        'directory_required' => 'Необхідно ввести назву директорії!',
        'file_invalid' => 'Неприпустима назва файлу!',
        'directory_invalid' => 'Неприпустима назва директорії!',
        'file_success_saved' => 'Файл успішно збережено!',
        'file_success_created' => 'Новий файл успішно створено!',
        'directory_success_created' => 'Нову директорію успішно створено!',
        'file_success_deleted' => 'Файл успішно видалено!',
        'directory_success_deleted' => 'Директорія успішно видалена!',
        'file_exist' => 'Файл з цією назвою вже існує!',
        'directory_exist' => 'Директорія з цією назвою вже існує!',
    ],

    'invitations' => [
        'creation_keys' => 'Створення ключів',
        'key_generation' => 'Генерація нових ключів',
        'send_to_user' => 'Надіслати ключ користувачеві',
        'sending_keys' => 'Розсилка ключів',
        'send_to_active_users' => 'Розіслати ключі активним користувачам',
        'keys_not_amount' => 'Не вказано кількість ключів!',
        'keys_success_created' => 'Ключі успішно створені!',
        'keys_success_sent' => 'Ключі успішно відправлені!',
        'keys_empty_recipients' => 'Відсутні отримувачі ключів!',
        'keys_success_deleted' => 'Вибрані ключі успішно видалені!',
    ],

    'ipbans' => [
        'history' => 'Історія автобанів',
        'empty_ip' => 'У бан-листі поки що порожньо!',
        'confirm_clear' => 'Ви дійсно хочете очистити список IP?',
        'ip_invalid' => 'Ви ввели неприпустиму IP-адресу',
        'ip_exists' => 'Введений IP вже є у списку!',
        'ip_success_added' => 'IP успішно додано до списку!',
        'ip_selected_deleted' => 'Вибрані IP успішно видалені зі списку!',
        'ip_success_cleared' => 'Список IP успішно очищений!',
    ],

    'logs' => [
        'page' => 'Сторінка',
        'referer' => 'Звідки',
        'confirm_clear' => 'Ви впевнені, що хочете очистити логи?',
        'empty_logs' => 'Логов ще немає!',
        'success_cleared' => 'Лог-файл успішно очищений!',
    ],

    'modules' => [
        'module' => 'Модуль',
        'migrations' => 'Міграції',
        'symlink' => 'Сімлінк',
        'empty_modules' => 'Модулі ще не завантажені!',
        'confirm_delete' => 'Ви дійсно хочете видалити модуль?',
        'hint' => 'Увага! При видаленні модуля будуть видалені всі міграції та зміни в БД',
        'module_not_found' => 'Цей модуль не знайдено!',
        'module_success_installed' => 'Модуль успішно встановлений!',
        'module_success_updated' => 'Модуль успішно оновлено!',
        'module_success_enabled' => 'Модуль успішно включений!',
        'module_success_disabled' => 'Модуль успішно вимкнено!',
        'module_success_deleted' => 'Модуль успішно видалено!',
    ],

    'notices' => [
        'confirm_delete' => 'Ви дійсно хочете видалити цей шаблон?',
        'empty_notices' => 'Шаблонів ще немає!',
        'edit' => 'Редагування шаблону',
        'edit_system_template' => 'Ви редагуєте системний шаблон',
        'system_template' => 'Системний шаблон',
        'create' => 'Створення шаблону',
        'notice_invalid' => 'Неприпустима назва типу шаблону!',
        'notice_length' => 'Занадто довгий або короткий тип шаблону!',
        'notice_exists' => 'Цей тип вже є в списку!',
        'notice_success_saved' => 'Шаблон успішно збережено!',
        'notice_not_found' => 'Цей шаблон не знайдено!',
        'notice_protect' => 'Заборонено видаляти захищений шаблон!',
        'notice_success_deleted' => 'Шаблон успішно видалено!',
    ],

    'reglists' => [
        'enabled' => 'Підтвердження реєстрацій увімкнено!',
        'disabled' => 'Підтвердження реєстрації вимкнено!',
        'empty_users' => 'Немає користувачів, які потребують підтвердження реєстрації!',
        'users_not_selected' => 'Відсутні обрані користувачі!',
        'users_success_approved' => 'Вибрані користувачі успішно схвалені!',
        'users_success_deleted' => 'Вибрані користувачі успішно видалені!',
    ],

    'rules' => [
        'empty_rules' => 'Правила сайту ще не встановлені!',
        'editing_rules' => 'Редагування правил',
        'variables' => 'Внутрішні змінні',
        'sitename' => 'Назва сайту',
        'rules_empty' => 'Ви не ввели текст із правилами сайту!',
        'rules_success_saved' => 'Правила успішно збережені!',
    ],

    'spam' => [
        'go_to_message' => 'Перейти до повідомлення',
        'empty_spam' => 'Скарг ще немає!',
    ],

    'paid_adverts' => [
        'top_all' => 'Все верх',
        'top' => 'Головна верх',
        'forum' => 'Форум',
        'bottom_all' => 'Все низ',
        'bottom' => 'Головна низ',
        'create_advert' => 'Розміщення реклами',
        'edit_advert' => 'Редагування реклами',
        'expires' => 'Закінчується',
        'expired' => 'Мир',
        'color' => 'Колір',
        'bold' => 'Жирний текст',
        'place' => 'Місце',
        'link' => 'Адреса сайту',
        'names' => 'Назви',
        'name' => 'Назва',
        'term' => 'Термін',
        'empty_links' => 'Рекламних посилань ще немає!',
        'confirm_delete_advert' => 'Ви підтверджуєте видалення рекламного посилання?',
        'not_found' => 'Рекламне посилання не знайдено!',
        'place_invalid' => 'Місце розміщення не існує!',
        'term_invalid' => 'Термін розміщення повинен бути більшим за поточний час!',
        'names_count' => 'Необхідно вказати щонайменше 1 назву!',
    ],

    'not_exists_boss' => '
        Увага! Відсутня профіль суперадміну<br>
        У базі даних не знайдено користувача з правами boss',
    'exists_install' => '
        Увага! Необхідно видалити файл app/Http/Controllers/InstallController.php<br>
        Наявність цього файлу може порушити безпеку сайту. Видаліть його прямо зараз!',

    'user_fields' => [
        'required' => 'Обов\'язкове',
        'edit_field' => 'Редагування поля',
        'create_field' => 'Створення поля',
        'empty_fields' => 'Полів користувача ще немає!',
        'input' => 'Однорядкове поле',
        'textarea' => 'Многорядкове поле',
    ],
];
