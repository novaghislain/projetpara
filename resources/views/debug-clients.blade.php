<!DOCTYPE html>
<html>
<head><title>Debug Clients</title></head>
<body>
    <h1>Debug Clients API</h1>
    <pre id="output">Loading...</pre>
    <script>
    fetch('/api/clients', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('output').textContent = JSON.stringify(data, null, 2);
    })
    .catch(err => {
        document.getElementById('output').textContent = 'Error: ' + err.message;
    });
    </script>
</body>
</html>
