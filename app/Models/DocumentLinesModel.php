<?php

namespace App\Models;

use CodeIgniter\Model;

class DocumentLinesModel extends Model
{
    protected $table = 'document_lines';
    protected $primaryKey = 'document_line_id';
    protected $allowedFields = ['document_line_id','document_id', 'product_id', 'uom_id', 'qty_ordered', 
    'qty_received', 'qty_accepted', 'qty_damaged', 'qty_short', 'qty_allocated', 'qty_picked', 'qty_shipped',
    'source_location_id', 'target_location_id'];
    
    public function getDocumentsLineDetail($docid = null)
    {
        $builder = $this->builder('document_lines dl')
            ->select('dl.*, mp.name AS productname, mp.sku_code AS sku, mp.rotation AS type, mu.name AS uomname, 
                    sl.location_code AS sourcelocation, tl.location_code AS targetlocation,
                    dll.lot_id, dll.qty_received as lot_qty, pl.lot_no, pl.mfg_date, pl.exp_date')
            ->join('master_products mp', 'mp.product_id = dl.product_id', 'left')
            ->join('master_uom mu', 'mu.uom_id = dl.uom_id', 'left')
            ->join('locations sl', 'sl.location_id = dl.source_location_id', 'left')
            ->join('locations tl', 'tl.location_id = dl.target_location_id', 'left')
            ->join('document_line_lots dll', 'dll.document_line_id = dl.document_line_id', 'left')
            ->join('product_lot pl', 'pl.lot_id = dll.lot_id', 'left');

        if ($docid) {
            $builder->where('dl.document_id', $docid);
        }

        return $builder->get()->getResultArray();
    }
}
