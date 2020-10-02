<?php

namespace VyDev\Commands\Build;

use VyDev\Commands\BaseCommand;

class MakeFormatter extends BaseCommand
{
    protected $name = 'make:formatter';
    protected $description = 'Create a new formatter.';
    protected $type = 'Formatter';
    protected $tag = 'formatter';

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
        }
    }
}