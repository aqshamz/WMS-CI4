<?php

namespace App\Models;

use CodeIgniter\Model;

class PartnerProductModel extends Model
{
    protected $table = 'partner_products';
    protected $primaryKey = 'partner_product_id';
    protected $allowedFields = ['partner_id','product_id', 'customer_sku', 'status'];

    public function getPartnerProductsDetail($partnerId = null)
    {
        $builder = $this->select('partner_products.*, master_products.name as product_name')
                        ->join('master_products', 'master_products.product_id = partner_products.product_id');

        if ($partnerId !== null) {
            $builder->where('partner_products.partner_id', $partnerId);
        }

        return $builder->findAll();
    }
}
