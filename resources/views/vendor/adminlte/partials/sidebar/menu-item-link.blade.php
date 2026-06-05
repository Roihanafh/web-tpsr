<li @isset($item['id']) id="{{ $item['id'] }}" @endisset class="nav-item">

    @php
        $isFirstItem = ($item['text'] ?? '') === 'Dashboard';
        $customClass = $isFirstItem ? 'nav-link-dashboard' : '';
    @endphp

    <a class="nav-link {{ $item['class'] }} {{ $customClass }} @isset($item['shift']) {{ $item['shift'] }} @endisset"
       href="{{ $item['href'] }}" @isset($item['target']) target="{{ $item['target'] }}" @endisset
       {!! $item['data-compiled'] ?? '' !!}>

        <i class="nav-icon {{ $item['icon'] ?? 'far fa-fw fa-circle' }} {{
            isset($item['icon_color']) ? 'text-'.$item['icon_color'] : ''
        }}"></i>

        <p>
            {{ $item['text'] }}

            @isset($item['label'])
                <span class="badge badge-{{ $item['label_color'] ?? 'primary' }} right">
                    {{ $item['label'] }}
                </span>
            @endisset
        </p>

    </a>

</li>
