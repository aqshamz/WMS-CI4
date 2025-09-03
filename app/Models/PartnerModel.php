<?php

namespace App\Models;

use CodeIgniter\Model;

class PartnerModel extends Model
{
    protected $table = 'partners';
    protected $primaryKey = 'partner_id';
    protected $allowedFields = ['name', 'role'];

    // public function getAllUom()
    // {
    //     return $this->select('product_id, sku_code, name, rotation, base_uom_id, barcode,
    //     is_pack_free, is_active')->findAll();
    // }
}
