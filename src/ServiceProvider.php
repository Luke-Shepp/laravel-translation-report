<?php

namespace Shepp\LaravelTranslationReport;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Shepp\LaravelTranslationReport\Console\Commands\MissingTranslationReport;

class ServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MissingTranslationReport::class,
            ]);
        }
    }
}
