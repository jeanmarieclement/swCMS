<?php

namespace App\Core\Database;

use App\Core\Database\Database;

/**
 * Base Migration class
 */

abstract class Migration
{
    /**
     * Constructor
     */
    /**
     * Migration constructor
     * Uses Database::getInstance() for DB access
     */
    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    /**
     * Execute the migration
     */
    abstract public function up();

    /**
     * Rollback the migration
     */
    abstract public function down();

    /**
     * Execute a raw SQL query
     *
     * @param string $sql
     * @return bool
     */
    /**
     * Execute a raw SQL query using the PDO instance
     * @param string $sql
     * @return bool|int
     */
    protected function execute($sql)
    {
        return $this->db->exec($sql);
    }

    /**
     * Add a foreign key constraint
     *
     * @param string $table
     * @param string $column
     * @param string $referenceTable
     * @param string $referenceColumn
     * @param string $onDelete
     * @param string $onUpdate
     * @return bool
     */
    /**
     * Add a foreign key constraint (MySQL only)
     * @param string $table
     * @param string $column
     * @param string $referenceTable
     * @param string $referenceColumn
     * @param string $onDelete
     * @param string $onUpdate
     * @return bool|int
     */
    protected function addForeignKey($table, $column, $referenceTable, $referenceColumn = 'id', $onDelete = 'CASCADE', $onUpdate = 'CASCADE')
    {
        $constraintName = "fk_{$table}_{$column}";
        $sql = "ALTER TABLE `{$table}` 
                ADD CONSTRAINT `{$constraintName}` 
                FOREIGN KEY (`{$column}`) 
                REFERENCES `{$referenceTable}` (`{$referenceColumn}`)
                ON DELETE {$onDelete} 
                ON UPDATE {$onUpdate}";

        return $this->execute($sql);
    }

    /**
     * Drop a foreign key constraint
     *
     * @param string $table
     * @param string $constraintName
     * @return bool
     */
    protected function dropForeignKey($table, $constraintName)
    {
        $sql = "ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraintName}`";
        return $this->execute($sql);
    }
}
