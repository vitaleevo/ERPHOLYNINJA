<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\ConsultationController;
use App\Http\Controllers\Api\PrescriptionController;
use App\Http\Controllers\Api\AccountPayableController;
use App\Http\Controllers\Api\AccountReceivableController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\CommissionController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PharmacyController;
use App\Http\Controllers\Api\TelemedicineController;
use App\Http\Controllers\Api\LabTestController;
use App\Http\Controllers\Api\LabCategoryController;
use App\Http\Controllers\Api\LabRequestController;
use App\Http\Controllers\Api\LabResultController;
use App\Http\Controllers\Api\LabEquipmentController;
use App\Http\Controllers\Api\LabProfileController;

// Rotas públicas de autenticação
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'registerClinic']);

// Rotas protegidas
Route::middleware(['auth:sanctum', 'clinic'])->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    
    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::post('/dashboard/clear-cache', [DashboardController::class, 'clearCache']);
    
    // Módulo Farmácia
    Route::prefix('pharmacy')->group(function () {
        Route::get('/medications', [PharmacyController::class, 'medications']);
        Route::post('/medications', [PharmacyController::class, 'storeMedication']);
        Route::get('/medications/{medication}', [PharmacyController::class, 'showMedication']);
        Route::post('/medications/{medication}/batches', [PharmacyController::class, 'storeBatch']);
        Route::post('/sales', [PharmacyController::class, 'storeSale']);
        Route::get('/alerts', [PharmacyController::class, 'alerts']);
        Route::get('/stock-summary', [PharmacyController::class, 'stockSummary']);
    });
    
    // Módulo Telemedicina
    Route::prefix('telemedicine')->group(function () {
        Route::get('/sessions', [TelemedicineController::class, 'index']);
        Route::post('/appointments/{appointmentId}/create-session', [TelemedicineController::class, 'createFromAppointment']);
        Route::get('/sessions/{id}', [TelemedicineController::class, 'show']);
        Route::post('/sessions/{id}/start', [TelemedicineController::class, 'start']);
        Route::post('/sessions/{id}/end', [TelemedicineController::class, 'end']);
        Route::post('/sessions/{id}/cancel', [TelemedicineController::class, 'cancel']);
        Route::post('/sessions/{id}/join', [TelemedicineController::class, 'join']);
        
        // Chat e Arquivos
        Route::get('/sessions/{sessionId}/messages', [TelemedicineController::class, 'getMessages']);
        Route::post('/sessions/{sessionId}/messages', [TelemedicineController::class, 'sendMessage']);
        Route::get('/sessions/{sessionId}/files', [TelemedicineController::class, 'getFiles']);
        Route::post('/sessions/{sessionId}/files', [TelemedicineController::class, 'uploadFile']);
        
        // Configurações e Utilitários
        Route::get('/sessions/{sessionId}/config', [TelemedicineController::class, 'getSessionConfig']);
        Route::get('/patients/{patientId}/history', [TelemedicineController::class, 'patientHistory']);
        Route::get('/statistics', [TelemedicineController::class, 'statistics']);
    });

    // Módulo Laboratório
    Route::prefix('lab')->group(function () {
        // Categorias de Exames
        Route::apiResource('categories', LabCategoryController::class);
        
        // Perfis de Exames
        Route::apiResource('profiles', LabProfileController::class);
        
        // Exames Laboratoriais
        Route::apiResource('tests', LabTestController::class);
        Route::get('tests/active/list', [LabTestController::class, 'activeTests']);
        
        // Pedidos de Exames
        Route::apiResource('requests', LabRequestController::class);
        Route::post('requests/{request}/update-status', [LabRequestController::class, 'updateStatus']);
        Route::post('requests/{request}/assign-technician', [LabRequestController::class, 'assignTechnician']);
        Route::post('requests/{request}/validate', [LabRequestController::class, 'validateRequest']);
        Route::post('requests/{request}/reject', [LabRequestController::class, 'reject']);
        
        // Resultados de Exames
        Route::apiResource('results', LabResultController::class);
        Route::post('results/bulk', [LabResultController::class, 'bulkStore']);
        Route::get('patients/{patientId}/results-history', [LabResultController::class, 'patientHistory']);
        
        // Equipamentos
        Route::apiResource('equipment', LabEquipmentController::class);
        Route::get('equipment/needs-calibration', [LabEquipmentController::class, 'needsCalibration']);
    });
    
    // CRUDs Principais
    Route::apiResource('patients', PatientController::class);
    Route::apiResource('appointments', AppointmentController::class);
    Route::apiResource('consultations', ConsultationController::class);
    Route::apiResource('prescriptions', PrescriptionController::class);
    
    // Módulo Financeiro
    Route::prefix('financial')->group(function () {
        // Contas a Pagar
        Route::apiResource('accounts-payable', AccountPayableController::class);
        Route::post('accounts-payable/{accountPayable}/mark-as-paid', [AccountPayableController::class, 'markAsPaid']);
        Route::post('accounts-payable/{accountPayable}/cancel', [AccountPayableController::class, 'cancel']);
        Route::get('accounts-payable/summary', [AccountPayableController::class, 'summary']);
        
        // Contas a Receber
        Route::apiResource('accounts-receivable', AccountReceivableController::class);
        Route::post('accounts-receivable/{accountReceivable}/mark-as-received', [AccountReceivableController::class, 'markAsReceived']);
        Route::post('accounts-receivable/{accountReceivable}/cancel', [AccountReceivableController::class, 'cancel']);
        Route::get('accounts-receivable/summary', [AccountReceivableController::class, 'summary']);
        
        // Faturas
        Route::apiResource('invoices', InvoiceController::class);
        Route::post('invoices/{invoice}/issue', [InvoiceController::class, 'issue']);
        Route::post('invoices/{invoice}/mark-as-paid', [InvoiceController::class, 'markAsPaid']);
        Route::post('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel']);
        
        // Comissões
        Route::apiResource('commissions', CommissionController::class);
        Route::post('commissions/{commission}/mark-as-paid', [CommissionController::class, 'markAsPaid']);
        Route::get('commissions/summary', [CommissionController::class, 'summary']);
    });
    
    // Operações adicionais
    Route::post('consultations/{consultation}/medical-records', [ConsultationController::class, 'addMedicalRecord']);
});
