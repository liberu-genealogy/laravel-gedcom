<?php

declare(strict_types=1);

namespace FamilyTree365\LaravelGedcom\Utils;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Optimized batch data operations using PHP 8.4 features
 */
final class BatchData
{
    private const int DEFAULT_CHUNK_SIZE = 1000;
    private const int MAX_CHUNK_SIZE = 5000;

    /**
     * Optimized upsert operation with automatic chunking
     */
    public static function upsert(
        string $modelClass, 
        string $conn, 
        array $values, 
        array $uniqueBy, 
        array $update = []
    ): bool {
        if (empty($values)) {
            return true;
        }

        // PHP 8.4: Use match for better performance
        $chunkSize = match (true) {
            count($values) > self::MAX_CHUNK_SIZE => self::MAX_CHUNK_SIZE,
            count($values) > self::DEFAULT_CHUNK_SIZE => self::DEFAULT_CHUNK_SIZE,
            default => count($values)
        };

        $chunks = array_chunk($values, $chunkSize);
        $success = true;

        foreach ($chunks as $chunk) {
            try {
                $result = app($modelClass)->on($conn)->upsert($chunk, $uniqueBy, $update);
                $success = $success && ($result !== false);
            } catch (\Throwable $e) {
                \Log::error("Batch upsert failed", [
                    'model' => $modelClass,
                    'connection' => $conn,
                    'chunk_size' => count($chunk),
                    'error' => $e->getMessage()
                ]);
                $success = false;
            }
        }

        return $success;
    }

    /**
     * High-performance bulk insert with transaction support
     */
    public static function bulkInsert(
        string $modelClass,
        string $conn,
        array $values,
        int $chunkSize = self::DEFAULT_CHUNK_SIZE
    ): bool {
        if (empty($values)) {
            return true;
        }

        return DB::connection($conn)->transaction(function () use ($modelClass, $conn, $values, $chunkSize) {
            $chunks = array_chunk($values, $chunkSize);

            foreach ($chunks as $chunk) {
                app($modelClass)->on($conn)->insert($chunk);
            }

            return true;
        });
    }

    /**
     * Optimized bulk update operation
     */
    public static function bulkUpdate(
        string $modelClass,
        string $conn,
        array $updates,
        string $keyColumn = 'id'
    ): int {
        if (empty($updates)) {
            return 0;
        }

        $model = app($modelClass)->on($conn);
        $table = $model->getTable();
        $updatedCount = 0;

        // PHP 8.4: Use array_chunk with optimized size
        $chunks = array_chunk($updates, self::DEFAULT_CHUNK_SIZE);

        foreach ($chunks as $chunk) {
            $cases = [];
            $ids = [];
            $columns = array_keys($chunk[0] ?? []);

            // Remove the key column from updates
            $columns = array_filter($columns, fn($col) => $col !== $keyColumn);

            foreach ($columns as $column) {
                $whenClauses = [];
                foreach ($chunk as $update) {
                    $id = $update[$keyColumn];
                    $value = $update[$column] ?? null;
                    $whenClauses[] = "WHEN {$keyColumn} = ? THEN ?";
                    $ids[] = $id;
                    $ids[] = $value;
                }

                $cases[$column] = "CASE " . implode(' ', $whenClauses) . " ELSE {$column} END";
            }

            if (!empty($cases)) {
                $setClauses = [];
                foreach ($cases as $column => $case) {
                    $setClauses[] = "{$column} = {$case}";
                }

                $updateIds = array_column($chunk, $keyColumn);
                $placeholders = str_repeat('?,', count($updateIds) - 1) . '?';

                $sql = "UPDATE {$table} SET " . implode(', ', $setClauses) . 
                       " WHERE {$keyColumn} IN ({$placeholders})";

                $bindings = array_merge($ids, $updateIds);
                $updatedCount += DB::connection($conn)->update($sql, $bindings);
            }
        }

        return $updatedCount;
    }

    /**
     * Memory-efficient batch processing with callback
     */
    public static function processBatch(
        array $data,
        callable $processor,
        int $chunkSize = self::DEFAULT_CHUNK_SIZE
    ): array {
        $results = [];
        $chunks = array_chunk($data, $chunkSize);

        foreach ($chunks as $chunk) {
            $chunkResults = $processor($chunk);
            if (is_array($chunkResults)) {
                $results = array_merge($results, $chunkResults);
            }

            // Force garbage collection for large datasets
            if (memory_get_usage() > 256 * 1024 * 1024) { // 256MB
                gc_collect_cycles();
            }
        }

        return $results;
    }

    /**
     * Optimized batch delete operation
     */
    public static function bulkDelete(
        string $modelClass,
        string $conn,
        array $ids,
        string $keyColumn = 'id'
    ): int {
        if (empty($ids)) {
            return 0;
        }

        $deletedCount = 0;
        $chunks = array_chunk($ids, self::DEFAULT_CHUNK_SIZE);

        foreach ($chunks as $chunk) {
            $deletedCount += app($modelClass)->on($conn)
                ->whereIn($keyColumn, $chunk)
                ->delete();
        }

        return $deletedCount;
    }
}