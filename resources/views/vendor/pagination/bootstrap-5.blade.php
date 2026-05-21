@if ($paginator->hasPages())
    <nav class="d-flex justify-items-center justify-content-between w-100 align-items-center">
        <!-- Mobile View (Next / Previous buttons) -->
        <div class="d-flex justify-content-between flex-fill d-sm-none w-100">
            <ul class="pagination w-100 d-flex justify-content-between align-items-center mb-0">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link text-decoration-none d-flex align-items-center gap-1"><i class="bi bi-chevron-left" style="font-size: 0.8rem; font-weight: 700;"></i> Previous</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link text-decoration-none d-flex align-items-center gap-1" href="{{ $paginator->previousPageUrl() }}" rel="prev"><i class="bi bi-chevron-left" style="font-size: 0.8rem; font-weight: 700;"></i> Previous</a>
                    </li>
                @endif

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link text-decoration-none d-flex align-items-center gap-1" href="{{ $paginator->nextPageUrl() }}" rel="next">Next <i class="bi bi-chevron-right" style="font-size: 0.8rem; font-weight: 700;"></i></a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link text-decoration-none d-flex align-items-center gap-1">Next <i class="bi bi-chevron-right" style="font-size: 0.8rem; font-weight: 700;"></i></span>
                    </li>
                @endif
            </ul>
        </div>

        <!-- Desktop / Tablet View -->
        <div class="d-none flex-sm-fill d-sm-flex align-items-sm-center justify-content-sm-between flex-wrap gap-3 w-100">
            <div style="flex-shrink: 0; min-width: 250px;">
                <p class="small text-muted mb-0 font-outfit" style="font-family: 'Outfit', sans-serif;">
                    {!! __('Showing') !!}
                    <span class="fw-bold text-dark">{{ $paginator->firstItem() }}</span>
                    {!! __('to') !!}
                    <span class="fw-bold text-dark">{{ $paginator->lastItem() }}</span>
                    {!! __('of') !!}
                    <span class="fw-bold text-dark">{{ $paginator->total() }}</span>
                    {!! __('results') !!}
                </p>
            </div>

            <div>
                <ul class="pagination mb-0">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                            <span class="page-link d-flex align-items-center gap-1" aria-hidden="true"><i class="bi bi-chevron-left" style="font-size: 0.8rem; font-weight: 700;"></i> Previous</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link d-flex align-items-center gap-1" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')"><i class="bi bi-chevron-left" style="font-size: 0.8rem; font-weight: 700;"></i> Previous</a>
                        </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                                @else
                                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <li class="page-item">
                            <a class="page-link d-flex align-items-center gap-1" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">Next <i class="bi bi-chevron-right" style="font-size: 0.8rem; font-weight: 700;"></i></a>
                        </li>
                    @else
                        <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                            <span class="page-link d-flex align-items-center gap-1" aria-hidden="true">Next <i class="bi bi-chevron-right" style="font-size: 0.8rem; font-weight: 700;"></i></span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
@endif
