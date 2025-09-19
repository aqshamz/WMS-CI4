<?php

namespace App\Controllers;

use App\Models\UomModel;

class MasterUomController extends BaseController
{

    
    public function index()
    {
        if (!hasPermission('view', 'Master', 'Uom')) { 
            return redirect()->to('/')->with('error', 'Unauthorized access.');
        }

        $createAllowed = false;
        if (hasPermission('create', 'Master', 'Uom')) { 
            $createAllowed = true;
        }

        $data['create'] = $createAllowed;

        return view('master/uom/index', $data);
    }

    public function getUoms()
    {
        $model = new UomModel();
        $uoms = $model->findAll();  

        $result = ['data' => []];

        $updateAllowed = false;
        if (hasPermission('update', 'Master', 'Uom')) { 
            $updateAllowed = true;
        }

        $deleteAllowed = false;
        if (hasPermission('delete', 'Master', 'Uom')) { 
            $deleteAllowed = true;
        }

        if (!empty($uoms)) {
            foreach ($uoms as $key => $uom) {
                $buttons = '';


                if ($updateAllowed) {
                    $buttons .= ' <button class="btn btn-sm btn-warning me-1 edit-uom"
                                    data-id="' . $uom['uom_id'] . '"
                                    data-name="' . htmlspecialchars($uom['name'], ENT_QUOTES, 'UTF-8') . '">
                                    <i class="fas fa-edit"></i>
                                  </button>';
                }

                if ($deleteAllowed) {
                    $buttons .= ' <button type="button" class="btn btn-sm btn-danger delete-uom"
                                    data-id="' . $uom['uom_id'] . '">
                                    <i class="fas fa-trash-alt"></i>
                                  </button>';
                }

                $result['data'][$key] = [
                    ($key + 1), // Row number
                    $uom['name'] ?? '-', // UOM name
                    $buttons // Action buttons
                ];
            }
        }

        return $this->response->setJSON($result);
    }

    public function addUom()
    {
        $model = new UomModel();
        $name = $this->request->getPost('name');
        if (empty($name)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'UOM Name is required',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        $data = [
            'name' => $name,
        ];

        if ($model->insert($data)) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'UOM added successfully',
                'csrfHash' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to add UOM',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(500);
        }
    }

    public function updateUom()
    {
        $model = new UomModel();
        $name = $this->request->getPost('name');
        $id = $this->request->getPost('id');
        if (empty($name) || empty($id)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'UOM Name is required',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        $data = [
            'name' => $name,
        ];

        if ($model->update($id, $data)) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'UOM updated successfully',
                'csrfHash' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to update UOM',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(500);
        }
    }

    public function deleteUom()
    {
        $model = new UomModel();
        $id = $this->request->getPost('id');
        if (empty($id)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to delete Uom',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        if ($model->delete($id)) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'UOM deleted successfully',
                'csrfHash' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to delete UOM',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(500);
        }
    }


}
