<?php

namespace Database\Migrations;

use Database\Migration;

class CreateItemLocationTable extends Migration
{
    public function up()
    {
        $sql = "
            IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'item_location')
            BEGIN
                CREATE TABLE item_location (
                    id INT PRIMARY KEY IDENTITY(1,1),
                    location NVARCHAR(255) NOT NULL,
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
            IF EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'item_location')
            BEGIN
                DROP TABLE item_location
            END
        ";

        $this->execute($sql);
    }
}
