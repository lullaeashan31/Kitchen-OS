<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\ScheduleAssignment;
use App\Models\ScheduleRequirement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request, string $kitchen_slug)
    {
        $roles = Role::orderBy('name')->get();
        $staff = User::where('role', \App\Enums\UserRole::Staff)
            ->where('onboarding_status', 'active')
            ->with('jobRole')
            ->orderBy('name')
            ->get();

        $weekStart = $request->input('week', now()->startOfWeek()->format('Y-m-d'));
        $start = Carbon::parse($weekStart)->startOfWeek(); // Monday
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $days[] = $start->copy()->addDays($i);
        }

        $requirements = ScheduleRequirement::with('role')->get()->groupBy('day_of_week');
        $assignments = ScheduleAssignment::whereBetween('date', [$days[0]->toDateString(), $days[6]->toDateString()])
            ->with(['user', 'role'])
            ->get()
            ->groupBy(fn($a) => $a->date->format('Y-m-d') . '_' . $a->role_id . '_' . $a->slot_index);

        $nextWeek = $start->copy()->addWeek()->format('Y-m-d');
        $prevWeek = $start->copy()->subWeek()->format('Y-m-d');

        return view('admin.schedule.index', compact(
            'roles',
            'staff',
            'days',
            'requirements',
            'assignments',
            'nextWeek',
            'prevWeek',
            'start'
        ));
    }

    public function saveRequirements(Request $request, string $kitchen_slug)
    {
        $request->validate([
            'requirements' => 'required|array',
            'requirements.*.day_of_week' => 'required|integer|min:1|max:7',
            'requirements.*.role_id' => 'required|exists:roles,id',
            'requirements.*.required_count' => 'required|integer|min:0|max:50',
        ]);
        ScheduleRequirement::query()->delete();
        foreach ($request->requirements as $r) {
            if (($r['required_count'] ?? 0) > 0) {
                ScheduleRequirement::create([
                    'day_of_week' => (int) $r['day_of_week'],
                    'role_id' => (int) $r['role_id'],
                    'required_count' => (int) $r['required_count'],
                ]);
            }
        }
        return redirect()->route('admin.schedule.index')->with('success', 'Weekly requirements saved.');
    }

    public function saveAssignments(Request $request, string $kitchen_slug)
    {
        $request->validate([
            'assignments' => 'nullable|array',
            'assignments.*.date' => 'required|date',
            'assignments.*.role_id' => 'required|exists:roles,id',
            'assignments.*.slot_index' => 'required|integer|min:1',
            'assignments.*.user_id' => 'nullable|exists:users,id',
        ]);
        $dates = collect($request->assignments ?? [])->pluck('date')->unique()->filter();
        if ($dates->isNotEmpty()) {
            ScheduleAssignment::whereBetween('date', [$dates->min(), $dates->max()])->delete();
        }
        foreach ($request->assignments ?? [] as $a) {
            if (empty($a['user_id'])) {
                continue;
            }
            ScheduleAssignment::create([
                'date' => $a['date'],
                'role_id' => $a['role_id'],
                'slot_index' => (int) $a['slot_index'],
                'user_id' => $a['user_id'],
            ]);
        }
        return redirect()->back()->with('success', 'Assignments saved.');
    }
}
