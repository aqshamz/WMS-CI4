<?php

namespace App\Models;

use CodeIgniter\Model;

class DocumentModel extends Model
{
    protected $table = 'documents';
    protected $primaryKey = 'document_id';
    protected $allowedFields = ['document_id','doc_type', 'partner_id', 'counterparty_id', 'warehouse_id', 'ref_number', 'status', 'ref_document_id'];
    
    public function getDocuments($type = null, $status = null)
    {
        $builder = $this->builder('documents d')
            ->select('d.*, vendor.name AS vendor, owner.name AS owner, w.name AS warehousename, ref_doc.ref_number AS doc_from, COUNT(dl.document_line_id) AS line_count')
            ->join('partners owner', 'owner.partner_id = d.partner_id', 'left')
            ->join('partners vendor', 'vendor.partner_id = d.counterparty_id', 'left')
            ->join('warehouses w', 'w.warehouse_id = d.warehouse_id', 'left')
            ->join('documents ref_doc', 'ref_doc.document_id = d.ref_document_id', 'left')
            ->join('document_lines dl', 'dl.document_id = d.document_id', 'left')
            ->groupBy('d.document_id');

        if ($type) {
            $builder->where('d.doc_type', $type);
        }

        if ($status) {
            $builder->where('d.status', $status);
        }

        return $builder->get()->getResultArray();
    }
}
