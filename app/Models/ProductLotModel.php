<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductLotModel extends Model
{
    protected $table = 'product_lot';
    protected $primaryKey = 'lot_id';
    protected $allowedFields = ['product_id','lot_no', 'mfg_date', 'exp_date'];
    
}
