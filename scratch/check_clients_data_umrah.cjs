const fs = require('fs');
const html = fs.readFileSync('scratch/test_http_create_umrah.html', 'utf8');
const p = html.indexOf('let clientsData');
console.log(html.substring(p, p + 500));
