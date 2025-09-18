<?php

namespace App\Models;

use CodeIgniter\Model;

class DocumentLinesModel extends Model
{
    protected $table = 'document_lines';
    protected $primaryKey = 'document_line_id';
    protected $allowedFields = ['document_line_id','document_id', 'product_id', 'uom_id', 'qty_ordered', 
    'qty_received', 'qty_accepted', 'qty_damaged', 'qty_short', 'qty_allocated', 'qty_picked', 'qty_shipped',
    'source_location_id', 'target_location_id', 'lot_id'];
    
}
