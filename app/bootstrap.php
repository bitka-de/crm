<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\ContactsController;
use App\Controllers\CompanyController;
use App\Controllers\DashboardController;
use App\Controllers\DocumentsController;
use App\Controllers\HomeController;
use App\Core\Router;
use App\Core\Session;
use App\Middleware\AuthMiddleware;

require_once __DIR__ . '/../vendor/autoload.php';

Session::start();

$router = new Router();

// Oeffentliche Routen
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

// Geschuetzte Routen
$router->get('/', [HomeController::class, 'index'], [AuthMiddleware::class]);
$router->get('/dashboard', [DashboardController::class, 'index'], [AuthMiddleware::class]);
$router->get('/company', [CompanyController::class, 'index'], [AuthMiddleware::class]);
$router->post('/company', [CompanyController::class, 'update'], [AuthMiddleware::class]);
$router->get('/contacts', [ContactsController::class, 'index'], [AuthMiddleware::class]);
$router->get('/contacts/show', [ContactsController::class, 'showContact'], [AuthMiddleware::class]);
$router->get('/contacts/companies/show', [ContactsController::class, 'showCompany'], [AuthMiddleware::class]);
$router->post('/contacts', [ContactsController::class, 'save'], [AuthMiddleware::class]);
$router->post('/contacts/delete', [ContactsController::class, 'delete'], [AuthMiddleware::class]);
$router->post('/contacts/statuses', [ContactsController::class, 'saveStatuses'], [AuthMiddleware::class]);
$router->post('/contacts/statuses/add', [ContactsController::class, 'addStatus'], [AuthMiddleware::class]);
$router->post('/contacts/statuses/delete', [ContactsController::class, 'deleteStatus'], [AuthMiddleware::class]);
$router->post('/contacts/companies', [ContactsController::class, 'saveCompanies'], [AuthMiddleware::class]);
$router->post('/contacts/companies/save', [ContactsController::class, 'saveCompany'], [AuthMiddleware::class]);
$router->post('/contacts/companies/delete', [ContactsController::class, 'deleteCompany'], [AuthMiddleware::class]);
$router->get('/documents', [DocumentsController::class, 'index'], [AuthMiddleware::class]);
$router->get('/documents/pdf', [DocumentsController::class, 'downloadPdf'], [AuthMiddleware::class]);
$router->post('/documents/offers', [DocumentsController::class, 'saveOffer'], [AuthMiddleware::class]);
$router->post('/documents/invoices', [DocumentsController::class, 'saveInvoice'], [AuthMiddleware::class]);
$router->post('/documents/reminders', [DocumentsController::class, 'saveReminder'], [AuthMiddleware::class]);
$router->post('/documents/delete', [DocumentsController::class, 'deleteEntry'], [AuthMiddleware::class]);
$router->get('/documents/invoices/edit', [DocumentsController::class, 'editInvoice'], [AuthMiddleware::class]);
$router->post('/documents/invoices/update', [DocumentsController::class, 'updateInvoice'], [AuthMiddleware::class]);

return $router;