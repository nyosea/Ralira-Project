<!DOCTYPE html>
<html>
<head>
    <title>Test JavaScript Syntax</title>
</head>
<body>

<h1>Test Psychologist Schedule Page</h1>

<div id="result"></div>

<script>
    fetch('./pages/psychologist/schedule.php', {
        method: 'GET'
    })
    .then(response => response.text())
    .then(html => {
        // Extract script content
        const scriptMatch = html.match(/<script>([\s\S]*?)<\/script>/);
        
        const result = document.getElementById('result');
        
        if (!scriptMatch) {
            result.innerHTML = '<p style="color: red;">No script found in page</p>';
            return;
        }
        
        const scriptContent = scriptMatch[1];
        
        // Try to detect syntax errors by checking for common issues
        result.innerHTML = '<pre style="background: #f0f0f0; padding: 15px; border-radius: 5px;">';
        result.innerHTML += 'Script length: ' + scriptContent.length + ' chars\n\n';
        
        // Check for matching braces
        const openBraces = (scriptContent.match(/{/g) || []).length;
        const closeBraces = (scriptContent.match(/}/g) || []).length;
        
        result.innerHTML += 'Open braces: ' + openBraces + '\n';
        result.innerHTML += 'Close braces: ' + closeBraces + '\n';
        
        if (openBraces !== closeBraces) {
            result.innerHTML += '<span style="color: red; font-weight: bold;">ERROR: Mismatched braces!</span>\n';
        } else {
            result.innerHTML += '<span style="color: green;">✓ Braces match</span>\n';
        }
        
        // Check for syntax errors by trying to eval in non-strict mode
        result.innerHTML += '\n--- Checking for obvious errors ---\n';
        
        const lines = scriptContent.split('\n');
        for (let i = 0; i < lines.length; i++) {
            const line = lines[i].trim();
            if (line.includes('getElementById(\'btnSaveSchedule\')')) {
                result.innerHTML += 'Line ' + (i+1) + ': Found btnSaveSchedule listener\n';
            }
        }
        
        result.innerHTML += '</pre>';
    })
    .catch(error => {
        console.error(error);
        document.getElementById('result').innerHTML = '<p style="color: red;">Error fetching page: ' + error.message + '</p>';
    });
</script>

</body>
</html>
