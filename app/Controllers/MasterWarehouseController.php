<?php

namespace App\Controllers;

use App\Models\WarehouseModel;
use App\Models\LocationModel;

class MasterWarehouseController extends BaseController
{
    
    public function index()
    {
        if (!hasPermission('view', 'Master', 'Warehouse')) { 
            return redirect()->to('/')->with('error', 'Unauthorized access.');
        }

        $createAllowed = false;
        if (hasPermission('create', 'Master', 'Warehouse')) { 
            $createAllowed = true;
        }

        $data['create'] = $createAllowed;

        $model = new WarehouseModel();
        $warehouses = $model->findAll();
        $data['warehouses'] = $warehouses;

        return view('master/warehouse/index', $data);
    }

    public function getWarehouse()
    {
        $model = new WarehouseModel();
        $warehouses = $model->findAll();  

        $result = ['data' => []];

        $updateAllowed = hasPermission('update', 'Master', 'Warehouse');
        $deleteAllowed = hasPermission('delete', 'Master', 'Warehouse');

        if (!empty($warehouses)) {
            foreach ($warehouses as $key => $warehouse) {
                $buttons = '';

                if ($updateAllowed) {
                    $buttons .= ' 
                        <form action="' . site_url('warehouse/setLocation') . '" method="post" class="d-inline">
                            ' . csrf_field() . '
                            <input type="hidden" name="id" value="' . $warehouse['warehouse_id'] . '">
                            <button type="submit" class="btn btn-sm btn-info me-1">
                                <i class="fas fa-eye"></i>
                            </button>
                        </form>';
                }

                if ($updateAllowed) {
                    $buttons .= ' <button class="btn btn-sm btn-warning me-1 editWarehouse"
                                    data-id="' . $warehouse['warehouse_id'] . '"
                                    data-name="' . $warehouse['name'] . '"
                                    data-address="' . $warehouse['address'] . '"
                                    >
                                    <i class="fas fa-edit"></i>
                                  </button>';
                }

                if ($deleteAllowed) {
                    $buttons .= ' <button type="button" class="btn btn-sm btn-danger deleteWarehouse"
                                    data-id="' . $warehouse['warehouse_id'] . '">
                                    <i class="fas fa-trash-alt"></i>
                                  </button>';
                }

                $result['data'][$key] = [
                    ($key + 1),
                    $warehouse['name'] ?? '-',
                    $warehouse['address'] ?? '-',
                    $buttons
                ];
            }
        }

        return $this->response->setJSON($result);
    }

    public function addWarehouse()
    {
        $model = new WarehouseModel();
        $name = $this->request->getPost('name');
        $address = $this->request->getPost('address');

        if (empty($name)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Name is required',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        if (empty($address)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Address is required',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        $data = [
            'name' => $name,
            'address' => $address,
        ];

        if ($model->insert($data)) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Warehouse added successfully',
                'csrfHash' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to add warehouse',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(500);
        }
    }

    public function updateWarehouse()
    {
        $model = new WarehouseModel();
        $id = $this->request->getPost('id');
        $name = $this->request->getPost('name');
        $address = $this->request->getPost('address');
        
        if (empty($id)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to update warehouse',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(500);
        }

        if (empty($name)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Name is required',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        if (empty($address)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Address is required',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        $data = [
            'name' => $name,
            'address' => $address,
        ];

        if ($model->update($id, $data)) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Warehouse updated successfully',
                'csrfHash' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to update Warehouse',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(500);
        }
    }

    public function deleteWarehouse()
    {
        $model = new WarehouseModel();
        $id = $this->request->getPost('id');
        if (empty($id)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to delete Warehouse',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        if ($model->delete($id)) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Warehouse deleted successfully',
                'csrfHash' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to delete Warehouse',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(500);
        }
    }

    public function setLocation()
    {
        $id = $this->request->getPost('id');

        if (!$id) {
            return redirect()->to('/warehouse');
        }

        session()->setFlashdata('detail_warehouse_id', $id);

        return redirect()->to('/warehouse/location');
    }

    public function location()
    {
        if (!hasPermission('edit', 'Master', 'Warehouse')) { 
            return redirect()->to('/')->with('error', 'Unauthorized access.');
        }

        $id = session()->getFlashdata('detail_warehouse_id'); 
        
        if (!$id) {
            return redirect()->to('/warehouse')->with('error', 'Warehouse not found.');
        }
        
        
        $createAllowed = false;
        if (hasPermission('create', 'Master', 'Warehouse')) { 
            $createAllowed = true;
        }
        
        $model = new WarehouseModel();
        $warehouse = $model->getWarehouseDetail($id);
        
        if (!$warehouse) {
            return redirect()->to('/warehouse')->with('error', 'Warehouse not found.');
        }
        
        $data['id'] = $id;
        $data['create'] = $createAllowed;
        $data['detail'] = $warehouse;

        return view('master/warehouse/location', $data);

    }

    public function getLocation()
    {
        $warehouseId = $this->request->getPost('id'); 

        if (!$warehouseId) {
            return $this->response->setJSON([
                'data'      => [],
                'csrfHash'  => csrf_hash(),
                'error'     => 'Warehouse ID is required'
            ]);
        }

        $model = new LocationModel();
        $locations = $model->getLocation($warehouseId);

        $result = ['data' => []];

        $updateAllowed = hasPermission('update', 'Master', 'Warehouse');
        $deleteAllowed = hasPermission('delete', 'Master', 'Warehouse');

        foreach ($locations as $key => $location) {
            $buttons = '';

            if ($updateAllowed) {
                $buttons .= '<button class="btn btn-sm btn-warning editLocation" 
                                data-id="' . $location['location_id'] . '"
                                data-code="' . $location['location_code'] . '"
                                data-type="' . $location['location_type'] . '"
                                >
                                <i class="fas fa-edit"></i>
                            </button> ';
            }

            if ($deleteAllowed) {
                $buttons .= '<button class="btn btn-sm btn-danger deleteLocation" data-id="' . $location['location_id'] . '">
                                <i class="fas fa-trash"></i>
                            </button>';
            }

            $result['data'][$key] = [
                ($key + 1),
                $location['location_code'] ?? '-',
                $location['location_type'] ?? '-',
                $buttons
            ];
        }

        return $this->response->setJSON([
            'data'      => $result['data'],
            'csrfHash'  => csrf_hash()
        ]);
    }

    public function addLocation()
    {
        $model = new LocationModel();
        $warehouseId = $this->request->getPost('id');
        $locationCode = $this->request->getPost('location_code');
        $locationType = $this->request->getPost('location_type');

        if (empty($warehouseId)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to add location',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(500);
        }
        
        if (empty($locationCode)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Location Code is required',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        if (empty($locationType)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Location Type is required',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        $exist = $model->where('warehouse_id', $warehouseId)->where('location_code', $locationCode)->first();

        if($exist){
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Location Code already used',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        $data = [
            'warehouse_id' => $warehouseId,
            'location_code' => $locationCode,
            'location_type' => $locationType,
        ];

        if ($model->insert($data)) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Location added successfully',
                'csrfHash' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to add location',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(500);
        }
    }

    public function updateLocation()
    {
        $model = new LocationModel();
        $warehouseId = $this->request->getPost('wid');
        $dataId = $this->request->getPost('id');
        $locationCode = $this->request->getPost('location_code');
        $locationType = $this->request->getPost('location_type');

        if (empty($warehouseId)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to update location',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(500);
        }
        
        if (empty($locationCode)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Location Code is required',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        if (empty($locationType)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Location Type is required',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        $exist = $model->where('warehouse_id', $warehouseId)->where('location_code', $locationCode)->first();

        if($exist){
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Location Code already used',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        $data = [
            'warehouse_id' => $warehouseId,
            'location_code' => $locationCode,
            'location_type' => $locationType,
        ];

        if ($model->update($dataId, $data)) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Location updated successfully',
                'csrfHash' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to update Location',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(500);
        }
    }

    public function deleteLocation()
    {
        $model = new LocationModel();
        $id = $this->request->getPost('id');
        if (empty($id)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to delete Location',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        if ($model->delete($id)) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Location deleted successfully',
                'csrfHash' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to delete Location',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(500);
        }
    }


}
