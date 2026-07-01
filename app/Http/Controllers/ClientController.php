<?php

namespace App\Http\Controllers;

use App\Exports\ClientExport;
use App\Models\Client;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ClientController extends Controller
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        //
    }

    public function activities($clientId)
    {
        $client = Client::findOrFail($clientId);

        $activities = $client->activities()
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json($activities);
    }

    public function export($clientId)
    {
        $client = Client::with('task')->findOrFail($clientId);

        $filename = 'client_' . $client->id . '_' . now()->format('Y-m-d_H-i') . '.xlsx';

        return Excel::download(new ClientExport($client), $filename);
    }
}
