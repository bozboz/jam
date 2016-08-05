<?php

namespace Bozboz\Jam\Console\Commands;

use Bozboz\Jam\Entities\EntityPath;
use Illuminate\Console\Command;

class AddOldUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jam:add-redirects';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add an old URL to redirect to a new one';

    /**
     * The Paths repository.
     *
     * @var Bozboz\Jam\Entities\EntityPath
     */
    private $paths;

    /**
     * Create a new command instance.
     *
     * @param  Bozboz\Jam\Entities\EntityPath  $paths
     * @return void
     */
    public function __construct(EntityPath $paths)
    {
        $this->paths = $paths;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->addRedirectFromOldUrl(
            $this->ask('What is the old URL you want to redirect?')
        );
    }

    /**
     * Redirect from old URL to a new, requested URL, then request to repeat
     * operation.
     *
     * @param  string  $oldUrl
     * @return mixed
     */
    protected function addRedirectFromOldUrl($oldUrl)
    {
        $oldUrl = $this->parseUrl($oldUrl);

        $entityPath = $this->paths->onlyTrashed()->wherePath($oldUrl)->first();

        if ($entityPath) {
            $this->error('URL "' . $oldUrl . '" already exists');
            $this->comment('Redirects to: ' . $entityPath->entity->canonical_path);
        } else {
            $this->redirectToNewUrl(
                $oldUrl,
                $this->ask('What URL do you want to redirect to?')
            );
        }

        return $this->askToAddAnother();
    }

    /**
     * Create redirect from old URL to a new one. If new URL isn't a recognised
     * path, return early with error.
     *
     * @param  string  $oldUrl
     * @param  string  $newUrl
     * @return void
     */
    protected function redirectToNewUrl($oldUrl, $newUrl)
    {
        $newUrl = $this->parseUrl($newUrl);

        $entityPath = $this->paths->wherePath($newUrl)->first();

        if ( ! $entityPath) {
            return $this->error('No entity found with URL "' . $newUrl . '"');
        }

        $this->comment('Redirect from: ' . $oldUrl);
        $this->comment('To: ' . $newUrl);

        $entityPath->entity->paths()->create([
            'path' => $oldUrl,
        ])->delete();
    }

    /**
     * Prompt the user to add another URL. If there is a negative response,
     * finish the command, otherwise, begin adding the redirect.
     *
     * @return mixed
     */
    protected function askToAddAnother()
    {
        $answer = $this->ask('Add another URL? Type URL or answer "no" to exit');

        if ( ! $answer || in_array(strtolower($answer), ['no', 'n'])) {
            return;
        }

        return $this->addRedirectFromOldUrl($answer);
    }

    /**
     * Parse a full URL to get the path
     *
     * @param  string  $url
     * @return string
     */
    protected function parseUrl($url)
    {
        return trim(parse_url($url, PHP_URL_PATH), '/');
    }
}
