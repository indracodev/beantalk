@extends('layouts.admin')

@section('title', 'Live Inbox')

@section('breadcrumb')
    <a href="{{ route('admin.inbox') }}" class="{{ $activeConversation ? 'text-apple-textTertiary hover:text-apple-textPrimary transition text-[11.5px] font-normal no-loader' : 'font-medium text-apple-textPrimary text-[12px]' }}">Inbox Messages</a>
    @if($activeConversation && $activeConversation->project)
        <span class="text-apple-textTertiary text-[11px]">/</span>
        <span id="topbar-integration-name" class="font-medium text-apple-textPrimary text-[12px] flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full inline-block" style="background-color: {{ $activeConversation->project->widgetSetting->primary_color ?? '#0071E3' }};"></span>
            <span>{{ $activeConversation->project->name }}</span>
        </span>
    @endif
@endsection


@section('content')
    @php
        $isExplicitChat = request()->route('id') !== null;
    @endphp
    <div class="flex-1 flex w-full h-full overflow-hidden" id="inboxWorkspace">

        <!-- ========================================== -->
        <!-- PANE 1: Conversation List (Resizable)      -->
        @include('admin.partials.inbox.conversation-list')

        <!-- Resizer Handle 1 (Between Conv List and Chat Feed) -->
        <div id="resizer-conv-chat" class="panel-resizer hidden md:block"
            title="Drag to resize conversation column (Double click to reset)"></div>

        <!-- ========================================== -->
        <!-- PANE 2: Active Chat Feed                   -->
        @if ($activeConversation)
        @php
            $activeDisplayName = $activeConversation->visitor->display_name ?? 'Tamu';
            $activeCustomerCode = $activeConversation->visitor->customer_code_formatted ?? 'CUS-0000';
            $activeInitials = strtoupper(
            substr(preg_replace('/[^a-zA-Z0-9]/', '', $activeConversation->visitor->name ?: 'Tamu'), 0, 2)
            );
            $activeSiteName = $activeConversation->project->name ?? 'Website';
            $activeProjectColor = $activeConversation->project->widgetSetting->primary_color ?? '#0071E3';
            $assignedStaffName = $activeConversation->assignedUser->name ?? 'Unassigned';
        @endphp
        @endif
        @include('admin.partials.inbox.thread')

        <!-- Resizer Handle 2 (Between Chat Feed and Customer Context) -->
        @if ($activeConversation)
            <div id="resizer-chat-context" class="panel-resizer hidden lg:block"
                title="Drag to resize customer context column (Double click to reset)"></div>

            <!-- Mobile Inspector Backdrop Overlay -->
            <div id="inspectorBackdrop" class="fixed inset-0 bg-black/30 backdrop-blur-xs z-30 lg:hidden hidden"
                onclick="closeInspectorMobile()"></div>

            <!-- ========================================== -->
            <!-- PANE 3: Customer Context Session           -->
            @include('admin.partials.inbox.customer-panel')
        @endif

        <!-- ========================================== -->
        <!-- RESOLVE TICKET MODAL WITH CLOSING GREETING -->
        @include('admin.partials.inbox.resolve-modal')

    </div>
@endsection

@push('scripts')
    <script>
        window.BT_INBOX = {{ \Illuminate\Support\Js::from([
            'activeConversationId' => $activeConversation?->id,
            'currentUserName' => Auth::user()->name,
            'initialLastMessageId' => (int) ($activeConversation?->messages->last()?->id ?? 0),
            'initialMaxTenantMessageId' => (int) ($maxMessageId ?? 0),
            'conversationStatus' => $activeConversation?->status ?? 'open',
            'listUrl' => route('admin.inbox', array_filter(['project_id' => request('project_id'), 'status' => request('status')])),
        ]) }};
    </script>
    <script src="@asset('js/pages/inbox-page.js')"></script>
    <script src="@asset('js/inbox/01-helpers-audio.js')"></script>
    <script src="@asset('js/inbox/02-feed.js')"></script>
    <script src="@asset('js/inbox/03a-thread-polling.js')"></script>
    <script src="@asset('js/inbox/03b-send-reply-bot.js')"></script>
    <script src="@asset('js/inbox/04-ticket-controls.js')"></script>
@endpush
