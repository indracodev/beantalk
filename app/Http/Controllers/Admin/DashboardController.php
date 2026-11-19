<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\Dashboard\DashboardMetrics;
use App\Services\Dashboard\DashboardPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Executive Dashboard + ekspor CSV. */
class DashboardController extends Controller
{
    /** GET /admin, /admin/dashboard */
    public function index(Request $request, DashboardMetrics $metrics): View
    {
        return view('admin.dashboard', $metrics->build($request->user()->tenant_id, DashboardPeriod::fromRequest($request)));
    }

    /** GET /admin/dashboard/export — CSV percakapan (maks 3000 baris, di-stream per 500). */
    public function export(Request $request): StreamedResponse
    {
        $custom = $request->filled(['start_date', 'end_date']);
        $period = DashboardPeriod::fromRequest($request);
        $filename = $period->isCustom
            ? "beantalk-telemetry-{$period->start->format('Ymd')}-to-{$period->end->format('Ymd')}.csv"
            : 'beantalk-telemetry-'.$request->input('period', '7d').'-'.now()->format('Y-m-d').'.csv';

        $query = Conversation::where('tenant_id', $request->user()->tenant_id)
            ->whereHas('messages')
            ->when($custom && $period->isCustom, fn ($q) => $q->whereBetween('created_at', [$period->start, $period->end]))
            ->with(['visitor', 'project', 'assignedUser'])
            ->orderByDesc('id')
            ->limit(3000);

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Customer Code', 'Visitor Name', 'Channel', 'Project', 'Status', 'Assigned To', 'Created At', 'Last Message Preview']);
            foreach ($query->get() as $c) {
                fputcsv($out, [
                    $c->id,
                    $c->visitor?->customer_code_formatted ?? '-',
                    $c->visitor?->display_name ?? 'Tamu',
                    $c->channel_label,
                    $c->project?->name ?? '-',
                    ucfirst($c->status),
                    $c->assignedUser?->name ?? 'Unassigned',
                    $c->created_at?->format('Y-m-d H:i:s') ?? '-',
                    $c->last_message_preview ?? '',
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
