<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Report;
use App\Models\Song;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:song,comment'],
            'id' => ['required', 'integer'],
            'reason' => ['required', 'in:copyright,inappropriate,spam,harassment,other'],
            'details' => ['nullable', 'string', 'max:2000'],
        ]);
        $model = $data['type'] === 'song' ? Song::findOrFail($data['id']) : Comment::findOrFail($data['id']);
        $duplicate = Report::where('user_id', $request->user()->id)->whereMorphedTo('reportable', $model)->where('status', 'open')->exists();
        if (!$duplicate) {
            $model->morphMany(Report::class, 'reportable')->create([
                'user_id' => $request->user()->id, 'reason' => $data['reason'], 'details' => $data['details'] ?? null,
            ]);
        }
        return back()->with('success', 'Báo cáo đã được gửi để quản trị viên xem xét.');
    }
}
