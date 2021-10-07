<?php

namespace Wardenyarn\Properties\Models;

use Wardenyarn\Properties\Properties;

trait HasProperties
{
    protected $propertiesBag;

    /**
     * Setup and return properties object
     * @return Properties
     */
    public function getPropertiesAttribute()
    {
        if (! isset($this->propertiesBag)) {
            $this->propertiesBag = new Properties($this);
        }

        return $this->propertiesBag;
    }

    /**
     * Returns model properties collection
     * @return Collection
     */
    public function getProperties()
    {
        return collect($this->properties);
    }

    /**
     * Set model properties
     * @return void
     */
    public function setProperties(array $properties)
    {
        $this->properties = $properties;
    }

    /**
     * Overwrite parent model fresh method
     * {@inheritdoc}
     */
    public function fresh($with = [])
    {
        $this->refreshProperties();
        parent::fresh($with);
    }

    /**
     * Overwrite parent model save method
     * {@inheritdoc}
     */
    public function save(array $options = [])
    {
        $result = parent::save($options);

        $this->saveAndRefreshProperties();

        return $result;
    }

    /**
     * Removes current properties
     * @return void
     */
    public function refreshProperties()
    {
        $this->propertiesBag = null;
    }

    /**
     * Save, then removes properties
     * @return void
     */
    public function saveAndRefreshProperties()
    {
        if ($this->propertiesBag !== null) {
            $this->propertiesBag->save();
            $this->refreshProperties();
        }
    }
}
