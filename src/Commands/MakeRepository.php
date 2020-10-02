<?php

namespace VyDev\Commands;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Exception\InvalidArgumentException;

class MakeRepository extends GeneratorCommand
{
    protected $name = 'make:repository';

    protected $description = 'Create a new repository.';

    protected $type = 'Repository';

    private $repositoryClass;
    private $repositoryPath;

    protected $config;

    protected $modelPath;

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
            $path = $this->getPath($this->repositoryPath);
            if($this->alreadyExists($this->getNameInput())) 
            {
                $this->error($this->type.' already exists!');
                return false;
            }
            $this->modelPath = $this->ask('Enter your model name ! [ @ to skip ]');
            $this->makeDirectory($path);
            $this->files->put($path, $this->buildClass($this->repositoryClass));
            $this->info($this->type.' created successfully.');
            if($this->confirm('Would you like to create criteria?'))
            {
                $criteriaName = $this->ask('Enter criteria !');
                if($criteriaName)
                {
                    Artisan::call("make:criteria $criteriaName");
                    $this->info("Create criteria successfully !");
                }
            }
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
            $this->config['repository_path'] .= "\\".$namespace;
        }
        $this->repositoryPath = $this->config['repository_path'];
        $explode = explode("\\",$this->repositoryPath);
        array_pop($explode);
        $this->repositoryClass = implode("\\",$explode);
        return $this;
    }
    protected function replaceClass($stub, $name)
    {
        if(!$this->argument('name'))
        {
            throw new InvalidArgumentException("Missing required argument model name");
        }
        $stub = parent::replaceClass($stub, $name);
        $replace = str_replace('repository_namespace',$this->repositoryClass, $stub);
        $replace = str_replace('repository_class', $this->model, $replace);
        if(isset($this->modelPath) && $this->modelPath != '@')
        {
            $replace = str_replace('your_model_path', $this->config['model']."\\".$this->modelPath, $replace);
        }
        return $replace;
    }
    protected function getStub()
    {
        return __DIR__."/../../resources/stubs/repository.stub";
    }
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . "\\" . $this->config['repository_path'];
    }
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the model class.'],
        ];
    }
}