<?php

namespace App\Controllers;

use App\Models\UomModel;
use App\Models\DocumentModel;
use App\Models\DocumentLinesModel;
use App\Models\PartnerModel;
use App\Models\WarehouseModel;
use App\Models\PartnerProductModel;
use App\Models\ProductUomModel;
use App\Models\ProductModel;

class PurchaseOrderController extends BaseController
{

    
    public function index()
    {
        if (!hasPermission('view', 'Inbound', 'Purchase Order')) { 
            return redirect()->to('/')->with('error', 'Unauthorized access.');
        }

        $createAllowed = false;
        if (hasPermission('create', 'Inbound', 'Purchase Order')) { 
            $createAllowed = true;
        }

        $data['create'] = $createAllowed;

        return view('inbound/orderin/index', $data);
    }

    public function dataPO()
    {
        $model = new DocumentModel();
        $po = $model->getDocuments('PO', NULL, NULL);

        $result = ['data' => []];

        $updateAllowed = false;
        if (hasPermission('update', 'Inbound', 'Purchase Order')) { 
            $updateAllowed = true;
        }

        $deleteAllowed = false;
        if (hasPermission('delete', 'Inbound', 'Purchase Order')) { 
            $deleteAllowed = true;
        }

        $createReceive = false;
        if (hasPermission('delete', 'Inbound', 'Receive')) { 
            $createReceive = true;
        }

        if (!empty($po)) {
            foreach ($po as $key => $po) {
                $buttons = '';

                $exist = $model->where('ref_document_id', $po['document_id'])->first();

                if ($createReceive) {
                    if(!$exist){
                        $buttons .= ' <button type="button" class="btn btn-sm btn-success create-receive"
                                        data-id="' . $po['document_id'] . '">
                                        <i class="fa-solid fa-box"></i>
                                      </button>';
                    } else{
                        if($po['status'] == "partial"){
                            $buttons .= ' <button type="button" class="btn btn-sm btn-success create-receive"
                                        data-id="' . $po['document_id'] . '">
                                        <i class="fa-solid fa-box"></i>
                                      </button>';
                        }
                    }
                }

                if ($updateAllowed) {
                    if(!$exist){
                        $buttons .= ' <form action="' . site_url('orderin/setEditPO') . '" method="post" class="d-inline">
                                            ' . csrf_field() . '
                                            <input type="hidden" name="id" value="' . $po['document_id'] . '">
                                            <button type="submit" class="btn btn-sm btn-info me-1">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </form>';
                    }
                }

                if ($deleteAllowed) {
                    if(!$exist){
                        $buttons .= ' <button type="button" class="btn btn-sm btn-danger delete-po"
                                        data-id="' . $po['document_id'] . '">
                                        <i class="fas fa-trash-alt"></i>
                                      </button>';
                    }
                }

                $status = $po['status'];
                switch ($status) {
                    case 'draft':
                        $status = '<span class="badge bg-secondary">Draft</span>';
                        break;
                    case 'open':
                        $status = '<span class="badge bg-info text-dark">Open</span>';
                        break;
                    case 'partial':
                        $status = '<span class="badge bg-warning text-dark">Partial</span>';
                        break;
                    case 'completed':
                        $status = '<span class="badge bg-success">Completed</span>';
                        break;
                    case 'cancelled':
                        $status = '<span class="badge bg-danger">Cancelled</span>';
                        break;
                    default:
                        $status = '<span class="badge bg-light text-dark">Unknown</span>';
                }

                $result['data'][$key] = [
                    $po['ref_number'],
                    $po['vendor'] ?? '-',
                    $po['warehousename'] ?? '-',
                    $po['line_count'] ?? 0,
                    $status ?? '-',
                    $buttons // Action buttons
                ];
            }
        }

        return $this->response->setJSON($result);
    }

    public function createPO()
    {
        if (!hasPermission('create', 'Inbound', 'Purchase Order')) { 
            return redirect()->to('/')->with('error', 'Unauthorized access.');
        }

        $partner = new PartnerModel();
        $vendors = $partner->where('role', 'vendor')->findAll();
        $customers = $partner->where('role', 'customer')->findAll();

        $model = new WarehouseModel();
        $warehouses = $model->findAll();
        
        $data['vendors'] = $vendors;
        $data['customers'] = $customers;
        $data['warehouses'] = $warehouses;

        return view('inbound/orderin/create', $data);
    }

    public function savePO()
    {
        if (!hasPermission('create', 'Inbound', 'Purchase Order')) { 
            return redirect()->to('/')->with('error', 'Unauthorized access.');
        }

        $validationRules = [
            'partner_id'            => 'required|integer',
            'counterparty_id'       => 'required|integer',
            'warehouse_id'          => 'required|integer',
            'ref_number'            => 'required|string',
            'products'              => 'required',
            'products.*.product_id' => 'required|integer',
            'products.*.uom_id'     => 'required|integer',
            'products.*.qty'        => 'required|numeric|greater_than[0]',
        ];

        if (! $this->validate($validationRules)) {
            session()->setFlashdata('error', $this->validator->getErrors());

            return redirect()->back();
        }

        $data = $this->request->getPost();
        $db   = \Config\Database::connect();
        $db->transBegin();

        try {
            $document = new DocumentModel();
            $docId = $document->insert([
                'doc_type'        => $data['doc_type'],
                'partner_id'      => $data['partner_id'],
                'counterparty_id' => $data['counterparty_id'],
                'warehouse_id'    => $data['warehouse_id'],
                'ref_number'      => $data['ref_number'],
                'status'          => $data['status'],
            ]);

            if (!$docId) {
                throw new \Exception('Failed to create purchase order header');
            }

            $documentLines = new DocumentLinesModel();
            foreach ($data['products'] as $line) {
                $lineId = $documentLines->insert([
                    'document_id' => $docId,
                    'product_id'  => $line['product_id'],
                    'uom_id'      => $line['uom_id'],
                    'qty_ordered' => $line['qty'],
                ]);

                if (! $lineId) {
                    throw new \Exception('Failed to insert purchase order line');
                }
            }

            $db->transCommit();
            session()->setFlashdata('success', 'Purchase Order saved successfully');
            return redirect()->to('/orderin');

        } catch (\Throwable $e) {

            $db->transRollback();
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->back();
        }

    }

    public function deletePO()
    {
        $id = $this->request->getPost('id');
        if (empty($id)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to delete Document',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        $db   = \Config\Database::connect();
        $db->transBegin();

        try {
            $document = new DocumentModel();
            $docId = $document->delete($id);

            if (!$docId) {
                throw new \Exception('Failed to delete purchase order header');
            }

            $documentLines = new DocumentLinesModel();
            $lineId = $documentLines->where('document_id', $id)->delete();
            if (! $lineId) {
                throw new \Exception('Failed to delete purchase order line');
            }

            $db->transCommit();

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Purchase Order deleted successfully',
                'csrfHash' => csrf_hash()
            ]);

        } catch (\Throwable $e) {

            $db->transRollback();
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to delete Purchase Order',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(500);
        }
    }

    public function setEditPO()
    {
        $id = $this->request->getPost('id');

        if (!$id) {
            return redirect()->to('/orderin');
        }

        session()->setFlashdata('detail_orderin', $id);

        return redirect()->to('/orderin/editPO');
    }

    public function editPO()
    {
        if (!hasPermission('update', 'Inbound', 'Purchase Order')) { 
            return redirect()->to('/')->with('error', 'Unauthorized access.');
        }

        $id = session()->getFlashdata('detail_orderin'); 
        
        if (!$id) {
            return redirect()->to('/orderin')->with('error', 'Purchase Order not found.');
        }
        
        $partner = new PartnerModel();
        $vendors = $partner->where('role', 'vendor')->findAll();
        $customers = $partner->where('role', 'customer')->findAll();

        $model = new WarehouseModel();
        $warehouses = $model->findAll();

        $document = new DocumentModel();
        $docheader = $document->find($id);

        if (!$docheader) {
            return redirect()->to('/orderin')->with('error', 'Purchase Order not found.');
        }

        $documentLines = new DocumentLinesModel();
        $docdetail = $documentLines->where('document_id', $id)->findAll();

        $productUomModel = new ProductUomModel();
        $productModel = new ProductModel();

        foreach ($docdetail as &$line) {
            $line['uoms'] = $productUomModel->getProductsWithUom($line['product_id']);

            $data = $productModel->getProductsWithUom($line['product_id']);
            $line['base_uom'] = $data['base_uom_id'];
            $line['base_uom_name'] = $data['uom_name'];
        }


        $data['vendors'] = $vendors;
        $data['customers'] = $customers;
        $data['warehouses'] = $warehouses;

        $data['docheader'] = $docheader;
        $data['docdetail'] = $docdetail;

        $data['doc_id'] = $id;

        $productModel = new PartnerProductModel();
        $products = $productModel->getPartnerProductsDetail($docheader['counterparty_id']); 

        $data['products'] = $products;

        return view('inbound/orderin/edit', $data);
    }

    public function updatePO()
    {
        if (!hasPermission('update', 'Inbound', 'Purchase Order')) { 
            return redirect()->to('/')->with('error', 'Unauthorized access.');
        }

        $validationRules = [
            'doc_id'                => 'required|integer',
            'partner_id'            => 'required|integer',
            'counterparty_id'       => 'required|integer',
            'warehouse_id'          => 'required|integer',
            'ref_number'            => 'required|string',
            'products'              => 'required',
            'products.*.product_id' => 'required|integer',
            'products.*.uom_id'     => 'required|integer',
            'products.*.qty'        => 'required|numeric|greater_than[0]',
        ];

        if (! $this->validate($validationRules)) {
            session()->setFlashdata('error', $this->validator->getErrors());

            return redirect()->back();
        }

        $data = $this->request->getPost();
        $db   = \Config\Database::connect();
        $db->transBegin();

        try {
            $document = new DocumentModel();
            $update = [
                'doc_type'        => $data['doc_type'],
                'partner_id'      => $data['partner_id'],
                'counterparty_id' => $data['counterparty_id'],
                'warehouse_id'    => $data['warehouse_id'],
                'ref_number'      => $data['ref_number'],
                'status'          => $data['status'],
            ];

            if (!$document->update($data['doc_id'], $update)) {
                throw new \Exception('Failed to update purchase order header');
            }

            $documentLines = new DocumentLinesModel();

            if(!$documentLines->where('document_id', $data['doc_id'])->delete()){
                throw new \Exception('Failed to update purchase order line');
            }

            foreach ($data['products'] as $line) {
                $lineId = $documentLines->insert([
                    'document_id' => $data['doc_id'],
                    'product_id'  => $line['product_id'],
                    'uom_id'      => $line['uom_id'],
                    'qty_ordered' => $line['qty'],
                ]);

                if (! $lineId) {
                    throw new \Exception('Failed to update purchase order line');
                }
            }

            $db->transCommit();
            session()->setFlashdata('success', 'Purchase Order updated successfully');
            return redirect()->to('/orderin');

        } catch (\Throwable $e) {

            $db->transRollback();
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->back();
        }

    }
}
