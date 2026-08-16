@component('mail::message')
# Hello {{ $developer->name }},

Super Admin has assigned a new development task to your workspace.

@component('mail::panel')
**Task Title:** {{ $taskTitle }}  
**Priority:** {{ $priority }}  
**Due Date:** {{ $dueDate }}  
@if(!empty($instructions))
**Additional Instructions:**  
{{ $instructions }}
@endif
@endcomponent

You can view and update the progress of this task in your Developer Workspace.

@component('mail::button', ['url' => $portalUrl, 'color' => 'success'])
View Work in Developer Workspace
@endcomponent

Thanks,  
**{{ config('app.name') }} Team**
@endcomponent
