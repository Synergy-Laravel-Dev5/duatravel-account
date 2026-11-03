const fs = require('fs');
const html = fs.readFileSync('scratch/test_http_edit_1.html', 'utf8');

function showAround(str, find) {
    const idx = str.indexOf(find);
    if (idx === -1) {
        console.log(find + ' not found');
        return;
    }
    console.log('--- ' + find + ' ---');
    console.log(str.substring(idx - 50, idx + 200));
}

showAround(html, 'id="person_passport_0"');
showAround(html, 'id="person_passport_1"');
showAround(html, 'id="person_name_0"');
showAround(html, 'id="person_name_1"');
