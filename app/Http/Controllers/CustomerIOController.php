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

    /**
     * Fetch paginated members of a given Customer.io Segment
     *
     * @param  int|string  $segmentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSegmentMembersPaginated(int|string $segmentId): JsonResponse
    {
        $page = (int) request()->query('page', 1);
        $perPage = (int) request()->query('per_page', 50);
        $start = request()->query('start');
        
        // If start token is provided, use it directly (for deep pagination)
        // Otherwise, calculate based on page number
        if (!$start && $page > 1) {
            // For page-based pagination, we need to calculate the start token
            // This is approximate since Customer.io uses cursor-based pagination
            $calculatedLimit = ($page - 1) * $perPage;
            if ($calculatedLimit > 0) {
                // For pages beyond 1, we'll need to make intermediate requests
                // to get the correct start token
                $tempStart = null;
                $currentPage = 1;
                
                while ($currentPage < $page) {
                    $tempResponse = Http::withToken(env('CUSTOMER_IO_API_KEY'))
                        ->get("https://api.customer.io/v1/segments/{$segmentId}/membership", [
                            'limit' => $perPage,
                            'start' => $tempStart
                        ]);
                    
                    if ($tempResponse->failed()) {
                        return response()->json([
                            'error' => 'Failed to fetch segment members',
                            'status' => $tempResponse->status(),
                        ], $tempResponse->status());
                    }
                    
                    $tempData = $tempResponse->json();
                    $tempStart = $tempData['next'] ?? null;
                    
                    if (!$tempStart) {
                        // No more pages available
                        return response()->json([
                            'data' => [],
                            'pagination' => [
                                'current_page' => $page,
                                'per_page' => $perPage,
                                'has_more' => false,
                                'next_page' => null,
                                'next_start_token' => null,
                            ]
                        ]);
                    }
                    
                    $currentPage++;
                    
                    // Add small delay to avoid rate limiting
                    if ($currentPage < $page) {
                        usleep(500000); // 0.5 seconds
                    }
                }
                
                $start = $tempStart;
            }
        }

        $url = "https://api.customer.io/v1/segments/{$segmentId}/membership";
        
        Log::info('Customer.io paginated API request', [
            'url' => $url,
            'segmentId' => $segmentId,
            'page' => $page,
            'per_page' => $perPage,
            'start' => $start,
        ]);
        
        $params = ['limit' => $perPage];
        if ($start) {
            $params['start'] = $start;
        }
        
        $response = Http::withToken(env('CUSTOMER_IO_API_KEY'))
            ->get($url, $params);

        if ($response->failed()) {
            Log::error('Customer.io paginated API request failed', [
                'segmentId' => $segmentId,
                'page' => $page,
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
        $nextToken = $data['next'] ?? null;

        return response()->json([
            'data' => $identifiers,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'has_more' => !is_null($nextToken),
                'next_page' => $nextToken ? $page + 1 : null,
                'next_start_token' => $nextToken,
            ]
        ]);
    }
}

