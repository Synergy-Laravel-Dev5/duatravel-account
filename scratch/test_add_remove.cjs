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
    '--user-data-dir=' + path.resolve(__dirname, 'chrome_profile_buttons')
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
        path: '/json/new?' + encodeURIComponent(htmlPath),
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
                
                setTimeout(() => {
                    evalId = send("Runtime.evaluate", {
                        expression: `(function() {
                            const res = {};
                            const paxInput = document.getElementById('no_of_pax');
                            res.initialPax = paxInput ? paxInput.value : null;
                            res.initialCards = document.querySelectorAll('#personsList .person-card').length;
                            
                            // Change pax to 3
                            if (paxInput) {
                                paxInput.value = '3';
                                paxInput.dispatchEvent(new Event('input', {bubbles: true}));
                                paxInput.dispatchEvent(new Event('change', {bubbles: true}));
                            }
                            res.cardsAfter3 = document.querySelectorAll('#personsList .person-card').length;
                            res.hotelPaxAfter3 = document.querySelectorAll('.hotel-person-row').length;
                            
                            // Change pax to 2
                            if (paxInput) {
                                paxInput.value = '2';
                                paxInput.dispatchEvent(new Event('input', {bubbles: true}));
                                paxInput.dispatchEvent(new Event('change', {bubbles: true}));
                            }
                            res.cardsAfter2 = document.querySelectorAll('#personsList .person-card').length;
                            res.hotelPaxAfter2 = document.querySelectorAll('.hotel-person-row').length;

                            return res;
                        })()`,
                        returnByValue: true
                    });
                }, 2000);
            };

            ws.onmessage = (msg) => {
                const parsed = JSON.parse(msg.data);
                if (parsed.id === evalId) {
                    console.log("Evaluation result:", JSON.stringify(parsed.result.result.value, null, 2));
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
