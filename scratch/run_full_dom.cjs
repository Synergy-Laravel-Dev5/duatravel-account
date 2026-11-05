const fs = require('fs');
const vm = require('vm');

const code = fs.readFileSync(__dirname + '/script_1.js', 'utf8');

// Proxy to log any accessed property or function call that might be undefined
const domLog = [];

function makeProxy(name, target = {}) {
    return new Proxy(target, {
        get(t, prop) {
            if (prop in t) return t[prop];
            if (typeof prop === 'symbol' || prop === 'then' || prop === 'inspect') return undefined;
            // return a callable function for unknown methods
            return function(...args) {
                // domLog.push(`${name}.${String(prop)} called with ${args.length} args`);
                return makeProxy(`${name}.${String(prop)}()`, { length: 0 });
            };
        },
        set(t, prop, val) {
            t[prop] = val;
            return true;
        }
    });
}

// Create a realistic document mock
const elementsById = new Map();

function createMockElement(tag, id = '') {
    const el = {
        tagName: tag.toUpperCase(),
        id: id,
        className: '',
        value: '',
        textContent: '',
        innerHTML: '',
        attributes: {},
        style: makeProxy('style'),
        children: [],
        parentElement: null,
        setAttribute(k, v) { this.attributes[k] = String(v); },
        getAttribute(k) { return this.attributes[k] !== undefined ? this.attributes[k] : null; },
        removeAttribute(k) { delete this.attributes[k]; },
        classList: {
            add() {}, remove() {}, contains() { return false; }
        },
        closest(sel) { return this; },
        querySelectorAll(sel) { return []; },
        querySelector(sel) { return null; },
        insertAdjacentHTML(pos, html) {
            this.innerHTML += html;
        },
        remove() {},
        addEventListener(event, fn) {
            this._listeners = this._listeners || {};
            this._listeners[event] = this._listeners[event] || [];
            this._listeners[event].push(fn);
        },
        dispatchEvent(event) {
            if (this._listeners && this._listeners[event]) {
                this._listeners[event].forEach(fn => fn({ target: this, preventDefault() {} }));
            }
        }
    };
    return el;
}

const mockDoc = {
    getElementById(id) {
        if (!elementsById.has(id)) {
            elementsById.set(id, createMockElement('div', id));
        }
        return elementsById.get(id);
    },
    querySelector(sel) {
        if (sel.startsWith('#')) return this.getElementById(sel.slice(1));
        return createMockElement('div');
    },
    querySelectorAll(sel) {
        return [];
    },
    addEventListener() {},
    location: { search: '', hash: '' }
};

const mock$ = function(sel) {
    const el = makeProxy('jQueryObj', {
        length: 1,
        val() { return ''; },
        attr() { return ''; },
        text() { return ''; },
        html() { return ''; },
        find() { return mock$('find'); },
        first() { return mock$('first'); },
        last() { return mock$('last'); },
        eq() { return mock$('eq'); },
        on() { return this; },
        trigger() { return this; },
        each(cb) { return this; },
        select2() { return this; },
        hasClass() { return false; },
        ready(cb) { if (cb) cb(); return this; }
    });
    return el;
};
mock$.fn = { select2() {} };
mock$.on = function() { return mock$; };
mock$.ready = function(cb) { if (cb) cb(); };

const sandbox = {
    window: {},
    document: mockDoc,
    $: mock$,
    jQuery: mock$,
    flatpickr: function() {},
    setTimeout(fn, ms) { if (fn) fn(); return 1; },
    clearTimeout() {},
    setInterval() {},
    clearInterval() {},
    console: console,
    Math, parseInt, parseFloat, isNaN, Date, Array, Object, String, Number, Boolean, RegExp, URLSearchParams, JSON, Set, Map
};
sandbox.window = sandbox;

try {
    vm.createContext(sandbox);
    vm.runInContext(code, sandbox);
    console.log("Full Script 1 executed without throwing errors!");
} catch(err) {
    console.error("Runtime error thrown in Script 1:", err);
}
