<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->renameColumnsAndConstraints(
            oldColumn: 'client_id',
            newColumn: 'tenant_id',
            referencedTableOverride: 'tenants'
        );

        $this->renameColumnsAndConstraints(
            oldColumn: 'client_subscription_id',
            newColumn: 'tenant_subscription_id',
            referencedTableOverride: 'tenants_subscriptions'
        );
    }

    public function down(): void
    {
        $this->renameColumnsAndConstraints(
            oldColumn: 'tenant_id',
            newColumn: 'client_id',
            referencedTableOverride: 'tenants'
        );

        $this->renameColumnsAndConstraints(
            oldColumn: 'tenant_subscription_id',
            newColumn: 'client_subscription_id',
            referencedTableOverride: 'tenants_subscriptions'
        );
    }

    private function renameColumnsAndConstraints(
        string $oldColumn,
        string $newColumn,
        string $referencedTableOverride
    ): void {
        $databaseName = DB::getDatabaseName();

        $foreignKeys = collect(DB::select(
            <<<'SQL'
            SELECT
                kcu.TABLE_NAME AS table_name,
                kcu.COLUMN_NAME AS column_name,
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
              AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
              AND kcu.COLUMN_NAME = ?
            ORDER BY kcu.TABLE_NAME
            SQL,
            [$databaseName, $oldColumn]
        ));

        foreach ($foreignKeys as $foreignKey) {
            DB::statement(sprintf(
                'ALTER TABLE `%s` DROP FOREIGN KEY `%s`',
                $foreignKey->table_name,
                $foreignKey->constraint_name
            ));
        }

        $columns = collect(DB::select(
            <<<'SQL'
            SELECT
                TABLE_NAME AS table_name,
                COLUMN_NAME AS column_name,
                COLUMN_TYPE AS column_type,
                IS_NULLABLE AS is_nullable,
                COLUMN_DEFAULT AS column_default,
                EXTRA AS extra
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = ?
              AND COLUMN_NAME = ?
            ORDER BY TABLE_NAME
            SQL,
            [$databaseName, $oldColumn]
        ));

        foreach ($columns as $column) {
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
                $column->table_name,
                $oldColumn,
                $newColumn,
                $column->column_type,
                $nullableSql,
                $defaultSql,
                $extraSql
            ));
        }

        foreach ($foreignKeys as $foreignKey) {
            $newConstraintName = $this->truncateConstraintName(
                str_replace($oldColumn, $newColumn, $foreignKey->constraint_name)
            );

            $referencedTable = $referencedTableOverride;
            $referencedColumn = $foreignKey->referenced_column_name ?? 'id';

            $updateRule = strtoupper((string) ($foreignKey->update_rule ?? 'NO ACTION'));
            $deleteRule = strtoupper((string) ($foreignKey->delete_rule ?? 'NO ACTION'));

            $onUpdateSql = $this->formatRule($updateRule);
            $onDeleteSql = $this->formatRule($deleteRule);

            DB::statement(sprintf(
                'ALTER TABLE `%s` ADD CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES `%s`(`%s`) ON UPDATE %s ON DELETE %s',
                $foreignKey->table_name,
                $newConstraintName,
                $newColumn,
                $referencedTable,
                $referencedColumn,
                $onUpdateSql,
                $onDeleteSql
            ));
        }
    }

    private function formatRule(string $rule): string
    {
        return match ($rule) {
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

