<?php

declare(strict_types=1);

namespace Silk;

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

/**
 * PDO singleton. One connection per process.
 *
 * Usage:
 *   $db = Database::getInstance();
 *   $rows = $db->query('SELECT ...', ['RM-001']);
 */
final class Database
{
    private static ?self $instance = null;
    private PDO $pdo;

    /**
     * Private constructor: use {@see getInstance()}.
     */
    private function __construct()
    {
        $host = defined('DB_HOST') ? DB_HOST : '127.0.0.1';
        $port = defined('DB_PORT') ? DB_PORT : '3306';
        $name = defined('DB_NAME') ? DB_NAME : 'silk_swarakarna';
        $user = defined('DB_USER') ? DB_USER : 'root';
        $pass = defined('DB_PASS') ? DB_PASS : '';

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // Throw as RuntimeException for Throwable catches
            throw new RuntimeException(
                'Database connection failed: ' . $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
    }

    private function __clone()
    {
    }

    /** Prevent unserialization. */
    public function __wakeup(): void
    {
        throw new RuntimeException('Cannot unserialize singleton');
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * SELECT query.
     * @param array<int|string, mixed> $params
     * @return array<int, array<string, mixed>>
     */
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Non-SELECT (INSERT/UPDATE/DELETE).
     * @return int Affected row count
     */
    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit the active transaction.
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Roll back the active transaction.
     */
    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * Expose the underlying PDO for advanced use (migrations, raw queries).
     * Prefer query() / execute() / lastInsertId() for normal operations.
     */
    public function pdo(): PDO
    {
        return $this->pdo;
    }
}
