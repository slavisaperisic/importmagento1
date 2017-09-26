<?php

/**
 * Class ConnectionORM
 */
class ConnectionORM
{
    /**
     * @var PDO $pdo
     */
    public $pdo;

    public function __construct(
        $pdo
    )
    {
        $this->dateTimestamp = time();
        $this->pdo = $pdo;
    }
}