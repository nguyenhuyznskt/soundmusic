<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlayEvent;
use App\Models\Report;
use App\Models\Song;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'users' => User::count(), 'artists' => User::where('role', 'artist')->count(),
            'songs' => Song::count(), 'pending' => Song::where('status', 'pending')->count(),
            'plays' => Song::sum('play_count'), 'reports' => Report::where('status', 'open')->count(),
        ];
        $topSongs = Song::with('user')->orderByDesc('play_count')->limit(10)->get();
        $recentUsers = User::latest()->limit(8)->get();
        $playsByDay = PlayEvent::selectRaw('DATE(played_at) as day, COUNT(*) as total')
            ->where('played_at', '>=', now()->subDays(13)->startOfDay())
            ->groupBy(DB::raw('DATE(played_at)'))->orderBy('day')->get();
        return view('admin.dashboard', compact('stats', 'topSongs', 'recentUsers', 'playsByDay'));
    }
}
