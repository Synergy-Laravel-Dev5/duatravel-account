const fs = require('fs');

const html = fs.readFileSync(__dirname + '/rendered_edit_7.html', 'utf8');

const scriptRegex = /<script\b[^>]*>([\s\S]*?)<\/script>/gi;
let match;
let scriptIdx = 0;

while ((match = scriptRegex.exec(html)) !== null) {
    scriptIdx++;
    if (scriptIdx === 2) {
        const code = match[1];
        console.log("Total length:", code.length);
        console.log("Total lines:", code.split('\n').length);
        // Print the last 40 lines
        const lines = code.split('\n');
        console.log("LAST 40 LINES:\n" + lines.slice(-40).join('\n'));
    }
}
