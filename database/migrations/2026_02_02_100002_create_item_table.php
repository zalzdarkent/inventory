<?php

namespace Database\Migrations;

use Database\Migration;

class CreateItemTable extends Migration
{
    public function up()
    {
        $sql = "
            IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'item_table')
            BEGIN
                CREATE TABLE item_table (
                    id INT PRIMARY KEY IDENTITY(1,1),
                    item_code NVARCHAR(50) NOT NULL UNIQUE,
                    name NVARCHAR(255) NOT NULL,
                    picture NVARCHAR(255) NULL,
                    description NVARCHAR(MAX) NULL,
                    is_active BIT DEFAULT 1,
                    created_at DATETIME DEFAULT GETDATE(),
                    updated_at DATETIME DEFAULT GETDATE()
                )
            END
        ";

        $this->execute($sql);
    }

    public function down()
    {
        $sql = "
            IF EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'item_table')
            BEGIN
                DROP TABLE item_table
            END
        ";

        $this->execute($sql);
    }
}
