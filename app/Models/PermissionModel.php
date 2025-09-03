<?php

namespace App\Models;

use CodeIgniter\Model;

class PermissionModel extends Model
{
    protected $table = 'group_permission';
    protected $primaryKey = 'group_id';
    protected $allowedFields = ['role_id', 'permission_id', 'menu_id', 'sub_menu_id'];

    public function getPermissionsByRole($roleId)
    {
        return $this->select('m.menu_id, m.menu_name, sm.sub_menu_id, sm.sub_menu_name, p.permission_name')
            ->join('permission p', 'p.permission_id = group_permission.permission_id')
            ->join('menu m', 'm.menu_id = group_permission.menu_id')
            ->join('sub_menu sm', 'sm.sub_menu_id = group_permission.sub_menu_id')
            ->where('group_permission.role_id', $roleId)
            ->findAll();
    }

}
