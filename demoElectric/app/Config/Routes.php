<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index'); 
$routes->get('/about', 'About::index'); 
$routes->get('/services', 'Services::index'); 
$routes->match(['get', 'post'], '/contact', 'Contact::index'); 
$routes->get('/register', 'Register::index'); 
$routes->post('/register', 'Register::create');
$routes->get('/login', 'Login::index');
$routes->post('/login', 'Login::authenticate');
$routes->get('/logout', 'Login::logout');
$routes->get('/accounts', 'Accounts::index');
$routes->get('/account/(:num)', 'Accounts::viewAccount/$1');
$routes->get('/accounts/create', 'Accounts::create');
$routes->post('/accounts/store', 'Accounts::store');
$routes->get('/accounts/edit/(:num)', 'Accounts::edit/$1');
$routes->post('/accounts/update/(:num)', 'Accounts::update/$1');
$routes->post('/accounts/delete/(:num)', 'Accounts::delete/$1');
