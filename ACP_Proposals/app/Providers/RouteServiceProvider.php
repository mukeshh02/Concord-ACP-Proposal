<?php

namespace Modules\ACP_Proposals\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
    }

    protected function mapWebRoutes(): void
    {
        Route::moduleWeb('ACP_Proposals', 'routes/web.php');
    }

    protected function mapApiRoutes(): void
    {
        Route::moduleApi('ACP_Proposals', 'routes/api.php');
    }
}
