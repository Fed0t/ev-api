<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStation;
use App\Http\Requests\ShowStation;
use App\Http\Requests\UpdateStation;
use App\Http\Resources\StationResource;
use App\Http\Resources\StationsResource;
use App\Models\Company;
use App\Models\Station;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromFile;
use Knuckles\Scribe\Attributes\Unauthenticated;

class StationController extends Controller
{
    /**
     * Show all stations.
     */
    #[Unauthenticated]
    #[Group(name: 'Stations')]
    #[Endpoint(
        title: 'Task 3. Show stations and filter by lat/long point distance of nearby points and by single company',
        description: 'List all stations',
    )]
    #[ResponseFromFile(
        file: 'doc-files/stations/show.json',
        description: 'List stations',
    )]
    public function show(ShowStation $request): StationsResource
    {
        $stations = Station::query();

        if ($request->latitude && $request->longitude) {
            $stations = Station::getNearby($request->latitude, $request->longitude, $request->distance_km ?? 10);
        }

        if ($company = $request->company) {
            $company = Company::find($company);
            $descendants = $company->descendants()->pluck('id')->toArray();
            $descendants[] = $company->id;

            $stations = $stations->whereIn('company_id', $descendants);
        }

        $stations->with('company');

        return new StationsResource($stations->get());
    }

    /**
     * Create company.
     */
    #[Unauthenticated]
    #[Group(name: 'Stations')]
    #[Endpoint(
        title: 'Create stations',
        description: 'List all stations',
    )]
    #[ResponseFromFile(
        file: 'doc-files/stations/show.json',
        description: 'Response for stations',
    )]
    public function create(CreateStation $request): StationResource
    {
        return new StationResource(Station::firstOrCreate([
            'name' => $request->name,
        ], [
            'address' => $request->address,
            'latitude' => $request->latitude,
            'company_id' => $request->company_id,
            'longitude' => $request->longitude,
        ]));
    }

    /**
     * Update company information.
     */
    #[Unauthenticated]
    #[Group(name: 'Stations')]
    #[Endpoint(
        title: 'Update stations',
        description: 'Add a parent to a company use parent_company parameter.',
    )]
    #[ResponseFromFile(
        file: 'doc-files/stations/show.json',
        description: 'Response for stations',
    )]
    public function update(UpdateStation $request, Station $station): StationResource
    {
        $data = $request->validated();

        if ($request->latitude && $request->longitude) {
            $point = [
                'type' => 'Point',
                'coordinates' => [ $request->latitude, $request->longitude ],
            ];
            $data['position'] = DB::raw("ST_GeomFromGeoJSON('".json_encode($point)."')");
        }

        $station->update($data);

        return new StationResource($station);
    }

    /**
     * Delete company.
     */
    #[Unauthenticated]
    #[Group(name: 'Stations')]
    #[Endpoint(
        title: 'Delete station',
        description: 'Delete stations',
    )]
    #[ResponseFromFile(
        file: 'doc-files/stations/show.json',
        description: 'Response for stations',
    )]
    public function delete(Station $station): JsonResponse
    {
        $station->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
