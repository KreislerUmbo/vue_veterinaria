<?php

use App\Http\Controllers\Appointment\AppointmentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Cliente\ClienteController;
use App\Http\Controllers\Pets\PetsController;
use App\Http\Controllers\Rol\RoleController;
use App\Http\Controllers\Staff\StaffController;
use App\Http\Controllers\Veterinarie\VeterinarieController;

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
    Route::resource("appointments", AppointmentController::class);
   
    
    Route::resource("clientes", ClienteController::class);
});
