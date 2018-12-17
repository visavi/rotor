<?php

use Phinx\Migration\AbstractMigration;

class RenameSmilesInSettings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("UPDATE settings SET name = 'stickermaxsize' WHERE name = 'smilemaxsize';");
        $this->execute("UPDATE settings SET name = 'stickermaxweight' WHERE name = 'smilemaxweight';");
        $this->execute("UPDATE settings SET name = 'stickerminweight' WHERE name = 'smileminweight';");
        $this->execute("UPDATE settings SET name = 'stickerlist' WHERE name = 'smilelist';");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("UPDATE settings SET name = 'smilemaxsize' WHERE name = 'stickermaxsize';");
        $this->execute("UPDATE settings SET name = 'smilemaxweight' WHERE name = 'stickermaxweight';");
        $this->execute("UPDATE settings SET name = 'smileminweight' WHERE name = 'stickerminweight';");
        $this->execute("UPDATE settings SET name = 'smilelist' WHERE name = 'stickerlist';");
    }
}
