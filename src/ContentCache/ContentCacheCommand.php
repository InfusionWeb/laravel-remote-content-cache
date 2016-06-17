<?php

namespace InfusionWeb\Laravel\ContentCache;

use Illuminate\Console\Command;

class ContentCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'content:cache
                            {profile=all : Profile to use for caching}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache content retrieved from external server(s)';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(ContentCache $contentcache)
    {
        $profile = $this->argument('profile');

        if ($profile == 'all') {
            // Get the names of all profiles.
            $profile = array_keys(config('contentcache'));

            // Remove the "default" profile.
            if (($key = array_search('default', $profile)) !== false) {
                unset($profile[$key]);
            }
        }

        foreach ((array) $profile as $name) {
            $contentcache->profile($name)->cache();

            $this->info("Remote content items cached from {$name} profile!");
        }
    }
}
