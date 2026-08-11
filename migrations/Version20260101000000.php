<?php

declare(strict_types=1);

namespace ForumifyPluginSkeletonPluginMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Creates the example table.
 *
 * Generate new migrations with `make migration` after changing your entities. Keep them
 * in this namespace: forumify registers the migrations directory under the namespace the
 * files declare, and that name ends up in the database as part of the executed versions.
 */
final class Version20260101000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the example table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE plugin_skeleton_example (
                id INT AUTO_INCREMENT NOT NULL,
                title VARCHAR(255) NOT NULL,
                content LONGTEXT DEFAULT NULL,
                created_at DATETIME NOT NULL,
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE plugin_skeleton_example');
    }
}
