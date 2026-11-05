import fs from 'fs';
import vm from 'vm';

const html = fs.readFileSync('scratch/rendered_edit_fresh.html', 'utf8');

const scriptRegex = /<script(?:\s+[^>]*?)?>([\s\S]*?)<\/script>/gi;
let match;
let scriptCode = '';
while ((match = scriptRegex.exec(html)) !== null) {
    if (match[1].includes('packagesData')) {
        scriptCode = match[1];
        break;
    }
}

function createMockEl(id) {
    return {
        id,
        tagName: 'DIV',
        value: id === 'no_of_pax' ? '2' : (id === 'booking_category' ? 'umrah' : (id === 'flight_currency' ? 'SAR' : '')),
        style: {
            display: '',
            setProperty() {},
            removeProperty() {}
        },
        classList: {
            add() {}, remove() {}, contains() { return false; }, toggle() {}
        },
        setAttribute() {},
        getAttribute() { return null; },
        removeAttribute() {},
        addEventListener(event, fn) {},
        insertAdjacentHTML(where, h) {
            console.log(`SUCCESS: insertAdjacentHTML called on #${id}! HTML length: ${h.length}`);
        },
        querySelectorAll(sel) {
            if (sel.includes('.hotel-block')) return [createMockEl('h0'), createMockEl('h1')];
            if (sel.includes('.route-block')) return [createMockEl('r0')];
            return [];
        },
        querySelector(sel) {
            return createMockEl(sel);
        },
        textContent: '',
        innerHTML: '',
        parentElement: null,
        closest() { return createMockEl('closest'); },
        selectedIndex: 0,
        options: [{ text: 'Polani', value: '1' }]
    };
}

const dom = {
    elements: {},
    getElementById(id) {
        if (!this.elements[id]) {
            this.elements[id] = createMockEl(id);
        }
        return this.elements[id];
    },
    querySelector(sel) {
        const id = sel.replace(/^[#.]/, '');
        return this.getElementById(id);
    },
    querySelectorAll(sel) {
        if (sel.includes('.hotel-block')) return [this.getElementById('h0'), this.getElementById('h1')];
        if (sel.includes('.route-block')) return [this.getElementById('r0')];
        return [];
    },
    addEventListener() {},
    removeEventListener() {}
};

const sandbox = {
    document: dom,
    window: {},
    console: console,
    setTimeout: (fn) => fn(),
    setInterval: () => {},
    clearTimeout: () => {},
    clearInterval: () => {},
    jQuery: function(sel) {
        return {
            on() { return this; },
            off() { return this; },
            each(fn) { return this; },
            val() { return ''; },
            trigger() { return this; },
            find() { return []; },
            select2() { return this; },
            hasClass() { return false; },
            addClass() { return this; },
            removeClass() { return this; },
            html() { return this; },
            text() { return this; },
            length: 1,
            eq() { return this; },
            first() { return this; },
            last() { return this; },
            attr() { return ''; },
            ready(fn) { fn(); }
        };
    },
    $: function(sel) {
        return sandbox.jQuery(sel);
    }
};
sandbox.jQuery.fn = { select2() {} };
sandbox.$.fn = sandbox.jQuery.fn;
sandbox.window = sandbox;

try {
    const script = new vm.Script(scriptCode);
    const context = vm.createContext(sandbox);
    script.runInContext(context);
    console.log('Script loaded in context!');

    console.log('\n--- Testing addNewHotel("makkah") ---');
    if (typeof context.addNewHotel === 'function') {
        context.addNewHotel('makkah');
        console.log('addNewHotel("makkah") finished without errors!');
    } else {
        console.error('addNewHotel is NOT a function!');
    }

    console.log('\n--- Testing addRoute click ---');
    const addRouteEl = dom.getElementById('addRoute');
    console.log('addRoute element exists:', !!addRouteEl);

} catch (e) {
    console.error('ERROR:', e);
}
