<?php

use App\Http\Controllers\API\AssetAPIController;
use App\Http\Controllers\API\AttendanceAPIController;
use App\Http\Controllers\API\ClientAPIController;
use App\Http\Controllers\API\ContactPersonAPIController;
use App\Http\Controllers\API\EmployeeAPIController;
use App\Http\Controllers\API\GroupTaskAPIController;
use App\Http\Controllers\API\InvoiceAPIController;
use App\Http\Controllers\API\LocationAddressAPIController;
use App\Http\Controllers\API\LocationAPIController;
use App\Http\Controllers\API\Mobile\AttendanceAPIController as AttendanceMobileAPIController;
use App\Http\Controllers\API\Mobile\EmployeeAPIController as EmployeeMobileAPIController;
use App\Http\Controllers\API\Mobile\SystemConfigAPIController as SystemConfigMobileAPIController;
use App\Http\Controllers\API\Mobile\WorkOrderAPIController as WorkOrderMobileAPIController;
use App\Http\Controllers\API\OfficialReceiptAPIController;
use App\Http\Controllers\API\OutlineAgreementAPIController;
use App\Http\Controllers\API\PurchaseOrderAPIController;
use App\Http\Controllers\API\PurchaseOrderHasSupplierAPIController;
use App\Http\Controllers\API\PurchaseOrderHasSupplierTaskAPIController;
use App\Http\Controllers\API\PurchaseOrderHasTaskAPIController;
use App\Http\Controllers\API\QuotationAPIController;
use App\Http\Controllers\API\RemarkTemplateAPIController;
use App\Http\Controllers\API\RoleAPIController;
use App\Http\Controllers\API\SubcontractorAPIController;
use App\Http\Controllers\API\SupplierAPIController;
use App\Http\Controllers\API\SupplierProductAPIController;
use App\Http\Controllers\API\SystemConfigAPIController;
use App\Http\Controllers\API\TaskAPIController;
use App\Http\Controllers\API\TemplateAPIController;
use App\Http\Controllers\API\UserAPIController;
use App\Http\Controllers\API\WorkInstructionAPIController;
use App\Http\Controllers\API\WorkOrderAPIController;
use App\Http\Controllers\API\WorkOrderHasTaskAPIController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:admins')->prefix('web')->group(function () {
    Route::post('genQuotation/{id}', [PurchaseOrderAPIController::class, 'genQuotation']);
    Route::post('genSupplierPO/{id}', [PurchaseOrderAPIController::class, 'genSupplierPO']);
    Route::post('genSubcontractorPO/{id}', [PurchaseOrderAPIController::class, 'genSubcontractorPO']);
    Route::get('genInvoice/{id}', [PurchaseOrderAPIController::class, 'genInvoice']);
    Route::get('genReceipt/{id}', [PurchaseOrderAPIController::class, 'genReceipt']);
    Route::post('genServiceReport/{id}', [WorkOrderAPIController::class, 'genReport']);
    Route::apiResource('clients', ClientAPIController::class);
    Route::post('clients/duplicate/{id}', [ClientAPIController::class, 'duplicate']);
    Route::apiResource('contactPeople', ContactPersonAPIController::class);
    Route::get('client/contactPeople/{id}', [ClientAPIController::class, 'showContactByClient']);
    Route::apiResource('suppliers', SupplierAPIController::class);
    Route::apiResource('employees', EmployeeAPIController::class);
    Route::apiResource('quotations', QuotationAPIController::class);
    Route::apiResource('purchaseOrders', PurchaseOrderAPIController::class);
    Route::apiResource('groupTasks', GroupTaskAPIController::class);
    Route::get('groupTaskNameList', [GroupTaskAPIController::class, 'nameList']);
    Route::get('productList/{supplierId}', [SupplierProductAPIController::class, 'productNameList']);
    Route::apiResource('locations', LocationAPIController::class);
    Route::apiResource('locationAddresses', LocationAddressAPIController::class);
    Route::apiResource('attendances', AttendanceAPIController::class);
    Route::apiResource('invoices', InvoiceAPIController::class);
    Route::apiResource('officialReceipts', OfficialReceiptAPIController::class);
    Route::apiResource('workInstructions', WorkInstructionAPIController::class);
    Route::apiResource('remarkTemplates', RemarkTemplateAPIController::class);
    Route::apiResource('workOrders', WorkOrderAPIController::class);
    Route::get('addressList/{id}', [WorkOrderAPIController::class, 'addressList']);
    Route::apiResource('subcontractors', SubcontractorAPIController::class);
    Route::apiResource('templates', TemplateAPIController::class);
    Route::apiResource('systemConfigs', SystemConfigAPIController::class);
    Route::apiResource('tasks', TaskAPIController::class);
    Route::apiResource('roles', RoleAPIController::class);
    Route::apiResource('outlineAgreements', OutlineAgreementAPIController::class);
    Route::apiResource('assets', AssetAPIController::class);
    Route::apiResource('products', SupplierProductAPIController::class);
    Route::patch('poSuppliers', [PurchaseOrderHasSupplierAPIController::class, 'updateAll']);
    Route::apiResource('poSuppliers', PurchaseOrderHasSupplierAPIController::class);
    Route::patch('poSubcontractors', [PurchaseOrderHasSubcontractorAPIController::class, 'updateAll']);
    Route::apiResource('poSubcontractors', PurchaseOrderHasSubcontractorAPIController::class);
    Route::apiResource('poTasks', PurchaseOrderHasTaskAPIController::class)->only('destroy');
    Route::apiResource('poSubcontractorTasks', PurchaseOrderHasSubcontractorTaskAPIController::class)->only('destroy');
    Route::apiResource('woTasks', WorkOrderHasTaskAPIController::class)->only('destroy');
    Route::get('users/self', [UserAPIController::class, 'self']);
    Route::apiResource('users', UserAPIController::class);

    Route::post('subcontractors/{id}/addTasks', [SubcontractorAPIController::class, 'addTasks']);
    Route::post('suppliers/{id}/updateProducts', [SupplierAPIController::class, 'updateProducts']);
    Route::post('subcontractors/{id}/updateTasks', [SubcontractorAPIController::class, 'updateTasks']);
    Route::post('purchaseOrders/{id}/fillSubconTasks', [PurchaseOrderAPIController::class, 'fillSubconTasks']);
    Route::post('purchaseOrders/{id}/updateProducts', [PurchaseOrderAPIController::class, 'updatePOProducts']);
});

Route::middleware('auth:employees')->group(function () {
    Route::get('self', [EmployeeMobileAPIController::class, 'self']);
    Route::post('changePassword', [EmployeeMobileAPIController::class, 'changePassword']);
    Route::apiResource('attendances', AttendanceMobileAPIController::class)->only(['index', 'show', 'store']);
    Route::apiResource('workOrders', WorkOrderMobileAPIController::class)->only(['index', 'show', 'update']);
    Route::apiResource('companyNotices', SystemConfigMobileAPIController::class)->only(['index', 'show']);
    Route::post('workOrders/updateResultImage/{id}', [WorkOrderMobileAPIController::class, 'updateResultImage']);
    Route::post('workOrders/updateSignature/{id}', [WorkOrderMobileAPIController::class, 'updateSignature']);
});
