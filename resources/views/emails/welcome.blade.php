@php($title = $title ?? 'Welcome!')
        <!doctype html>
<html>
<body>
<h1>{{ $title }}</h1>
<p>Hello {{ $name ?? 'there' }},</p>
<p>{{ $body ?? 'Thanks for joining.' }}</p>
</body>
</html>
