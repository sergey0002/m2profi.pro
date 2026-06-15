@if ($paginator->hasPages())
    <div class="pagination-container">
        <ul class="pagination">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link">«</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">«</a>
                </li>
            @endif

            {{-- Custom Pagination Elements: First 3 + Last 2 --}}
            @php
                $totalPages = $paginator->lastPage();
                $currentPage = $paginator->currentPage();
                
                // Build page array
                $pages = [];
                
                if ($totalPages <= 5) {
                    // Show all pages if 5 or fewer
                    $pages = range(1, $totalPages);
                } else {
                    // Always show first 3 pages
                    $pages = [1, 2, 3];
                    
                    // Add ellipsis if there's a gap
                    if ($totalPages > 5) {
                        $pages[] = '...';
                    }
                    
                    // Add last 2 pages
                    $pages[] = $totalPages - 1;
                    $pages[] = $totalPages;
                }
            @endphp

            @foreach ($pages as $page)
                @if ($page === '...')
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link">{{ $page }}</span>
                    </li>
                @elseif ($page == $currentPage)
                    <li class="page-item active" aria-current="page">
                        <span class="page-link">{{ $page }}</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->url($page) }}">{{ $page }}</a>
                    </li>
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">»</a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link">»</span>
                </li>
            @endif
        </ul>
    </div>
@endif

<style>
.pagination {
    display: flex;
    padding-left: 0;
    list-style: none;
    border-radius: 0.25rem;
    margin: 0;
}
.page-item {
    margin: 0 2px;
}
.page-link {
    position: relative;
    display: block;
    padding: 0.5rem 0.75rem;
    margin-left: -1px;
    line-height: 1.25;
    color: #333;
    background-color: #fff;
    border: 1px solid #dee2e6;
    text-decoration: none;
    border-radius: 4px;
}
.page-item.active .page-link {
    z-index: 3;
    color: #fff;
    background-color: #00A896;
    border-color: #00A896;
}
.page-item.disabled .page-link {
    color: #6c757d;
    pointer-events: none;
    cursor: auto;
    background-color: #fff;
    border-color: #dee2e6;
}
.page-link:hover {
    background-color: #e9ecef;
    border-color: #dee2e6;
    color: #00A896;
}
</style>
