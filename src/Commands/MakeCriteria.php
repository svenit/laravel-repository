<?php

namespace VyDev\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Exception\InvalidArgumentException;

class MakeCriteria extends GeneratorCommand
{
    protected $name = 'make:criteria';

    protected $description = 'Create a new criteria.';

    protected $type = 'Criteria';

    private $criteriaClass;
    private $criteriaPath;

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
            $path = $this->getPath($this->criteriaPath);
            if($this->alreadyExists($this->getNameInput())) 
            {
                $this->error($this->type.' already exists!');
                return false;
            }
            $this->makeDirectory($path);
            $this->files->put($path, $this->buildClass($this->criteriaClass));
            $this->info($this->type.' created successfully.');
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
        $regex = explode('/',$name);
        $modelName = $regex[count($regex) - 1];
        $this->model = $modelName;
        foreach($regex as $namespace)
        {
            $this->config['criteria_path'] .= "\\".$namespace;
        }
        $this->criteriaPath = $this->config['criteria_path'];
        $explode = explode("\\",$this->criteriaPath);
        array_pop($explode);
        $this->criteriaClass = implode("\\",$explode);
        return $this;
    }
    protected function replaceClass($stub, $name)
    {
        if(!$this->argument('name'))
        {
            throw new InvalidArgumentException("Missing required argument model name");
        }
        $stub = parent::replaceClass($stub, $name);
        $replace = str_replace('criteria_namespace',$this->criteriaClass, $stub);
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