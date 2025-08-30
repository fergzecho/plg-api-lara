<?php

use App\Http\Controllers\CustomerIOController;

Route::middleware('api.key')->group(function () {
    Route::get('/segments/{id}/members', [CustomerIOController::class, 'getSegmentMembers']);
});
