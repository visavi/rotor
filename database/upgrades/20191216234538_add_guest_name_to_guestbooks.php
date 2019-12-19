<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddGuestNameToGuestbooks extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change(): void
    {
        $table = $this->table('guestbooks');
        $table->addColumn('guest_name', 'string', ['limit' => 20, 'after' => 'reply', 'null' => true])
            ->update();
    }
}
