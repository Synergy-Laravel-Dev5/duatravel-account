const fs = require('fs');
const html = fs.readFileSync('scratch/test_http_edit_1.html', 'utf8');

const idx = html.indexOf('function syncFlightPersons()');
console.log(html.substring(idx + 2500, idx + 6000));
