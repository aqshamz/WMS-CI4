<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table = 'master_products';
    protected $primaryKey = 'product_id';
    protected $allowedFields = ['sku_code','name', 'rotation', 'base_uom_id', 'barcode', 'is_pack_free', 'is_active'];

    // public function getAllUom()
    // {
    //     return $this->select('product_id, sku_code, name, rotation, base_uom_id, barcode,
    //     is_pack_free, is_active')->findAll();
    // }

    public function getProductsWithUom($productId = null)
    {
        $builder = $this->select('master_products.*, master_uom.name as uom_name')
                    ->join('master_uom', 'master_uom.uom_id = master_products.base_uom_id');

        if ($productId !== null) {
            return $builder->where('master_products.product_id', $productId)->first(); 
        }

        return $builder->findAll();
    }

}
