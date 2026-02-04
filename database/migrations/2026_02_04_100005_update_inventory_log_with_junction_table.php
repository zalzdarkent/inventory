<?php

namespace Database\Migrations;

use Database\Migration;

class UpdateInventoryLogWithJunctionTable extends Migration
{
    public function up()
    {
        $sql = "
            IF EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'inventory_log')
            BEGIN
                -- Add the new junction table foreign key column if it doesn't exist
                IF NOT EXISTS (SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_NAME = 'inventory_log' AND COLUMN_NAME = 'item_table_location_id')
                BEGIN
                    ALTER TABLE inventory_log
                    ADD item_table_location_id INT NULL;
                    
                    ALTER TABLE inventory_log
                    ADD CONSTRAINT FK_InventoryLog_ItemTableLocations 
                    FOREIGN KEY (item_table_location_id) REFERENCES item_table_locations(id);
                END
            END
        ";

        $this->execute($sql);
    }

    public function down()
    {
        $sql = "
            IF EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'inventory_log')
            BEGIN
                IF EXISTS (SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
                    WHERE TABLE_NAME = 'inventory_log' AND CONSTRAINT_NAME = 'FK_InventoryLog_ItemTableLocations')
                BEGIN
                    ALTER TABLE inventory_log
                    DROP CONSTRAINT FK_InventoryLog_ItemTableLocations;
                END
                
                IF EXISTS (SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_NAME = 'inventory_log' AND COLUMN_NAME = 'item_table_location_id')
                BEGIN
                    ALTER TABLE inventory_log
                    DROP COLUMN item_table_location_id;
                END
            END
        ";

        $this->execute($sql);
    }
}
