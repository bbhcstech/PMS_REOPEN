<option value=""></option>
@if($selectedLanguage && ! $languages->contains('code', $selectedLanguage))
    <option value="{{ $selectedLanguage }}" selected>{{ $selectedLanguage }}</option>
@endif
@foreach($languages as $language)
    <option value="{{ $language->code }}" @if($language->flag_url) data-flag="{{ $language->flag_url }}" @endif @selected($selectedLanguage === $language->code)>{{ $language->name }}</option>
@endforeach
