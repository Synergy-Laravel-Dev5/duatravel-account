const { spawn } = require('child_process');
const http = require('http');
const path = require('path');

const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const htmlPath = 'file:///' + path.resolve(__dirname, 'edit_rendered.html').replace(/\\/g, '/');

const chrome = spawn(chromePath, [
    '--headless=new',
    '--remote-debugging-port=9225',
    '--disable-gpu',
    '--no-sandbox',
    '--user-data-dir=' + path.resolve(__dirname, 'chrome_profile_visa_click')
]);

function waitForPort(retries = 20) {
    if (retries <= 0) {
        console.error("Chrome failed to start");
        chrome.kill();
        process.exit(1);
    }
    http.get('http://127.0.0.1:9225/json/version', () => {
        connectCDP();
    }).on('error', () => {
        setTimeout(() => waitForPort(retries - 1), 250);
    });
}

function connectCDP() {
    const req = http.request({
        host: '127.0.0.1',
        port: 9225,
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
                                // Switch to visa tab
                                const tabBtn = document.querySelector('[data-bs-target="#tab-visa"]');
                                if (tabBtn) tabBtn.click();
                                logs.push('Visa tab active: ' + document.getElementById('tab-visa')?.classList.contains('active'));

                                setTimeout(() => {
                                    // Find first visa supplier select
                                    const select = document.querySelector('select.visa-supplier-select');
                                    const s2 = $(select).next('.select2-container');
                                    logs.push('Select2 exists: ' + (s2.length > 0));
                                    const selection = s2.find('.select2-selection')[0];
                                    if (selection) {
                                        // Click to open
                                        selection.dispatchEvent(new MouseEvent('mousedown', { bubbles: true }));
                                        selection.dispatchEvent(new MouseEvent('mouseup', { bubbles: true }));
                                        selection.click();
                                        logs.push('Clicked select2-selection');
                                    }

                                    setTimeout(() => {
                                        const dropdown = document.querySelector('.select2-dropdown');
                                        logs.push('Dropdown exists: ' + !!dropdown);
                                        if (dropdown) {
                                            const isVisible = $(dropdown).is(':visible');
                                            const offset = $(dropdown).offset();
                                            logs.push('Dropdown visible: ' + isVisible + ', top=' + offset.top + ', left=' + offset.left);
                                            const options = dropdown.querySelectorAll('.select2-results__option');
                                            logs.push('Options in dropdown: ' + options.length);
                                            options.forEach((opt, idx) => {
                                                logs.push('  opt ' + idx + ': text=' + opt.innerText + ', aria-selected=' + opt.getAttribute('aria-selected'));
                                            });

                                            // Click an option if available
                                            if (options.length > 1) {
                                                options[1].dispatchEvent(new MouseEvent('mouseup', { bubbles: true }));
                                                options[1].click();
                                                logs.push('Clicked option 1');
                                            }
                                        }

                                        setTimeout(() => {
                                            logs.push('After click option: select value=' + select.value + ', s2 text=' + s2.text().trim().replace(/\\s+/g, ' '));
                                            resolve(logs);
                                        }, 200);
                                    }, 200);
                                }, 200);
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
