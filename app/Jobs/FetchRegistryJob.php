<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\ModuleRegistry;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class FetchRegistryJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Реестр за сетью: одна попытка, вторая упрётся в тот же таймаут
     */
    public int $tries = 1;

    public function __construct(
        public int $registryId,
    ) {
    }

    /**
     * Кнопку обновления жмут по нескольку раз подряд, пока ничего не меняется:
     * без этого в очередь ложился бы десяток опросов одного реестра
     */
    public function uniqueId(): string
    {
        return (string) $this->registryId;
    }

    public function handle(): void
    {
        ModuleRegistry::query()->find($this->registryId)?->fetch(force: true);
    }
}
