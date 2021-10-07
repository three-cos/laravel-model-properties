<?php

namespace Wardenyarn\Properties\Console;

use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

use Wardenyarn\Properties\Models\Property;

class FillProperties extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'properties:fill';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get all the properties from models in App\\Model and persist them into DB';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        foreach ($this->collectProperties() as $name => $info) {
            $property = Property::firstOrNew(['name' => $name]);
            $property->name = $name;
            $property->cast = $info['cast'];
            $property->save();

            $this->info("Saved {$name} property as {$info['cast']}");
        }

        return 0;
    }

    protected function collectProperties()
    {
        $properties = collect([]);

        foreach ($this->getModels() as $modelClass) {
            $model = new $modelClass;
            $properties = $properties->merge($model->getProperties());
        }

        return $properties;
    }

    /*
    Thanks to https://stackoverflow.com/a/60310985 !
     */
    protected function getModels(): Collection
    {
        $models = collect(File::allFiles(app_path()))
            ->map(function ($item) {
                $path = $item->getRelativePathName();
                $class = sprintf('\%s%s',
                    Container::getInstance()->getNamespace(),
                    strtr(substr($path, 0, strrpos($path, '.')), '/', '\\'));

                return $class;
            })
            ->filter(function ($class) {
                $valid = false;

                if (class_exists($class) && method_exists($class, 'getProperties')) {
                    $reflection = new \ReflectionClass($class);
                    $valid = $reflection->isSubclassOf(Model::class) &&
                        !$reflection->isAbstract();
                }

                return $valid;
            });

        return $models->values();
    }
}
