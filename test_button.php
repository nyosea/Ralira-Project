<!DOCTYPE html>
<html>
<head>
    <title>Button Test</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .test-button {
            padding: 10px 20px;
            background: #4caf50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px 0;
        }
        .test-button:hover {
            background: #45a049;
        }
        #log {
            background: #f0f0f0;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            height: 300px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 12px;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<h1>Test Save Schedule Button</h1>

<p>This page loads the schedule.php and tests if the button listener is working</p>

<button class="test-button" onclick="testLoadPage()">Load Schedule Page & Test</button>
<button class="test-button" onclick="clearLog()">Clear Log</button>

<div id="log">Log messages appear here...</div>

<script>
    const logDiv = document.getElementById('log');
    
    function log(msg) {
        logDiv.innerHTML += msg + '<br>';
        logDiv.scrollTop = logDiv.scrollHeight;
    }
    
    function clearLog() {
        logDiv.innerHTML = '';
    }
    
    function testLoadPage() {
        log('=== Testing Schedule Page ===');
        
        // Create iframe to load page
        const iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.onload = function() {
            try {
                const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                
                // Check if button exists
                const button = iframeDoc.getElementById('btnSaveSchedule');
                if (button) {
                    log('✓ Button found with ID: btnSaveSchedule');
                    log('  Button HTML: ' + button.outerHTML.substring(0, 100) + '...');
                } else {
                    log('✗ Button NOT found!');
                }
                
                // Check if script variables exist
                const script = iframeDoc.querySelector('script');
                if (script) {
                    log('✓ Script tag found');
                    
                    // Try to access window variables
                    try {
                        const selectedPsych = iframe.contentWindow.selectedPsychologist;
                        log('  selectedPsychologist: ' + selectedPsych);
                    } catch (e) {
                        log('  Error accessing selectedPsychologist: ' + e.message);
                    }
                } else {
                    log('✗ No script found');
                }
                
                // Try to click button programmatically
                log('\n--- Attempting to click button ---');
                if (button) {
                    button.click();
                    log('✓ Button clicked');
                } else {
                    log('✗ Cannot click - button not found');
                }
                
            } catch (e) {
                log('Error: ' + e.message);
                log('Stack: ' + e.stack);
            }
        };
        
        iframe.src = './pages/psychologist/schedule.php';
        document.body.appendChild(iframe);
        
        log('Loading schedule.php in iframe...');
    }
</script>

</body>
</html>
