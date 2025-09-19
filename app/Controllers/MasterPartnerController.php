<?php

namespace App\Controllers;

use App\Models\PartnerModel;
use App\Models\PartnerProductModel;
use App\Models\ProductModel;

class MasterPartnerController extends BaseController
{

    
    public function index()
    {
        if (!hasPermission('view', 'Master', 'Partner')) { 
            return redirect()->to('/')->with('error', 'Unauthorized access.');
        }

        $createAllowed = false;
        if (hasPermission('create', 'Master', 'Partner')) { 
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
        if (hasPermission('update', 'Master', 'Partner')) { 
            $updateAllowed = true;
        }

        $deleteAllowed = false;
        if (hasPermission('delete', 'Master', 'Partner')) { 
            $deleteAllowed = true;
        }

        if (!empty($partners)) {
            foreach ($partners as $key => $partner) {
                $buttons = '';

                if ($updateAllowed) {
                    $buttons .= ' 
                        <form action="' . site_url('partner/setProduct') . '" method="post" class="d-inline">
                            ' . csrf_field() . '
                            <input type="hidden" name="id" value="' . $partner['partner_id'] . '">
                            <button type="submit" class="btn btn-sm btn-info me-1">
                                <i class="fas fa-eye"></i>
                            </button>
                        </form>';
                }

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

    public function setProduct()
    {
        $id = $this->request->getPost('id');

        if (!$id) {
            return redirect()->to('/partner');
        }

        session()->setFlashdata('detail_partner_id', $id);

        return redirect()->to('/partner/product');
    }

    public function product()
    {
        if (!hasPermission('edit', 'Master', 'Partner')) { 
            return redirect()->to('/')->with('error', 'Unauthorized access.');
        }

        $id = session()->getFlashdata('detail_partner_id'); 
        
        if (!$id) {
            return redirect()->to('/partner')->with('error', 'Product not found.');
        }
        
        
        $createAllowed = false;
        if (hasPermission('create', 'Master', 'Partner')) { 
            $createAllowed = true;
        }
        
        $model = new PartnerModel();
        $partner = $model->getPartnerDetail($id);
        
        if (!$partner) {
            return redirect()->to('/partner')->with('error', 'Partner not found.');
        }
        
        $listProduct = new ProductModel();
        $products = $listProduct->findAll();  

        $data['id'] = $id;
        $data['create'] = $createAllowed;
        $data['detail'] = $partner;
        $data['products'] = $products;

        return view('master/partner/product', $data);

    }

    public function getProduct()
    {
        $partnerId = $this->request->getPost('id'); 

        if (!$partnerId) {
            return $this->response->setJSON([
                'data'      => [],
                'csrfHash'  => csrf_hash(),
                'error'     => 'Partner ID is required'
            ]);
        }

        $model = new PartnerProductModel();
        $products = $model->getPartnerProductsDetail($partnerId);

        $result = ['data' => []];

        $updateAllowed = hasPermission('update', 'Master', 'Partner');
        $deleteAllowed = hasPermission('delete', 'Master', 'Partner');

        foreach ($products as $key => $product) {
            $buttons = '';

            if ($updateAllowed) {
                $buttons .= '<button class="btn btn-sm btn-warning editProduct" 
                                data-id="' . $product['partner_product_id'] . '"
                                data-product="' . $product['product_id'] . '"
                                data-sku="' . $product['customer_sku'] . '"
                                >
                                <i class="fas fa-edit"></i>
                            </button> ';
            }

            if ($deleteAllowed) {
                $buttons .= '<button class="btn btn-sm btn-danger deleteProduct" data-id="' . $product['partner_product_id'] . '">
                                <i class="fas fa-trash"></i>
                            </button>';
            }

            $result['data'][$key] = [
                ($key + 1),
                $product['product_name'] ?? '-',
                $product['customer_sku'] ?? '-',
                $buttons
            ];
        }

        return $this->response->setJSON([
            'data'      => $result['data'],
            'csrfHash'  => csrf_hash()
        ]);
    }

    public function addProduct()
    {
        $model = new PartnerProductModel();
        $partnerId = $this->request->getPost('id');
        $productId = $this->request->getPost('product_id');
        $customerSku = $this->request->getPost('customer_sku');

        if (empty($partnerId)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to add product',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(500);
        }
        
        if (empty($productId)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Product is required',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        if (empty($customerSku)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'SKU is required',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        $exist = $model->where('partner_id', $partnerId)->where('product_id', $productId)->first();

        if($exist){
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Product already used',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        $existSku = $model->where('customer_sku', $customerSku)->where('partner_id', $partnerId)->first();
        if($existSku){
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Sku already used',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        $data = [
            'partner_id' => $partnerId,
            'product_id' => $productId,
            'customer_sku' => $customerSku,
        ];

        if ($model->insert($data)) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Product added successfully',
                'csrfHash' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to add product',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(500);
        }
    }

    public function updateProduct()
    {
        $model = new PartnerProductModel();
        $dataId = $this->request->getPost('id');
        $partnerId = intval($this->request->getPost('partner_id'));
        $productReal = intval($this->request->getPost('product_real'));
        $productId = intval($this->request->getPost('product_id'));
        $customerSku = $this->request->getPost('customer_sku');

        if (empty($dataId)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to add product',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(500);
        }
        
        if (empty($customerSku)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Product is required',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        if (empty($productId)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'SKU is required',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        $bool = $productId == $productReal;

        if(!$bool){
            $exist = $model->where('product_id', $productId)->where('partner_id', $partnerId)->first();
    
            if($exist){
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Product already used',
                    'csrfHash' => csrf_hash()
                ])->setStatusCode(400);
            }
        }

        $existSku = $model->where('customer_sku', $customerSku)->where('partner_id', $partnerId)->first();
        if($existSku){
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Sku already used',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        $data = [
            'product_id' => $productId,
            'customer_sku' => $customerSku,
        ];

        if ($model->update($dataId, $data)) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Product updated successfully',
                'csrfHash' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to update Product',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(500);
        }
    }

    public function deleteProduct()
    {
        $model = new PartnerProductModel();
        $id = $this->request->getPost('id');
        if (empty($id)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to delete product',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        if ($model->delete($id)) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Product deleted successfully',
                'csrfHash' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to delete Product',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(500);
        }
    }


}
