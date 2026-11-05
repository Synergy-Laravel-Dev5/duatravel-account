const { spawn } = require('child_process');
const http = require('http');
const path = require('path');

const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const htmlPath = 'file:///' + path.resolve(__dirname, 'edit_rendered.html').replace(/\\/g, '/');

const chrome = spawn(chromePath, [
    '--headless=new',
    '--remote-debugging-port=9223',
    '--disable-gpu',
    '--no-sandbox',
    '--user-data-dir=' + path.resolve(__dirname, 'chrome_profile_comp')
]);

function waitForPort(retries = 20) {
    if (retries <= 0) {
        console.error("Chrome failed to start");
        chrome.kill();
        process.exit(1);
    }
    http.get('http://127.0.0.1:9223/json/version', () => {
        connectCDP();
    }).on('error', () => {
        setTimeout(() => waitForPort(retries - 1), 250);
    });
}

function connectCDP() {
    const req = http.request({
        host: '127.0.0.1',
        port: 9223,
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

            ws.onmessage = (msg) => {
                const parsed = JSON.parse(msg.data);
                if (parsed.method === 'Page.loadEventFired') {
                    setTimeout(() => {
                        evalId = send("Runtime.evaluate", {
                            expression: `(function() {
                                let err = null;
                                try {
                                    addNewHotel('makkah');
                                } catch(e) {
                                    err = e.message;
                                }
                                return {
                                    err,
                                    hotelsCount: document.querySelectorAll('#hotelsList .hotel-block').length,
                                    hotelsListExists: !!document.getElementById('hotelsList')
                                };
                            })()`,
                            returnByValue: true
                        });
                    }, 500);
                }
                if (parsed.id === evalId) {
                    console.log("Result:", JSON.stringify(parsed.result?.result?.value, null, 2));
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
