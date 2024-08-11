<?php

use App\Http\Controllers\AutoCompleteController;
use Illuminate\Support\Facades\Route;

Route::get('autocomplete/{model}', [AutoCompleteController::class, 'index'])->name('autocomplete.index');

