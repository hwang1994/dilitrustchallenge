<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DocumentController;

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

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [IndexController::class, 'index'])->name('home');
Route::get('/logout', [IndexController::class, 'logout'])->name('logout');

Route::post('/login', [LoginController::class, 'login']);
Route::post('/signup', [LoginController::class, 'signup']);

Route::post('/newdocument', [DocumentController::class, 'createDocument']);
Route::get('/download/{file}', [DocumentController::class, 'downloadDocument']);
Route::get('/downloadzip', [DocumentController::class, 'downloadZip']);
Route::delete('/delete/{file}', [DocumentController::class, 'deleteDocument']); // laravel supports method spoofing
Route::delete('/deleteall', [DocumentController::class, 'deleteAllDocuments'])->middleware('admin'); // laravel supports method spoofing


