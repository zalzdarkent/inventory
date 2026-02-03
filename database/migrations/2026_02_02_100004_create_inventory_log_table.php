<?php

namespace Database\Migrations;

use Database\Migration;

class CreateInventoryLogTable extends Migration
{
    public function up()
    {
        $sql = "
            IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'inventory_log')
            BEGIN
                CREATE TABLE inventory_log (
                    id INT PRIMARY KEY IDENTITY(1,1),
                    item_id INT NOT NULL,
                    location_id INT NOT NULL,
                    transaction_type NVARCHAR(50) NOT NULL,
                    qty_mutation INT NOT NULL,
                    qty INT NOT NULL,
                    notes NVARCHAR(MAX) NULL,
                    user_id INT NULL,
                    created_at DATETIME DEFAULT GETDATE(),
                    FOREIGN KEY (item_id) REFERENCES item_table(id),
                    FOREIGN KEY (location_id) REFERENCES item_location(id),
                    FOREIGN KEY (user_id) REFERENCES users(id)
                )
            END
        ";

        $this->execute($sql);
    }

    public function down()
    {
        $sql = "
            IF EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'inventory_log')
            BEGIN
                DROP TABLE inventory_log
            END
        ";

        $this->execute($sql);
    }
}
