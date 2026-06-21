<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(){
        $data = Client::with('invoices')->orderBy('id','desc')->get();
        return response()->json($data);
    }

    public function store(Request $request){
        $validated = $request->validate([
            'fullname' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string',
            'ifu' => 'nullable|string|max:255',
        ]);

        $client = Client::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Client cree avec succes',
            'data' => $client,
        ], 200);
    }

    public function show(CLient $client){
        $client->load('invoices');
        return response()->json([
            'success' => true,
            'data' => $client,
        ]);
    }

    public function update(Request $request, Client $client){
        $validated = $request->validate([
            'fullname' => 'sometimes|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string',
            'ifu' => 'nullable|string|max:255',
        ]);

        $client->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Client mis a jour',
            'data' => $client,
        ]);
    }

    public function destroy(Client $client){
        $client->delete();
        return response()->json([
            'success' => true,
            'message' => 'Client supprime',
        ]);
    }
}
