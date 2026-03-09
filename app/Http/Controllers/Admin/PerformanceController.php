<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PerformanceReview;
use Illuminate\Http\Request;

class PerformanceController extends Controller
{
    public function index(string $kitchen_slug)
    {
        $staff = User::where('role', 'staff')
            ->where('onboarding_status', 'active')
            ->with([
                'performanceReviews' => function ($q) {
                    $q->latest();
                }
            ])
            ->get();

        return view('admin.performance.index', compact('staff'));
    }

    public function create(Request $request, string $kitchen_slug)
    {
        $user = User::findOrFail($request->user_id);

        // Check if review already exists for this month
        $existing = PerformanceReview::where('user_id', $user->id)
            ->where('month', date('n'))
            ->where('year', date('Y'))
            ->exists();

        if ($existing) {
            return redirect()->route('admin.performance.index')->with('error', 'Review for this month already exists.');
        }

        return view('admin.performance.create', compact('user'));
    }

    public function store(Request $request, string $kitchen_slug)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'month' => 'required|integer',
            'year' => 'required|integer',
            'sop_compliance' => 'required|integer|min:1|max:5',
            'hygiene' => 'required|integer|min:1|max:5',
            'punctuality' => 'required|integer|min:1|max:5',
            'teamwork' => 'required|integer|min:1|max:5',
            'technical_skill' => 'required|integer|min:1|max:5',
        ]);

        $user = User::findOrFail($request->user_id);

        $totalScore = $request->sop_compliance + $request->hygiene + $request->punctuality + $request->teamwork + $request->technical_skill;
        $bonusAmount = ($totalScore / 25) * ($user->max_variable_amount ?? 0);

        PerformanceReview::create([
            'user_id' => $request->user_id,
            'reviewer_id' => auth()->id(),
            'month' => $request->month,
            'year' => $request->year,
            'sop_compliance' => $request->sop_compliance,
            'hygiene' => $request->hygiene,
            'punctuality' => $request->punctuality,
            'teamwork' => $request->teamwork,
            'technical_skill' => $request->technical_skill,
            'total_score' => $totalScore,
            'bonus_amount' => $bonusAmount,
            'comments' => $request->comments,
        ]);

        return redirect()->route('admin.performance.index')->with('success', 'Performance review submitted successfully.');
    }
}
