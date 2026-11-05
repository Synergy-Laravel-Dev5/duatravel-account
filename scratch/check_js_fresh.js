const fs = require('fs');
const vm = require('vm');

const html = fs.readFileSync('scratch/rendered_edit_fresh.html', 'utf8');

const scriptRegex = /<script(?:\s+[^>]*?)?>([\s\S]*?)<\/script>/gi;
let match;
let count = 0;
while ((match = scriptRegex.exec(html)) !== null) {
    count++;
    if (match[1].trim().length > 100) {
        console.log(`Checking script ${count}, length: ${match[1].length}`);
        try {
            new vm.Script(match[1]);
            console.log(`Script ${count} syntax OK.`);
        } catch(e) {
            console.error(`Script ${count} SYNTAX ERROR:`, e);
        }
    }
}
