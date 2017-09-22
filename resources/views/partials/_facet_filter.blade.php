@foreach($facets as $key => $text)
<div class="checkbox">
    <label for="{{ $facetName.$key }}">
        <input
                type="checkbox"
                name="{{ $facetName }}[]"
                id="{{ $facetName.$key }}"
                value="{{ $key }}"
                class="auto-submit"
                @if($request && $request->get($facetName))
                    {{ in_array($key, $request->get($facetName)) ? 'checked' : '' }}
                @endif
        >
        {{ $text }}
    </label>
</div>
@endforeach