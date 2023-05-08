<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCompany;
use App\Http\Requests\ShowCompany;
use App\Http\Requests\UpdateCompany;
use App\Http\Resources\CompaniesResource;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromFile;
use Knuckles\Scribe\Attributes\Unauthenticated;

class CompanyController extends Controller
{
    /**
     * Show all companies with descendants.
     */
    #[Unauthenticated]
    #[Group(name: 'Companies')]
    #[Endpoint(
        title: 'Show companies',
        description: 'List all companies',
    )]
    #[ResponseFromFile(
        file: 'doc-files/companies/show.json',
        description: 'Response for companies',
    )]
    public function show(ShowCompany $request): AnonymousResourceCollection
    {
        $companies = Company::when($request->company, function ($query, $company) {
            $query->where('id', $company);
        })->orderBy('id')->get();

        return CompaniesResource::collection($companies);
    }

    /**
     * Create company.
     */
    #[Unauthenticated]
    #[Group(name: 'Companies')]
    #[Endpoint(
        title: 'Create companies',
        description: 'List all companies',
    )]
    #[ResponseFromFile(
        file: 'doc-files/companies/show.json',
        description: 'Response for companies',
    )]
    public function create(CreateCompany $request): CompanyResource
    {
        return new CompanyResource(Company::newCompany($request->validated()));
    }

    /**
     * Update company information.
     *
     * @throws Exception
     */
    #[Unauthenticated]
    #[Group(name: 'Companies')]
    #[Endpoint(
        title: 'Update companies',
        description: 'Add a parent to a company use parent_company parameter.',
    )]
    #[ResponseFromFile(
        file: 'doc-files/companies/show.json',
        description: 'Response for companies',
    )]
    public function update(UpdateCompany $request, Company $company): CompanyResource
    {
        if ($request->add_company) {
            $company->addDescendant(Company::find($request->add_company));
        } else {
            $company->update($request->validated());
        }

        return new CompanyResource($company);
    }

    /**
     * Delete company.
     */
    #[Unauthenticated]
    #[Group(name: 'Companies')]
    #[Endpoint(
        title: 'Delete company',
        description: 'Delete companies',
    )]
    #[ResponseFromFile(
        file: 'doc-files/companies/show.json',
        description: 'Response for companies',
    )]
    public function delete(Company $company): JsonResponse
    {
        $company->deleteDescendant();

        return response()->json(['message' => 'Deleted']);
    }
}
