const fs = require('fs');
const vm = require('vm');

const code = fs.readFileSync(__dirname + '/script_1.js', 'utf8');

// Build sandbox with mocked browser globals
const elements = new Map();

function getOrCreate(id, tag = 'div', cls = '') {
    if (!elements.has(id)) {
        const el = {
            id,
            tagName: tag.toUpperCase(),
            className: cls,
            value: '',
            textContent: '',
            style: { setProperty: () => {}, removeProperty: () => {} },
            classList: { add: () => {}, remove: () => {}, contains: () => false },
            setAttribute: () => {},
            getAttribute: () => null,
            removeAttribute: () => {},
            closest: () => null,
            children: [],
            querySelectorAll: () => [],
            querySelector: () => null,
            insertAdjacentHTML: (pos, html) => {
                // record that item was inserted
                el._inserted = el._inserted || [];
                el._inserted.push(html);
            },
            remove: () => {},
            addEventListener: () => {},
        };
        elements.set(id, el);
    }
    return elements.get(id);
}

// Pre-populate key IDs
getOrCreate('hotelsList');
getOrCreate('routesList');
getOrCreate('personsList');
getOrCreate('visasList');
getOrCreate('flightPersonsList');
getOrCreate('no_of_pax').value = '2';
getOrCreate('pax_adults').value = '2';
getOrCreate('flight_currency').value = 'PKR';
getOrCreate('bar_pax');
getOrCreate('bar_pax_badge');

const mockDocument = {
    getElementById: (id) => getOrCreate(id),
    querySelectorAll: (sel) => {
        if (sel === '.hotel-block') return [];
        if (sel === '.flight-person-row') return [];
        if (sel === '.person-card') return [getOrCreate('p0'), getOrCreate('p1')];
        if (sel === '.visa-card') return [getOrCreate('v0'), getOrCreate('v1')];
        if (sel === '.person-pax-type-select') return [{ value: 'adult' }, { value: 'adult' }, { value: 'adult' }];
        if (sel === '.flight-time-picker') return [];
        return [];
    },
    querySelector: (sel) => {
        if (sel.startsWith('#')) return getOrCreate(sel.slice(1));
        return null;
    },
    addEventListener: () => {},
    location: { search: '', hash: '' },
};

const mockJQuery = function(sel) {
    return {
        ready: (fn) => fn && fn(),
        on: () => {},
        trigger: () => {},
        val: () => '',
        attr: () => '',
        find: () => ({ each: () => {} }),
        hasClass: () => false,
        select2: () => {},
        first: () => ({
            find: () => ({ val: () => '', attr: () => '' }),
            length: 0
        }),
        last: () => ({
            find: () => ({ val: () => '', attr: () => '' }),
            length: 0
        }),
        length: 1
    };
};
mockJQuery.fn = { select2: () => {} };
mockJQuery.on = () => {};

const sandbox = {
    window: {},
    document: mockDocument,
    $: mockJQuery,
    jQuery: mockJQuery,
    setTimeout: (fn) => fn(),
    clearTimeout: () => {},
    console: console,
    Math: Math,
    parseInt: parseInt,
    parseFloat: parseFloat,
    isNaN: isNaN,
    Date: Date,
    Array: Array,
    Object: Object,
    String: String,
    Number: Number,
    Boolean: Boolean,
    RegExp: RegExp,
    URLSearchParams: URLSearchParams,
    JSON: JSON,
    Set: Set,
    Map: Map,
    hotelsData: [{ name: 'Test Makkah Hotel', place: 'Makkah' }],
    suppliersData: [{ id: 1, name: 'Test Supplier' }],
    routesData: [{ start_place: 'Jeddah', end_place: 'Makkah' }],
    airlinesData: [{ name: 'PIA', iata_code: 'PK' }],
    vehiclesData: [{ vehicle_type: 'Bus' }],
    existingPersons: [],
    existingBooking: { no_of_pax: 2 },
    defaultVouchers: { hotel: 'HTL-2026-00001', transport: 'TRN-2026-00001' }
};
sandbox.window = sandbox;

try {
    vm.createContext(sandbox);
    vm.runInContext(code, sandbox);
    console.log("Script executed successfully without error!");

    // Test addNewHotel('makkah')
    console.log("Testing addNewHotel('makkah')...");
    sandbox.window.addNewHotel('makkah');
    sandbox.window.addNewHotel('madinah');
    sandbox.window.addNewHotel('other');
    const hotelsList = elements.get('hotelsList');
    console.log("Hotels inserted count:", hotelsList._inserted ? hotelsList._inserted.length : 0);

    // Test addNewRoute()
    console.log("Testing addNewRoute()...");
    sandbox.window.addNewRoute();
    const routesList = elements.get('routesList');
    console.log("Routes inserted count:", routesList._inserted ? routesList._inserted.length : 0);

    // Test handlePaxChange(3)
    console.log("Testing handlePaxChange(3)...");
    sandbox.window.handlePaxChange(3);
    const personsList = elements.get('personsList');
    console.log("Persons inserted count:", personsList._inserted ? personsList._inserted.length : 0);
    console.log("Pax Adults value:", elements.get('pax_adults').value);

    console.log("ALL TESTS COMPLETED SUCCESSFULLY!");
} catch(e) {
    console.error("Test execution failed:", e);
}
