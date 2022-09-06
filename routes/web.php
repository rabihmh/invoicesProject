<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoicesDetailsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes(['register' => false]);
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::resource('invoices', 'App\Http\Controllers\InvoicesController');
Route::resource('sections', 'App\Http\Controllers\SectionsController');
Route::resource('products', 'App\Http\Controllers\ProductsController');
Route::resource('InvoiceAttachments', 'App\Http\Controllers\InvoicesAttachmentsTableController');
Route::get('/section/{id}', 'App\Http\Controllers\InvoicesController@getproducts');
Route::get('/InvoicesDetails/{id}', 'App\Http\Controllers\InvoicesDetailsController@index');
Route::get('download/{invoice_number}/{file_name}', 'App\Http\Controllers\InvoicesDetailsController@get_file');
Route::get('View_file/{invoice_number}/{file_name}', 'App\Http\Controllers\InvoicesDetailsController@open_file');
Route::post('delete_file', 'App\Http\Controllers\InvoicesDetailsController@destroy')->name('delete_file');
Route::get('edit_invoice/{id}', 'App\Http\Controllers\InvoicesController@edit_invoice');
Route::get('/Status_show/{id}', 'App\Http\Controllers\InvoicesController@show')->name('Status_show');
Route::post('/Status_Update/{id}', 'App\Http\Controllers\InvoicesController@Status_Update')->name('Status_Update');
Route::get('Archive', 'App\Http\Controllers\InvoicesArchiveController@index')->name('invoices.archive');
Route::post('Archive/destroy', 'App\Http\Controllers\InvoicesArchiveController@destroy')->name('invoices.archive.destroy');
Route::post('Archive/update', 'App\Http\Controllers\InvoicesArchiveController@update')->name('invoices.archive.update');
Route::get('Print_invoice/{id}', 'App\Http\Controllers\InvoicesController@Print_invoice');
Route::get('Invoice_Paid', 'App\Http\Controllers\InvoicesController@Invoice_Paid');

Route::get('Invoice_UnPaid', 'App\Http\Controllers\InvoicesController@Invoice_UnPaid');

Route::get('Invoice_Partial', 'App\Http\Controllers\InvoicesController@Invoice_Partial');
Route::get('export', 'App\Http\Controllers\InvoicesController@export')->name('export.invoices');
Route::group(['middleware' => ['auth']], function () {
    Route::resource('roles', 'App\Http\Controllers\RoleController');
    Route::resource('users', 'App\Http\Controllers\UserController');
});
Route::get('invoices_report', 'App\Http\Controllers\Invoices_Report@index');

Route::post('Search_invoices', 'App\Http\Controllers\Invoices_Report@Search_invoices');

Route::get('customers_report', 'App\Http\Controllers\Customers_Report@index')->name("customers_report");

Route::post('Search_customers', 'App\Http\Controllers\Customers_Report@Search_customers');

Route::get('MarkAsRead_all', 'App\Http\Controllers\InvoicesController@MarkAsRead_all')->name('MarkAsRead_all');

Route::get('unreadNotifications_count', 'App\Http\Controllers\InvoicesController@unreadNotifications_count')->name('unreadNotifications_count');

Route::get('unreadNotifications', 'App\Http\Controllers\InvoicesController@unreadNotifications')->name('unreadNotifications');

Route::get('/{page}', 'App\Http\Controllers\AdminController@index');
