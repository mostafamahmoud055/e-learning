@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="https://www.facebook.com/photo/?fbid=693395112831744&set=a.552878840216706" class="logo" alt="Sphinx E-learning">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
