<?php

namespace Way\Generators\Generators;

use Way\Generators\Cache;
use Illuminate\Filesystem\Filesystem as File;

class RequestedCacheNotFound extends \Exception {}

abstract class Generator {

    /**
     * File path to generate
     *
     * @var string
     */
    public $path;

    /**
     * File system instance
     * @var File
     */
    protected $file;

    /**
     * Cache
     * @var Cache
     */
    protected $cache;

    /**
     * Constructor
     *
     * @param $file
     */
    public function __construct(File $file, Cache $cache)
    {
        $this->file = $file;
        $this->cache = $cache;
    }

    /**
     * Compile template and generate
     *
     * @param  string $path
     * @param  string $template Path to template
     * @return boolean
     */
    public function make($path, $template)
    {
        // $path = /var/www/bintra/app/controllers/BooksController.php
        // $template = /var/www/bintra/vendor/way/generators/src/Way/Generators/Commands/../Generators/templates/controller.txt
        // BooksController, le quita el .php
        $this->name = basename($path, '.php');
        //mete en el variable local path el path que viene
        $this->path = $this->getPath($path);

        // guarda la plantilla en $template
        $template = $this->getTemplate($template, $this->name);
        
        // si el fichero no exite, lo crea
        if (! $this->file->exists($this->path))
        {
            return $this->file->put($this->path, $template) !== false;
        }

        return false;
    }

    /**
     * Get the path to the file
     * that should be generated
     *
     * @param  string $path
     * @return string
     */
    protected function getPath($path)
    {
        // By default, we won't do anything, but
        // it can be overridden from a child class
        return $path;
    }

    /**
     * Determines whether the specified template
     * points to the scaffolds directory
     *
     * @param  string $template
     * @return boolean
     */
    protected function needsScaffolding($template)
    {
        return str_contains($template, 'scaffold');
    }

    /**
     * Get compiled template
     *
     * @param  string $template
     * @param  $name Name of file
     * @return string
     */
    abstract protected function getTemplate($template, $name);
}