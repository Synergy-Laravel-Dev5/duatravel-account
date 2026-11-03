const fs = require('fs');
const html = fs.readFileSync('scratch/rendered_edit.html', 'utf8');

// Parse element IDs and values from the HTML
const idRegex = /id=["']([^"']+)["']/g;
let m;
const domElements = {};

function createEl(id) {
  return {
    id: id,
    value: '',
    innerHTML: '',
    textContent: '',
    style: {},
    classList: {
      contains: () => false,
      add: () => {},
      remove: () => {}
    },
    querySelectorAll: (sel) => [],
    querySelector: (sel) => null,
    addEventListener: () => {},
    setAttribute: () => {},
    getAttribute: (attr) => attr === 'data-idx' ? '0' : '',
    children: []
  };
}

while ((m = idRegex.exec(html)) !== null) {
  domElements[m[1]] = createEl(m[1]);
}

// Find input values
const valRegex = /<input[^>]*id=["']([^"']+)["'][^>]*value=["']([^"']*)["']/g;
while ((m = valRegex.exec(html)) !== null) {
  if (domElements[m[1]]) domElements[m[1]].value = m[2];
}

console.log('Parsed', Object.keys(domElements).length, 'DOM elements by id');
console.log('no_of_pax value:', domElements['no_of_pax'] ? domElements['no_of_pax'].value : 'NOT FOUND');
console.log('flightPersonsList exists:', !!domElements['flightPersonsList']);

const scriptRegex = /<script\b[^>]*>([\s\S]*?)<\/script>/gi;
let match;
let i = 0;
while ((match = scriptRegex.exec(html)) !== null) {
  i++;
  if (i === 2) {
    const code = match[1];
    const globalContext = {
      window: {},
      document: {
        getElementById: (id) => domElements[id] || (domElements[id] = createEl(id)),
        querySelector: (sel) => createEl('sel'),
        querySelectorAll: (sel) => [],
        addEventListener: () => {}
      },
      $: function(selector) {
        return {
          on: () => {},
          val: () => '',
          trigger: () => {},
          find: () => [],
          each: () => {},
          first: () => ({ length: 0, find: () => ({ val: () => '' }) }),
          ready: (fn) => fn(),
          length: 0
        };
      },
      console: console,
      setTimeout: (fn) => { try { fn(); } catch(e) { console.error('setTimeout error:', e); } },
      Math: Math,
      parseInt: parseInt,
      parseFloat: parseFloat,
      isNaN: isNaN,
      Array: Array,
      Object: Object,
      JSON: JSON
    };
    globalContext.window = globalContext;
    globalContext.$.on = () => {};
    globalContext.$.ajax = () => {};
    globalContext.$.fn = {};

    const vm = require('vm');
    const ctx = vm.createContext(globalContext);
    try {
      vm.runInContext(code, ctx);
      console.log('Script 2 executed!');
      console.log('flightPersonsList innerHTML length:', domElements['flightPersonsList'].innerHTML.length);
      if (domElements['flightPersonsList'].innerHTML.length > 0) {
        console.log('flightPersonsList contains Passenger-Wise:', domElements['flightPersonsList'].innerHTML.includes('Passenger-Wise Flight & Ticket Management'));
      }
    } catch (e) {
      console.error('Script 2 error:', e);
    }
  }
}
