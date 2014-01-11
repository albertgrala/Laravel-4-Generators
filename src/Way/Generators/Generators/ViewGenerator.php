<?php

namespace Way\Generators\Generators;

use Illuminate\Support\Pluralizer;

class ViewGenerator extends Generator {

    /**
     * Fetch the compiled template for a view
     *
     * @param  string $template Path to template
     * @param  string $name
     * @return string Compiled template
     */
    protected function getTemplate($template, $name)
    {
        //Schema::hasTable('users')
        //TODO que mire si existe la tabla, si exsite no ejecutar
        //$users = DB::table('users')->count();

       // echo ('cd '.app_path());
        //exec('cd '.app_path());
        //     echo('php artisan migrate');
        //exec('php artisan migrate');
        $this->template = $this->file->get($template);

        if ($this->needsScaffolding($template))
        {
            return $this->getScaffoldedTemplate($name);
        }
//var_dump($template);
        // Otherwise, just set the file
        // contents to the file name
        return $name;
    }

    /**
     * Get the scaffolded template for a view
     *
     * @param  string $name
     * @return string Compiled template
     */
    protected function getScaffoldedTemplate($name)
    {
        //dd($name);
        $model = $this->cache->getModelName();

        /*$pluralModel = Pluralizer::plural($model); // posts
        $formalModel = ucwords($pluralModel); // Posts
        $className = Pluralizer::singular($formalModel);*/

        $synonymous = $this->getSynonymous($model);
        // Create and Edit views require form elements
        if ($name === 'create.blade') {
          $formElements = $this->makeFormElements();
          $this->template = str_replace('{{formElements}}', $formElements, $this->template);
        } elseif ($name === 'edit.blade') {
          $formElements = $this->makeFormElements($model);
          $this->template = str_replace('{{formElements}}', $formElements, $this->template);
        }
    //}

        // Replace template vars in view
       /* foreach(array('model', 'pluralModel', 'formalModel', 'className') as $var)
        {
            $this->template = str_replace('{{'.$var.'}}', $$var, $this->template);
        }*/

        // And finally create the table rows
        list($headings, $fields, $editAndDeleteLinks) = $this->makeTableRows($model);
        $this->template = str_replace('{{headings}}', implode(PHP_EOL."\t\t\t\t", $headings), $this->template);
        $this->template = str_replace('{{fields}}', implode(PHP_EOL."\t\t\t\t\t", $fields) . PHP_EOL . $editAndDeleteLinks, $this->template);

        $this->template = str_replace('{{nameFirst}}', $synonymous['first'], $this->template);
        $this->template = str_replace('{{name}}', $synonymous['lower'], $this->template);
        $this->template = str_replace('{{namePlural}}', $synonymous['plural'], $this->template);
        $this->template = str_replace('{{nameUpperAll}}', $synonymous['upperAll'], $this->template);
      //  $this->template = str_replace('{{nameController}}', $synonymous['original'], $this->template);

        return $this->template;
    }

    /**
     * Get synonymous of the name controller
     *
     * @param  string $model Model name
     * @return array
     */
    protected function getSynonymous($model)
    {
        //dd($model);
        $name = array();
        //get original value User
         //get the ucfirst $name without 'sController'.  Get booksController make Book
        $name['first'] = $model;

        //$name['first'] = strstr($model, 'sController', true);

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
     * Create the table rows
     *
     * @param  string $model
     * @return Array
     */
    protected function makeTableRows($model)
    {
        $pluralModel = Pluralizer::plural($model); // posts

        $fields = $this->cache->getFields();

        // First, we build the table headings
        $headings = array_map(function($field) {
            return '<th>' . ucwords($field) . '</th>';
        }, array_keys($fields));

        // And then the rows, themselves
        $fields = array_map(function($field) use ($model) {
            return "<td>{{{ \$$model->$field }}}</td>";
        }, array_keys($fields));

        // Now, we'll add the edit and delete buttons.
        /*$editAndDelete = <<<EOT
                    <td>{{ link_to_route('{$pluralModel}.edit', 'Edit', array(\${$model}->id), array('class' => 'btn btn-info')) }}</td>
                    <td>
                        {{ Form::open(array('method' => 'DELETE', 'route' => array('{$pluralModel}.destroy', \${$model}->id))) }}
                            {{ Form::submit('Delete', array('class' => 'btn btn-danger')) }}
                        {{ Form::close() }}
                    </td>
EOT;*/

        return array($headings, $fields, null);
    }

    /**
     * Add Laravel methods, as string,
     * for the fields
     *
     * @return string
     */
    public function makeFormElements($model = null)
    {
        $formMethods = array();
//dd($this->cache->getFields());
        foreach($this->cache->getFields() as $name => $type)
        {
            $formalName = ucwords($name);

            // TODO: add remaining types
            switch($type)
            {
                case 'integer':
                   $element = "{{ Form::input('number', '$name') }}";
                    break;

                case 'text':
                    $element = "{{ Form::textarea('$name') }}";
                    break;

                case 'boolean':
                    $element = "{{ Form::checkbox('$name') }}";
                    break;

                default:
                    if($model != null)
                    {    
                      $value = '$'.$model."->".$name;
                      $element = "{{ Form::text('$name', $value, array('id' => '$name')) }}";
                    } else {
                      $element = "{{ Form::text('$name', null, array('id' => '$name')) }}";  
                    }
                    break;
            }

            // Now that we have the correct $element,
            // We can build up the HTML fragment
            $frag = <<<EOT
        <p class="inline-large-label button-height">
            {{ Form::label('$name', '$formalName:') }}
            $element
        </p>

EOT;

            $formMethods[] = $frag;
        }

        return implode(PHP_EOL, $formMethods);
    }

}
