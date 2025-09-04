<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\PermissionModel;
use App\Models\MenuModel;
use App\Models\PartnerModel;

class MasterPartnerController extends BaseController
{

    
    public function index()
    {
        if (!hasPermission('view', 'Master', 'Partner', true)) { 
            return redirect()->to('/')->with('error', 'Unauthorized access.');
        }

        $createAllowed = false;
        if (hasPermission('create', 'Master', 'Partner', false)) { 
            $createAllowed = true;
        }

        $data['create'] = $createAllowed;

        return view('master/partner/index', $data);
    }

    public function getPartners()
    {
        $model = new PartnerModel();
        $partners = $model->findAll();  

        $result = ['data' => []];

        $updateAllowed = false;
        if (hasPermission('update', 'Master', 'Partner', false)) { 
            $updateAllowed = true;
        }

        $deleteAllowed = false;
        if (hasPermission('delete', 'Master', 'Partner', false)) { 
            $deleteAllowed = true;
        }

        if (!empty($partners)) {
            foreach ($partners as $key => $partner) {
                $buttons = '';


                if ($updateAllowed) {
                    $buttons .= ' <button class="btn btn-sm btn-warning me-1 edit-partner"
                                    data-id="' . $partner['partner_id'] . '"
                                    data-name="' . htmlspecialchars($partner['name'], ENT_QUOTES, 'UTF-8') . '"
                                    data-role="' . htmlspecialchars($partner['role'], ENT_QUOTES, 'UTF-8') . '"
                                    >
                                    <i class="fas fa-edit"></i>
                                  </button>';
                }

                if ($deleteAllowed) {
                    $buttons .= ' <button type="button" class="btn btn-sm btn-danger delete-partner"
                                    data-id="' . $partner['partner_id'] . '">
                                    <i class="fas fa-trash-alt"></i>
                                  </button>';
                }

                $result['data'][$key] = [
                    ($key + 1),
                    $partner['role'] ?? '-',
                    $partner['name'] ?? '-',
                    $buttons
                ];
            }
        }

        return $this->response->setJSON($result);
    }

    public function addPartner()
    {
        $model = new PartnerModel();
        $name = $this->request->getPost('name');
        $role = $this->request->getPost('role');
        if (empty($role)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Type is required',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        if (empty($name)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Name is required',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        $data = [
            'name' => $name,
            'role' => $role,
        ];

        if ($model->insert($data)) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Partner added successfully',
                'csrfHash' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to add partner',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(500);
        }
    }

    public function updatePartner()
    {
        $model = new PartnerModel();
        $name = $this->request->getPost('name');
        $role = $this->request->getPost('role');
        $id = $this->request->getPost('id');
        if (empty($name) || empty($id)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Partner Name is required',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        if (empty($role)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Type is required',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        $data = [
            'name' => $name,
            'role' => $role,
        ];

        if ($model->update($id, $data)) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Partner updated successfully',
                'csrfHash' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to update Partner',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(500);
        }
    }

    public function deletePartner()
    {
        $model = new PartnerModel();
        $id = $this->request->getPost('id');
        if (empty($id)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to delete Partner',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        if ($model->delete($id)) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Partner deleted successfully',
                'csrfHash' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to delete Partner',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(500);
        }
    }


}
