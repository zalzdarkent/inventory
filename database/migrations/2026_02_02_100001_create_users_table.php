<?php

namespace Database\Migrations;

use Database\Migration;

class CreateUsersTable extends Migration
{
    public function up()
    {
        $sql = "
            IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'users')
            BEGIN
                CREATE TABLE users (
                    id INT PRIMARY KEY IDENTITY(1,1),
                    email NVARCHAR(255) NOT NULL UNIQUE,
                    username NVARCHAR(100) NOT NULL UNIQUE,
                    password NVARCHAR(255) NOT NULL,
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
            IF EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'users')
            BEGIN
                DROP TABLE users
            END
        ";

        $this->execute($sql);
    }
}
