<?php

namespace App\Providers;

use App\Models\CustomizedItem;
use App\Models\StandardItem;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
          Relation::morphMap([
            'standard'   => StandardItem::class,
            'customized' => CustomizedItem::class,
        ]);

    }
}
