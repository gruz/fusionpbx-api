@if(config('fpbx.domain.allow_select'))
<x-form-select name="domain_name" :options="$domains" :label="__('Domain')" />
@else
{{-- <x-form-input name="domain_name" :label="__('Domain') . ' (' . __('Leave empty for default domain') . ')'" autofocus /> --}}
<x-form-input name="domain_name" :label="__('Domain')" autofocus />
@endif
