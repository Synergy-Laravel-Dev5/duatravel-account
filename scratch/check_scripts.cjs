const fs = require('fs');

const html = fs.readFileSync(__dirname + '/edit_rendered.html', 'utf8');

// Extract all <script>...</script> tags
const regex = /<script\b[^>]*>([\s\S]*?)<\/script>/gi;
let match;
let i = 0;

while ((match = regex.exec(html)) !== null) {
    const code = match[1];
    if (!code.trim()) continue;
    i++;
    const filename = `${__dirname}/script_${i}.js`;
    fs.writeFileSync(filename, code);
    console.log(`Saved script ${i} (${code.length} chars)`);
}
