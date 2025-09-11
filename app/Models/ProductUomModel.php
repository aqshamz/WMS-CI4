<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductUomModel extends Model
{
    protected $table = 'product_uom';
    protected $primaryKey = 'product_uom_id';
    protected $allowedFields = ['product_id','uom_id', 'factor_to_base', 'is_default'];

    public function getProductsWithUom($productId = null)
    {
        $builder = $this->select('product_uom.*, master_products.name as product_name, master_uom.name as uom_name')
                        ->join('master_products', 'master_products.product_id = product_uom.product_id')
                        ->join('master_uom', 'master_uom.uom_id = product_uom.uom_id');

        if ($productId !== null) {
            $builder->where('product_uom.product_id', $productId);
        }

        return $builder->findAll();
    }
}
