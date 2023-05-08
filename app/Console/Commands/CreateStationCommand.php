<?php

namespace App\Console\Commands;

use App\Models\Station;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateStationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ev:stations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate the stations table and reload the geojson';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $geojson = file_get_contents(resource_path('/stations.geojson'));
        // Delete all existing records from the monuments table
        //        Station::truncate();

        // Decode the geojson file content (it's json after all) into an array, create a Laravel collection
        // from the features element, loop through each feature and create a monument in the
        // database. For the geom field, we use a raw expression to use the ST_GeomFromGeoJSON
        // function, passing it the feature's geometry fragment re-encoded to json.

        $features = collect(json_decode($geojson, true)['features'])->each(function ($feature) {
            Station::create([
                'name' => $feature['properties']['name'],
                'address' => $feature['properties']['address'],
                'latitude' => $feature['geometry']['coordinates'][0],
                'longitude' => $feature['geometry']['coordinates'][1],
                'position' => DB::raw("ST_GeomFromGeoJSON('".json_encode($feature['geometry'])."')"),
            ]);
        });

        $this->info($features->count().' monuments loaded successfully.');

        return 0;
    }
}
