const { spawn } = require('child_process');
const http = require('http');
const path = require('path');

const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const htmlPath = 'file:///' + path.resolve(__dirname, 'edit_rendered.html').replace(/\\/g, '/');

const chrome = spawn(chromePath, [
    '--headless=new',
    '--remote-debugging-port=9227',
    '--disable-gpu',
    '--no-sandbox',
    '--user-data-dir=' + path.resolve(__dirname, 'chrome_profile_verify_all')
]);

function waitForPort(retries = 20) {
    if (retries <= 0) {
        console.error("Chrome failed to start");
        chrome.kill();
        process.exit(1);
    }
    http.get('http://127.0.0.1:9227/json/version', () => {
        connectCDP();
    }).on('error', () => {
        setTimeout(() => waitForPort(retries - 1), 250);
    });
}

function connectCDP() {
    const req = http.request({
        host: '127.0.0.1',
        port: 9227,
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
                                const results = {};

                                // 1. HOTEL TAB & ADDITIONAL ROOM TEST
                                document.querySelector('a[href="#tab-hotel"]').click();
                                const firstHotel = document.querySelector('.hotel-block');
                                const baseCostText = firstHotel.querySelector('.hotel-cost-sar-disp').textContent;
                                const baseSellText = firstHotel.querySelector('.hotel-sell-sar-disp').textContent;
                                const nights = parseFloat(firstHotel.querySelector('.hotel-nights')?.value) || 1;
                                results.baseCost = baseCostText;
                                results.baseSell = baseSellText;
                                results.nights = nights;

                                // Add Additional Room
                                addAdditionalRoomToHotel(0);
                                const extraItem = firstHotel.querySelector('.additional-room-item:last-child');
                                results.extraRoomAdded = !!extraItem;

                                if (extraItem) {
                                    const costInp = extraItem.querySelector('.add-room-cost');
                                    const sellInp = extraItem.querySelector('.add-room-sell');
                                    costInp.value = '100';
                                    costInp.dispatchEvent(new Event('input', { bubbles: true }));
                                    sellInp.value = '250';
                                    sellInp.dispatchEvent(new Event('input', { bubbles: true }));

                                    results.afterExtraCost = firstHotel.querySelector('.hotel-cost-sar-disp').textContent;
                                    results.afterExtraSell = firstHotel.querySelector('.hotel-sell-sar-disp').textContent;

                                    // Check passenger rows divide
                                    const paxRows = firstHotel.querySelectorAll('.hotel-person-row');
                                    results.paxCostValues = Array.from(paxRows).map(r => r.querySelector('.hotel-person-cost').value);
                                    results.paxSellValues = Array.from(paxRows).map(r => r.querySelector('.hotel-person-sell').value);

                                    // Remove additional room
                                    extraItem.querySelector('button.btn-outline-danger').click();
                                    results.afterRemoveCost = firstHotel.querySelector('.hotel-cost-sar-disp').textContent;
                                    results.afterRemoveSell = firstHotel.querySelector('.hotel-sell-sar-disp').textContent;
                                }

                                // 2. HOTEL CITY FILTERING TEST
                                const makkahOpts = buildHotelsOptions('makkah');
                                const madinahOpts = buildHotelsOptions('madinah');
                                const otherOpts = buildHotelsOptions('other');

                                results.makkahContainsMadinah = madinahOpts.length > 0 && makkahOpts.includes('Dar Al Taqwa');
                                results.madinahContainsMakkah = makkahOpts.length > 0 && madinahOpts.includes('Swissôtel Makkah');
                                results.otherContainsMakkah = makkahOpts.length > 0 && otherOpts.includes('Swissôtel Makkah');
                                results.otherContainsMadinah = madinahOpts.length > 0 && otherOpts.includes('Dar Al Taqwa');
                                results.otherHasKarachi = otherOpts.includes('Karachi');

                                // 3. VISA TAB & SUPPLIER SELECTION
                                document.querySelector('a[href="#tab-visa"]').click();
                                const defSup = document.getElementById('default_visa_supplier_id');
                                if (defSup && defSup.options.length > 1) {
                                    defSup.value = defSup.options[1].value;
                                    defSup.dispatchEvent(new Event('change', { bubbles: true }));

                                    const vSelects = document.querySelectorAll('#visasList select.visa-supplier-select');
                                    results.visasSuppliers = Array.from(vSelects).map(s => {
                                        const s2Text = $(s).next('.select2-container').find('.select2-selection__rendered').text().trim();
                                        return { val: s.value, s2Text: s2Text };
                                    });
                                }

                                resolve(results);
                            }))
                        `;
                        evalId = send("Runtime.evaluate", { expression: testExpr, awaitPromise: true, returnByValue: true });
                    }, 500);
                }

                if (parsed.id === evalId) {
                    if (parsed.result && parsed.result.exceptionDetails) {
                        console.error("Eval Exception:", parsed.result.exceptionDetails);
                    } else {
                        console.log("TEST RESULTS:\n", JSON.stringify(parsed.result?.result?.value, null, 2));
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
