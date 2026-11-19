<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

class Conversation extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'project_id',
        'visitor_id',
        'contact_id',
        'assigned_user_id',
        'status',
        'is_bot_active',
        'bot_handoff_at',
        'priority',
        'channel',
        'page_url',
        'page_title',
        'last_message_at',
        'last_message_preview',
        'unread_agent_count',
        'unread_visitor_count',
        'telegram_topic_id',
        'telegram_notif_sent',
    ];

    protected $appends = ['channel_label', 'last_message_time'];

    protected function casts(): array
    {
        return [
            'is_bot_active' => 'boolean',
            'bot_handoff_at' => 'datetime',
            'last_message_at' => 'datetime',
            'unread_agent_count' => 'integer',
            'unread_visitor_count' => 'integer',
            'telegram_notif_sent' => 'boolean',
        ];
    }

    protected function channelLabel(): Attribute
    {
        return Attribute::get(fn () => match ($this->channel) {
            'whatsapp' => 'WhatsApp',
            'instagram' => 'Instagram',
            'facebook' => 'Messenger',
            default => 'Web Chat',
        });
    }

    protected function lastMessageTime(): Attribute
    {
        return Attribute::get(function () {
            $date = $this->last_message_at;

            return match (true) {
                ! $date => '-',
                $date->isToday() => $date->format('H:i'),
                $date->isYesterday() => 'Kemarin',
                $date->isCurrentYear() => $date->format('d/m'),
                default => $date->format('d/m/Y'),
            };
        });
    }

    /**
     * Daftar inbox CS: hanya tiket berpesan, filter status (open|mine|closed|all), project, pencarian.
     *
     * @param  array{status?: ?string, project_id?: mixed, search?: ?string}  $filters
     */
    #[Scope]
    protected function inbox(Builder $query, User $user, array $filters): void
    {
        $status = $filters['status'] ?? 'open';
        $search = trim((string) ($filters['search'] ?? ''));

        $query->where('tenant_id', $user->tenant_id)
            ->whereHas('messages')
            ->with(['visitor', 'project.widgetSetting', 'assignedUser', 'latestMessage'])
            ->when($filters['project_id'] ?? null, fn ($q, $id) => $q->where('project_id', $id))
            ->when($status === 'mine', fn ($q) => $q->where('assigned_user_id', $user->id)->where('status', 'open'))
            ->when($status === 'closed', fn ($q) => $q->where('status', 'closed'))
            ->when(! in_array($status, ['mine', 'closed', 'all'], true), fn ($q) => $q->where('status', 'open'))
            ->when($search !== '', fn ($q) => $q->where(fn ($sub) => $sub
                ->whereHas('visitor', fn ($v) => $v->whereLike('name', "%{$search}%")->orWhereLike('customer_code', "%{$search}%"))
                ->orWhereHas('contact', fn ($c) => $c->whereLike('name', "%{$search}%")->orWhereLike('email', "%{$search}%")->orWhereLike('phone', "%{$search}%"))
                ->orWhereLike('last_message_preview', "%{$search}%")))
            ->orderByRaw('COALESCE(last_message_at, updated_at, created_at) DESC');
    }

    /**
     * Belum dibaca oleh $user (status baca per-user):
     * - masih menunggu balasan (balasan siapa pun membuatnya terbaca untuk semua),
     * - belum dipegang CS lain (tanpa assignee atau assignee = $user),
     * - $user belum membuka pesan pengunjung terakhir. Superadmin tidak pernah tercatat membaca
     *   (lihat markReadBy), jadi baginya chat tetap "baru" sampai ada yang membalas.
     */
    #[Scope]
    protected function unreadFor(Builder $query, User $user): void
    {
        $query->where('conversations.unread_agent_count', '>', 0)
            ->where(fn ($q) => $q->whereNull('conversations.assigned_user_id')->orWhere('conversations.assigned_user_id', $user->id))
            ->whereNotExists(fn ($q) => $q->selectRaw('1')
                ->from('conversation_participants as cp')
                ->whereColumn('cp.conversation_id', 'conversations.id')
                ->where('cp.user_id', $user->id)
                ->whereRaw("cp.last_read_message_id >= (SELECT MAX(m.id) FROM messages m WHERE m.conversation_id = conversations.id AND m.sender_type = 'visitor')"));
    }

    /** ID percakapan (dari daftar $ids) yang belum dibaca $user — 1 query untuk seluruh daftar. */
    public static function unreadIdsFor(User $user, array $ids): Collection
    {
        return $ids ? static::whereIn('id', $ids)->unreadFor($user)->pluck('id') : collect();
    }

    /** Catat bahwa $user sudah membuka percakapan sampai pesan terakhir. Superadmin dikecualikan. */
    public function markReadBy(User $user): void
    {
        if ($user->isSuperAdmin() || $this->unread_agent_count <= 0) {
            return;
        }

        ConversationParticipant::upsert([[
            'conversation_id' => $this->id,
            'user_id' => $user->id,
            'last_read_message_id' => (int) Message::where('conversation_id', $this->id)->max('id'),
            'joined_at' => now(),
        ]], ['conversation_id', 'user_id'], ['last_read_message_id']);
    }

    /** Counter sidebar inbox dalam 1 query agregasi. */
    public static function inboxCounts(User $user, mixed $projectId = null): array
    {
        $row = static::where('tenant_id', $user->tenant_id)
            ->whereHas('messages')
            ->when($projectId, fn ($q, $id) => $q->where('project_id', $id))
            ->selectRaw("COUNT(*) AS total_all,
                SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) AS total_open,
                SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) AS total_closed,
                SUM(CASE WHEN assigned_user_id = ? AND status = 'open' THEN 1 ELSE 0 END) AS total_mine", [$user->id])
            ->toBase()
            ->first();

        return [
            'all' => (int) ($row->total_all ?? 0),
            'open' => (int) ($row->total_open ?? 0),
            'closed' => (int) ($row->total_closed ?? 0),
            'mine' => (int) ($row->total_mine ?? 0),
        ];
    }

    /** Kartu percakapan untuk feed polling inbox. */
    public function toInboxCard(int $visitorTickets = 1, bool $isUnread = false): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'project_color' => $this->project?->widgetSetting?->primary_color ?? '#0071E3',
            'visitor_id' => $this->visitor_id,
            'customer_name' => $this->visitor?->display_name ?? 'Tamu',
            'customer_code' => $this->visitor?->customer_code,
            'initials' => $this->visitor?->initials ?? 'TM',
            'channel_label' => $this->channel_label,
            'project_name' => $this->project?->name ?? 'Website',
            'last_message_preview' => $this->last_message_preview ?: 'Percakapan baru diinisialisasi...',
            'last_message_at' => $this->last_message_at?->toIso8601String(),
            'last_message_time' => $this->last_message_time,
            'unread_agent_count' => $isUnread ? (int) $this->unread_agent_count : 0,
            'is_unread' => $isUnread,
            'status' => $this->status,
            'is_bot_active' => (bool) ($this->is_bot_active ?? true),
            'assigned_user_id' => $this->assigned_user_id,
            'visitor_tickets_count' => $visitorTickets,
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withPivot('joined_at', 'last_read_message_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('id');
    }

    /** Pesan terakhir (1 subquery MAX(id), bukan memuat semua pesan saat eager load). */
    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /** Pesan terakhir yang boleh dilihat pengunjung (tanpa catatan internal). */
    public function latestPublicMessage(): HasOne
    {
        return $this->hasOne(Message::class)->ofMany(['id' => 'max'], fn ($q) => $q->where('is_internal_note', false));
    }
}
