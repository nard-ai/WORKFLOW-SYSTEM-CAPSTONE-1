<?php

use App\Models\Department;
use Illuminate\Support\Facades\Route;

Route::get('/debug/departments', function () {
    $departments = Department::orderBy('dept_name')->get();
    return response()->json($departments);
})->middleware(['auth', 'verified']);
