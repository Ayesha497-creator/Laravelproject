<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// Ye line top par add karein
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (app()->isLocal()) {
            $this->app->register(\VIACreative\SudoSu\ServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // --- YE LINE ADD KAREIN ---
        // Ye aapke .env file ke APP_URL ko base banayega
        URL::forceRootUrl(config('app.url'));
        // --------------------------

        \App\Models\User::observe(\App\Observers\UserObserver::class);
        \App\Models\Reply::observe(\App\Observers\ReplyObserver::class);
        \App\Models\Topic::observe(\App\Observers\TopicObserver::class);
        \App\Models\Link::observe(\App\Observers\LinkObserver::class);

        \Illuminate\Pagination\Paginator::useBootstrap();
    }
}
