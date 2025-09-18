<?php

namespace App\Controllers;

use App\Models\DocumentModel;

class DocumentController extends BaseController
{
    public function index()
{
    $type = $this->request->getGet('type');
    $status = $this->request->getGet('status'); 

    $model = new DocumentModel();

    if ($type) {
        $model = $model->where('doc_type', $type);
    }

    if ($status) {
        $model = $model->where('status', $status);
    }

    $data = $model->findAll();

    return $this->response->setJSON($data);
}

}
