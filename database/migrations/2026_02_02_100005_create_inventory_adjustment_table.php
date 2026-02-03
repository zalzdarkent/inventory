<?php

namespace Database\Migrations;

use Database\Migration;

class CreateInventoryAdjustmentTable extends Migration
{
    public function up()
    {
        $sql = "
            IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'inventory_adjustment')
            BEGIN
                CREATE TABLE inventory_adjustment (
                    id INT PRIMARY KEY IDENTITY(1,1),
                    item_id INT NOT NULL,
                    location_id INT NOT NULL,
                    previous_stock INT NOT NULL,
                    adjusted_qty INT NOT NULL,
                    adj_type NVARCHAR(100) NULL,
                    new_stock INT NOT NULL,
                    notes NVARCHAR(MAX) NULL,
                    user_id INT NULL,
                    status NVARCHAR(50) DEFAULT 'ACTIVE',
                    created_at DATETIME DEFAULT GETDATE(),
                    updated_at DATETIME DEFAULT GETDATE(),
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
            IF EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'inventory_adjustment')
            BEGIN
                DROP TABLE inventory_adjustment
            END
        ";

        $this->execute($sql);
    }
}
