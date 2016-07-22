<?php

namespace InfusionWeb\Laravel\ContentCache;

use Carbon\Carbon;
use Guzzle;
use Storage;

class Profile
{

    protected $endpoint = '';

    protected $query = [];

    protected $filters = [];

    protected $fields = [];

    protected $keys = [];

    public function __construct($profile = '')
    {
        $this->setEndpoint(config("contentcache.{$profile}.endpoint", config("contentcache.default.endpoint")));

        $this->setQuery(config("contentcache.{$profile}.query", config("contentcache.default.query")));

        $this->setFilters(config("contentcache.{$profile}.filters", config("contentcache.default.filters")));

        $this->setFields(config("contentcache.{$profile}.fields", config("contentcache.default.fields")));

        $this->setKeys(config("contentcache.{$profile}.keys", config("contentcache.default.keys")));
    }

    public function setFilters($filters)
    {
        return $this->filters = (array) $filters;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function filter($results)
    {
        $filters = $this->getFilters();

        foreach ($results as $item) {
            // Iterate through each attribute of the result item object.
            foreach (get_object_vars($item) as $name => $value) {
                // If we have a filter for this attribute...
                $args = explode(':', $name);
                $name = array_shift($args);

                if (array_key_exists($name, $filters)) {
                    // Run the filter
                    $method = 'filter'.ucfirst($filters[$name]);

                    if (method_exists($this, $method)) {
                        // Deal with arrays correctly.
                        if (is_array($item->$name)) {
                            foreach ($item->$name as &$val) {
                                $val = $this->$method($val, $args);
                            }
                        } else {
                            $item->$name = $this->$method($item->$name, $args);
                        }
                    }
                }
            }
        }
    }

    protected function filterString($value, $args = [])
    {
        return (string) $value;
    }

    protected function filterInt($value, $args = [])
    {
        return (int) $value;
    }

    protected function filterDate($value, $args = [])
    {
        return Carbon::parse($value);
    }

    protected function filterFile($value, $args = [])
    {
        try {
            $file_contents = Guzzle::get($value)->getBody()->__toString();
        }
        catch (\GuzzleHttp\Exception\ClientException $e) {
            // Guzzle couldn't get the original file.
            return false;
        }

        $disk = Storage::disk(config('contentcache.default.filesystem'));

        $path = config('contentcache.default.path');

        $filename = basename(parse_url($value, PHP_URL_PATH));

        $location = $path ? $path.'/'.$filename : $filename;

        // Figure out if the file has changed before writing it.
        $need_to_save = (strlen($file_contents) != $disk->size($location));

        if ($need_to_save) {
            $was_saved = $disk->put($location, $file_contents);
        }

        // Return the correct image URL.
        if (!$need_to_save || $was_saved) {
            $domain = config('contentcache.default.domain');

            if ($domain) {
                return config('contentcache.default.schema', 'http').'://'.$domain.'/'.$location;
            }
            else {
                return $disk->url($location);
            }
        }

        return false;
    }

    filterImage($value, $args = [])
    {
        $style = array_shift($args);

        $image_url = $this->filterFile($value);

        if (!$style) {
            // Didn't request modification.
            return $image_url;
        }

        $valid_types = [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG];

        $image_type = @exif_imagetype($image_url);

        if (!in_array($image_type, $valid_types)) {
            // Not a valid image. Don't modify.
            return $image_url;
        }

        // Modify the image, based on style definition in config.
        #TODO

        return $image_url;
    }

    public function setEndpoint($endpoint)
    {
        return $this->endpoint = $endpoint;
    }

    public function getEndpoint()
    {
        return $this->endpoint;
    }

    public function setQuery($query)
    {
        return $this->query = (array) $query;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function setKeys($keys)
    {
        return $this->keys = (array) $keys;
    }

    public function getKeys()
    {
        return $this->keys;
    }

    public function setFields($fields)
    {
        return $this->fields = (array) $fields;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function field($results)
    {
        $fields = $this->getFields();

        foreach ($results as $item) {
            // Iterate through each attribute of the result item object.
            foreach (get_object_vars($item) as $name => $value) {
                // And each field we want to create...
                foreach ($fields as $add => $existing) {
                    $method = 'field'.ucfirst($add);

                    // Do we have a method to create the requested field?
                    if (method_exists($this, $method)) {
                        // Handle use of multiple property names.
                        $parts = [];

                        foreach ((array) $existing as $field) {
                            $parts[] = $item->$field;
                        }

                        $item->$add = $this->$method( implode(' ', $parts) );
                    }
                }
            }
        }
    }

    protected function fieldSlug($value)
    {
        $value = implode(' ', (array) $value);

        return str_slug($value, '-');
    }

}
