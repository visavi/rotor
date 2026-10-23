<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\File;
use App\Models\User;

/**
 * Права на вложения записей
 */
class FilePolicy
{
    /**
     * Удаляет и переставляет вложение его автор или администрация
     */
    public function delete(User $user, File $file): bool
    {
        return $file->user_id === $user->id || $user->isAdmin();
    }
}
