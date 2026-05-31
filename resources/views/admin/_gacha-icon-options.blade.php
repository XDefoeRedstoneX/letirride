{{-- <option> list for the prize icon picker, grouped by category.
     Wrap in your own <select> (bind x-model as needed). --}}
<option value="">— Auto (by reward type / subcategory) —</option>
@foreach($iconCategories as $catKey => $catLabel)
    @php $opts = $iconsByCategory[$catKey] ?? collect(); @endphp
    @if($opts->count())
        <optgroup label="{{ $catLabel }}">
            @foreach($opts as $ic)
                <option value="{{ $ic->key }}">{{ $ic->label }}</option>
            @endforeach
        </optgroup>
    @endif
@endforeach
