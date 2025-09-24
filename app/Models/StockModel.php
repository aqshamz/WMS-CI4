<?php

namespace App\Models;

use CodeIgniter\Model;

class StockModel extends Model
{
    protected $table = 'stock';
    protected $primaryKey = 'stock_id';
    protected $allowedFields = ['product_id','partner_id', 'location_id', 'lot_id', 'uom_id', 'qty_on_hand'];
    
    public function addInboundStock(array $stockData, string $type = 'FIFO')
    {
        if ($type === 'FEFO') {
            $where = [
                'product_id'  => $stockData['product_id'],
                'partner_id'  => $stockData['partner_id'],
                'location_id' => $stockData['location_id'],
                'uom_id'      => $stockData['uom_id'],
                'lot_id'      => $stockData['lot_id'] ?? null,
            ];

            $existing = $this->where($where)->first();

            if ($existing) {
                return $this->update($existing['stock_id'], [
                    'qty_on_hand' => $existing['qty_on_hand'] + $stockData['qty_on_hand']
                ]);
            }
        }

        return $this->insert($stockData);
    }
}
