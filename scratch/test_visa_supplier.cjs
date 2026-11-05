const { spawn } = require('child_process');
const http = require('http');
const path = require('path');

const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const htmlPath = 'file:///' + path.resolve(__dirname, 'edit_rendered.html').replace(/\\/g, '/');

const chrome = spawn(chromePath, [
    '--headless=new',
    '--remote-debugging-port=9224',
    '--disable-gpu',
    '--no-sandbox',
    '--user-data-dir=' + path.resolve(__dirname, 'chrome_profile_visa')
]);

function waitForPort(retries = 20) {
    if (retries <= 0) {
        console.error("Chrome failed to start");
        chrome.kill();
        process.exit(1);
    }
    http.get('http://127.0.0.1:9224/json/version', () => {
        connectCDP();
    }).on('error', () => {
        setTimeout(() => waitForPort(retries - 1), 250);
    });
}

function connectCDP() {
    const req = http.request({
        host: '127.0.0.1',
        port: 9224,
        path: '/json/new?about:blank',
        method: 'PUT'
    }, (res) => {
        let data = '';
        res.on('data', chunk => data += chunk);
        res.on('end', () => {
            const page = JSON.parse(data);
            const ws = new globalThis.WebSocket(page.webSocketDebuggerUrl);
            let msgId = 1;
            let evalId = 0;
            function send(method, params = {}) {
                const id = msgId++;
                ws.send(JSON.stringify({ id, method, params }));
                return id;
            }

            ws.onopen = () => {
                send("Runtime.enable");
                send("Page.enable");
                send("Page.navigate", { url: htmlPath });
            };

            ws.onmessage = (event) => {
                const parsed = JSON.parse(event.data);
                if (parsed.method === "Page.loadEventFired") {
                    console.log("Page loaded. Testing visa suppliers...");
                    setTimeout(() => {
                        const testExpr = `
                            (function() {
                                const logs = [];
                                // 1. Click Visa tab
                                const visaTabBtn = document.querySelector('button[data-bs-target="#tab-visa"], a[href="#tab-visa"], [data-bs-target="#tab-visa"]');
                                if (visaTabBtn) {
                                    visaTabBtn.click();
                                    logs.push('Clicked Visa tab');
                                } else {
                                    logs.push('Visa tab button not found');
                                }

                                // 2. Check suppliers in .visa-supplier-select
                                const selects = document.querySelectorAll('select.visa-supplier-select');
                                logs.push('Found ' + selects.length + ' visa-supplier-select elements');
                                selects.forEach((s, idx) => {
                                    logs.push('Select ' + idx + ': value=' + s.value + ', options count=' + s.options.length + ', has select2=' + $(s).hasClass('select2-hidden-accessible'));
                                    for(let i=0; i<Math.min(s.options.length, 5); i++) {
                                        logs.push('  opt ' + i + ': val=' + s.options[i].value + ', text=' + s.options[i].text);
                                    }
                                    const s2Container = $(s).next('.select2-container');
                                    logs.push('  s2Container exists: ' + (s2Container.length > 0) + ', visible: ' + s2Container.is(':visible') + ', text: ' + s2Container.text().trim().replace(/\\s+/g, ' '));
                                });

                                // 3. Test changing select value directly or via select2
                                if (selects.length > 0) {
                                    const first = selects[0];
                                    if (first.options.length > 1) {
                                        const testVal = first.options[1].value;
                                        $(first).val(testVal).trigger('change');
                                        logs.push('After triggering change to val ' + testVal + ': select val=' + first.value + ', s2 text=' + $(first).next('.select2-container').text().trim().replace(/\\s+/g, ' '));
                                    }
                                }

                                // 4. Test applyDefaultVisaToAll
                                const defSup = document.getElementById('default_visa_supplier_id');
                                logs.push('default_visa_supplier_id exists: ' + !!defSup + ', options=' + (defSup ? defSup.options.length : 0));
                                if (defSup && defSup.options.length > 1) {
                                    defSup.value = defSup.options[1].value;
                                    logs.push('Set default_visa_supplier_id to ' + defSup.value);
                                    applyDefaultVisaToAll();
                                    selects.forEach((s, idx) => {
                                        const s2Container = $(s).next('.select2-container');
                                        logs.push('After applyDefaultVisaToAll -> Select ' + idx + ': val=' + s.value + ', s2 text=' + s2Container.text().trim().replace(/\\s+/g, ' '));
                                    });
                                }

                                // 5. Check hotel city filtering
                                logs.push('--- Hotel options test ---');
                                if (typeof buildHotelsOptions === 'function') {
                                    const makkahOpts = buildHotelsOptions('makkah');
                                    const madinahOpts = buildHotelsOptions('madinah');
                                    const otherOpts = buildHotelsOptions('other');
                                    logs.push('makkahOpts length: ' + makkahOpts.length);
                                    logs.push('madinahOpts length: ' + madinahOpts.length);
                                    logs.push('otherOpts length: ' + otherOpts.length);
                                } else {
                                    logs.push('buildHotelsOptions not defined');
                                }

                                return logs;
                            })()
                        `;
                        evalId = send("Runtime.evaluate", { expression: testExpr, returnByValue: true });
                    }, 500);
                }

                if (parsed.id === evalId) {
                    if (parsed.result && parsed.result.exceptionDetails) {
                        console.error("Eval Exception:", parsed.result.exceptionDetails);
                    } else {
                        console.log("Evaluation Results:", JSON.stringify(parsed.result?.result?.value, null, 2));
                    }
                    ws.close();
                    chrome.kill();
                    process.exit(0);
                }
            };
        });
    });
    req.end();
}

waitForPort();
