<?php

namespace VyDev\Commands\Build;

use VyDev\Commands\BaseCommand;
use Illuminate\Support\Facades\Artisan;

class MakeRepository extends BaseCommand
{
    protected $name = 'make:repository';
    protected $description = 'Create a new repository.';
    protected $type = 'Repository';
    protected $tag = 'repository';

    public function buildCommand()
    {
        if($this->getConfig()) {
            $this->setRepositoryClass();
            $path = $this->getPath($this->config[$this->tag]);
            if (file_exists($path) || $this->alreadyExists($this->getNameInput())) {
                $this->error($this->getNameInput().' already exists!');
                return false;
            }
            $this->makeDirectory($path);
            $this->files->put($path, $this->buildClass($this->commandClass));
            $this->info($this->type.' created successfully.');
            if ($this->confirm('Would you like to create a Criteria?')) {
                $criteriaName = $this->ask('Enter name of Criteria !');
                if ($criteriaName) {
                    Artisan::call("make:criteria {$criteriaName}");
                    $this->info("Create criteria successfully !");
                }
            }
            if ($this->confirm('Would you like to create a Formatter?')) {
                $formatterName = $this->ask('Enter name of Formatter !');
                if ($formatterName) {
                    Artisan::call("make:formatter {$formatterName}");
                    $this->info("Create formatter successfully !");
                }
            }
        }
    }
}