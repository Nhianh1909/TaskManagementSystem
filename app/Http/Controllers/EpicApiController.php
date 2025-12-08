<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EpicApiController extends Controller
{
    /**
     * Get epics as JSON for AJAX refresh
     */
    public function getEpicsJson()
    {
        $user = Auth::user();
        $team = $user->teams()->first();
        
        if (!$team) {
            return response()->json(['epics' => []], 200);
        }

        $epics = $team->epics()
            ->with(['userStories' => function($query) {
                $query->orderBy('order_index', 'asc');
            }])
            ->get()
            ->map(function($epic) {
                return [
                    'id' => $epic->id,
                    'title' => $epic->title,
                    'description' => $epic->description,
                    'stories_count' => $epic->userStories->count(),
                    'total_points' => $epic->userStories->sum('story_point'),
                ];
            });

        return response()->json(['epics' => $epics], 200);
    }
}
