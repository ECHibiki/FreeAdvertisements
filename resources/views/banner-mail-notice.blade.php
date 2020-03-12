<h2>New Banner @ {{ $time }}</h2>
<p style="color:green">Name:  {{ $name }}</p><br/>
<p style="color:blue">URL: {{$url}}</p>
@if ($err)
<hr/>
<p>Previous Send Errors: <pre>{{ $err }}</pre>
@endif
