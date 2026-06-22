@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

@php
    $dashboard_url = View::getSection('dashboard_url') ?? config('adminlte.dashboard_url', 'home');
    if (config('adminlte.use_route_url', false)) {
        $dashboard_url = $dashboard_url ? route($dashboard_url) : '';
    } else {
        $dashboard_url = $dashboard_url ? url($dashboard_url) : '';
    }
    $auth_user = auth()->user();
    $user_role = $auth_user?->roles->first()?->name ?? 'User';
@endphp

<a href="{{ $dashboard_url }}"
    @if($layoutHelper->isLayoutTopnavEnabled())
        class="navbar-brand {{ config('adminlte.classes_brand') }}"
    @else
        class="brand-link {{ config('adminlte.classes_brand') }}"
    @endif>

    {{-- Brand Icon Circle --}}
    <div class="brand-image-wrapper" style="display: inline-flex; align-items: center; margin-right: 10px;">
        <div class="brand-icon-circle" style="
            background: linear-gradient(135deg, #22c55e, #16a34a);
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(34, 197, 94, 0.3);
        ">
            <i class="fas fa-running"></i>
        </div>
    </div>

    {{-- Brand Text Section --}}
    <div class="brand-text-wrapper" style="display: inline-flex; flex-direction: column; min-width: 0;">
        <span class="brand-text font-weight-bold {{ config('adminlte.classes_brand_text') }}" style="
            font-size: 1.05rem;
            color: #ffffff;
            font-weight: 700;
            margin: 0;
            line-height: 1.2;
        ">
            {!! config('adminlte.logo', '<b>TPSR</b>-PJKR') !!}
        </span>
        
        {{-- User Info - Hidden on collapse with CSS --}}
        <span class="brand-user-info" style="
            font-size: 0.75rem;
            color: #8fa3b8;
            font-weight: 500;
            margin-top: 2px;
            line-height: 1.2;
        " data-hide-on-collapse="true">

            {{ ($user_role == 'guru') ? "Guru TPSR PJOK" : $user_role }}
        </span>
    </div>

</a>
