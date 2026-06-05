<li @isset($item['id']) id="{{ $item['id'] }}" @endisset class="nav-header {{ $item['class'] ?? '' }}" style="
    color: #6b7a8f !important;
    text-transform: uppercase !important;
    font-size: 0.75rem !important;
    font-weight: 700 !important;
    letter-spacing: 0.5px !important;
    padding: 1.25rem 1rem 0.75rem 1rem !important;
    margin: 0 !important;
">

    {{ is_string($item) ? $item : $item['header'] }}

</li>
