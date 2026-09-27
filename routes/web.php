<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CronController;
use App\Http\Controllers\front\FrontController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\StatisicsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VisitsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/admins', function () {
    return redirect()->route('login');
});

Route::get('info', [FrontController::class, 'info']);
Route::get('profile', [FrontController::class, 'info']);
Route::get('sendprofilesms', [FrontController::class, 'sendprofilesms']);

Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'login')->name('login');
    Route::post('/Auth', 'makeAuth')->name('auth')->middleware('throttle:5,1');
    Route::get('/logout', 'logout')->name('logout');
});

Route::middleware(['auth:admin'])->prefix('dashbord')->group(function () {
    Route::get('/', [IndexController::class, 'dashbord'])->name('DashBord');
    Route::get('user/search-suggestions', [UserController::class, 'searchSuggestions'])->name('user.suggestions')->middleware('throttle:120,1');
    Route::match(['get', 'post'], 'user/searchresult', [UserController::class, 'searchUsers'])->name('user.search');
    Route::resource('user', UserController::class);
    Route::resource('visits', VisitsController::class)->except(['create', 'store']);
    Route::patch('visits/reminders/{reminder}/complete', [VisitsController::class, 'completeReminder'])->name('visits.reminders.complete');
    Route::delete('visits/reminders/{reminder}', [VisitsController::class, 'destroyReminder'])->name('visits.reminders.destroy');
    Route::get('visits/create/{id}', [VisitsController::class, 'create'])->name('visits.create');
    Route::post('visits/sore/{id}', [VisitsController::class, 'store'])->name('visits.store');
    Route::get('/convert', [IndexController::class, 'convertdata'])->name('convertdata');
    Route::get('info/visits/todaysvisits/', [VisitsController::class, 'todaysVisit'])->name('visits.todaysvisit');
    Route::get('visit/add/{$user_id}', [VisitsController::class, 'create'])->name('add.visit');

    Route::prefix('statisics')->controller(StatisicsController::class)->group(function () {
        Route::get('/daily', 'daysStatisics')->name('daysStatisics');
    });

    Route::prefix('managers')->controller(AuthController::class)->group(function () {
        Route::get('list', 'admins')->name('admins.list');
        Route::get('/admin/{id}', 'adminEdit')->name('admin.edit');
        Route::get('update/{id}', 'updateAdmin')->name('admins.update');
        Route::get('store', 'storeAdmin')->name('admins.store');
        Route::get('delete/admin/{id}', 'deleteAdmin')->name('admins.delete');
    });

    Route::resource('messages', SmsController::class);

    Route::get('appointments/patient', [AppointmentController::class, 'findPatient'])->name('appointments.patient')->middleware('throttle:120,1');
    Route::get('appointments-all', [AppointmentController::class, 'all'])->name('appointments.all');
    Route::resource('appointments', AppointmentController::class)->only(['index', 'store', 'edit', 'update', 'destroy']);
    Route::get('reminders', [VisitsController::class, 'reminders'])->name('reminders.index');

    Route::get('add/admin', [AuthController::class, 'addAdmin'])->name('admins.add');
});

Route::get('/cronsmatab/cron1', [CronController::class, 'cron1']);
Route::get('/cronsmatab/cron2', [CronController::class, 'sendStatisicData']);
Route::get('/cronsmatab/appointment-reminders', [CronController::class, 'appointmentReminders'])
    ->name('cron.appointment-reminders')->middleware('throttle:10,1');
Route::get('/cronsmatab/follow-up-reminders', [CronController::class, 'followUpReminders'])
    ->name('cron.follow-up-reminders')->middleware('throttle:10,1');
