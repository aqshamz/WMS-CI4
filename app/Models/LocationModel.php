<?php

namespace App\Models;

use CodeIgniter\Model;

class LocationModel extends Model
{
    protected $table = 'locations';
    protected $primaryKey = 'location_id';
    protected $allowedFields = ['location_id','warehouse_id', 'location_code', 'location_type'];

    public function getLocation($warehouseId = null)
    {
        $builder = $this->select('locations.*');

        if ($warehouseId !== null) {
            $builder->where('locations.warehouse_id', $warehouseId);
        }

        return $builder->findAll();
    }
}
