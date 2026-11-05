const { spawn } = require('child_process');
const http = require('http');
const path = require('path');

const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const htmlPath = 'file:///' + path.resolve(__dirname, 'edit_rendered.html').replace(/\\/g, '/');

const chrome = spawn(chromePath, [
    '--headless=new',
    '--remote-debugging-port=9226',
    '--disable-gpu',
    '--no-sandbox',
    '--user-data-dir=' + path.resolve(__dirname, 'chrome_profile_s2_tab')
]);

function waitForPort(retries = 20) {
    if (retries <= 0) {
        console.error("Chrome failed to start");
        chrome.kill();
        process.exit(1);
    }
    http.get('http://127.0.0.1:9226/json/version', () => {
        connectCDP();
    }).on('error', () => {
        setTimeout(() => waitForPort(retries - 1), 250);
    });
}

function connectCDP() {
    const req = http.request({
        host: '127.0.0.1',
        port: 9226,
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
                    setTimeout(() => {
                        const testExpr = `
                            (new Promise((resolve) => {
                                const logs = [];
                                // Bootstrap 5 tab switch
                                const tabEl = document.querySelector('a[href="#tab-visa"]');
                                if (tabEl) {
                                    tabEl.click();
                                    logs.push('Clicked a[href="#tab-visa"]');
                                }

                                setTimeout(() => {
                                    const visaTab = document.getElementById('tab-visa');
                                    logs.push('Visa tab is active: ' + visaTab.classList.contains('active') + ', display: ' + window.getComputedStyle(visaTab).display);

                                    const select = document.querySelector('select.visa-supplier-select');
                                    const s2 = $(select).next('.select2-container');
                                    logs.push('Select2 width: ' + s2.outerWidth() + ', select2 rendered text: ' + s2.find('.select2-selection__rendered').text().trim());

                                    // Let us test opening select2
                                    $(select).select2('open');
                                    logs.push('Called $(select).select2("open")');

                                    setTimeout(() => {
                                        const openDropdown = document.querySelector('.select2-container--open');
                                        logs.push('Select2 is open: ' + !!openDropdown);
                                        const results = document.querySelectorAll('.select2-results__option');
                                        logs.push('Results count: ' + results.length);
                                        results.forEach((r, idx) => {
                                            logs.push('  result ' + idx + ': ' + r.innerText);
                                        });

                                        resolve(logs);
                                    }, 200);
                                }, 300);
                            }))
                        `;
                        evalId = send("Runtime.evaluate", { expression: testExpr, awaitPromise: true, returnByValue: true });
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
