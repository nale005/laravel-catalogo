<?php

use App\Http\Controllers\ProductoController;

Route::get('productos/list', [ProductoController::class, 'list'])->name('productos.list');
Route::resource('productos', ProductoController::class);