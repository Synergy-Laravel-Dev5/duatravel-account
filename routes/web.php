<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseTransactionController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserActivityController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\FollowUpController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\TrainingSessionController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\AirlineController;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\TrainController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\TravelRouteController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\RoomInventoryController;




Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    Route::get('/', function () {
        return redirect()->route('login');
    });
});



Route::get('/booking/{booking}/hajj-agreement', [BookingController::class, 'agreement'])->middleware('signed')->name('booking.agreement');
Route::post('/save-agreement-signature', [BookingController::class, 'saveAgreementSignature'])->name('save.agreement.signature');
Route::middleware(['auth:web,company'])->group(function () {
        Route::get('/', [DashboardController::class, 'welcome'])->name('welcome');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/change-password', [AuthController::class, 'changePassword'])->name('change.password');
    Route::post('/change-password', [AuthController::class, 'changePasswordPost'])->name('change.password.post');


    Route::prefix('admin')->group(function () {
        Route::controller(RoleController::class)
            ->prefix('role')
            ->group(function () {
                Route::get('/', 'index')->name('role.index');
                Route::get('create', 'create')->name('role.create');
                Route::post('store', 'store')->name('role.store');
                Route::get('edit/{id}', 'edit')->name('role.edit');
                Route::put('update/{id}', 'update')->name('role.update');
                Route::delete('delete/{id}', 'destroy')->name('role.delete');
                Route::get('trash', 'trash')->name('role.trash');
                Route::put('restore/{id}', 'restore')->name('role.restore');
            });
        Route::controller(UserController::class)
            ->prefix('user')
            ->group(function () {
                Route::get('/', 'index')->name('user.index');
                Route::get('create', 'create')->name('user.create');
                Route::post('store', 'store')->name('user.store');
                Route::get('edit/{id}', 'edit')->name('user.edit');
                Route::put('update/{id}', 'update')->name('user.update');
                Route::delete('delete/{id}', 'destroy')->name('user.delete');
                Route::get('trash', 'trash')->name('user.trash');
                Route::get('restore/{id}', 'restore')->name('user.restore');
            });

        Route::controller(UserActivityController::class)
            ->prefix('user_activity')
            ->group(function () {
                Route::get('/', 'index')->name('user_activity.index');
            });

        Route::controller(ClientController::class)
            ->prefix('client')
            ->group(function () {
                Route::get('/',            'index')->name('client.index');
                Route::get('create',       'create')->name('client.create');
                Route::post('store',       'store')->name('client.store');
                Route::get('edit/{id}',    'edit')->name('client.edit');
                Route::put('update/{id}',  'update')->name('client.update');
                Route::delete('delete/{id}', 'destroy')->name('client.delete');
                Route::get('trash',        'trash')->name('client.trash');
                Route::get('restore/{id}', 'restore')->name('client.restore');
            });

        Route::controller(BookingController::class)
            ->prefix('booking')
            ->group(function () {
                Route::get('/',            'index')->name('booking.index');
                Route::get('create',       'create')->name('booking.create');
                Route::post('store',       'store')->name('booking.store');
                Route::get('show/{id}',    'show')->name('booking.show');
                Route::get('edit/{id}',    'edit')->name('booking.edit');
                Route::put('update/{id}',  'update')->name('booking.update');
                Route::delete('delete/{id}', 'destroy')->name('booking.delete');
                Route::get('trash',        'trash')->name('booking.trash');
                Route::get('restore/{id}', 'restore')->name('booking.restore');
                Route::get('voucher/{booking}', 'voucher')->name('booking.voucher');
            });

        Route::controller(ExpenseController::class)
            ->prefix('expense')
            ->group(function () {

                Route::get('/', 'index')->name('expense.index');
                Route::get('create', 'create')->name('expense.create');
                Route::post('store', 'store')->name('expense.store');

                Route::get('edit/{id}', 'edit')->name('expense.edit');
                Route::put('update/{id}', 'update')->name('expense.update');

                Route::delete('delete/{id}', 'destroy')->name('expense.delete');
                Route::get('trash', 'trash')->name('expense.trash');
                Route::get('restore/{id}', 'restore')->name('expense.restore');
            });

        Route::controller(ExpenseTransactionController::class)
            ->prefix('expense-transaction')
            ->group(function () {
                Route::get('/', 'index')->name('expense.transaction.index');
                Route::get('create', 'create')->name('expense.transaction.create');
                Route::post('store', 'store')->name('expense.transaction.store');
                Route::get('edit/{id}', 'edit')->name('expense.transaction.edit');
                Route::put('update/{id}', 'update')->name('expense.transaction.update');
                Route::delete('delete/{id}', 'destroy')->name('expense.transaction.delete');
                Route::get('report/filter', 'reportFilter')->name('expense.transaction.report.filter');
                Route::post('report', 'report')->name('expense.transaction.report');
            });

        Route::controller(TransactionController::class)
            ->prefix('transaction')
            ->group(function () {
                Route::get('/', 'index')->name('transaction.index');
                Route::get('create', 'create')->name('transaction.create');
                Route::post('store', 'store')->name('transaction.store');
                Route::get('trash', 'trash')->name('transaction.trash');
                Route::post('restore/{id}', 'restore')->name('transaction.restore');
                Route::get('ledger/filter', 'ledgerFilter')->name('transaction.ledger.filter');
                Route::post('ledger/view', 'ledgerView')->name('transaction.ledger.view');
                Route::get('company-ledger/filter', 'companyLedgerFilter')->name('transaction.company-ledger.filter');
                Route::get('company-ledger/view', 'companyLedgerView')->name('transaction.company-ledger.view');
                Route::get('show/{id}', 'show')->name('transaction.show');
                Route::get('edit/{id}', 'edit')->name('transaction.edit');
                Route::put('update/{id}', 'update')->name('transaction.update');
                Route::post('delete/{id}', 'destroy')->name('transaction.destroy');
                Route::get('invoice/filter', 'invoiceFilter')->name('transaction.invoice.filter');
                Route::get('invoice/{id}', 'invoice')->name('transaction.invoice');
            });

        Route::controller(CompanyController::class)->prefix('company')->group(function () {
            Route::get('/', 'index')->name('company.index');
            Route::get('create', 'create')->name('company.create');
            Route::post('store', 'store')->name('company.store');
            Route::get('show/{id}', 'show')->name('company.show');
            Route::get('edit/{id}', 'edit')->name('company.edit');
            Route::delete('delete/{id}', 'destroy')->name('company.delete');
            Route::put('update/{id}', 'update')->name('company.update');
            Route::get('trash', 'trash')->name('company.trash');
            Route::get('restore/{id}', 'restore')->name('company.restore');
        });

        Route::controller(PackageController::class)
            ->prefix('package')->group(function () {
                Route::get('/', 'index')->name('package.index');
                Route::get('create', 'create')->name('package.create');
                Route::post('store', 'store')->name('package.store');
                Route::get('edit/{id}', 'edit')->name('package.edit');
                Route::get('show/{id}', 'show')->name('package.show');
                Route::get('pdf/{id}', 'pdf')->name('package.pdf');
                Route::put('update/{id}', 'update')->name('package.update');
                Route::delete('delete/{id}', 'destroy')->name('package.delete');
                Route::get('trash', 'trash')->name('package.trash');
                Route::get('restore/{id}', 'restore')->name('package.restore');
            });

        Route::controller(TrainingSessionController::class)
            ->prefix('training-session')->group(function () {
                Route::get('/', 'index')->name('training-session.index');
                Route::get('create', 'create')->name('training-session.create');
                Route::post('store', 'store')->name('training-session.store');
                Route::get('edit/{id}', 'edit')->name('training-session.edit');
                Route::put('update/{id}', 'update')->name('training-session.update');
                Route::delete('delete/{id}', 'destroy')->name('training-session.delete');
            });

        Route::controller(LeadController::class)->prefix('lead')->group(function () {
            Route::get('/', 'index')->name('lead.index');
            Route::get('create', 'create')->name('lead.create');
            Route::post('store', 'store')->name('lead.store');
            Route::get('show/{lead}', 'show')->name('lead.show');
            Route::get('edit/{lead}', 'edit')->name('lead.edit');
            Route::put('update/{lead}', 'update')->name('lead.update');
            Route::delete('delete/{lead}', 'destroy')->name('lead.delete');
            Route::post('convert-to-client/{id}', 'convertToClient')->name('lead.convert-to-client');
            Route::get('trash', 'trash')->name('lead.trash');
            Route::get('restore/{id}', 'restore')->name('lead.restore');
        });

        Route::controller(QuotationController::class)->prefix('quotation')->group(function () {
            Route::get('/', 'index')->name('quotation.index');
            Route::get('create', 'create')->name('quotation.create');
            Route::post('store', 'store')->name('quotation.store');
            Route::get('show/{id}', 'show')->name('quotation.show');
            Route::get('edit/{id}', 'edit')->name('quotation.edit');
            Route::put('update/{id}', 'update')->name('quotation.update');
            Route::delete('delete/{id}', 'destroy')->name('quotation.delete');
            Route::get('pdf/{id}', 'pdf')->name('quotation.pdf');
            Route::get('trash', 'trash')->name('quotation.trash');
            Route::get('restore/{id}', 'restore')->name('quotation.restore');
        });

        Route::controller(RoomInventoryController::class)->prefix('room-inventory')->group(function () {
            Route::get('/', 'index')->name('room-inventory.index');
            Route::get('create', 'create')->name('room-inventory.create');
            Route::post('store', 'store')->name('room-inventory.store');
            Route::get('edit/{id}', 'edit')->name('room-inventory.edit');
            Route::put('update/{id}', 'update')->name('room-inventory.update');
            Route::delete('delete/{id}', 'destroy')->name('room-inventory.delete');
            Route::get('check-availability', 'checkAvailability')->name('room-inventory.check-availability');
            Route::get('hotel-summary/{hotel_id}', 'hotelSummary')->name('room-inventory.hotel-summary');
        });

        Route::controller(HotelController::class)->prefix('hotel')->group(function () {
            Route::get('/', 'index')->name('hotel.index');
            Route::get('create', 'create')->name('hotel.create');
            Route::post('store', 'store')->name('hotel.store');
            Route::get('show/{id}', 'show')->name('hotel.show');
            Route::get('edit/{id}', 'edit')->name('hotel.edit');
            Route::put('update/{id}', 'update')->name('hotel.update');
            Route::delete('delete/{id}', 'destroy')->name('hotel.delete');
            Route::get('trash', 'trash')->name('hotel.trash');
            Route::get('restore/{id}', 'restore')->name('hotel.restore');
        });

        Route::controller(AirlineController::class)->prefix('airline')->group(function () {
            Route::get('/', 'index')->name('airline.index');
            Route::get('create', 'create')->name('airline.create');
            Route::post('store', 'store')->name('airline.store');
            Route::get('show/{id}', 'show')->name('airline.show');
            Route::get('edit/{id}', 'edit')->name('airline.edit');
            Route::put('update/{id}', 'update')->name('airline.update');
            Route::delete('delete/{id}', 'destroy')->name('airline.delete');
            Route::get('trash', 'trash')->name('airline.trash');
            Route::get('restore/{id}', 'restore')->name('airline.restore');
        });

        Route::controller(FlightController::class)->prefix('flight')->group(function () {
            Route::get('/', 'index')->name('flight.index');
            Route::get('create', 'create')->name('flight.create');
            Route::post('store', 'store')->name('flight.store');
            Route::get('show/{id}', 'show')->name('flight.show');
            Route::get('edit/{id}', 'edit')->name('flight.edit');
            Route::put('update/{id}', 'update')->name('flight.update');
            Route::delete('delete/{id}', 'destroy')->name('flight.delete');
            Route::get('trash', 'trash')->name('flight.trash');
            Route::get('restore/{id}', 'restore')->name('flight.restore');
        });

        Route::controller(TrainController::class)->prefix('train')->group(function () {
            Route::get('/', 'index')->name('train.index');
            Route::post('store', 'store')->name('train.store');
            Route::put('update/{id}', 'update')->name('train.update');
            Route::delete('delete/{id}', 'destroy')->name('train.delete');
            Route::get('trash', 'trash')->name('train.trash');
            Route::get('restore/{id}', 'restore')->name('train.restore');
        });

        Route::controller(VehicleController::class)->prefix('vehicle')->group(function () {
            Route::get('/', 'index')->name('vehicle.index');
            Route::get('create', 'create')->name('vehicle.create');
            Route::post('store', 'store')->name('vehicle.store');
            Route::get('show/{id}', 'show')->name('vehicle.show');
            Route::get('edit/{id}', 'edit')->name('vehicle.edit');
            Route::put('update/{id}', 'update')->name('vehicle.update');
            Route::delete('delete/{id}', 'destroy')->name('vehicle.delete');
            Route::get('trash', 'trash')->name('vehicle.trash');
            Route::get('restore/{id}', 'restore')->name('vehicle.restore');
        });

        Route::controller(RouteController::class)->prefix('route')->group(function () {
            Route::get('/', 'index')->name('route.index');
            Route::get('create', 'create')->name('route.create');
            Route::post('store', 'store')->name('route.store');
            Route::get('edit/{id}', 'edit')->name('route.edit');
            Route::put('update/{id}', 'update')->name('route.update');
            Route::delete('delete/{id}', 'destroy')->name('route.delete');
            Route::get('trash', 'trash')->name('route.trash');
            Route::get('restore/{id}', 'restore')->name('route.restore');
        });

        Route::controller(TravelRouteController::class)->prefix('travel-route')->group(function () {
            Route::get('/', 'index')->name('travel-route.index');
            Route::get('create', 'create')->name('travel-route.create');
            Route::post('store', 'store')->name('travel-route.store');
            Route::get('show/{id}', 'show')->name('travel-route.show');
            Route::get('edit/{id}', 'edit')->name('travel-route.edit');
            Route::put('update/{id}', 'update')->name('travel-route.update');
            Route::delete('delete/{id}', 'destroy')->name('travel-route.delete');
            Route::get('trash', 'trash')->name('travel-route.trash');
            Route::get('restore/{id}', 'restore')->name('travel-route.restore');
        });





        Route::post('/leads/{lead}/follow-ups', [FollowUpController::class, 'store'])->name('followUp.store');
        Route::post('/follow-ups/{followUp}/take', [FollowUpController::class, 'take'])->name('followUp.take');
    });
});
