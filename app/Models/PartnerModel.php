<?php

namespace App\Models;

use CodeIgniter\Model;

class PartnerModel extends Model
{
    protected $table = 'partners';
    protected $primaryKey = 'partner_id';
    protected $allowedFields = ['name', 'role'];

    public function getPartnerDetail($partnerId = null)
    {
        $builder = $this->select('partners.*');

        if ($partnerId !== null) {
            return $builder->where('partners.partner_id', $partnerId)->first(); 
        }

        return $builder->findAll();
    }
}
