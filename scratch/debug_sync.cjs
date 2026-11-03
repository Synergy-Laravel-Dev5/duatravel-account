const fs = require('fs');
const html = fs.readFileSync('scratch/rendered_edit.html', 'utf8');

// Let's extract existingBooking, existingPersons, suppliersData, etc.
const scriptIdx = html.indexOf('const existingBooking');
console.log('Script index:', scriptIdx);

// Let's find syncFlightPersons in HTML
const syncIdx = html.indexOf('function syncFlightPersons()');
const syncCode = html.substring(syncIdx, html.indexOf('document.getElementById(\'no_of_pax\')', syncIdx));

console.log('Found syncFlightPersons code length:', syncCode.length);
