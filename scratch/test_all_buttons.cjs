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
    '--user-data-dir=' + path.resolve(__dirname, 'chrome_profile_all_btns')
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
                                const results = {};

                                // TEST Madinah Hotel
                                const count0 = document.querySelectorAll('#hotelsList .hotel-block').length;
                                document.getElementById('btnAddMadinahHotel').click();
                                const count1 = document.querySelectorAll('#hotelsList .hotel-block').length;
                                results.madinahAdded = count1 - count0;

                                // TEST Other Hotel
                                document.getElementById('btnAddOtherHotel').click();
                                const count2 = document.querySelectorAll('#hotelsList .hotel-block').length;
                                results.otherAdded = count2 - count1;

                                // TEST Rapid Clicks on Add Route (Debounce test: 3 rapid clicks)
                                const rCount0 = document.querySelectorAll('#routesList .route-block').length;
                                const btnR = document.getElementById('addRoute');
                                btnR.click();
                                btnR.click();
                                btnR.click();
                                const rCount1 = document.querySelectorAll('#routesList .route-block').length;
                                results.routesAfter3RapidClicks = rCount1 - rCount0; // Should be 1 due to debounce!

                                // TEST Add Additional Room to Hotel 0
                                const h0 = document.querySelector('.hotel-block');
                                const roomsBefore = h0 ? h0.querySelectorAll('.additional-room-item').length : 0;
                                addAdditionalRoomToHotel(0);
                                const roomsAfter = h0 ? h0.querySelectorAll('.additional-room-item').length : 0;
                                results.additionalRoomAdded = roomsAfter - roomsBefore;

                                return results;
                            })()`,
                            returnByValue: true
                        });
                    }, 500);
                }
                if (parsed.id === evalId) {
                    if (parsed.result && parsed.result.exceptionDetails) {
                        console.error("Eval Exception:", parsed.result.exceptionDetails);
                    } else {
                        console.log("Evaluation result:", JSON.stringify(parsed.result?.result?.value, null, 2));
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
