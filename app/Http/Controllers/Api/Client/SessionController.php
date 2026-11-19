<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Api\Client\Concerns\ResolvesVisitorConversation;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Visitor;
use App\Models\WidgetSetting;
use App\Services\BusinessHoursService;
use App\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SessionController extends Controller
{
    use ResolvesVisitorConversation;

    public function __construct(
        private ConversationService $conversations,
        private BusinessHoursService $businessHours,
    ) {}

    /** POST /api/v1/client/session/init — mulai/lanjutkan sesi pengunjung + tema widget. */
    public function init(Request $request): JsonResponse
    {
        $data = $request->validate([
            'visitor_uuid' => 'nullable|string|max:36',
            'name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'page_url' => 'nullable|string|max:500',
            'page_title' => 'nullable|string|max:255',
        ]);

        $project = $this->project($request);
        $visitor = $this->conversations->getOrCreateVisitor(
            $project,
            $data['visitor_uuid'] ?? (string) Str::uuid(),
            $request->ip(),
            $request->userAgent(),
            $data['name'] ?? null,
            $data['email'] ?? null,
        );

        $active = $this->conversations->getActiveConversation($project, $visitor);
        $ws = $project->widgetSetting;

        return $this->ok([
            'visitor' => $visitor->toWidgetArray(),
            'conversation' => $active ? [
                'id' => $active->id,
                'status' => $active->status,
                'channel' => $active->channel,
                'channel_label' => $active->channel_label,
                'last_message_at' => $active->last_message_at?->toIso8601String(),
                'unread_visitor_count' => $active->unread_visitor_count,
                // Hidrasi riwayat langsung (catatan internal tidak pernah ke widget).
                'messages' => $active->messages()->where('is_internal_note', false)->take(100)->get()
                    ->map(fn (Message $m) => $m->toWidgetArray()),
            ] : null,
            'conversations' => $this->conversations->getVisitorConversations($project, $visitor)
                ->map(fn ($c) => $this->conversations->toWidgetSummary($c)),
            'project' => ['id' => $project->id, 'name' => $project->name],
            'widget' => WidgetSetting::widgetConfig($ws),
            'business_hours' => $ws ? $this->businessHours->getScheduleSummary($ws) : ['enabled' => false],
            'is_within_business_hours' => $ws ? $this->businessHours->isWithinBusinessHours($ws) : true,
        ]);
    }

    /** POST /api/v1/client/session/profile — perbarui nama/email pengunjung. */
    public function updateProfile(Request $request): JsonResponse
    {
        $data = $request->validate([
            'visitor_uuid' => 'required|string|max:36',
            'name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
        ]);

        if (empty($data['name']) && empty($data['email'])) {
            return $this->fail('VALIDATION_ERROR', 'Setidaknya nama atau email harus diisi.', 422);
        }

        $visitor = Visitor::with('contact')
            ->where('project_id', $this->project($request)->id)
            ->where('visitor_uuid', $data['visitor_uuid'])
            ->first();

        if (! $visitor) {
            return $this->fail('VISITOR_NOT_FOUND', 'Data pengunjung tidak ditemukan untuk project ini.', 404);
        }

        $visitor->update(array_filter([
            'name' => ! empty($data['name']) ? strip_tags(trim($data['name'])) : null,
            'email' => ! empty($data['email']) ? strtolower(trim($data['email'])) : null,
        ]));

        if (! empty($data['email']) && $visitor->contact) {
            $visitor->contact->update(['email' => $visitor->email]);
        }

        return $this->ok(['visitor' => $visitor->toWidgetArray()]);
    }
}
