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
                    console.log("Page loaded!");
                    setTimeout(() => {
                        evalId = send("Runtime.evaluate", {
                            expression: `(function() {
                                const results = {};
                                results.url = document.location.href;
                                results.title = document.title;

                                // TEST 1: Add Makkah Hotel (single click)
                                const beforeHotels = document.querySelectorAll('#hotelsList .hotel-block').length;
                                const btnMakkah = document.getElementById('btnAddMakkahHotel');
                                results.btnMakkahFound = !!btnMakkah;
                                if (btnMakkah) btnMakkah.click();
                                const afterHotels = document.querySelectorAll('#hotelsList .hotel-block').length;
                                results.beforeHotels = beforeHotels;
                                results.afterHotels = afterHotels;
                                results.hotelsAddedOn1Click = afterHotels - beforeHotels;

                                // TEST 2: Remove Hotel
                                const lastHotel = document.querySelector('#hotelsList .hotel-block:last-child');
                                const removeHtlBtn = lastHotel ? lastHotel.querySelector('.remove-hotel') : null;
                                if (removeHtlBtn) removeHtlBtn.click();
                                const afterRemoveHotels = document.querySelectorAll('#hotelsList .hotel-block').length;
                                results.hotelsAfterRemove = afterRemoveHotels;

                                // TEST 3: Add Route (single click)
                                const beforeRoutes = document.querySelectorAll('#routesList .route-block').length;
                                const btnRoute = document.getElementById('addRoute');
                                results.btnRouteFound = !!btnRoute;
                                if (btnRoute) btnRoute.click();
                                const afterRoutes = document.querySelectorAll('#routesList .route-block').length;
                                results.beforeRoutes = beforeRoutes;
                                results.afterRoutes = afterRoutes;
                                results.routesAddedOn1Click = afterRoutes - beforeRoutes;

                                // TEST 4: Remove Route
                                const lastRoute = document.querySelector('#routesList .route-block:last-child');
                                const removeRteBtn = lastRoute ? lastRoute.querySelector('.remove-route') : null;
                                if (removeRteBtn) removeRteBtn.click();
                                const afterRemoveRoutes = document.querySelectorAll('#routesList .route-block').length;
                                results.routesAfterRemove = afterRemoveRoutes;

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
