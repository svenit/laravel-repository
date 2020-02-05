<?php

namespace VyDev\Providers;

use Illuminate\Support\Composer;
use VyDev\Commands\MakeCriteria;
use VyDev\Commands\MakeRepository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{

    protected $defer = true;

    public function register()
    {
        $this->registerCommands();
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/repositories.php' => config_path('repositories.php'),
        ]);
        if($this->app->runningInConsole()) 
        {
            $this->commands([
                MakeRepository::class,
                MakeCriteria::class
            ]);
        }
    }

    public function registerCommands()
    {
        $this->registerInstallCommand();
    }

    public function registerInstallCommand()
    {
        $this->commands([
            MakeRepository::class,
            MakeCriteria::class
        ]);
    }
}
