<?php

namespace InfusionWeb\Laravel\ContentCache;

use Carbon\Carbon;

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
                if (array_key_exists($name, $filters)) {
                    // Run the filter
                    $method = 'filter'.ucfirst($filters[$name]);

                    if (method_exists($this, $method)) {
                        // Deal with arrays correctly.
                        if (is_array($item->$name)) {
                            foreach ($item->$name as &$val) {
                                $val = $this->$method($val);
                            }
                        } else {
                            $item->$name = $this->$method($item->$name);
                        }
                    }
                }
            }
        }
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
