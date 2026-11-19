{{-- Bar pagination bersama (Visitors, Logs). --}}
@props(['paginator', 'noun'])

<!-- 3. COMPREHENSIVE RESPONSIVE PAGINATION BAR -->
<div class="px-4 py-3 border-t border-apple-border flex flex-col sm:flex-row items-center justify-between gap-3 text-[11.5px] text-apple-textSecondary bg-apple-canvas/40">
    <!-- Left Info -->
    <div class="text-center sm:text-left">
        @if($paginator->total() > 0)
            Menampilkan <span class="font-semibold text-apple-textPrimary">{{ $paginator->firstItem() }}</span> &ndash;
            <span class="font-semibold text-apple-textPrimary">{{ $paginator->lastItem() }}</span> dari
            <span class="font-semibold text-apple-textPrimary">{{ number_format($paginator->total()) }}</span> {{ $noun }}
        @else
            Menampilkan <span class="font-semibold text-apple-textPrimary">0</span> {{ $noun }}
        @endif
    </div>

    <!-- Right Pagination Navigation -->
    @if($paginator->hasPages())
        <div class="flex items-center gap-1 flex-wrap justify-center">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="px-2.5 py-1 rounded-md border border-apple-border opacity-40 cursor-not-allowed bg-white font-medium text-[11px]">&larr; Prev</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="px-2.5 py-1 rounded-md border border-apple-border hover:bg-white text-apple-textPrimary transition shadow-2xs font-medium text-[11px]">&larr; Prev</a>
            @endif

            {{-- Page Numbers (Compact Window on Tablet & Desktop) --}}
            <div class="hidden sm:flex items-center gap-1">
                @php
                    $start = max(1, $paginator->currentPage() - 2);
                    $end = min($paginator->lastPage(), $paginator->currentPage() + 2);
                @endphp

                @if($start > 1)
                    <a href="{{ $paginator->url(1) }}" class="w-7 h-7 flex items-center justify-center rounded-md border border-apple-border hover:bg-white text-apple-textPrimary transition text-[11px]">1</a>
                    @if($start > 2)
                        <span class="px-1 text-apple-textTertiary">...</span>
                    @endif
                @endif

                @for ($page = $start; $page <= $end; $page++)
                    @if ($page == $paginator->currentPage())
                        <span class="w-7 h-7 flex items-center justify-center rounded-md bg-apple-textPrimary text-white font-semibold text-[11px] shadow-2xs">{{ $page }}</span>
                    @else
                        <a href="{{ $paginator->url($page) }}" class="w-7 h-7 flex items-center justify-center rounded-md border border-apple-border hover:bg-white text-apple-textPrimary transition text-[11px]">{{ $page }}</a>
                    @endif
                @endfor

                @if($end < $paginator->lastPage())
                    @if($end < $paginator->lastPage() - 1)
                        <span class="px-1 text-apple-textTertiary">...</span>
                    @endif
                    <a href="{{ $paginator->url($paginator->lastPage()) }}" class="w-7 h-7 flex items-center justify-center rounded-md border border-apple-border hover:bg-white text-apple-textPrimary transition text-[11px]">{{ $paginator->lastPage() }}</a>
                @endif
            </div>

            {{-- Mobile Page Counter Badge --}}
            <span class="sm:hidden px-2 text-[11px] font-medium text-apple-textTertiary">
                Hal {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </span>

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="px-2.5 py-1 rounded-md border border-apple-border hover:bg-white text-apple-textPrimary transition shadow-2xs font-medium text-[11px]">Next &rarr;</a>
            @else
                <span class="px-2.5 py-1 rounded-md border border-apple-border opacity-40 cursor-not-allowed bg-white font-medium text-[11px]">Next &rarr;</span>
            @endif
        </div>
    @endif
</div>
