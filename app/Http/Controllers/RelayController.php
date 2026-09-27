<?php

namespace App\Http\Controllers;

use App\Models\Recommendation;
use App\Services\Graph8Service;
use App\Services\RelayCoordinatorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class RelayController extends Controller
{
    public function index(Request $request): View
    {
        $recommendations = Recommendation::query()
            ->where(
                'user_id',
                $request->user()->id
            )
            ->with('simulation')
            ->latest()
            ->get();

        $pendingCount = $recommendations
            ->where('status', 'pending')
            ->count();

        $approvedCount = $recommendations
            ->where('status', 'approved')
            ->count();

        $executedCount = $recommendations
            ->where('status', 'executed')
            ->count();

        return view('modules.relay.index', [
            'recommendations' =>
                $recommendations,
            'pendingCount' =>
                $pendingCount,
            'approvedCount' =>
                $approvedCount,
            'executedCount' =>
                $executedCount,
            'graph8Configured' =>
                $this->graph8Configured(),
        ]);
    }

    public function approve(
        Request $request,
        Recommendation $recommendation
    ): RedirectResponse {
        $this->authorizeRecommendation(
            $request,
            $recommendation
        );

        abort_unless(
            $recommendation->status ===
                'pending',
            422,
            'Only pending recommendations can be approved.'
        );

        $recommendation->update([
            'status' => 'approved',
            'approved_by' =>
                $request->user()->id,
            'approved_at' => now(),
            'error_message' => null,
        ]);

        return redirect()
            ->route('relay.index')
            ->with(
                'success',
                'Recommendation approved for graph8 execution.'
            );
    }

    public function reject(
        Request $request,
        Recommendation $recommendation
    ): RedirectResponse {
        $this->authorizeRecommendation(
            $request,
            $recommendation
        );

        abort_unless(
            in_array(
                $recommendation->status,
                [
                    'pending',
                    'approved',
                ],
                true
            ),
            422,
            'This recommendation cannot be rejected.'
        );

        $recommendation->update([
            'status' => 'rejected',
            'error_message' => null,
        ]);

        return redirect()
            ->route('relay.index')
            ->with(
                'success',
                'Recommendation rejected.'
            );
    }

    public function execute(
        Request $request,
        Recommendation $recommendation,
        Graph8Service $graph8Service,
        RelayCoordinatorService $relayCoordinatorService
    ): RedirectResponse {
        $this->authorizeRecommendation(
            $request,
            $recommendation
        );

        if ($recommendation->status === 'executed') {
            return redirect()
                ->route('relay.index')
                ->with(
                    'success',
                    'Recommendation was already executed successfully.'
                );
        }

        abort_unless(
            $recommendation->status ===
                'approved',
            422,
            'Only approved recommendations can be executed.'
        );

        try {
            $response = $graph8Service
                ->executeRecommendation(
                    $recommendation
                );

            $response['results']['relay_coordinator'] =
                $relayCoordinatorService->send(
                    $recommendation,
                    $response['target'] ?? [],
                    $response['metadata'] ?? []
                );

            $recommendation->update([
                'status' => 'executed',
                'executed_at' => now(),
                'execution_response' =>
                    $response,
                'error_message' => null,
            ]);

            return redirect()
                ->route('relay.index')
                ->with(
                    'success',
                    'Approved report created a graph8 note and task, then reached Relay8 Coordinator successfully.'
                );
        } catch (Throwable $exception) {
            report($exception);

            $recommendation->update([
                'error_message' =>
                    $exception->getMessage(),
            ]);

            return redirect()
                ->route('relay.index')
                ->withErrors([
                    'graph8' =>
                        $exception->getMessage(),
                ]);
        }
    }

    private function authorizeRecommendation(
        Request $request,
        Recommendation $recommendation
    ): void {
        abort_unless(
            $recommendation->user_id ===
                $request->user()->id,
            403
        );
    }

    private function graph8Configured(): bool
    {
        return filled(
            config('graph8.base_url')
        )
            && filled(
                config('graph8.api_token')
            )
            && filled(
                config('graph8.relay.agent_id')
            );
    }
}
