@php
    $key = 'pdf_'.$kind;
    $assetKey = $key.'_asset_id';
    $imageKey = $key.'_image';
    $nameKey = $key.'_name';
    $id = 'recPdf'.ucfirst($kind).'Image';
    $oldAsset = old($assetKey);
    $selected = is_scalar($oldAsset) ? $assets->first(fn ($asset) => (string) $asset->id === (string) $oldAsset) : null;
    $oldName = old($nameKey);
    $nameCounts = $assets->countBy('name');
    $size = \App\Support\RecruitmentPdfBranding::WIDTH.' × '.$height;
    $error = $errors->first($assetKey) ?: ($errors->first($imageKey) ?: $errors->first($nameKey));
@endphp

<section class="rec-branding-card" data-recruitment-branding aria-labelledby="{{ $id }}Title">
    <div class="rec-branding-heading">
        <span class="rec-branding-icon" aria-hidden="true"><i class="bx {{ $kind === 'header' ? 'bx-align-top' : 'bx-align-bottom' }}"></i></span>
        <div>
            <h6 id="{{ $id }}Title">Company {{ ucfirst($kind) }} Image</h6>
            <p>{{ $kind === 'header' ? 'Top' : 'Bottom' }} of every PDF page</p>
        </div>
        <span class="rec-branding-size">{{ $size }} px</span>
    </div>

    <div>
        <label for="{{ $id }}Source" class="form-label">{{ ucfirst($kind) }} source</label>
        <select class="form-select @error($assetKey) is-invalid @enderror" id="{{ $id }}Source" name="{{ $assetKey }}" data-branding-source aria-describedby="{{ $id }}Feedback">
            <option value="">Upload a new {{ $kind }}</option>
            @if($assets->isNotEmpty())
                <optgroup label="Saved for this company">
                    @foreach($assets as $asset)
                        <option value="{{ $asset->id }}" data-preview="{{ route('recruitment.branding.preview', $asset->id) }}" @selected($selected?->id === $asset->id)>
                            {{ $asset->name }}{{ $nameCounts[$asset->name] > 1 ? ' (#'.$asset->id.')' : '' }}
                        </option>
                    @endforeach
                </optgroup>
            @endif
        </select>
    </div>

    <div class="rec-branding-upload" data-branding-upload @if($selected) hidden @endif>
        <div>
            <label class="form-label" for="{{ $id }}">Upload {{ $kind }}</label>
            <input type="file" name="{{ $imageKey }}" id="{{ $id }}" class="form-control @error($imageKey) is-invalid @enderror"
                   accept=".jpg,.jpeg,.png,image/png,image/jpeg" @if($selected) disabled @else required @endif
                   data-branding-file data-width="{{ \App\Support\RecruitmentPdfBranding::WIDTH }}" data-height="{{ $height }}"
                   data-max-bytes="{{ \App\Support\RecruitmentPdfBranding::MAX_KB * 1024 }}" aria-describedby="{{ $id }}Help {{ $id }}Feedback">
            <p id="{{ $id }}Help" class="rec-branding-hint">JPG or PNG, up to 2 MB. Use the exact dimensions shown above.</p>
        </div>
        <div>
            <label for="{{ $id }}Name" class="form-label">Save as <span class="rec-branding-hint">(optional)</span></label>
            <input type="text" class="form-control @error($nameKey) is-invalid @enderror" id="{{ $id }}Name" name="{{ $nameKey }}"
                   value="{{ is_string($oldName) ? $oldName : '' }}" maxlength="100" placeholder="Defaults to the file name" data-branding-name @disabled($selected)
                   aria-describedby="{{ $id }}Feedback">
        </div>
    </div>

    <img id="{{ $id }}Preview" class="rec-branding-preview" data-branding-preview alt="Company {{ $kind }} preview"
         @if($selected) src="{{ route('recruitment.branding.preview', $selected->id) }}" @else hidden @endif>
    <p id="{{ $id }}Feedback" class="rec-branding-feedback {{ $error ? 'is-error' : '' }}" data-branding-feedback aria-live="polite" @unless($error) hidden @endunless>{{ $error }}</p>
</section>
