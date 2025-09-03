<?php

namespace App\Models;

use CodeIgniter\Model;

class UomModel extends Model
{
    protected $table = 'master_uom';
    protected $primaryKey = 'uom_id';
    protected $allowedFields = ['name'];

    // public function getAllUom()
    // {
    //     return $this->select('product_id, sku_code, name, rotation, base_uom_id, barcode,
    //     is_pack_free, is_active')->findAll();
    // }
}
