<details class="details">
    <summary> {{ $name }} </summary>
    @if(!$c->children->isEmpty())
    <ul class="nested-list">
        @foreach ($c->children as $x)
            @if(!$x->children->isEmpty())
                @include('sections.sub-section', ['c' => $x, 'name' => $x->name])
            @else
                <li class="nested-item"> {{ $x->name }} </li>
            @endif
        @endforeach
    </ul>
    @endif
</details>