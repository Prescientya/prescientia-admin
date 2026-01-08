@if ($paginator->hasPages())
<nav aria-label="Pagination" style="padding:12px 0;">
    <div style="display:flex;align-items:center;justify-content:center;gap:14px;flex-wrap:wrap;">
        <div style="color:#6c757d;font-size:0.95rem;">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</div>

        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span style="padding:8px 12px;border-radius:8px;border:1px solid #e9ecef;color:#adb5bd;background:#f8f9fa;">&laquo; Prev</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" style="padding:8px 12px;border-radius:8px;border:1px solid #e9ecef;color:#343a40;background:#fff;text-decoration:none;">&laquo; Prev</a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span style="padding:8px 10px;color:#6c757d;">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span style="padding:8px 12px;border-radius:8px;background:#111;color:#fff;font-weight:600;">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" style="padding:8px 12px;border-radius:8px;border:1px solid transparent;color:#111;background:#fff;text-decoration:none;">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" style="padding:8px 12px;border-radius:8px;border:1px solid #e9ecef;color:#343a40;background:#fff;text-decoration:none;">Next &raquo;</a>
            @else
                <span style="padding:8px 12px;border-radius:8px;border:1px solid #e9ecef;color:#adb5bd;background:#f8f9fa;">Next &raquo;</span>
            @endif

            {{-- Last Page Button --}}
            <a href="{{ $paginator->url($paginator->lastPage()) }}" style="padding:8px 12px;border-radius:8px;background:#111;color:#fff;text-decoration:none;margin-left:6px;">Last</a>
        </div>
    </div>
</nav>
@endif
