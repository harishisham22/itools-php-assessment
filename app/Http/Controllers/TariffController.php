<?php

namespace App\Http\Controllers;

use App\Http\Resources\TariffResource;
use App\Models\Tariff;
use App\Http\Requests\StoreTariffRequest;
use App\Http\Requests\UpdateTariffRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class TariffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTariffRequest $request): JsonResponse
    {
        DB::beginTransaction();

        $tariff = Tariff::create($request->all());

        DB::commit();

        return response()->json([
            'data' => TariffResource::make($tariff),
            'message' => 'Tariff created successfully.'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tariff $tariff): TariffResource
    {
        return TariffResource::make($tariff);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tariff $tariff)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTariffRequest $request, Tariff $tariff): TariffResource
    {
        DB::beginTransaction();

        $tariff->update($request->all());

        DB::commit();

        // TODO: queue jobs that would recalculate buildings affected by the tariff rate.

        return TariffResource::make($tariff);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tariff $tariff): Response
    {
        DB::beginTransaction();

        $tariff->delete();

        DB::commit();

        // TODO: queue jobs that would recalculate buildings affected by the tariff rate.

        return response()->noContent();
    }
}
