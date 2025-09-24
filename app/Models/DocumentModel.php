<?php

namespace App\Models;

use CodeIgniter\Model;

class DocumentModel extends Model
{
    protected $table = 'documents';
    protected $primaryKey = 'document_id';
    protected $allowedFields = ['document_id','doc_type', 'partner_id', 'counterparty_id', 'warehouse_id', 'ref_number', 'status', 'ref_document_id'];
    
    public function getDocuments($type = null, $status = null, $id = null)
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

        if($id){
            $builder->where('d.document_id', $id);
            return $builder->get()->getRowArray();
        }

        return $builder->get()->getResultArray();
    }

    public function createInboundWithLines($poId)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $sqlDoc = 'INSERT INTO documents (doc_type, partner_id, counterparty_id, warehouse_id, ref_number, status, ref_document_id)
                    SELECT ?, partner_id, counterparty_id, warehouse_id, ref_number, "open", document_id
                    FROM documents 
                    WHERE document_id = ?';

            $db->query($sqlDoc, ['INBOUND', $poId]);

            $newDocId = $db->insertID();

            if (!$newDocId) {
                throw new \Exception("Failed to create inbound document.");
            }

            $sqlLines = 'INSERT INTO document_lines (document_id, product_id, uom_id, qty_ordered)
                        SELECT ?, product_id, uom_id, (qty_ordered - qty_received) AS qty_ordered
                        FROM document_lines 
                        WHERE document_id = ? AND (qty_ordered - qty_received) > 0';

            $db->query($sqlLines, [$newDocId, $poId]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception("Transaction failed.");
            }

            return true;

        } catch (\Throwable $e) {
            $db->transRollback(); // rollback on error
            log_message('error', '[Inbound Error] ' . $e->getMessage());
            return false;
        }
    }

}
