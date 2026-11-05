const fs = require('fs');
const html = fs.readFileSync('scratch/edit_rendered.html', 'utf-8');

// Extract all inline script blocks
const regex = /<script(?![^>]*src=)[^>]*>([\s\S]*?)<\/script>/gi;
let match;
let count = 0;
while ((match = regex.exec(html)) !== null) {
    count++;
    const code = match[1];
    console.log(`Checking script block ${count} (length: ${code.length})...`);
    try {
        new Function(code);
        console.log(`Script block ${count} parsed OK (no syntax errors)`);
    } catch (e) {
        console.error(`SYNTAX ERROR in script block ${count}:`, e.message);
        // Find line number
        const lines = code.split('\n');
        console.error('Around error:');
    }
}
