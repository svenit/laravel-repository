<?php

namespace VyDev\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Exception\InvalidArgumentException;

abstract class BaseCommand extends GeneratorCommand
{
    protected $config;
    protected $commandClass;
    protected $commandPath;
    protected $model;

    abstract public function buildCommand();

    public function handle()
    {
        $this->buildCommand();
    }

    public function getConfig()
    {
        if (!file_exists(base_path()."/config/repositories.php")) {
            $this->error("Can not found config/repositories. Have you published provider yet ?");
            return false;
        }
        $config = config('repositories');
        $this->config = [
            'repository' => $config['repository_namespace'],
            'criteria' => $config['criteria_namespace'],
            'formatter' => $config['formatter_namespace'],
            'model' => $config['default_model'],
        ];
        return true;
    }

    public function setRepositoryClass()
    {
        $name = ucwords($this->argument('name'));
        $regex = explode('/',$name);
        $modelName = $regex[count($regex) - 1];
        $this->model = $modelName;
        foreach ($regex as $namespace) {
            $this->config[$this->tag] .= "\\".$namespace;
        }
        $this->commandPath = $this->config[$this->tag];
        $explode = explode("\\",$this->commandPath);
        array_pop($explode);
        $this->commandClass = implode("\\",$explode);
        return $this;
    }

    protected function replaceClass($stub, $name)
    {
        if (!$this->argument('name')) {
            throw new InvalidArgumentException("Missing required argument model name");
        }
        $stub = parent::replaceClass($stub, $name);
        $replace = str_replace("{$this->tag}_namespace", $this->commandClass, $stub);
        $replace = str_replace("{$this->tag}_class", $this->model, $replace);
        return $replace;
    }

    protected function getStub()
    {
        return __DIR__."/../../resources/stubs/{$this->tag}.stub";
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . "\\" . $this->config[$this->tag];
    }

    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the model class.'],
        ];
    }

    public function __set($property, $value)
    {
        $this->{$property} = $value;
    }
}