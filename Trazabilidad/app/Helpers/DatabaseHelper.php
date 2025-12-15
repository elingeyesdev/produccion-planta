<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class DatabaseHelper
{
    /**
     * Obtiene el siguiente ID de una secuencia de PostgreSQL, sincronizando primero si es necesario
     * 
     * @param string $sequenceName Nombre de la secuencia (ej: 'operator_seq')
     * @param string $tableName Nombre de la tabla (ej: 'operator')
     * @param string $idColumn Nombre de la columna ID (ej: 'operator_id')
     * @return int
     */
    public static function getNextSequenceId(string $sequenceName, string $tableName, string $idColumn): int
    {
        // Sincronizar la secuencia con el máximo ID existente
        $maxId = DB::table($tableName)->max($idColumn) ?? 0;
        
        try {
            DB::statement("SELECT setval('{$sequenceName}', {$maxId}, true)");
        } catch (\Exception $e) {
            // Si la secuencia no existe, crearla
            DB::statement("CREATE SEQUENCE IF NOT EXISTS {$sequenceName} START WITH " . ($maxId + 1));
        }
        
        // Obtener el siguiente ID de la secuencia
        $result = DB::selectOne("SELECT nextval('{$sequenceName}') as id");
        return $result->id;
    }
    
    /**
     * Inserta un registro usando SQL directo con nextval para evitar conflictos
     * 
     * @param string $tableName Nombre de la tabla
     * @param string $sequenceName Nombre de la secuencia
     * @param string $idColumn Nombre de la columna ID
     * @param array $data Datos a insertar (sin incluir el ID)
     * @return int El ID generado
     */
    public static function insertWithAutoId(string $tableName, string $sequenceName, string $idColumn, array $data): int
    {
        // Sincronizar la secuencia
        $maxId = DB::table($tableName)->max($idColumn) ?? 0;
        
        try {
            DB::statement("SELECT setval('{$sequenceName}', {$maxId}, true)");
        } catch (\Exception $e) {
            DB::statement("CREATE SEQUENCE IF NOT EXISTS {$sequenceName} START WITH " . ($maxId + 1));
        }
        
        // Construir la consulta SQL con nextval
        $columns = array_keys($data);
        $columns[] = $idColumn;
        $placeholders = array_fill(0, count($data), '?');
        $placeholders[] = "nextval('{$sequenceName}')";
        
        $columnsStr = implode(', ', $columns);
        $placeholdersStr = implode(', ', $placeholders);
        $values = array_values($data);
        
        $sql = "INSERT INTO {$tableName} ({$columnsStr}) VALUES ({$placeholdersStr}) RETURNING {$idColumn}";
        
        $result = DB::selectOne($sql, $values);
        return $result->{$idColumn};
    }
}

