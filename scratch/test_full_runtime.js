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

console.log('Script code length:', scriptCode.length);

function createMockEl(id) {
    return {
        id,
        tagName: 'DIV',
        value: id === 'no_of_pax' ? '2' : (id === 'booking_category' ? 'umrah' : ''),
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
        addEventListener(event, fn) {
            // console.log(`addEventListener on #${id}: ${event}`);
        },
        insertAdjacentHTML(where, h) {
            console.log(`insertAdjacentHTML called on #${id}, length: ${h.length}`);
        },
        querySelectorAll() { return []; },
        querySelector() { return null; },
        textContent: '',
        innerHTML: '',
        parentElement: null,
        closest() { return null; }
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
            each() { return this; },
            val() { return ''; },
            trigger() { return this; },
            find() { return []; },
            select2() { return this; },
            hasClass() { return false; },
            addClass() { return this; },
            removeClass() { return this; },
            html() { return this; },
            text() { return this; },
            length: 0,
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
sandbox.window = sandbox;

try {
    const script = new vm.Script(scriptCode);
    const context = vm.createContext(sandbox);
    script.runInContext(context);
    console.log('SCRIPT EXECUTED TO COMPLETION WITHOUT ANY TOP-LEVEL RUNTIME ERRORS!');
} catch (e) {
    console.error('RUNTIME ERROR DURING SCRIPT EXECUTION:', e);
}
