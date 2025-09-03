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
    });

    $routes->group('admin', ['filter' => 'rolecheck:1'], function($routes) {
        $routes->get('users', 'UserController::index');
        $routes->get('roles', 'RoleController::index');
    });

    $routes->group('vendor', ['filter' => 'rolecheck:2'], function($routes) {
        $routes->get('orders', 'OrderController::index');
    });
});



