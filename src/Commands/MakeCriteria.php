<?php

namespace VyDev\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Exception\InvalidArgumentException;

class MakeCriteria extends GeneratorCommand
{
    protected $name = 'vy:criteria';

    protected $description = 'Create a new criteria.';

    protected $type = 'Criteria';

    private $criteriaClass;

    protected $config;

    /**
     * The name of class being generated.
     *
     * @var string
     */
    private $model;

    public function handle()
    {
        if($this->getConfig())
        {
            $this->setRepositoryClass();
            $path = $this->getPath($this->criteriaClass);
            if($this->alreadyExists($this->getNameInput())) 
            {
                $this->error($this->type.' already exists!');
                return false;
            }
            $this->makeDirectory($path);
            $this->files->put($path, $this->buildClass($this->criteriaClass));
            $this->info($this->type.' created successfully.');
            $this->info("Created criteria : $this->criteriaClass");
        }
    }

    public function getConfig()
    {
        if(!file_exists(base_path()."/config/repositories.php"))
        {
            $this->error("Can not found config/repositories. Have you published provider yet ?");
            return false;
        }
        $this->config = [
            'repository_path' => config('repositories.repository_namespace'),
            'criteria_path' => config('repositories.criteria_namespace'),
            'model' => config('repositories.default_model'),
        ];
        return true;
    }
    public function setRepositoryClass()
    {
        $name = ucwords($this->argument('name'));
        $this->model = $name;
        $this->criteriaClass = $this->config['criteria_path']."\\".$name;
        return $this;
    }
    protected function replaceClass($stub, $name)
    {
        if(!$this->argument('name'))
        {
            throw new InvalidArgumentException("Missing required argument model name");
        }
        $stub = parent::replaceClass($stub, $name);
        $replace = str_replace('criteria_namespace',$this->config['criteria_path'], $stub);
        $replace = str_replace('criteria_class', $this->model, $replace);
        return $replace;
    }
    protected function getStub()
    {
        return __DIR__."/../../resources/stubs/criteria.stub";
    }
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . "\\" . $this->config['criteria_path'];
    }
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the model class.'],
        ];
    }
}