<?php

namespace InfusionWeb\Laravel\ContentCache;

use Illuminate\Support\Collection;
use Carbon\Carbon;
use Guzzle;
use Intervention\Image\ImageManagerStatic as Image;
use Storage;

class Profile
{

    protected $endpoint = '';

    protected $query = [];

    protected $filters = [];

    protected $fields = [];

    protected $keys = [];

    protected $image_derivatives = [];

    protected $image_styles = [];

    public function __construct($profile = '')
    {
        $this->setEndpoint(config("contentcache.{$profile}.endpoint", config("contentcache.default.endpoint")));

        $this->setQuery(config("contentcache.{$profile}.query", config("contentcache.default.query")));

        $this->setFilters(config("contentcache.{$profile}.filters", config("contentcache.default.filters")));

        $this->setFields(config("contentcache.{$profile}.fields", config("contentcache.default.fields")));

        $this->setKeys(config("contentcache.{$profile}.keys", config("contentcache.default.keys")));

        $this->setImageDerivatives(config("contentcache.{$profile}.image_derivatives", config("contentcache.default.image_derivatives")));

        // Define usable image styles.
        $default = (array) config("contentcache.default.image_style", []);
        $image_styles = array_merge($default, (array) config("contentcache.{$profile}.image_style"));
        $this->setImageStyles($image_styles);
    }

    public function setFilters($filters)
    {
        return $this->filters = (array) $filters;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function filter(Collection $collection)
    {
        $collection->transform(function ($item, $key) {
            $filters = collect($this->getFilters());
            $fields = $filters->keys()->intersect($item->attributeKeys());

            foreach ($fields->all() as $field) {
                $method = 'filter'.ucfirst($filters->get($field));

                // Make sure filter method is defined.
                if (method_exists($this, $method)) {
                    // Deal with array values correctly.
                    if (is_array($item->$field)) {
                        // We have to deal with the attribute indirectly
                        // because it is an overloaded property.
                        $item_field = [];

                        foreach ($item->$field as $value) {
                            $item_field[] = $this->$method($value);
                        }

                        $item->$field = $item_field;
                    } else {
                        $item->$field = $this->$method($item->$field);
                    }
                }
            }

            return $item;
        });
    }

    protected function filterString($value)
    {
        return (string) $value;
    }

    protected function filterInt($value)
    {
        return (int) $value;
    }

    protected function filterDate($value)
    {
        return Carbon::parse($value);
    }

    protected function filterFile($value)
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

    public function setImageDerivatives($image_derivatives)
    {
        return $this->image_derivatives = (array) $image_derivatives;
    }

    public function getImageDerivatives()
    {
        return $this->image_derivatives;
    }

    public function setImageStyles($image_styles)
    {
        return $this->image_styles = (array) $image_styles;
    }

    public function getImageStyles()
    {
        return $this->image_styles;
    }

    public function setFields($fields)
    {
        return $this->fields = (array) $fields;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function field(Collection $collection)
    {
        $collection->transform(function ($item, $key) {
            $fields = collect($this->getFields());

            // Loop through new fields to be created.
            foreach($fields->all() as $to_add => $existing) {
                $method = 'field'.ucfirst($to_add);

                // Do we have a method to create the requested field?
                if (method_exists($this, $method)) {
                    // Handle use of multiple property names.
                    $args = [];

                    foreach ((array) $existing as $field) {
                        $args[] = $item->$field;
                    }

                    $item->$to_add = $this->$method($args);
                }
            }

            return $item;
        });
    }

    protected function fieldSlug($args)
    {
        $value = implode(' ', (array) $args);

        return str_slug($value, '-');
    }

    public function createImageDerivatives(Collection $collection)
    {
        $collection->transform(function ($item, $key) {
            $image_styles = collect($this->getImageStyles());
            $image_derivatives = collect($this->getImageDerivatives());

            $fields = $image_derivatives->keys()->intersect($item->attributeKeys());

            foreach ($fields->all() as $field) {
                $styles = collect($image_derivatives->get($field))->intersect($image_styles->keys());

                foreach ($styles->all() as $style) {
                    // Create image derivative and add it to $item.
                    if ($new_image = $this->generateImageDerivative($item->$field, $style)) {
                        // We have to deal with the attribute indirectly
                        // because it is an overloaded property.
                        if ($item->hasAttribute('image_derivatives')) {
                            $item_image_derivatives = (array) $item->image_derivatives;
                        } else {
                            $item_image_derivatives = [];
                        }

                        $item_image_derivatives[$style] = $new_image;

                        $item->image_derivatives = $item_image_derivatives;
                    }
                }
            }

            return $item;
        });
    }

    protected function generateImageDerivative($uri, $image_style)
    {
        // Cache original image.
        $image_url = $this->filterFile($uri);

        $valid_types = [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG];

        $image_type = @exif_imagetype($image_url);

        if (!in_array($image_type, $valid_types)) {
            // Not a valid image. Don't modify.
            return false;
        }

        // Modify the image, based on style definition in config.
        $width = config("contentcache.default.image_style.{$image_style}.width");
        $height = config("contentcache.default.image_style.{$image_style}.height");

        $image = Image::make($image_url)->fit($width, $height, function ($constraint) {
            $constraint->upsize();
        });

        $disk = Storage::disk(config('contentcache.default.filesystem'));

        $path = config('contentcache.default.path');

        $filename = basename(parse_url($image_url, PHP_URL_PATH));

        $location = $path ? $path.'/'.$image_style.'/'.$filename : $image_style.'/'.$filename;

        $file_contents = $image->stream()->__toString();

        // Figure out if the file has changed before writing it.
        $need_to_save = (strlen($file_contents) != $disk->size($image_url));

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
    }

}
