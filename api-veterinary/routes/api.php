<?php

use App\Http\Controllers\Appointment\AppointmentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Cliente\ClienteController;
use App\Http\Controllers\MedicalRecord\MedicalRecordController;
use App\Http\Controllers\MedicalRecord\PaymentController;
use App\Http\Controllers\Pets\PetsController;
use App\Http\Controllers\Rol\RoleController;
use App\Http\Controllers\Staff\StaffController;
use App\Http\Controllers\Surgerie\SurgerieController;
use App\Http\Controllers\Vaccination\VaccinationController;
use App\Http\Controllers\Veterinarie\VeterinarieController;
use App\Models\MedicalRecord;

Route::group([
    // 'middleware' => 'api',
    'prefix' => 'auth',
    //  'middleware'=>['auth:api'] //,'role:writer'
], function ($router) {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout'); //middleware('auth:api')->
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh'); //middleware('auth:api')->
    Route::post('/me', [AuthController::class, 'me'])->name('me'); //middleware('auth:api')->
});
Route::group([
    'middleware' => ['auth:api']
], function ($router) {
    Route::resource('role', RoleController::class);
    Route::post("staffs/{id}", [StaffController::class, "update"]); //PASO 09 DE EDICION. por tener imagen se agregar otra ruta 
    Route::resource('staffs', StaffController::class);

    Route::post("veterinaries/{id}", [VeterinarieController::class, "update"]);
    Route::get("veterinaries/config", [VeterinarieController::class, "config"]);
    Route::resource("veterinaries", VeterinarieController::class);

    Route::post("pets/{id}", [PetsController::class, "update"]);
    Route::resource("pets", PetsController::class);

    Route::get("appointments/search-pets/{search}", [AppointmentController::class, "searchPets"]);
    Route::post("appointments/filter-availability", [AppointmentController::class, "filter"]);
    Route::post("appointments/index", [AppointmentController::class, "index"]);
    Route::resource("appointments", AppointmentController::class);
   
    //rutas para los registros medicos
    Route::get("medical-records/calendar", [MedicalRecordController::class, "calendar"]);// calendario de registros medicos
    Route::put("medical-records/update_aux/{id}", [MedicalRecordController::class, "update_aux"]);// actualizar estado de cita y notas del registro medico que esta en el calendario
    Route::put("medical-records/pet", [MedicalRecordController::class, "index"]);

    //rutas para las vacunaciones
    Route::post("vaccinations/index", [VaccinationController::class, "index"]); 
    Route::resource("vaccinations", VaccinationController::class);// rutas para las vacunaciones para el CRUD
    
    //rutas para las cirugias
    Route::post("surgeries/index", [SurgerieController::class, "index"]);
    Route::resource("surgeries", SurgerieController::class);

    //rutas para los pagos de los servicios medicos(citas, vacunaciones, cirugias)
    Route::post("payments/index", [PaymentController::class, "index"]);
    Route::resource("payments", PaymentController::class);

    Route::resource("clientes", ClienteController::class);
});

Route::get('appointment-excel', [AppointmentController::class, 'downloadExcel']); // esta ruta es para descargar el excel de las citas
Route::get('vaccination-excel', [VaccinationController::class, 'downloadExcel']); // esta ruta es para descargar el excel de las vacunaciones
Route::get('surgerie-excel', [SurgerieController::class, 'downloadExcel']); // esta ruta es para descargar el excel de las cirugias
Route::get('payments-excel',[PaymentController::class,'downloadExcel']); //