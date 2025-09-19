<?php

function isLoggedIn()
{
    return session()->get('isLoggedIn') === true;
}

function currentUser()
{
    return session()->get();
}

function hasRole($role)
{
    return session()->get('role_id') === $role;
}

function hasPermission($permissionName, $menuId = null, $subMenuId = null)
{
    $session = session();
    $permissions = $session->get('permissions') ?? [];

    foreach ($permissions as $p) {
        if ($p['permission_name'] == $permissionName &&
            $p['menu_name'] == $menuId &&
            $p['sub_menu_name'] == $subMenuId) {
            return true;
        }
    }

    return false;
}
