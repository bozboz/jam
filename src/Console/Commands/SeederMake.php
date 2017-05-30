<?php

namespace Bozboz\Jam\Console\Commands;

use Bozboz\Jam\Templates\Template;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use Symfony\Component\Console\Input\InputArgument;

class SeederMake extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'jam:make-seeder {type} {template}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new seeder based on a JAM template';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Seeder';

    /**
     * The Composer instance.
     *
     * @var \Illuminate\Support\Composer
     */
    protected $composer;

    private $template;

    /**
     * Create a new command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  \Illuminate\Support\Composer  $composer
     * @return void
     */
    public function __construct(Filesystem $files, Composer $composer)
    {
        parent::__construct($files);

        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->template = Template::whereTypeAlias($this->argument('type'))
            ->whereAlias($this->argument('template'))
            ->with('fields.options')
            ->first();

        if ( ! $this->template) {
            throw new \Exception("Couldn't find template, type: " . $this->argument('type') . ", template: " . $this->argument('template'));
        }

        parent::fire();

        $this->composer->dumpAutoloads();
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        return str_replace('{seed}', view('jam::stubs.seeder')->withTemplate($this->template)->render(), $stub);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/../../Stubs/seeder.stub';
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        return $this->laravel->databasePath().'/seeds/'.$name.'.php';
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return studly_case(trim($this->argument('type')) . trim($this->argument('template'))) . 'JamTemplate';
    }

    /**
     * Parse the name and format according to the root namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function parseName($name)
    {
        return $name;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['type', InputArgument::REQUIRED, 'The type alias'],
            ['template', InputArgument::REQUIRED, 'The template alias'],
        ];
    }
}
