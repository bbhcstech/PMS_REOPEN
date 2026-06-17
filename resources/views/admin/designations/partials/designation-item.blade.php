<li class="hierarchy-node" data-id="{{ $designation->id }}" data-level="{{ $designation->level ?? 0 }}">
    <div class="designation-item">
        <button type="button" class="drag-handle" title="Drag to reorganize">
            <i class="fas fa-grip-vertical"></i>
        </button>
        <div class="designation-node-icon"><i class="fas fa-user-tie"></i></div>
        <div class="designation-node-copy">
            <strong>{{ $designation->name }}</strong>
            <span>{{ $designation->unique_code ?? 'No code' }}</span>
        </div>
        <span class="node-level level-{{ $designation->level ?? 0 }}">L{{ $designation->level ?? 0 }}</span>
        <button type="button" class="toggle-children {{ $designation->children->count() ? '' : 'is-hidden' }}" title="Expand or collapse children">
            <i class="fas fa-chevron-down"></i>
        </button>
    </div>

    <ul class="hierarchy-list nested-list">
        @foreach($designation->children->sortBy([['order', 'asc'], ['name', 'asc']]) as $child)
            @include('admin.designations.partials.designation-item', ['designation' => $child])
        @endforeach
    </ul>
</li>
