<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Project;
use App\Models\Visitor;
use App\Services\VisitorAnalytics;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/** Pengunjung terlacak: daftar terfilter, analitik trafik, dan drawer detail. */
class VisitorController extends Controller
{
    private const SORTABLE = ['id', 'last_seen_at', 'created_at', 'name', 'customer_code', 'conversations_count', 'ip_address'];

    private const PER_PAGE = [15, 25, 50, 100];

    /** GET /admin/visitors */
    public function index(Request $request, VisitorAnalytics $analytics): View
    {
        $projects = Project::where('tenant_id', $request->user()->tenant_id)->get();
        $sort = in_array($request->input('sort'), self::SORTABLE, true) ? $request->input('sort') : 'last_seen_at';
        $dir = strtolower($request->input('dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int) $request->input('per_page'), self::PER_PAGE, true) ? (int) $request->input('per_page') : 25;

        $visitors = Visitor::whereIn('project_id', $projects->modelKeys())
            ->filter($request->only(['search', 'project_id', 'status', 'device', 'browser', 'os', 'date_range']))
            // 3 tiket terbaru per visitor via window function (bukan memuat semua tiket).
            ->with(['project', 'contact', 'conversations' => fn ($q) => $q->latest('id')->limit(3)])
            ->withCount('conversations')
            ->orderBy($sort, $dir)
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $visitorStats = $analytics->summary($projects);
        $chattingVisitorsCount = $analytics->chattingCount($projects);
        $trafficStats = $analytics->traffic($projects, $request->input('chart_range'), (int) $visitorStats->total_visitors, $chattingVisitorsCount);

        return view('admin.visitors', compact('visitors', 'projects', 'visitorStats', 'chattingVisitorsCount', 'trafficStats'));
    }

    /** GET /admin/visitors/{id} — detail jejak pengunjung (drawer AJAX). */
    public function show(Request $request, string $id): JsonResponse
    {
        $visitor = Visitor::whereHas('project', fn ($q) => $q->where('tenant_id', $request->user()->tenant_id))
            ->with(['project', 'contact', 'conversations' => fn ($q) => $q->with('latestMessage')->latest('id')])
            ->findOrFail($id);

        return $this->ok([
            'id' => $visitor->id,
            'customer_code' => $visitor->customer_code_formatted,
            'name' => $visitor->name ?: 'Tamu Tanpa Nama',
            'display_name' => $visitor->display_name,
            'email' => $visitor->email,
            'visitor_uuid' => $visitor->visitor_uuid,
            'ip_address' => $visitor->ip_address,
            'user_agent' => $visitor->user_agent,
            'device_type' => $visitor->device_type,
            'browser_name' => $visitor->browser_name,
            'os_name' => $visitor->os_name,
            'is_online' => $visitor->is_online,
            'first_seen' => $visitor->created_at?->format('d M Y, H:i') ?? '-',
            'last_seen' => $visitor->last_seen_at?->format('d M Y, H:i:s') ?? '-',
            'last_seen_human' => $visitor->last_seen_at?->diffForHumans() ?? '-',
            'project' => [
                'id' => $visitor->project?->id,
                'name' => $visitor->project?->name ?? 'Default Project',
                'slug' => $visitor->project?->slug ?? '',
            ],
            'contact' => $visitor->contact?->only(['name', 'email', 'phone', 'custom_attributes']),
            'conversations' => $visitor->conversations->map(fn (Conversation $c) => [
                'id' => $c->id,
                'status' => $c->status,
                'channel' => $c->channel,
                'page_url' => $c->page_url,
                'page_title' => $c->page_title,
                'last_message_at' => $c->last_message_at?->format('d M Y, H:i'),
                'last_message_preview' => $c->last_message_preview ?? ($c->latestMessage ? Str::limit(strip_tags($c->latestMessage->content), 80) : 'Belum ada pesan'),
                'inbox_url' => route('admin.inbox', ['id' => $c->id]),
            ]),
        ]);
    }
}
