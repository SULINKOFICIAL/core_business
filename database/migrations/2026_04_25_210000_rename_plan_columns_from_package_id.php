<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->renameColumnWithForeignKeys('orders', 'package_id', 'plan_id');
        $this->renameColumnWithForeignKeys('tenants_plans_items', 'package_id', 'plan_id');
    }

    public function down(): void
    {
        $this->renameColumnWithForeignKeys('orders', 'plan_id', 'package_id');
        $this->renameColumnWithForeignKeys('tenants_plans_items', 'plan_id', 'package_id');
    }

    private function renameColumnWithForeignKeys(string $tableName, string $oldColumn, string $newColumn): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        if (!Schema::hasColumn($tableName, $oldColumn) || Schema::hasColumn($tableName, $newColumn)) {
            return;
        }

        $databaseName = DB::getDatabaseName();

        $foreignKeys = collect(DB::select(
            <<<'SQL'
            SELECT
                kcu.CONSTRAINT_NAME AS constraint_name,
                kcu.REFERENCED_TABLE_NAME AS referenced_table_name,
                kcu.REFERENCED_COLUMN_NAME AS referenced_column_name,
                rc.UPDATE_RULE AS update_rule,
                rc.DELETE_RULE AS delete_rule
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
            LEFT JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS rc
                ON rc.CONSTRAINT_SCHEMA = kcu.CONSTRAINT_SCHEMA
                AND rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
            WHERE kcu.CONSTRAINT_SCHEMA = ?
              AND kcu.TABLE_NAME = ?
              AND kcu.COLUMN_NAME = ?
              AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
            SQL,
            [$databaseName, $tableName, $oldColumn]
        ));

        foreach ($foreignKeys as $foreignKey) {
            DB::statement(sprintf(
                'ALTER TABLE `%s` DROP FOREIGN KEY `%s`',
                $tableName,
                $foreignKey->constraint_name
            ));
        }

        $column = DB::selectOne(
            <<<'SQL'
            SELECT
                COLUMN_TYPE AS column_type,
                IS_NULLABLE AS is_nullable,
                COLUMN_DEFAULT AS column_default,
                EXTRA AS extra
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
            LIMIT 1
            SQL,
            [$databaseName, $tableName, $oldColumn]
        );

        if (!$column) {
            return;
        }

        $nullableSql = $column->is_nullable === 'YES' ? 'NULL' : 'NOT NULL';
        $defaultSql = '';

        if ($column->column_default !== null) {
            if (strtoupper((string) $column->column_default) === 'CURRENT_TIMESTAMP') {
                $defaultSql = ' DEFAULT CURRENT_TIMESTAMP';
            } else {
                $defaultSql = " DEFAULT '" . addslashes((string) $column->column_default) . "'";
            }
        } elseif ($column->is_nullable === 'YES') {
            $defaultSql = ' DEFAULT NULL';
        }

        $extraSql = !empty($column->extra) ? ' ' . $column->extra : '';

        DB::statement(sprintf(
            'ALTER TABLE `%s` CHANGE `%s` `%s` %s %s%s%s',
            $tableName,
            $oldColumn,
            $newColumn,
            $column->column_type,
            $nullableSql,
            $defaultSql,
            $extraSql
        ));

        foreach ($foreignKeys as $foreignKey) {
            $newConstraintName = $this->truncateConstraintName(
                str_replace($oldColumn, $newColumn, $foreignKey->constraint_name)
            );

            $referencedTable = $foreignKey->referenced_table_name;
            $referencedColumn = $foreignKey->referenced_column_name ?? 'id';

            $updateRule = $this->formatRule((string) ($foreignKey->update_rule ?? 'NO ACTION'));
            $deleteRule = $this->formatRule((string) ($foreignKey->delete_rule ?? 'NO ACTION'));

            DB::statement(sprintf(
                'ALTER TABLE `%s` ADD CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES `%s`(`%s`) ON UPDATE %s ON DELETE %s',
                $tableName,
                $newConstraintName,
                $newColumn,
                $referencedTable,
                $referencedColumn,
                $updateRule,
                $deleteRule
            ));
        }
    }

    private function formatRule(string $rule): string
    {
        return match (strtoupper($rule)) {
            'RESTRICT' => 'RESTRICT',
            'CASCADE' => 'CASCADE',
            'SET NULL' => 'SET NULL',
            'NO ACTION' => 'NO ACTION',
            default => 'NO ACTION',
        };
    }

    private function truncateConstraintName(string $constraintName): string
    {
        return strlen($constraintName) <= 64
            ? $constraintName
            : substr($constraintName, 0, 64);
    }
};
