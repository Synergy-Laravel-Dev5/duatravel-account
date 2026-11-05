const { spawn } = require('child_process');
const http = require('http');
const path = require('path');

const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const htmlPath = 'file:///' + path.resolve(__dirname, 'edit_rendered.html').replace(/\\/g, '/');

const chrome = spawn(chromePath, [
    '--headless=new',
    '--remote-debugging-port=9222',
    '--disable-gpu',
    '--no-sandbox',
    '--user-data-dir=' + path.resolve(__dirname, 'chrome_profile')
]);

console.log("Starting Chrome...");

function waitForPort(retries = 20) {
    if (retries <= 0) {
        console.error("Chrome failed to start in time");
        chrome.kill();
        process.exit(1);
    }
    http.get('http://127.0.0.1:9222/json/version', (res) => {
        let data = '';
        res.on('data', chunk => data += chunk);
        res.on('end', () => {
            console.log("Chrome CDP ready!");
            connectCDP();
        });
    }).on('error', () => {
        setTimeout(() => waitForPort(retries - 1), 250);
    });
}

async function connectCDP() {
    // Put /json/new
    const req = http.request({
        host: '127.0.0.1',
        port: 9222,
        path: '/json/new?' + encodeURIComponent(htmlPath),
        method: 'PUT'
    }, (res) => {
        let data = '';
        res.on('data', chunk => data += chunk);
        res.on('end', () => {
            const page = JSON.parse(data);
            const wsUrl = page.webSocketDebuggerUrl;
            console.log("Connecting to page:", wsUrl);

            const ws = new globalThis.WebSocket(wsUrl);

            let msgId = 1;
            function send(method, params = {}) {
                const id = msgId++;
                ws.send(JSON.stringify({ id, method, params }));
                return id;
            }

            ws.onopen = () => {
                console.log("WebSocket connected!");
                send('Console.enable');
                send('Runtime.enable');
                send('Page.enable');
            };

            ws.onmessage = (event) => {
                const msg = JSON.parse(event.data);
                if (msg.method === 'Runtime.exceptionThrown') {
                    console.error("BROWSER EXCEPTION:", JSON.stringify(msg.params.exceptionDetails));
                }
                if (msg.method === 'Console.messageAdded') {
                    console.log("CONSOLE [" + msg.params.message.level + "]:", msg.params.message.text);
                }
                if (msg.result && msg.result.result) {
                    console.log("RESULT (" + msg.id + "):", JSON.stringify(msg.result.result.value));
                }
            };

            setTimeout(async () => {
                console.log("Checking window functions and initial counts in browser...");
                send('Runtime.evaluate', {
                    expression: `({
                        hasAddNewHotel: typeof window.addNewHotel,
                        hasAddNewRoute: typeof window.addNewRoute,
                        hasAddAdditionalRoom: typeof window.addAdditionalRoomToHotel,
                        hasHandlePaxChange: typeof window.handlePaxChange,
                        paxCount: document.getElementById('no_of_pax')?.value,
                        personsCount: document.querySelectorAll('#personsList .person-card').length,
                        visasCount: document.querySelectorAll('#visasList .visa-card').length,
                        hotelsCount: document.querySelectorAll('#hotelsList .hotel-block').length,
                        routesCount: document.querySelectorAll('#routesList .route-block').length
                    })`,
                    returnByValue: true
                });

                // Test clicking all buttons
                setTimeout(() => {
                    console.log("Testing addNewHotel, addNewRoute, and addAdditionalRoomToHotel...");
                    send('Runtime.evaluate', {
                        expression: `(function() {
                            const res = {};
                            try {
                                const hBefore = document.querySelectorAll('#hotelsList .hotel-block').length;
                                window.addNewHotel('makkah');
                                const hAfter = document.querySelectorAll('#hotelsList .hotel-block').length;
                                res.hotelAdded = (hAfter === hBefore + 1);
                                res.hotelCount = hAfter;
                            } catch(e) {
                                res.hotelError = e.message;
                            }

                            try {
                                const rBefore = document.querySelectorAll('#routesList .route-block').length;
                                window.addNewRoute();
                                const rAfter = document.querySelectorAll('#routesList .route-block').length;
                                res.routeAdded = (rAfter === rBefore + 1);
                                res.routeCount = rAfter;
                            } catch(e) {
                                res.routeError = e.message;
                            }

                            try {
                                const roomsBefore = document.querySelectorAll('.additional-room-item').length;
                                window.addAdditionalRoomToHotel(0);
                                const roomsAfter = document.querySelectorAll('.additional-room-item').length;
                                res.roomAdded = (roomsAfter === roomsBefore + 1);
                                res.roomCount = roomsAfter;
                            } catch(e) {
                                res.roomError = e.message;
                            }

                            try {
                                window.handlePaxChange(2);
                                res.pax2Persons = document.querySelectorAll('#personsList .person-card').length;
                                res.pax2Visas = document.querySelectorAll('#visasList .visa-card').length;
                                res.barPax = document.getElementById('bar_pax')?.textContent;
                                res.barPaxBadge = document.getElementById('bar_pax_badge')?.textContent;
                                res.barPaxBadgeHeader = document.getElementById('bar_pax_badge_header')?.textContent;
                            } catch(e) {
                                res.pax2Error = e.message;
                            }

                            try {
                                const hBeforeRemove = document.querySelectorAll('#hotelsList .hotel-block').length;
                                const firstRemoveBtn = document.querySelector('#hotelsList .remove-hotel');
                                res.hasHotelRemoveBtn = !!firstRemoveBtn;
                                if (firstRemoveBtn) {
                                    firstRemoveBtn.click();
                                    const hAfterRemove = document.querySelectorAll('#hotelsList .hotel-block').length;
                                    res.hotelRemoved = (hAfterRemove === hBeforeRemove - 1);
                                    res.hCountAfterRemove = hAfterRemove;
                                }
                            } catch(e) {
                                res.removeHotelError = e.message;
                            }

                            try {
                                const rBeforeRemove = document.querySelectorAll('#routesList .route-block').length;
                                const firstRouteRemoveBtn = document.querySelector('#routesList .remove-route');
                                res.hasRouteRemoveBtn = !!firstRouteRemoveBtn;
                                if (firstRouteRemoveBtn) {
                                    firstRouteRemoveBtn.click();
                                    const rAfterRemove = document.querySelectorAll('#routesList .route-block').length;
                                    res.routeRemoved = (rAfterRemove === rBeforeRemove - 1);
                                    res.rCountAfterRemove = rAfterRemove;
                                }
                            } catch(e) {
                                res.removeRouteError = e.message;
                            }

                            return res;
                        })()`,
                        returnByValue: true
                    });
                }, 1000);

                setTimeout(() => {
                    ws.close();
                    chrome.kill();
                    process.exit(0);
                }, 4000);
            }, 2000);
        });
    });
    req.end();
}

waitForPort();
