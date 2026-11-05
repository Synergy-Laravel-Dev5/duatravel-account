const fs = require('fs');
const content = fs.readFileSync('scratch/edit_rendered.html', 'utf8');

// Find hotelsData in edit_rendered.html
const match = content.match(/const hotelsData\s*=\s*(\[[\s\S]*?\]);/);
if (!match) {
    console.error("hotelsData not found!");
    process.exit(1);
}
const hotelsData = JSON.parse(match[1]);

function buildHotelsOptions(filterCity = null, activeCategory = 'umrah') {
    let list = Array.isArray(hotelsData) ? hotelsData : [];

    const domesticPlaces = ['karachi', 'lahore', 'islamabad', 'rawalpindi', 'peshawar', 'multan', 'faisalabad', 'quetta', 'sialkot', 'pakistan'];
    const pilgrimagePlaces = ['makkah', 'madinah', 'azizia', 'mina', 'arafat', 'jeddah', 'saudi'];

    if (activeCategory === 'domestic') {
        list = list.filter(h => {
            const p = (((typeof h.place === 'object' && h.place !== null) ? h.place?.value : h.place) || h.address || '').toLowerCase();
            return domesticPlaces.some(dp => p.includes(dp));
        });
    } else if (activeCategory === 'international') {
        list = list.filter(h => {
            const p = (((typeof h.place === 'object' && h.place !== null) ? h.place?.value : h.place) || h.address || '').toLowerCase();
            const isPilgrim = pilgrimagePlaces.some(pp => p.includes(pp));
            const isDom = domesticPlaces.some(dp => p.includes(dp));
            return !isPilgrim && !isDom;
        });
    } else {
        if (filterCity && filterCity !== 'other') {
            const fc = filterCity.toLowerCase();
            list = list.filter(h => {
                const p = (((typeof h.place === 'object' && h.place !== null) ? h.place?.value : h.place) || h.address || '').toLowerCase();
                return p.includes(fc);
            });
        }
    }

    return list.map(h => h.name + ' (' + (h.place || '') + ')');
}

console.log("Makkah hotels count:", buildHotelsOptions('makkah').length);
console.log("Makkah hotels:", buildHotelsOptions('makkah'));

console.log("Madinah hotels count:", buildHotelsOptions('madinah').length);
console.log("Madinah hotels:", buildHotelsOptions('madinah'));

console.log("Null filter hotels count:", buildHotelsOptions(null).length);
