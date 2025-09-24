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
use App\Models\LocationModel;
use App\Models\ProductLotModel;
use App\Models\DocumentLineLotsModel;
use App\Models\StockModel;

class ReceiveController extends BaseController
{

    
    public function index()
    {
        if (!hasPermission('view', 'Inbound', 'Receive')) { 
            return redirect()->to('/')->with('error', 'Unauthorized access.');
        }

        return view('inbound/receive/index');
    }

    public function dataReceive()
    {
        $model = new DocumentModel();
        $po = $model->getDocuments('INBOUND', NULL, NULL);

        $result = ['data' => []];

        $updateAllowed = false;
        if (hasPermission('update', 'Inbound', 'Receive')) { 
            $updateAllowed = true;
        }

        $deleteAllowed = false;
        if (hasPermission('delete', 'Inbound', 'Receive')) { 
            $deleteAllowed = true;
        }

        if (!empty($po)) {
            foreach ($po as $key => $po) {
                $buttons = '';

                $exist = $model->where('ref_document_id', $po['document_id'])->first();


                if ($updateAllowed) {
                    if(!$exist){
                        $buttons .= ' <form action="' . site_url('receive/setProcessReceive') . '" method="post" class="d-inline">
                                            ' . csrf_field() . '
                                            <input type="hidden" name="id" value="' . $po['document_id'] . '">
                                            <button type="submit" class="btn btn-sm btn-info me-1">
                                                <i class="fas fa-inbox"></i>
                                            </button>
                                        </form>';
                    }
                }

                if ($deleteAllowed) {
                    $buttons .= ' <button type="button" class="btn btn-sm btn-danger delete-receive"
                                    data-id="' . $po['document_id'] . '">
                                    <i class="fas fa-trash-alt"></i>
                                  </button>';
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

    public function createReceive()
    {
        if (!hasPermission('create', 'Inbound', 'Receive')) { 
            return redirect()->to('/')->with('error', 'Unauthorized access.');
        }

        $id = $this->request->getPost('id');
        if (empty($id)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to create Receive Document',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        $document = new DocumentModel();
        $create = $document->createInboundWithLines($id);

        if(!$create){
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to create Receive Document',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(500);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Receive Document created successfully',
            'csrfHash' => csrf_hash()
        ]);
    }

    public function deleteReceive()
    {
        $id = $this->request->getPost('id');
        if (empty($id)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to delete Receive Document',
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
                'message' => 'Receive Document deleted successfully',
                'csrfHash' => csrf_hash()
            ]);

        } catch (\Throwable $e) {

            $db->transRollback();
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to delete Receive Document',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(500);
        }
    }

    public function setProcessReceive()
    {
        $id = $this->request->getPost('id');

        if (!$id) {
            return redirect()->to('/receive');
        }

        session()->setFlashdata('detail_receive', $id);

        return redirect()->to('/receive/processReceive');
    }

    public function showReceive()
    {
        if (!hasPermission('update', 'Inbound', 'Receive')) { 
            return redirect()->to('/')->with('error', 'Unauthorized access.');
        }

        $id = session()->getFlashdata('detail_receive'); 
        
        if (!$id) {
            return redirect()->to('/receive')->with('error', 'Receive Document not found.');
        }
        
        $partner = new PartnerModel();

        $model = new WarehouseModel();
        $warehouses = $model->findAll();

        $document = new DocumentModel();
        $docheader = $document->getDocuments('INBOUND', NULL, $id);

        $location = new LocationModel();
        $loc = $location->where('location_type', 'staging')->findAll();

        if (!$docheader) {
            return redirect()->to('/orderin')->with('error', 'Purchase Order not found.');
        }

        $documentLines = new DocumentLinesModel();
        
        // $docdetail = $documentLines->getDocumentsLineDetail($id);
        // $data['docdetail'] = $docdetail;

        $rawLines = $documentLines->getDocumentsLineDetail($id);

        $docdetail = [];
        foreach ($rawLines as $row) {
            $lineId = $row['document_line_id'];
            if (!isset($docdetail[$lineId])) {
                $docdetail[$lineId] = [
                    'document_line_id'      => $lineId,
                    'sku'                   => $row['sku'],
                    'productname'           => $row['productname'],
                    'type'                  => $row['type'],
                    'uomname'               => $row['uomname'],
                    'qty_ordered'           => $row['qty_ordered'],
                    'qty_received'          => $row['qty_received'],
                    'product_id'            => $row['product_id'],
                    'uom_id'                => $row['uom_id'],
                    'source_location_id'    => $row['source_location_id'],
                    'lots'                  => []
                ];
            }

            if ($row['lot_id']) {
                $docdetail[$lineId]['lots'][] = [
                    'lot_id'   => $row['lot_id'],
                    'lot_no'   => $row['lot_no'],
                    'mfg_date' => $row['mfg_date'],
                    'exp_date' => $row['exp_date'],
                    'qty'      => $row['lot_qty'],
                ];
            }
        }

        $data['docdetail'] = array_values($docdetail);
        $data['docheader'] = $docheader;
        $data['loc'] = $loc;

        $data['doc_id'] = $id;

        return view('inbound/receive/process', $data);
    }

    public function processReceive()
    {
        if (!hasPermission('update', 'Inbound', 'Receive')) {
            return redirect()->to('/')->with('error', 'Unauthorized access.');
        }

        $validationRules = [
            'doc_id'                => 'required|integer',
            'ref_number'            => 'required|string',
            'lines'                 => 'required',
            'lines.*.location_id'   => 'required|integer',
        ];

        if (! $this->validate($validationRules)) {
            session()->setFlashdata('error', $this->validator->getErrors());
            session()->setFlashdata('detail_receive', $this->request->getPost('doc_id'));
            return redirect()->back();
        }

        $data = $this->request->getPost();
        $db   = \Config\Database::connect();
        $db->transBegin();

        try {
            $document = new DocumentModel();

            // determine statuses & whether to touch stock
            $doc_status = 'open';
            $ref_doc_status = 'open';
            $stock = false;

            if (isset($data['status']) && $data['status'] === 'partial') {
                $doc_status = 'completed';
                $ref_doc_status = 'partial';
                $stock = true;
            } elseif (isset($data['status']) && $data['status'] === 'completed') {
                $doc_status = 'completed';
                $ref_doc_status = 'completed';
                $stock = true;
            }

            // Update receive document header
            $update = [
                'ref_number' => $data['ref_number'],
                'status'     => $doc_status,
            ];
            if (!$document->update($data['doc_id'], $update)) {
                throw new \Exception('Failed to update purchase order header');
            }

            // Update reference document status only if provided
            if (!empty($data['ref_doc_id'])) {
                $updateRefDoc = ['status' => $ref_doc_status];
                if (!$document->update($data['ref_doc_id'], $updateRefDoc)) {
                    throw new \Exception('Failed to update reference document status');
                }
            }

            $documentLines = new DocumentLinesModel();
            $productLotDb  = new ProductLotModel();
            $documentLineLots = new DocumentLineLotsModel();
            $stocks = new StockModel();

            foreach ($data['lines'] as $lineIndex => $line) {
                // basic safety: ensure required base keys exist
                if (!isset($line['doc_line_id'])) {
                    throw new \Exception("Missing doc_line_id on line {$lineIndex}");
                }
                if (!isset($line['product_id'])) {
                    throw new \Exception("Missing product_id on line {$lineIndex}");
                }

                // FEFO handling
                if (isset($line['type']) && $line['type'] === 'FEFO') {
                    $qty = 0;

                    // Only handle lots if lots were submitted in the form (allows saving draft with no lots)
                    if (!empty($line['lots']) && is_array($line['lots'])) {

                        // Validate each lot item (manual validation for flexible form shapes)
                        foreach ($line['lots'] as $li => $lot) {
                            if (
                                !isset($lot['lot_no']) || trim($lot['lot_no']) === '' ||
                                !isset($lot['mfg_date']) || trim($lot['mfg_date']) === '' ||
                                !isset($lot['exp_date']) || trim($lot['exp_date']) === '' ||
                                !isset($lot['qty']) || $lot['qty'] === ''
                            ) {
                                throw new \Exception("Fill all lot data");
                            }

                            // cast qty to numeric and check >= 0
                            $lotQty = (float) $lot['qty'];
                            if ($lotQty < 0) {
                                throw new \Exception("Lot qty must be >= 0");
                            }
                        }

                        // delete existing lots for that line (we are replacing them with submitted ones)
                        // Only delete when we're about to re-insert new lots
                        $documentLineLots->where('document_line_id', $line['doc_line_id'])->delete();

                        // insert each lot and optionally update stock
                        foreach ($line['lots'] as $lot) {
                            $lotQty = (float) $lot['qty'];
                            $qty += $lotQty;

                            // product lot: find or create
                            $productLot = [
                                'product_id' => $line['product_id'],
                                'lot_no'     => $lot['lot_no'],
                                'mfg_date'   => $lot['mfg_date'],
                                'exp_date'   => $lot['exp_date'],
                            ];

                            $existingLot = $productLotDb->where([
                                'product_id' => $line['product_id'],
                                'lot_no'     => $lot['lot_no'],
                            ])->first();

                            if ($existingLot) {
                                $newLotId = $existingLot['lot_id'];
                            } else {
                                if (!$productLotDb->insert($productLot)) {
                                    throw new \Exception('Failed to create Lot Product');
                                }
                                $newLotId = $productLotDb->insertID();
                            }

                            // document_line_lots insert
                            $lineLot = [
                                'document_line_id' => $line['doc_line_id'],
                                'lot_id'           => $newLotId,
                                'qty_received'     => $lotQty,
                            ];
                            if (!$documentLineLots->insert($lineLot)) {
                                throw new \Exception('Failed to create Document Line Lot');
                            }

                            // stock (only when commit/partial/completed)
                            if ($stock && $lotQty > 0) {
                                $newStocks = [
                                    'product_id'  => $line['product_id'],
                                    'partner_id'  => $data['partner_id'],
                                    'location_id' => $line['location_id'],
                                    'lot_id'      => $newLotId,
                                    'uom_id'      => $line['uom_id'],
                                    'qty_on_hand' => $lotQty,
                                    'in_date'     => date('Y-m-d H:i:s'),
                                ];

                                if (! $stocks->addInboundStock($newStocks, 'FEFO')) {
                                    throw new \Exception('Failed to create/update FEFO stock');
                                }
                            }
                        } // end foreach lots

                        // qty cannot exceed ordered
                        if (intval($qty) > intval($line['qty_ordered'])) {
                            throw new \Exception('Exceeds max allowed qty ordered (' . intval($line['qty_ordered']) . ')');
                        }

                        // update qty_received since we re-inserted lots
                        $updateLines = [
                            'qty_received'       => $qty,
                            'source_location_id' => $line['location_id'],
                        ];

                    } else {
                        // No lots submitted:
                        // - Do not delete existing lots (we left DB as-is)
                        // - Do not change qty_received (so draft without lots won't clear actual values)
                        $updateLines = [
                            'source_location_id' => $line['location_id'],
                        ];
                    }

                } else {
                    // FIFO / non-FEFO
                    $qtyReceive = isset($line['qty_receive']) ? (float)$line['qty_receive'] : 0;

                    // check exceed
                    if (intval($qtyReceive) > intval($line['qty_ordered'])) {
                        throw new \Exception('Exceeds max allowed qty ordered (' . intval($line['qty_ordered']) . ')');
                    }

                    // insert/update stock only on commit/partial
                    if ($stock && $qtyReceive > 0) {
                        $newStocks = [
                            'product_id'  => $line['product_id'],
                            'partner_id'  => $data['partner_id'],
                            'location_id' => $line['location_id'],
                            'uom_id'      => $line['uom_id'],
                            'qty_on_hand' => $qtyReceive,
                            'in_date'     => date('Y-m-d H:i:s'),
                        ];

                        if (! $stocks->addInboundStock($newStocks, 'FIFO')) {
                            throw new \Exception('Failed to create FIFO stock');
                        }
                    }

                    $updateLines = [
                        'qty_received'       => $qtyReceive,
                        'source_location_id' => $line['location_id'],
                    ];
                } // end FEFO / FIFO

                // persist document line update (always update source_location_id)
                if (!$documentLines->update($line['doc_line_id'], $updateLines)) {
                    throw new \Exception('Failed to update document line #' . $line['doc_line_id']);
                }
            } // end foreach lines

            // validate partial rule (your business rule)
            if (isset($data['status']) && $data['status'] === 'partial') {
                $allFulfilled = true;
                foreach ($data['lines'] as $line) {
                    $ordered = (float) $line['qty_ordered'];
                    $received = (float) ($line['type'] === 'FEFO'
                        ? (isset($line['lots']) ? array_sum(array_column($line['lots'], 'qty')) : 0)
                        : (isset($line['qty_receive']) ? $line['qty_receive'] : 0)
                    );
                    if ($received < $ordered) {
                        $allFulfilled = false;
                        break;
                    }
                }
                if ($allFulfilled) {
                    throw new \Exception("Cannot set status to Partial because all lines are already fully received. Use Finish instead.");
                }
            }

            $db->transCommit();
            session()->setFlashdata('success', 'Process Received success');
            return redirect()->to('/receive');

        } catch (\Throwable $e) {
            $db->transRollback();
            session()->setFlashdata('error', $e->getMessage());
            session()->setFlashdata('detail_receive', $data['doc_id'] ?? null);
            return redirect()->back();
        }
    }

}
