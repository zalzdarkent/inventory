<?php

namespace Database\Migrations;

use Database\Migration;

class MigrateInventoryLogDataToJunctionTable extends Migration
{
    public function up()
    {
        $sql = "
            IF EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'inventory_log')
            BEGIN
                -- Untuk setiap kombinasi item_id dan location_id yang ada di inventory_log,
                -- cek apakah sudah ada di item_table_locations, jika tidak buat entry baru
                INSERT INTO item_table_locations (location_id, item_id, created_at)
                SELECT DISTINCT il.location_id, il.item_id, GETDATE()
                FROM inventory_log il
                WHERE il.item_id IS NOT NULL 
                  AND il.location_id IS NOT NULL
                  AND NOT EXISTS (
                    SELECT 1 FROM item_table_locations itl 
                    WHERE itl.item_id = il.item_id 
                    AND itl.location_id = il.location_id
                  );
                
                -- Update inventory_log dengan junction table ID
                UPDATE il
                SET il.item_table_location_id = itl.id
                FROM inventory_log il
                INNER JOIN item_table_locations itl ON il.item_id = itl.item_id 
                    AND il.location_id = itl.location_id
                WHERE il.item_table_location_id IS NULL;
                
                -- Update inventory_adjustment dengan junction table ID
                UPDATE ia
                SET ia.item_table_location_id = itl.id
                FROM inventory_adjustment ia
                INNER JOIN item_table_locations itl ON ia.item_id = itl.item_id 
                    AND ia.location_id = itl.location_id
                WHERE ia.item_table_location_id IS NULL;
            END
        ";

        $this->execute($sql);
    }

    public function down()
    {
        // Reverse: just clear the junction IDs
        $sql = "
            IF EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'inventory_log')
            BEGIN
                UPDATE inventory_log SET item_table_location_id = NULL;
            END
            
            IF EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'inventory_adjustment')
            BEGIN
                UPDATE inventory_adjustment SET item_table_location_id = NULL;
            END
        ";

        $this->execute($sql);
    }
}
