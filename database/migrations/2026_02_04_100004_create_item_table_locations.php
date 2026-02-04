<?php

namespace Database\Migrations;

use Database\Migration;

class CreateItemTableLocations extends Migration
{
    public function up()
    {
        $sql = "
            IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'item_table_locations')
            BEGIN
                CREATE TABLE item_table_locations (
                    id INT PRIMARY KEY IDENTITY(1,1),
                    location_id INT NOT NULL,
                    item_id INT NOT NULL,
                    created_at DATETIME DEFAULT GETDATE(),
                    updated_at DATETIME DEFAULT GETDATE(),
                    FOREIGN KEY (location_id) REFERENCES item_location(id),
                    FOREIGN KEY (item_id) REFERENCES item_table(id),
                    UNIQUE(location_id, item_id)
                )
            END
        ";

        $this->execute($sql);
    }

    public function down()
    {
        $sql = "
            IF EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'item_table_locations')
            BEGIN
                DROP TABLE item_table_locations
            END
        ";

        $this->execute($sql);
    }
}
