<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif;">
<x-mail-header :title="$title" />

<div style="padding:20px;">
    {{ $slot }}
</div>

<x-mail-footer />
</body>
</html>
