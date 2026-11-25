<div class="row mb-3">
    <div class="col-md-6">
        <label for="given_to">Given To (Optional)</label>
        <input type="text" class="form-control" id="given_to" name="given_to" maxlength="255" value="{{ old('given_to', $appreciation->given_to ?? '') }}">
    </div>
    <div class="col-md-6">
        <label for="given_on">Given On (Optional)</label>
        <input type="date" class="form-control" id="given_on" name="given_on" value="{{ old('given_on', isset($appreciation) && $appreciation->given_on ? $appreciation->given_on->format('Y-m-d') : '') }}">
        @error('given_on')<div class="text-danger">{{ $message }}</div>@enderror
    </div>
</div>
<div class="mb-3">
    <label for="status">Status</label>
    <select class="form-control" id="status" name="status" required>
        <option value="active" @selected(old('status', $appreciation->status ?? 'active') === 'active')>Active</option>
        <option value="inactive" @selected(old('status', $appreciation->status ?? 'active') === 'inactive')>Inactive</option>
    </select>
</div>
