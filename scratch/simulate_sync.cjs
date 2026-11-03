const fs = require('fs');
const html = fs.readFileSync('scratch/test_http_edit_1.html', 'utf8');

// Let's find syncFlightPersons in HTML
const idx = html.indexOf('function syncFlightPersons()');
console.log(html.substring(idx, idx + 3500));
