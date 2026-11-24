@if ($paginator->hasPages())
    <nav class="puihaha-pagination" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="pagination-arrow is-disabled" aria-disabled="true" aria-label="Previous page">‹</span>
        @else
            <a class="pagination-arrow" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page">‹</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="pagination-ellipsis">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="pagination-page is-current" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="pagination-page" href="{{ $url }}" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a class="pagination-arrow" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page">›</a>
        @else
            <span class="pagination-arrow is-disabled" aria-disabled="true" aria-label="Next page">›</span>
        @endif
    </nav>
@endif
