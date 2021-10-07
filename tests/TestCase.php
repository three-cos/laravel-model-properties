<?php

namespace Wardenyarn\Properties\Tests;

use Wardenyarn\Properties\PropertiesServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
	public function setUp(): void
	{
		parent::setUp();
	}

	protected function getPackageProviders($app)
	{
		return [
			PropertiesServiceProvider::class,
		];
	}

	protected function getEnvironmentSetUp($app)
	{
		include_once __DIR__ . '/../database/migrations/create_test_model_table.php.stub';
		(new \CreateTestModelTable)->up();

		include_once __DIR__ . '/../database/migrations/create_properties_table.php.stub';
		(new \CreatePropertiesTable)->up();
	}
}