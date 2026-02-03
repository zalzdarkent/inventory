<?php

namespace Database;

abstract class Migration
{
    protected $koneksi;

    public function __construct($koneksi)
    {
        $this->koneksi = $koneksi;
    }

    /**
     * Run the migration.
     * Execute SQL to create tables and schema
     */
    abstract public function up();

    /**
     * Reverse the migration.
     * Execute SQL to drop tables and reverse schema
     */
    abstract public function down();

    /**
     * Execute SQL query
     */
    protected function execute($sql)
    {
        $stmt = sqlsrv_query($this->koneksi, $sql);

        if ($stmt === false) {
            throw new \Exception("SQL Error: " . print_r(sqlsrv_errors(), true));
        }

        return $stmt;
    }

    /**
     * Get migration name from class
     */
    public function getName()
    {
        $reflection = new \ReflectionClass($this);
        return $reflection->getShortName();
    }
}
