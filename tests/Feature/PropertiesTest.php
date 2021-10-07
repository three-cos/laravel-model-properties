<?php

namespace Wardenyarn\Properties\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Wardenyarn\Properties\Tests\TestCase;
use Wardenyarn\Properties\Models\Property;
use Wardenyarn\Properties\Models\TestModel;

class PropertiesTest extends TestCase
{
    use RefreshDatabase;

    public function makeModel()
    {
        $model = TestModel::create([
            'name' => 'test name',
        ]);

        $model->setProperties([
            'nickname' => [
                'cast' => 'string',
            ],
            'float_property' => [
                'cast' => 'float',
            ],
            'datetime_property' => [
                'cast' => 'datetime',
            ],
            'property_with_default_value' => [
                'cast' => 'string',
                'default' => 'Default string',
            ]
        ]);

        $this->makeProperties($model);

        return $model;
    }

    public function makeProperties($model)
    {
        foreach ($model->getProperties() as $name => $info) {
            $property = Property::firstOrNew(['name' => $name]);
            $property->name = $name;
            $property->cast = $info['cast'];
            $property->save();
        }
    }

    /**
    * @test
    */
    public function it_saves_changes() 
    {
        $model = $this->makeModel();

        $model->properties->nickname = 'test_nickname_property';
        $model->properties->save();

        $this->assertEquals('test_nickname_property', $model->properties->nickname);
        $this->assertDatabaseHas('property_values', [
            'value' => 'test_nickname_property',
        ]);
    }

    /**
    * @test
    */
    public function it_saves_changes_when_parent_model_saved() 
    {
        $model = $this->makeModel();

        $model->properties->nickname = 'a nickname';
        
        $model->save(); // save parent model

        $this->assertEquals('a nickname', $model->properties->nickname);
        $this->assertDatabaseHas('property_values', [
            'value' => 'a nickname',
        ]);
    }

    /**
    * @test
    */
    public function it_doesnt_save_missing_properties() 
    {
        $model = $this->makeModel();

        // this property does not exist in TestModel
        $model->properties->missing_property = 'not exists!';

        $model->properties->save();

        $this->assertNull($model->properties->missing_property);
        $this->assertDatabaseMissing('property_values', [
            'value' => 'not exists!',
        ]);
    }

    /**
    * @test
    */
    public function it_cast_properties_values()
    {
        $model = $this->makeModel();

        // pass a strings
        $model->properties->float_property = ' 100 ';
        $model->properties->datetime_property = '06.10.2021 00:00:00';
        $model->properties->save();

        // Retrieve fresh casted properties
        $model->fresh();

        $this->assertEquals(100.0, $model->properties->float_property);
        $this->assertInstanceOf('Illuminate\Support\Carbon', $model->properties->datetime_property);
    }

    /**
    * @test
    */
    public function it_support_mass_assignment() 
    {
        $model = $this->makeModel();

        $model->properties->set([
            'nickname' => 'Cool nickname',
            'float_property' => '555.55',
            'non_exist_property' => 'this property will not be saved',
        ]);

        $model->properties->save();

        $this->assertDatabaseHas('property_values', [
            'value' => 'Cool nickname',
        ]);

        $this->assertDatabaseHas('property_values', [
            'value' => '555.55',
        ]);

        $this->assertDatabaseMissing('property_values', [
            'value' => 'this property will not be saved',
        ]);
    }

    /**
    * @test
    */
    public function it_uses_default_values_for_empty_properties() 
    {
        $model = $this->makeModel();

        $this->assertEquals('Default string', $model->properties->property_with_default_value);
        $this->assertDatabaseMissing('property_values', [
            'value' => 'Default string',
        ]);
    }

    /**
    * @test
    */
    public function it_saves_properties_of_new_model_instance() 
    {
        $model = new TestModel;
        $this->makeProperties($model);

        $model->name = 'test model';
        $model->properties->nickname = 'new nickname';
        $model->save();

        $this->assertDatabaseHas('test_models', [
            'name' => 'test model',
        ]);

        $this->assertDatabaseHas('property_values', [
            'value' => 'new nickname',
        ]);
    }

    /**
    * @test
    */
    public function it_trows_exception_when_property_is_missing() 
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $model = new TestModel;
        // skip properties creation
        // $this->makeProperties($model);

        $model->name = 'test model';
        $model->properties->nickname = 'new nickname';
        $model->save();
    }
}
