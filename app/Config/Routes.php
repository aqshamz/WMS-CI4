<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// app/Config/Routes.php

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Auth');
$routes->setDefaultMethod('login');

$routes->get('/login', 'Auth::login');  
$routes->post('/auth/doLogin', 'Auth::doLogin'); 
$routes->get('/logout', 'Auth::logout'); 


$routes->group('', ['filter' => 'authcheck'], function($routes) {
    $routes->get('/', 'Home::index');

    $routes->group('uom', function($routes) {
        $routes->get('/', 'MasterUomController::index');
        $routes->get('data', 'MasterUomController::getUoms');
        $routes->post('add', 'MasterUomController::addUom');
        $routes->post('update', 'MasterUomController::updateUom');
        $routes->post('delete', 'MasterUomController::deleteUom');
    });
    
    $routes->group('partner', function($routes) {
        $routes->get('/', 'MasterPartnerController::index');
        $routes->get('data', 'MasterPartnerController::getPartners');
        $routes->post('add', 'MasterPartnerController::addPartner');
        $routes->post('update', 'MasterPartnerController::updatePartner');
        $routes->post('delete', 'MasterPartnerController::deletePartner');

        $routes->post('setProduct', 'MasterPartnerController::setProduct');
        $routes->get('product', 'MasterPartnerController::product');
        $routes->post('data-product', 'MasterPartnerController::getProduct');
        $routes->post('add-product', 'MasterPartnerController::addProduct');
        $routes->post('update-product', 'MasterPartnerController::updateProduct');
        $routes->post('delete-product', 'MasterPartnerController::deleteProduct');
    });

    $routes->group('product', function($routes) {
        $routes->get('/', 'MasterProductController::index');
        $routes->get('data', 'MasterProductController::getProduct');
        $routes->post('add', 'MasterProductController::addProduct');
        $routes->post('update', 'MasterProductController::updateProduct');
        $routes->post('delete', 'MasterProductController::deleteProduct');

        $routes->post('setConvertion', 'MasterProductController::setConvertion');
        $routes->get('convertion', 'MasterProductController::convertion');
        $routes->post('data-convertion', 'MasterProductController::getConvertion');
        $routes->post('add-convertion', 'MasterProductController::addConvertion');
        $routes->post('update-convertion', 'MasterProductController::updateConvertion');
        $routes->post('delete-convertion', 'MasterProductController::deleteConvertion');
    });

    $routes->group('warehouse', function($routes) {
        $routes->get('/', 'MasterWarehouseController::index');
        $routes->get('data', 'MasterWarehouseController::getWarehouse');
        $routes->post('add', 'MasterWarehouseController::addWarehouse');
        $routes->post('update', 'MasterWarehouseController::updateWarehouse');
        $routes->post('delete', 'MasterWarehouseController::deleteWarehouse');

        $routes->post('setLocation', 'MasterWarehouseController::setLocation');
        $routes->get('location', 'MasterWarehouseController::location');
        $routes->post('data-location', 'MasterWarehouseController::getLocation');
        $routes->post('add-location', 'MasterWarehouseController::addLocation');
        $routes->post('update-location', 'MasterWarehouseController::updateLocation');
        $routes->post('delete-location', 'MasterWarehouseController::deleteLocation');
    });

    $routes->group('admin', ['filter' => 'rolecheck:1'], function($routes) {
        $routes->get('users', 'UserController::index');
        $routes->get('roles', 'RoleController::index');
    });

    $routes->group('vendor', ['filter' => 'rolecheck:2'], function($routes) {
        $routes->get('orders', 'OrderController::index');
    });
});



