<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $reports = Report::with(['user', 'reportable', 'resolver'])->when($request->status, fn ($q, $s) => $q->where('status', $s))->latest()->paginate(20)->withQueryString();
        return view('admin.reports', compact('reports'));
    }
    public function resolve(Request $request, Report $report): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:reviewing,resolved,dismissed'], 'admin_note' => ['nullable', 'string', 'max:2000']]);
        $report->update(['status' => $data['status'], 'admin_note' => $data['admin_note'] ?? null, 'resolved_by' => $request->user()->id, 'resolved_at' => in_array($data['status'], ['resolved','dismissed'], true) ? now() : null]);
        return back()->with('success', 'Đã cập nhật báo cáo.');
    }
}
