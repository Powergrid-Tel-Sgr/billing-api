<?php

namespace App\Http\Controllers;

use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return ['services' => ServiceResource::collection(Service::latest()->get())];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required']
        ]);

        $service = Service::make($request);

        return ['service' => new ServiceResource($service)];
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Service  $service
     * @return \Illuminate\Http\Response
     */
    public function show(Service $service)
    {
        return ['service' => new ServiceResource($service)];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Service  $service
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Service $service)
    {
        $request->validate([
            'name' => ['required']
        ]);

        $service->vendor_id = $request->vendor["id"];
        $service->node_id = $request->node["id"];
        $service->name = $request->name;
        $service->cpNumber = $request->cpNumber;
        $service->capacity = $request->capacity;
        $service->linkFrom = $request->linkFrom;
        $service->linkTo = $request->linkTo;
        $service->doco = $request->doco;
        $service->lastMile = $request->lastMile;
        $service->annualInvoiceValue = $request->annualInvoiceValue;
        $service->sharePercent = $request->sharePercent;
        $service->discountOffered = $request->discountOffered;
        $service->annualVendorValue = $request->annualVendorValue;
        $service->unitRate = $request->unitRate;
        $service->totalPODays = $request->totalPODays;

        $service->save();

        return ['service' => new ServiceResource($service)];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Service  $service
     * @return \Illuminate\Http\Response
     */
    public function destroy(Service $service)
    {
        $service->delete();
        return ['service' => new ServiceResource($service)];
    }

    public function import(Request $request)
    {
        $services = $request->services;

        $saved = Service::all();

        $newServices = [];

        foreach ($services as $service) {
            $sc = (object) $service;

            $r = $saved->contains(function ($s) use ($sc) {
                return $s->cpNumber === $sc->cpNumber;
            });

            if ($r) {
                return "{$sc->name} has failed";
            }

            $service = new Service();

            $service->vendor_id = (string)$sc->vendor["id"];
            $service->node_id = (string)$sc->node["id"];
            $service->name = $sc->name;
            $service->cpNumber = $sc->cpNumber;
            $service->capacity = $sc->capacity;
            $service->linkFrom = $sc->linkFrom;
            $service->linkTo = $sc->linkTo;
            $service->doco = $sc->doco;
            $service->lastMile = $sc->lastMile;
            $service->annualInvoiceValue = $sc->annualInvoiceValue;
            $service->sharePercent = $sc->sharePercent;
            $service->discountOffered = $sc->discountOffered;
            $service->annualVendorValue = $sc->annualVendorValue;
            $service->unitRate = $sc->unitRate;
            $service->totalPODays = $sc->totalPODays;

            $service->save();

            $newServices[] = new ServiceResource($service);
        }

        return ["services" => $newServices];
    }
}
