@foreach($nav_items as $nav)
<a href="{{ $nav['route'] }}" class="text-center {{ $nav['active'] ? 'active' : '' }}">
    <span class="fa fa-3x fa-{{ $nav['icon'] }}"></span>
    <span class="nav-text">{{ $nav['text'] }}</span>
</a>
@endforeach
