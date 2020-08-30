<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\DB;

trait AltersEnumMigtationTrait {
    private function alterEnum($tableName, $columnName, $options, $defaultValue = null)
    {
        $dbDriver = config("database.connections." . config('database.default') . ".driver");
        $statements = [];
        if ($dbDriver == 'mysql') {
            $statements[0] = "ALTER TABLE " . $tableName . " MODIFY COLUMN " . $columnName . " ENUM(" . join( ', ', array_map(function ($value) { return "'$value'"; }, $options)) . ") default";
            if (is_null($defaultValue)) {
                $statements[0] .= " NULL";
            } else {
                $statements[0] .= " '" . $defaultValue . "'";
            }
        } elseif ($dbDriver == 'pgsql') {
            $check = "${tableName}_${columnName}_check";
            $statements[0] = sprintf('ALTER TABLE %s DROP CONSTRAINT %s;', $tableName, $check);

            $enumString = join( ', ', array_map(function ($value){
                return sprintf("'%s'::character varying", $value);
            }, $options));
            $statements[1] = sprintf('ALTER TABLE %s ADD CONSTRAINT %s CHECK (%s::TEXT = ANY (ARRAY[%s]::TEXT[]))', $tableName, $check, $columnName, $enumString);
        } else {
            throw new Exception("Can not modify column {$columnName} for database driver {$dbDriver}");
        }

        foreach ($statements as $statement) {
            DB::statement($statement);
        }
    }
}