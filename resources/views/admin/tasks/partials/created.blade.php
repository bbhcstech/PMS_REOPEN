<!doctype html>
<html lang="en">
<head>
    @include('partials.pwa')<meta charset="utf-8"><title>Task created</title></head>
<body>
<p>Task created successfully.</p>
<script>window.parent.postMessage('task-form-saved', window.location.origin);</script>
@include('partials.responsive-workspace', ['responsiveShell' => 'auth'])
</body>
</html>
