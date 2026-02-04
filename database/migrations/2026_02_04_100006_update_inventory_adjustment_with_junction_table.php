<?php

namespace Database\Migrations;

use Database\Migration;

class UpdateInventoryAdjustmentWithJunctionTable extends Migration
{
    public function up()
    {
        $sql = "
            IF EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'inventory_adjustment')
            BEGIN
                -- Add the new junction table foreign key column if it doesn't exist
                IF NOT EXISTS (SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_NAME = 'inventory_adjustment' AND COLUMN_NAME = 'item_table_location_id')
                BEGIN
                    ALTER TABLE inventory_adjustment
                    ADD item_table_location_id INT NULL;
                    
                    ALTER TABLE inventory_adjustment
                    ADD CONSTRAINT FK_InventoryAdjustment_ItemTableLocations 
                    FOREIGN KEY (item_table_location_id) REFERENCES item_table_locations(id);
                END
            END
        ";

        $this->execute($sql);
    }

    public function down()
    {
        $sql = "
            IF EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'inventory_adjustment')
            BEGIN
                IF EXISTS (SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
                    WHERE TABLE_NAME = 'inventory_adjustment' AND CONSTRAINT_NAME = 'FK_InventoryAdjustment_ItemTableLocations')
                BEGIN
                    ALTER TABLE inventory_adjustment
                    DROP CONSTRAINT FK_InventoryAdjustment_ItemTableLocations;
                END
                
                IF EXISTS (SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_NAME = 'inventory_adjustment' AND COLUMN_NAME = 'item_table_location_id')
                BEGIN
                    ALTER TABLE inventory_adjustment
                    DROP COLUMN item_table_location_id;
                END
            END
        ";

        $this->execute($sql);
    }
}
