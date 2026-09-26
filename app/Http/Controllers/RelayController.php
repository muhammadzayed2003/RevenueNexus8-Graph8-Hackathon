<?php

namespace App\Http\Controllers;

use App\Models\Recommendation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RelayController extends Controller
{
    public function index(Request $request): View
    {
        $recommendations = Recommendation::query()
            ->where('user_id', $request->user()->id)
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
            'recommendations' => $recommendations,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'executedCount' => $executedCount,
        ]);
    }

    public function approve(
        Request $request,
        Recommendation $recommendation
    ): RedirectResponse {
        abort_unless(
            $recommendation->user_id === $request->user()->id,
            403
        );

        abort_unless(
            $recommendation->status === 'pending',
            422,
            'Only pending recommendations can be approved.'
        );

        $recommendation->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return redirect()
            ->route('relay.index')
            ->with('success', 'Recommendation approved for execution.');
    }

    public function reject(
        Request $request,
        Recommendation $recommendation
    ): RedirectResponse {
        abort_unless(
            $recommendation->user_id === $request->user()->id,
            403
        );

        abort_unless(
            in_array(
                $recommendation->status,
                ['pending', 'approved'],
                true
            ),
            422,
            'This recommendation cannot be rejected.'
        );

        $recommendation->update([
            'status' => 'rejected',
        ]);

        return redirect()
            ->route('relay.index')
            ->with('success', 'Recommendation rejected.');
    }
}