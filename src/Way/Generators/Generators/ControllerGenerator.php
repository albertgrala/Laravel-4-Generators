<?php

namespace Way\Generators\Generators;

use Illuminate\Filesystem\Filesystem as File;
use Illuminate\Support\Pluralizer;

class ControllerGenerator extends Generator {

    /**
     * Fetch the compiled template for a controller
     *
     * @param  string $template Path to template
     * @param  string $name
     * @return string Compiled template
     */
    protected function getTemplate($template, $nameController)
    {
        //$name contains the lower controller name without 's Controller'. Get booksController make book
        //$nameToUpper contains the upper controller name without 's Controller'. Get booksController make Book
        //$namePlural contains the lower plural controller name. Get booksController make books
        
        $this->template = $this->file->get($template);
        
        //get the ucfirst $name without 'sController'.  Get booksController make Book
        $nameFirst = strstr($nameController, 'sController', true);

        //get the controller name without 'sController'. Get booksController make book
        $name = strtolower($nameFirst);

        //get the plural $name. Get booksController make books
        $namePlural = Pluralizer::plural($name);

        //get the plural $name controller name with all Uper. Get booksController make Books        
        $nameUpperAll = ucfirst($namePlural);
        
        //get the singular $name
        //$nameSingular = Pluralizer::singular($nameController);

        if ($this->needsScaffolding($template))
        {
            $this->template = $this->getScaffoldedController($template, $nameController);
        }

        //dd($name);
        $this->template = str_replace('{{nameFirst}}', $nameFirst, $this->template);
        $this->template = str_replace('{{name}}', $name, $this->template);
        $this->template = str_replace('{{namePlural}}', $namePlural, $this->template);
        $this->template = str_replace('{{nameUpperAll}}', $nameUpperAll, $this->template);
        return str_replace('{{nameController}}', $nameController, $this->template);
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