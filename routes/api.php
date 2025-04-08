<?php

declare(strict_types=1);

use App\Helpers\Routes\RouteHelper;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    RouteHelper::includeRouteFiles(
        directory: __DIR__ . '/api/v1',
    );
});
