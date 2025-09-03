<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table = 'master_products';
    protected $primaryKey = 'product_id';
    // protected $allowedFields = ['role_id', 'permission_id', 'menu_id', 'sub_menu_id'];

    public function getAllProduct()
    {
        return $this->select('product_id, sku_code, name, rotation, base_uom_id, barcode,
        is_pack_free, is_active')->findAll();
    }
}
