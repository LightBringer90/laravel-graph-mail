<?php

namespace ProgressiveStudios\GraphMail\Http\Controllers\Ui;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use ProgressiveStudios\GraphMail\Models\OutboundMail;

class DashboardController extends Controller
{
    /**
     * HTML dashboard view
     */
    public function index()
    {
        $counts = [
            'queued' => OutboundMail::where('status', 'queued')->count(),
            'sent'   => OutboundMail::where('status', 'sent')->count(),
            'failed' => OutboundMail::where('status', 'failed')->count(),
        ];

        $last24Raw = OutboundMail::where('created_at', '>=', now()->subDay())
            ->selectRaw('status, count(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $last24 = [
            'queued' => (int)($last24Raw['queued'] ?? 0),
            'sent'   => (int)($last24Raw['sent'] ?? 0),
            'failed' => (int)($last24Raw['failed'] ?? 0),
        ];

        $recent = OutboundMail::latest()->orderByDesc('id')->take(10)->get();

        return view('graph-mail::graph-mail.dashboard', compact('counts', 'last24', 'recent'));
    }

    /**
     * JSON endpoint used by the auto-refreshing JS on the dashboard.
     */
    public function data(Request $request)
    {
        $counts = [
            'queued' => OutboundMail::where('status', 'queued')->count(),
            'sent'   => OutboundMail::where('status', 'sent')->count(),
            'failed' => OutboundMail::where('status', 'failed')->count(),
        ];

        $last24Raw = OutboundMail::where('created_at', '>=', now()->subDay())
            ->selectRaw('status, count(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $last24 = [
            'queued' => (int)($last24Raw['queued'] ?? 0),
            'sent'   => (int)($last24Raw['sent'] ?? 0),
            'failed' => (int)($last24Raw['failed'] ?? 0),
        ];

        $recent = OutboundMail::latest()->take(10)->get()->map(function ($m) {
            return [
                'id'            => $m->id,
                'subject'       => $m->subject,
                'status'        => $m->status,
                'status_label'  => ucfirst($m->status),
                'created_human' => $m->created_at->diffForHumans(),
                'sent_at'       => $m->sent_at?->format('Y-m-d H:i:s'),
                'show_url'      => route('graphmail.mails.show', $m),
            ];
        });

        return response()->json([
            'counts' => $counts,
            'last24' => $last24,
            'recent' => $recent,
        ]);
    }
}
