<?php

namespace VyDev\Providers;

use Illuminate\Support\Composer;
use Illuminate\Filesystem\Filesystem;
use VyDev\Commands\Build\MakeCriteria;
use Illuminate\Support\ServiceProvider;
use VyDev\Commands\Build\MakeFormatter;
use VyDev\Commands\Build\MakeRepository;
use VyDev\Providers\EventServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{

    protected $defer = true;

    private $commands = [
        MakeRepository::class,
        MakeCriteria::class,
        MakeFormatter::class,
    ];

    public function register()
    {
        $this->registerInstallCommand();
        $this->app->register(EventServiceProvider::class);
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/repositories.php' => config_path('repositories.php'),
        ]);
        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }
    }

    public function registerInstallCommand()
    {
        $this->commands($this->commands);
    }
}
