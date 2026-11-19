@extends('layouts.admin')

@section('title', 'Connected Channels & Integrations')
@section('header_title', 'Connected Channels & Integrations')

@section('content')
<div class="flex-1 overflow-y-auto p-3 sm:p-4 md:p-6 pb-20 md:pb-6 flex flex-col gap-4 sm:gap-5 bg-apple-canvas/30 w-full">
    <!-- Header with Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-apple-border gap-2.5">
        <div>
            <h2 class="text-[16px] sm:text-[17px] font-semibold text-apple-textPrimary tracking-tight">Connected Channels &amp; Integrasi Web</h2>
            <p class="text-[11.5px] sm:text-[12px] text-apple-textSecondary">Deploy unified chat across Shopify, WooCommerce, or custom websites with a 1-line script tag.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" onclick="openModal('modalNewIntegration')" class="inline-flex items-center gap-1.5 bg-apple-blue hover:bg-apple-blueHover text-white px-3 py-1.5 rounded-lg text-[11.5px] sm:text-[12px] font-medium transition shadow-apple-sm cursor-pointer">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                <span>Connect Website</span>
            </button>
            <a href="/demo-store.html" target="_blank" class="inline-flex items-center gap-1.5 bg-white border border-apple-border hover:bg-apple-canvas text-apple-textPrimary px-2.5 py-1.5 rounded-lg text-[11.5px] sm:text-[12px] font-medium transition shadow-2xs">
                <svg class="w-3.5 h-3.5 text-apple-textSecondary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                    <polyline points="15 3 21 3 21 9"></polyline>
                    <line x1="10" y1="14" x2="21" y2="3"></line>
                </svg>
                <span>Demo Store</span>
            </a>
        </div>
    </div>

    @include('admin.partials.integrations.cards')
</div>

@include('admin.partials.integrations.create-modal')
@endsection

@push('scripts')
<script src="@asset('js/pages/integrations.js')"></script>
@endpush

