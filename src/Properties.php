<?php

namespace Wardenyarn\Properties;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Wardenyarn\Properties\Models\Property;
use Wardenyarn\Properties\Models\PropertyValue;

class Properties
{
	/**
	 * Parent model
	 * @var Model
	 */
	protected $model;

	/**
	 * Collection of available model properties
	 * @var Collection
	 */
	protected $model_properties;

	/**
	 * Collection of property values
	 * @var Collection
	 */
	protected $values;

	/**
	 * Properties from DB
	 * @var Array
	 */
	protected $persisted_properties;


	public function __construct(Model $model)
	{
		$this->model = $model;
		$this->model_properties = $model->getProperties();
		$this->retrieveValues();
	}

	/**
	 * Get existing property values from cache or db for current model
	 * @return void
	 */
	protected function retrieveValues()
	{
        $persisted_values = Cache::rememberForever($this->getCacheKey(), function() {
        	return PropertyValue::where([
	            'entity_type' => get_class($this->model), 
	            'entity_id' => $this->model->id,
	        ])->with('property')->get();
        });

        $this->persisted_properties = $persisted_values->reduce(function ($props, $value) {
            $props[$value->property->name] = $value;
            
            return $props;
        }) ?? [];

        $values = $persisted_values->reduce(function ($props, $value) {
            $value->mergeCasts([
                'value' => $value->property->cast,
            ]);
            
            $props[$value->property->name] = $value->value ?? $default_value;
            
            return $props;
        });

        $values = collect($values);

        $default_values = $this->model_properties->reduce(function($props, $value, $property_name) {
            $props[$property_name] = (isset($value['default'])) 
	            ? $value['default']
	            : null ;
            
            return $props;
        });

        $default_values = collect($default_values);

        $this->values = $default_values->merge($values);
	}

	/**
	 * Save properties
	 * @return void
	 */
	public function save()
	{
		Cache::forget($this->getCacheKey());

		$this->values->each(function($value, $property_name) {
			$property_value = $this->getPropertyValue($property_name);

			// if something changed - save it
		    if ($property_value->value != $value) {
		    	$property_value->value = $value;
			    $property_value->save();
		    }
		});
	}

	/**
	 * Get existing or new PropertyValue instance
	 * for a property named $property_name
	 * @param  string $property_name
	 * @return PropertyValue | InvalidArgumentException
	 */
	protected function getPropertyValue($property_name)
	{
		if (array_key_exists($property_name, $this->persisted_properties)) {
			return $this->persisted_properties[$property_name];
		} 

		if ($property = Property::where('name', $property_name)->first()) {
			return PropertyValue::firstOrNew([
				'entity_type'   => get_class($this->model),
				'entity_id'     => $this->model->id,
				'property_type' => get_class($property),
				'property_id'   => $property->id,
			]);
		}
		
		throw new \InvalidArgumentException("Missing {$property_name} property");
	}

	/**
	 * Set multiple properties
	 * @param array $properties
	 * @return void
	 */
	public function set(array $properties)
	{
		collect($properties)->each(function ($value, $property) {
		    $this->$property = $value;
		});
	}

	/**
	 * Get property
	 * @param  string $property_name
	 * @return mixed
	 */
	public function __get($property_name)
	{
		return $this->values->get($property_name);
	}
	
	/**
	 * Set property if it exist in model properties
	 * @param  string $property_name
	 * @param  mixed $value
	 * @return void
	 */
	public function __set($property_name, $value)
	{
		if ($this->model_properties->keys()->contains($property_name)) {
			$this->values[$property_name] = $value;
		}
	}

	/**
	 * Generates cache key
	 * @return string
	 */
	protected function getCacheKey()
	{
		return config('model-properties.cache_key_prefix', 'properties')
				.'_'.get_class($this->model)
				.'_'.$this->model->id;
	}
}