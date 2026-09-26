<?php

namespace App\Http\Controllers;

use App\Models\Graph8Event;
use App\Models\Recommendation;
use App\Models\Simulation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;

        $simulationQuery = Simulation::query()
            ->where('user_id', $userId);

        $recommendationQuery = Recommendation::query()
            ->where('user_id', $userId);

        $simulationCount = (clone $simulationQuery)->count();

        $completedSimulationCount = (clone $simulationQuery)
            ->where('status', 'completed')
            ->count();

        $averageScore = (clone $simulationQuery)
            ->where('status', 'completed')
            ->avg('score');

        $pendingRecommendationCount = (clone $recommendationQuery)
            ->where('status', 'pending')
            ->count();

        $approvedRecommendationCount = (clone $recommendationQuery)
            ->where('status', 'approved')
            ->count();

        $executedRecommendationCount = (clone $recommendationQuery)
            ->where('status', 'executed')
            ->count();

        $moduleCounts = (clone $simulationQuery)
            ->selectRaw('module, COUNT(*) as total')
            ->groupBy('module')
            ->pluck('total', 'module');

        $recentSimulations = (clone $simulationQuery)
            ->latest()
            ->take(6)
            ->get();

        $graph8EventCount = Graph8Event::count();

        $latestGraph8Event = Graph8Event::query()
            ->latest('occurred_at')
            ->first();

        $graph8Configured = filled(config('graph8.base_url'))
            && filled(config('graph8.api_token'));

        return view('dashboard', [
            'simulationCount' => $simulationCount,
            'completedSimulationCount' => $completedSimulationCount,
            'averageScore' => $averageScore,
            'pendingRecommendationCount' => $pendingRecommendationCount,
            'approvedRecommendationCount' => $approvedRecommendationCount,
            'executedRecommendationCount' => $executedRecommendationCount,
            'moduleCounts' => $moduleCounts,
            'recentSimulations' => $recentSimulations,
            'graph8EventCount' => $graph8EventCount,
            'latestGraph8Event' => $latestGraph8Event,
            'graph8Configured' => $graph8Configured,
        ]);
    }
}
