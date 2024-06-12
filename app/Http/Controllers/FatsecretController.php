<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FatsecretController extends Controller
{
    private $clientID;
    private $clientSecret;

    public function __construct()
    {
        $this->clientID = env('FATSECRET_CLIENT_ID');
        $this->clientSecret = env('FATSECRET_CLIENT_SECRET');
    }

    public function getToken(Request $request)
    {
        $response = Http::asForm()->withBasicAuth($this->clientID, $this->clientSecret)
            ->post("https://oauth.fatsecret.com/connect/token", [
                "grant_type" => "client_credentials",
                "scope" => "basic",
            ]);

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json(['error' => 'Failed to retrieve token'], $response->status());
    }

    public function search(Request $request)
    {
        if (!$request->input('token')) {
            return response()->json(['error' => 'Token is required'], 400);
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $request->token
        ])->get("https://platform.fatsecret.com/rest/server.api", [
            'method' => 'foods.search.v3',
            'search_expression' => $request->search,
            'format' => 'json',
            'include_sub_categories' => 'true',
            'flag_default_serving' => 'true',
            'max_results' => 10
        ]);

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json(['error' => 'Failed to retrieve data'], $response->status());
    }
}
