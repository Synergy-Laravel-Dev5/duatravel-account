const fs = require('fs');
const vm = require('vm');

const html = fs.readFileSync(__dirname + '/rendered_edit_7.html', 'utf8');

const scriptRegex = /<script\b[^>]*>([\s\S]*?)<\/script>/gi;
let match;
let scriptIdx = 0;

while ((match = scriptRegex.exec(html)) !== null) {
    scriptIdx++;
    const code = match[1];
    if (!code.trim()) continue;
    
    try {
        new vm.Script(code);
        console.log(`Script block #${scriptIdx}: OK (length ${code.length})`);
    } catch (e) {
        console.error(`Script block #${scriptIdx} SYNTAX ERROR: ${e.message}`);
    }
}
