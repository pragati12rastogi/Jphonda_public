   <li class="@isset($menu['child']) treeview @endisset @if($menu['link']==Request::getRequestUri()) active @else {{$menu['link']}} @endif">
    <a href="{{ $menu['link'] }}" style="overflow:hidden;text-overflow:ellipsis">
        <i class=""></i> 
        <span title="{{ $menu['name'] }}">{{ $menu['name'] }}</span>
        @isset($menu['child'])
        <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
        </span> 
        @endisset
    </a>
    @isset($menu['child'])
        <ul class="treeview-menu">
            @each('layouts.sidebar', $menu['child'], 'menu')
        </ul>
    @endisset
    </li>
