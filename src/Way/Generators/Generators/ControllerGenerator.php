<?php

namespace Way\Generators\Generators;

use Illuminate\Filesystem\Filesystem as File;
use Illuminate\Support\Pluralizer;

class ControllerGenerator extends Generator {

    protected $directory;
    protected $container;
    protected $lib_path;
    protected $file;

    public function __construct(File $file)
    {
        $this->directory = app_path().'/lib';
        $this->container = $this->directory.'/aitiba';
        $this->file = $file;
    }
    protected function fillRepository($name)
    {
        $file = base_path()."/vendor/way/generators/src/Way/Generators/Commands/../Generators/templates/repository.txt";
        $template = $this->file->get($file);

        $template = str_replace('{{nameFirst}}', $name['first'], $template);
        //$template = str_replace('{{name}}', $name['lower'], $template);
        //$template = str_replace('{{namePlural}}', $name['plural'], $template);
        //$template = str_replace('{{nameUpperAll}}', $name['upperAll'], $template);
        //$template =  str_replace('{{nameController}}', $name['original'], $template);
        $this->file->put($this->lib_path.'/'.$name['first'].'Repository.php', $template);
       // return true;
    }


    protected function fillRepositoryImplements($name)
    {
        $file = base_path()."/vendor/way/generators/src/Way/Generators/Commands/../Generators/templates/repositoryImplements.txt";
        $template = $this->file->get($file);

        $template = str_replace('{{nameFirst}}', $name['first'], $template);
        $template = str_replace('{{name}}', $name['lower'], $template);
        $template = str_replace('{{namePlural}}', $name['plural'], $template);
        $template = str_replace('{{nameUpperAll}}', $name['upperAll'], $template);
        //$template =  str_replace('{{nameController}}', $name['original'], $template);
        $this->file->put($this->lib_path.'/'.$name['first'].'RepositoryImplements.php', $template);
    }

    protected function fillServiceProvider($name)
    {
        $file = base_path()."/vendor/way/generators/src/Way/Generators/Commands/../Generators/templates/serviceProvider.txt";
        $template = $this->file->get($file);

        $template = str_replace('{{nameFirst}}', $name['first'], $template);
        $this->file->put($this->lib_path.'/'.$name['first'].'ServiceProvider.php', $template);

        // add resource route to routes.php

       $search = "Illuminate\Workbench\WorkbenchServiceProvider',";
       $replace = $search."\n        'aitiba\\".$name['first']."\\".$name['first']."ServiceProvider',";
            $file = app_path() . '/config/app.php';
         $template = $this->file->get($file);
         $template =  str_replace($search, $replace, $template);
       //  dd($template);
          $this->file->put($file, $template);

        
        /*$this->file->append(
            app_path() . '/config/app.php',
            $user." PRUEBA"
        );*/
         $this->file->append(
            app_path() . '/routes.php',
            "\n\nRoute::resource('".$name['plural']."', '".$name['original']."');"
        );
         //regenerate composer auto-load file.
        exec("composer.phar dump-autoload");

    }

    /**
     * Fetch the compiled template for a controller
     *
     * @param  string $template Path to template
     * @param  string $name
     * @return string Compiled template
     */
    protected function getTemplate($template, $nameController)
    {
        $name = $this->getSynonymous($nameController);
       //dd($name);
        // set the lib controle route 
        $this->lib_path = $this->container.'/'.$name['first'];
        
        // create folders if necesary
        $this->folders($name['first']);

        // fill the controller
        $this->template = $this->file->get($template);
        
        // fill the repository
        $this->fillRepository($name);

        //fill Repository Implements
        $this->fillRepositoryImplements($name);
        
        // fill the Service Provider
        $this->fillServiceProvider($name);
        
       
        if ($this->needsScaffolding($template))
        {
            $this->template = $this->getScaffoldedController($template, $nameController);
        }

        $this->template = str_replace('{{nameFirst}}', $name['first'], $this->template);
        $this->template = str_replace('{{name}}', $name['lower'], $this->template);
        $this->template = str_replace('{{namePlural}}', $name['plural'], $this->template);
        $this->template = str_replace('{{nameUpperAll}}', $name['upperAll'], $this->template);
        return str_replace('{{nameController}}', $name['original'], $this->template);
    }

     /**
     * Get synonymous of the name controller
     *
     * @param  string $nameController Controller name
     * @return array
     */
    protected function getSynonymous($nameController)
    {
        //dd($nameController);
        $name = array();
        //get original value
        $name['original'] = $nameController;

         //get the ucfirst $name without 'sController'.  Get booksController make Book
        $name['first'] = strstr($nameController, 'sController', true);

        //get the controller name without 'sController'. Get booksController make book
        $name['lower'] = strtolower($name['first']);

        //get the plural $name. Get booksController make books
        $name['plural'] = Pluralizer::plural($name['lower']);

        //get the plural $name controller name with all Uper. Get booksController make Books        
        $name['upperAll'] = ucfirst($name['plural']);

        //get the singular $name['original']
        //$name['singular'] = Pluralizer::singular($name['original']);
        return $name;
    }
    /**
     * Create the neccesary folders for library
     */
    protected function folders($package)
    {
        // create app/lib if not exist
        if(!$this->file->isDirectory($this->directory))
        {
            $this->file->makeDirectory($this->directory);
        }

        // create app/lib/aitiba if not exist
        if(!$this->file->isDirectory($this->container))
        {
            $this->file->makeDirectory($this->container);
        }

        // create app/lib/aitiba/book if not exist
        if(!$this->file->isDirectory($this->lib_path))
        {
            $this->file->makeDirectory($this->lib_path);
        }
        //return "folders";
    }

    /**
     * Get template for a scaffold
     *
     * @param  string $template Path to template
     * @param  string $name
     * @return string
     */
    protected function getScaffoldedController($template, $name)
    {
        $collection = strtolower(str_replace('Controller', '', $name)); // dogs
        $modelInstance = Pluralizer::singular($collection); // dog
        $modelClass = ucwords($modelInstance); // Dog

        foreach(array('modelInstance', 'modelClass', 'collection') as $var)
        {
            $this->template = str_replace('{{'.$var.'}}', $$var, $this->template);
        }

        return $this->template;
    }
}