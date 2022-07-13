

        
    <li >
        @if ($menu['map_id']=='all')
        <input type="hidden" name="menu[]" value="{{$menu['id']}}">

        @else                       
            <input type="checkbox" name="menu[]" value="{{$menu['id']}}{{$menu['map_id']!=0 ?','.$menu['map_id']:''}}" {{$menu['user_id'] != NULL?'checked':''}}>
            <label class="1custom-unchecked " >{{ $menu['name'] }}</label>
        @endif
    @isset($menu['children'])
        <ul class="">
            @each('layouts.menulist', $menu['children'], 'menu')
        </ul>
    @endisset
    </li>