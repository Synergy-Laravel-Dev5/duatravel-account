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
    '--user-data-dir=' + path.resolve(__dirname, 'chrome_profile_5')
]);

function waitForPort(retries = 20) {
    if (retries <= 0) {
        console.error("Chrome failed to start in time");
        chrome.kill();
        process.exit(1);
    }
    http.get('http://127.0.0.1:9226/json/version', (res) => {
        let data = '';
        res.on('data', chunk => data += chunk);
        res.on('end', () => {
            connectCDP();
        });
    }).on('error', () => {
        setTimeout(() => waitForPort(retries - 1), 250);
    });
}

async function connectCDP() {
    const req = http.request({
        host: '127.0.0.1',
        port: 9226,
        path: '/json/new?' + encodeURIComponent(htmlPath),
        method: 'PUT'
    }, (res) => {
        let data = '';
        res.on('data', chunk => data += chunk);
        res.on('end', () => {
            const page = JSON.parse(data);
            const ws = new globalThis.WebSocket(page.webSocketDebuggerUrl);
            let msgId = 1;
            function send(method, params = {}) {
                const id = msgId++;
                ws.send(JSON.stringify({ id, method, params }));
                return id;
            }

            ws.onopen = () => {
                send('Console.enable');
                send('Runtime.enable');
                send('Page.enable');
            };

            function runTests() {
                send('Runtime.evaluate', {
                    expression: `(function() {
                        const out = {};
                        try {
                            out.initialHotels = document.querySelectorAll('#hotelsList .hotel-block').length;
                            out.initialRoutes = document.querySelectorAll('#routesList .route-block').length;

                            // 1. Add Makkah Hotel
                            window.addNewHotel('makkah');
                            out.afterAddHotel = document.querySelectorAll('#hotelsList .hotel-block').length;

                            // 2. Remove newly added hotel
                            const lastHotel = document.querySelector('#hotelsList .hotel-block:last-child');
                            const removeHotelBtn = lastHotel.querySelector('.remove-hotel');
                            out.newHotelHasRemoveBtn = !!removeHotelBtn;
                            if (removeHotelBtn) {
                                removeHotelBtn.click();
                                out.afterRemoveNewHotel = document.querySelectorAll('#hotelsList .hotel-block').length;
                            }

                            // 3. Add Route
                            window.addNewRoute();
                            out.afterAddRoute = document.querySelectorAll('#routesList .route-block').length;

                            // 4. Remove newly added route
                            const lastRoute = document.querySelector('#routesList .route-block:last-child');
                            const removeRouteBtn = lastRoute.querySelector('.remove-route');
                            out.newRouteHasRemoveBtn = !!removeRouteBtn;
                            if (removeRouteBtn) {
                                removeRouteBtn.click();
                                out.afterRemoveNewRoute = document.querySelectorAll('#routesList .route-block').length;
                            }

                            // 5. Test Pax 2 -> 3 -> 2
                            const paxInput = document.getElementById('no_of_pax');
                            paxInput.value = '3';
                            paxInput.dispatchEvent(new Event('input', { bubbles: true }));
                            paxInput.dispatchEvent(new Event('change', { bubbles: true }));
                            out.pax3_persons = document.querySelectorAll('#personsList .person-card').length;
                            out.pax3_visas = document.querySelectorAll('#visasList .visa-card').length;

                            paxInput.value = '2';
                            paxInput.dispatchEvent(new Event('input', { bubbles: true }));
                            paxInput.dispatchEvent(new Event('change', { bubbles: true }));
                            out.pax2_persons = document.querySelectorAll('#personsList .person-card').length;
                            out.pax2_visas = document.querySelectorAll('#visasList .visa-card').length;
                            out.barPaxBadgeHeader = document.getElementById('bar_pax_badge_header')?.textContent;
                            out.barPaxBadge = document.getElementById('bar_pax_badge')?.textContent;
                        } catch(e) {
                            out.err = e.message;
                        }
                        return out;
                    })()`,
                    returnByValue: true
                });

                setTimeout(() => {
                    ws.close();
                    chrome.kill();
                    process.exit(0);
                }, 2000);
            }

            ws.onmessage = (event) => {
                const msg = JSON.parse(event.data);
                if (msg.method === 'Page.loadEventFired') {
                    console.log("Page.loadEventFired received!");
                    setTimeout(runTests, 1000);
                }
                if (msg.result && msg.result.result) {
                    console.log("FINAL TEST RESULT:", JSON.stringify(msg.result.result.value));
                }
            };
        });
    });
    req.end();
}

waitForPort();
