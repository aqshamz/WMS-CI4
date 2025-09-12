<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\UomModel;
use App\Models\ProductUomModel;

class MasterProductController extends BaseController
{

    
    public function index()
    {
        if (!hasPermission('view', 'Master', 'Product', true)) { 
            return redirect()->to('/')->with('error', 'Unauthorized access.');
        }

        $createAllowed = false;
        if (hasPermission('create', 'Master', 'Product', false)) { 
            $createAllowed = true;
        }

        $data['create'] = $createAllowed;

        $model = new UomModel();
        $uoms = $model->findAll();
        $data['uoms'] = $uoms;

        return view('master/product/index', $data);
    }

    public function getProduct()
    {
        $model = new ProductModel();
        $products = $model->getProductsWithUom();  

        $result = ['data' => []];

        $updateAllowed = hasPermission('update', 'Master', 'Product', false);
        $deleteAllowed = hasPermission('delete', 'Master', 'Product', false);

        if (!empty($products)) {
            foreach ($products as $key => $product) {
                $buttons = '';

                if ($updateAllowed) {
                    $buttons .= ' 
                        <form action="' . site_url('product/setConvertion') . '" method="post" class="d-inline">
                            ' . csrf_field() . '
                            <input type="hidden" name="id" value="' . $product['product_id'] . '">
                            <button type="submit" class="btn btn-sm btn-info me-1">
                                <i class="fas fa-eye"></i>
                            </button>
                        </form>';
                }

                if ($updateAllowed) {
                    $buttons .= ' <button class="btn btn-sm btn-warning me-1 edit-product"
                                    data-id="' . $product['product_id'] . '"
                                    data-uom="' . $product['base_uom_id'] . '"
                                    data-fee="' . $product['is_pack_free'] . '"
                                    data-active="' . $product['is_active'] . '"
                                    data-sku="' . htmlspecialchars($product['sku_code'], ENT_QUOTES, 'UTF-8') . '"
                                    data-name="' . htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') . '"
                                    data-rotation="' . htmlspecialchars($product['rotation'], ENT_QUOTES, 'UTF-8') . '"
                                    >
                                    <i class="fas fa-edit"></i>
                                  </button>';
                }

                if ($deleteAllowed) {
                    $buttons .= ' <button type="button" class="btn btn-sm btn-danger delete-product"
                                    data-id="' . $product['product_id'] . '">
                                    <i class="fas fa-trash-alt"></i>
                                  </button>';
                }

                

                $handle;
                if($product['is_pack_free'] && $product['is_pack_free'] == 1){
                    $handle = '<span class="badge bg-success">Free</span>';
                }else{
                    $handle = '<span class="badge bg-primary">Paid</span>';
                }

                $active;
                if($product['is_active'] && $product['is_active'] == 1){
                    $active = '<span class="badge bg-success">Active</span>';
                }else{
                    $active = '<span class="badge bg-danger">Non-Active</span>';
                }


                $result['data'][$key] = [
                    ($key + 1),
                    $product['sku_code'] ?? '-',
                    $product['name'] ?? '-',
                    $product['rotation'] ?? '-',
                    $product['uom_name'] ?? '-',
                    $handle ?? '-',
                    $active ?? '-',
                    $buttons
                ];
            }
        }

        return $this->response->setJSON($result);
    }

    public function addProduct()
    {
        $model = new ProductModel();
        $skuCode = $this->request->getPost('sku_code');
        $name = $this->request->getPost('name');
        $rotation = $this->request->getPost('rotation');
        $baseUomId = $this->request->getPost('base_uom_id');
        $barcode = '';
        $is_pack_free = $this->request->getPost('is_pack_free');
        $is_active = $this->request->getPost('is_active');

        if (empty($skuCode)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Sku is required',
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

        if (empty($rotation)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Type is required',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        if (empty($baseUomId)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Uom Base is required',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        $data = [
            'sku_code' => $skuCode,
            'name' => $name,
            'rotation' => $rotation,
            'base_uom_id' => $baseUomId,
            'barcode' => $barcode,
            'is_pack_free' => $is_pack_free,
            'is_active' => $is_active,
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
        $model = new ProductModel();
        $id = $this->request->getPost('id');
        $skuCode = $this->request->getPost('sku_code');
        $name = $this->request->getPost('name');
        $rotation = $this->request->getPost('rotation');
        $baseUomId = $this->request->getPost('base_uom_id');
        $barcode = '';
        $is_pack_free = $this->request->getPost('is_pack_free');
        $is_active = $this->request->getPost('is_active');

        if (empty($id)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to update product',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(500);
        }

        if (empty($skuCode)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Sku is required',
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

        if (empty($rotation)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Type is required',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        if (empty($baseUomId)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Uom Base is required',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        $data = [
            'sku_code' => $skuCode,
            'name' => $name,
            'rotation' => $rotation,
            'base_uom_id' => $baseUomId,
            'barcode' => $barcode,
            'is_pack_free' => $is_pack_free,
            'is_active' => $is_active,
        ];

        if ($model->update($id, $data)) {
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
        $model = new ProductModel();
        $id = $this->request->getPost('id');
        if (empty($id)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to delete Product',
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

    public function setConvertion()
    {
        $id = $this->request->getPost('id');

        if (!$id) {
            return redirect()->to('/product');
        }

        session()->setFlashdata('detail_product_id', $id);

        return redirect()->to('/product/convertion');
    }

    public function convertion()
    {
        if (!hasPermission('edit', 'Master', 'Product', true)) { 
            return redirect()->to('/')->with('error', 'Unauthorized access.');
        }

        $id = session()->getFlashdata('detail_product_id'); 
        
        if (!$id) {
            return redirect()->to('/product')->with('error', 'Product not found.');
        }
        
        
        $createAllowed = false;
        if (hasPermission('create', 'Master', 'Product', false)) { 
            $createAllowed = true;
        }
        
        $model = new ProductModel();
        $product = $model->getProductsWithUom($id);
        
        if (!$product) {
            return redirect()->to('/product')->with('error', 'Product not found.');
        }
        
        $listUom = new UomModel();
        $uoms = $listUom->whereNotIn('uom_id', [$product['base_uom_id']])->findAll();

        $data['id'] = $id;
        $data['create'] = $createAllowed;
        $data['detail'] = $product;
        $data['uoms'] = $uoms;

        return view('master/product/convertion', $data);

    }

    public function getConvertion()
    {
        $productId = $this->request->getPost('id'); 

        if (!$productId) {
            return $this->response->setJSON([
                'data'      => [],
                'csrfHash'  => csrf_hash(),
                'error'     => 'Product ID is required'
            ]);
        }

        $model = new ProductUomModel();
        $convertions = $model->getProductsWithUom($productId);

        $result = ['data' => []];

        $updateAllowed = hasPermission('update', 'Master', 'Product', false);
        $deleteAllowed = hasPermission('delete', 'Master', 'Product', false);

        foreach ($convertions as $key => $convertion) {
            $buttons = '';

            if ($updateAllowed) {
                $buttons .= '<button class="btn btn-sm btn-warning editConvertion" 
                                data-id="' . $convertion['product_uom_id'] . '"
                                data-uom="' . $convertion['uom_id'] . '"
                                data-factor="' . $convertion['factor_to_base'] . '"
                                >
                                <i class="fas fa-edit"></i>
                            </button> ';
            }

            if ($deleteAllowed) {
                $buttons .= '<button class="btn btn-sm btn-danger deleteConvertion" data-id="' . $convertion['product_uom_id'] . '">
                                <i class="fas fa-trash"></i>
                            </button>';
            }

            $result['data'][$key] = [
                ($key + 1),
                $convertion['uom_name'] ?? '-',
                $convertion['factor_to_base'] ?? '-',
                $buttons
            ];
        }

        return $this->response->setJSON([
            'data'      => $result['data'],
            'csrfHash'  => csrf_hash()
        ]);
    }

    public function addConvertion()
    {
        $model = new ProductUomModel();
        $productId = $this->request->getPost('id');
        $uomId = $this->request->getPost('uom_id');
        $convertion = intval($this->request->getPost('convertion'));

        if (empty($productId)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to add convertion',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(500);
        }
        
        if (empty($uomId)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Uom Convertion is required',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        if (empty($convertion)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Convertion is required',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        $exist = $model->where('product_id', $productId)->where('uom_id', $uomId)->first();

        if($exist){
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Uom Convertion already used',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        $data = [
            'product_id' => $productId,
            'uom_id' => $uomId,
            'factor_to_base' => $convertion,
        ];

        if ($model->insert($data)) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Convertion added successfully',
                'csrfHash' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to add convertion',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(500);
        }
    }

    public function updateConvertion()
    {
        $model = new ProductUomModel();
        $productId = $this->request->getPost('pid');
        $dataId = $this->request->getPost('id');
        $uomId = intval($this->request->getPost('uom_id'));
        $uomReal = intval($this->request->getPost('uom_real'));
        $convertion = intval($this->request->getPost('convertion'));

        if (empty($productId)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to add convertion',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(500);
        }
        
        if (empty($uomId)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Uom Convertion is required',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        if (empty($convertion)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Convertion is required',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        $bool = $uomId == $uomReal;

        if(!$bool){
            $exist = $model->where('product_id', $productId)->where('uom_id', $uomId)->first();
    
            if($exist){
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Uom Convertion already used',
                    'csrfHash' => csrf_hash()
                ])->setStatusCode(400);
            }
        }


        $data = [
            'uom_id' => $uomId,
            'factor_to_base' => $convertion,
        ];

        if ($model->update($dataId, $data)) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Convertion updated successfully',
                'csrfHash' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to update Convertion',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(500);
        }
    }

    public function deleteConvertion()
    {
        $model = new ProductUomModel();
        $id = $this->request->getPost('id');
        if (empty($id)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to delete Convertion',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(400);
        }

        if ($model->delete($id)) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Convertion deleted successfully',
                'csrfHash' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to delete Convertion',
                'csrfHash' => csrf_hash()
            ])->setStatusCode(500);
        }
    }


}
