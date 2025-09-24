<?php

namespace App\Models;

use CodeIgniter\Model;

class DocumentLineLotsModel extends Model
{
    protected $table = 'document_line_lots';
    protected $primaryKey = 'line_lot_id';
    protected $allowedFields = ['document_line_id','lot_id', 'qty_received', 'qty_accepted', 'qty_damaged'];
    
}
