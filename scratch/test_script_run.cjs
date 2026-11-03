const fs = require('fs');
const html = fs.readFileSync('scratch/rendered_edit.html', 'utf8');

const scriptRegex = /<script\b[^>]*>([\s\S]*?)<\/script>/gi;
let match;
let i = 0;
while ((match = scriptRegex.exec(html)) !== null) {
  i++;
  if (i === 2) {
    const code = match[1];
    
    const elements = {};
    function mockElement(tag, id = '') {
      return {
        tagName: tag.toUpperCase(),
        id: id,
        value: '1',
        innerHTML: '',
        textContent: '',
        style: {},
        classList: { contains: () => false, add: () => {}, remove: () => {} },
        querySelectorAll: () => [],
        querySelector: () => null,
        addEventListener: () => {},
        setAttribute: () => {},
        getAttribute: () => null,
        insertAdjacentHTML: (pos, str) => {}
      };
    }
    
    const globalContext = {
      window: {},
      document: {
        getElementById: (id) => elements[id] || (elements[id] = mockElement('div', id)),
        querySelector: (sel) => mockElement('div'),
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
      setTimeout: (fn) => {},
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
      console.log('Execution: SUCCESS without any runtime error!');
      const flList = elements['flightPersonsList'];
      console.log('flightPersonsList innerHTML length:', flList ? flList.innerHTML.length : 'NULL');
    } catch (e) {
      console.error('Execution ERROR:', e);
    }
  }
}
