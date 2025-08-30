<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CustomerIOController extends Controller
{
    /**
     * Fetch all members of a given Customer.io Segment
     *
     * @param  int|string  $segmentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSegmentMembers(int|string $segmentId): JsonResponse
    {
        set_time_limit(0);
        
        $allMembers = [];
        $start = request()->query('start');
        $limit = request()->query('limit', 100);

        do {
            $url = "https://api.customer.io/v1/segments/{$segmentId}/membership";
            
            Log::info('Customer.io API request', [
                'url' => $url,
                'segmentId' => $segmentId,
                'start' => $start,
                'limit' => $limit,
            ]);
            
            $params = ['limit' => $limit];
            if ($start) {
                $params['start'] = $start;
            }
            
            $response = Http::withToken(env('CUSTOMER_IO_API_KEY'))
                ->get($url, $params);

            if ($response->failed()) {
                Log::error('Customer.io API request failed', [
                    'segmentId' => $segmentId,
                    'start' => $start,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return response()->json([
                    'error' => 'Failed to fetch segment members',
                    'status' => $response->status(),
                    'details' => $response->json() ?? $response->body(),
                ], $response->status());
            }

            $data = $response->json();

            $identifiers = $data['identifiers'] ?? [];
            $allMembers = array_merge($allMembers, $identifiers);

            $start = $data['next'] ?? null;

            if ($start) {
                sleep(1);
            }

        } while ($start);

        return response()->json($allMembers);
    }
}

