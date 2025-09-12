<?php

namespace App\Models;

use CodeIgniter\Model;

class WarehouseModel extends Model
{
    protected $table = 'warehouses';
    protected $primaryKey = 'warehouse_id';
    protected $allowedFields = ['name','address'];

    public function getWarehouseDetail($warehouseId = null)
    {
        $builder = $this->select('warehouses.*');

        if ($warehouseId !== null) {
            return $builder->where('warehouses.warehouse_id', $warehouseId)->first(); 
        }

        return $builder->findAll();
    }
}
