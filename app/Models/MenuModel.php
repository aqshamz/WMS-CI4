<?php

namespace App\Models;
use CodeIgniter\Model;

class MenuModel extends Model
{
    protected $table = 'mst_menu'; // adjust to your table name

    public function getMenuByRole($roleId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('group_permission gp')
            ->select('m.menu_id, m.menu_name, m.menu_icon, sm.sub_menu_id, sm.sub_menu_name, sm.sub_menu_icon, sm.sub_menu_url')
            ->join('permission p', 'p.permission_id = gp.permission_id', 'left')
            ->join('menu m', 'm.menu_id = gp.menu_id', 'left')
            ->join('sub_menu sm', 'sm.sub_menu_id = gp.sub_menu_id', 'left')
            ->where('gp.role_id', $roleId)
            ->groupBy('m.menu_id, m.menu_name, m.menu_icon, sm.sub_menu_id, sm.sub_menu_name, sm.sub_menu_icon, sm.sub_menu_url')
            ->orderBy('m.menu_id, sm.sub_menu_id');

        $result = $builder->get()->getResultArray();

        // group menu with submenus
        $menuData = [];
        foreach ($result as $row) {
            $menuId = $row['menu_id'];

            if (!isset($menuData[$menuId])) {
                $menuData[$menuId] = [
                    'menu_id'   => $row['menu_id'],
                    'menu_name' => $row['menu_name'],
                    'menu_icon' => $row['menu_icon'],
                    'submenus'  => []
                ];
            }

            if (!empty($row['sub_menu_id'])) {
                $menuData[$menuId]['submenus'][] = [
                    'sub_menu_id'   => $row['sub_menu_id'],
                    'sub_menu_name' => $row['sub_menu_name'],
                    'sub_menu_icon' => $row['sub_menu_icon'],
                    'sub_menu_url' => $row['sub_menu_url']
                ];
            }
        }
        return $menuData;
    }
}
