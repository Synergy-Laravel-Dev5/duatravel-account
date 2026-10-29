
;

        const packagesData     = {};
        const clientsData      = {};
        const hotelsData       = {};
        const suppliersData    = {};
        const airlinesData     = {};
        const routesData       = {};
        const vehiclesData     = {};
        const existingVisas    = {};
        const existingPersons  = {};
        const existingBooking  = {};
        const transactionsPaid = "dummy";

        const suppliersOptions = `<option value="">-- Select Supplier --</option>` +
            (typeof suppliersData !== 'undefined' ? suppliersData.map(s => `<option value="${s.id}">${s.name}</option>`).join('') : '');

        const hotelsOptions = `<option value="">Select Hotel</option>` +
            (typeof hotelsData !== 'undefined' ? hotelsData.map(h => `<option value="${h.name}">${h.name} (${h.city || h.place || ''})</option>`).join('') : '');

        function buildRoutesOptions() {
            return `<option value="">-- Select Route --</option>` +
                (Array.isArray(routesData) ? routesData.map(r => {
                    const val = (r.start_place && r.end_place) ? `${r.start_place} TO ${r.end_place}` : (r.formatted_route || '');
                    const txt = (r.start_place && r.end_place) ? `${r.start_place} → ${r.end_place}` : (r.formatted_route || '');
                    return `<option value="${val}">${txt}</option>`;
                }).join('') : '');
        }
        let routesOptions = buildRoutesOptions();

        function buildAirlinesOptions() {
            return `<option value="">-- Select Airline (Optional) --</option>` +
                (Array.isArray(airlinesData) ? airlinesData.map(a => `<option value="${a.name}">${a.name}${a.iata_code ? ' (' + a.iata_code + ')' : ''}</option>`).join('') : '');
        }
        let airlinesOptions = buildAirlinesOptions();

        function buildVehiclesOptions() {
            return `<option value="">-- Select Vehicle --</option>` +
                (Array.isArray(vehiclesData) ? vehiclesData.map(v => {
                    const val = v.brand_name || v.vehicle_type || '';
                    const label = v.vehicle_type + (v.brand_name ? ` (${v.brand_name})` : '');
                    return `<option value="${val}">${label}</option>`;
                }).join('') : '');
        }
        let vehiclesOptions  = buildVehiclesOptions();

        function updatePersonFullName(idx) {
            const sur   = document.getElementById(`person_surname_${idx}`)?.value || '';
            const given = document.getElementById(`person_given_name_${idx}`)?.value || '';
            const combined = (sur + ' ' + given).trim();
            const el = document.getElementById(`person_name_${idx}`);
            if (el) el.value = combined;

            // Sync with flight table passenger name
            const flNameDisp = document.querySelector(`#flight_person_row_${idx} .person-flight-name-disp`);
            const flNameInput = document.querySelector(`#flight_person_row_${idx} .person-flight-name`);
            if (flNameDisp && combined) flNameDisp.textContent = combined;
            if (flNameInput && combined) flNameInput.value = combined;

            // Sync with all hotel tables passenger names
            document.querySelectorAll(`.hotel-person-row[data-person-idx="${idx}"]`).forEach(pRow => {
                const hNameDisp = pRow.querySelector('.person-hotel-name-disp');
                const hNameInput = pRow.querySelector('.person-hotel-name');
                if (hNameDisp && combined) hNameDisp.textContent = combined;
                if (hNameInput && combined) hNameInput.value = combined;
            });
        }

        // Point 12: Dropdown Auto-Refresh for Clients
        async function refreshClientsDropdown(showNotification = true) {
            try {
                const clientSelect = document.getElementById('clientSelect');
                const curVal = clientSelect ? clientSelect.value : '';
                const res = await fetch('"dummy"');
                const data = await res.json();
                if (Array.isArray(data)) {
                    if (clientSelect) {
                        let html = '<option value="">-- Select Client --</option>';
                        data.forEach(c => {
                            const compStr = c.company_name ? ` (${c.company_name})` : '';
                            const phoneCnic = c.phone || c.cnic || '';
                            const isSel = (c.id == curVal) ? 'selected' : '';
                            html += `<option value="${c.id}" data-name="${c.name}" data-phone="${c.phone || ''}" data-cnic="${c.cnic || ''}" data-passport="${c.passport_number || ''}" data-company="${c.company_name || ''}" ${isSel}>${c.name}${compStr} - ${phoneCnic}</option>`;
                        });
                        clientSelect.innerHTML = html;
                        if (curVal) clientSelect.value = curVal;
                        if (typeof $ !== 'undefined' && $(clientSelect).data('select2')) {
                            $(clientSelect).trigger('change.select2');
                        }
                    }
                    if (showNotification) {
                        showAutoRefreshToast('Clients dropdown refreshed without losing data!');
                    }
                }
            } catch(e) {
                console.error('Error refreshing clients:', e);
            }
        }

        // Point 12: Dropdown Auto-Refresh for Packages
        async function refreshPackagesDropdown(showNotification = true) {
            try {
                const pkgSelect = document.getElementById('packageSelect');
                const curVal = pkgSelect ? pkgSelect.value : '';
                const res = await fetch('"dummy"');
                const data = await res.json();
                if (Array.isArray(data)) {
                    if (pkgSelect) {
                        let html = '<option value="">-- Select Package --</option>';
                        data.forEach(p => {
                            const codeStr = p.code ? ` (${p.code})` : '';
                            const isSel = (p.id == curVal) ? 'selected' : '';
                            html += `<option value="${p.id}" ${isSel}>${p.name}${codeStr}</option>`;
                        });
                        pkgSelect.innerHTML = html;
                        if (curVal) pkgSelect.value = curVal;
                    }
                    if (showNotification) {
                        showAutoRefreshToast('Packages dropdown refreshed without losing data!');
                    }
                }
            } catch(e) {
                console.error('Error refreshing packages:', e);
            }
        }

        function showAutoRefreshToast(msg) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show p-2 px-3 mb-2 shadow position-fixed top-0 end-0 m-3';
            alertDiv.style.zIndex = '99999';
            alertDiv.innerHTML = `<i class="mdi mdi-check-circle me-1"></i> <strong>Refreshed!</strong> ${msg} <button type="button" class="btn-close p-2" data-bs-dismiss="alert"></button>`;
            document.body.appendChild(alertDiv);
            setTimeout(() => alertDiv.remove(), 3500);
        }

        function handleDefaultVisaCurrencyChange(val) {
            const costRoeInput = document.getElementById('default_visa_cost_roe');
            const saleRoeInput = document.getElementById('default_visa_sale_roe');
            const currLabels = document.querySelectorAll('.def-visa-curr-label');

            currLabels.forEach(el => el.textContent = val);

            if (val === 'PKR') {
                if (costRoeInput) { costRoeInput.value = '1.0000'; costRoeInput.readOnly = true; }
                if (saleRoeInput) { saleRoeInput.value = '1.0000'; saleRoeInput.readOnly = true; }
            } else {
                if (costRoeInput) {
                    costRoeInput.readOnly = false;
                    if (val === 'SAR') costRoeInput.value = '75.0000';
                    else if (val === 'MYR') costRoeInput.value = '63.0000';
                    else if (val === 'USD') costRoeInput.value = '278.0000';
                    else if (val === 'AED') costRoeInput.value = '76.0000';
                }
                if (saleRoeInput) {
                    saleRoeInput.readOnly = false;
                    if (val === 'SAR') saleRoeInput.value = '76.0000';
                    else if (val === 'MYR') saleRoeInput.value = '64.0000';
                    else if (val === 'USD') saleRoeInput.value = '280.0000';
                    else if (val === 'AED') saleRoeInput.value = '77.0000';
                }
            }
        }

        // Point 14: Apply Default Visa to All Passengers
        function applyDefaultVisaToAll() {
            const defSup = document.getElementById('default_visa_supplier_id')?.value;
            const defCurr = document.getElementById('default_visa_currency')?.value || 'SAR';
            const isPkr = (defCurr === 'PKR');
            const defCostRoe = isPkr ? '1.0000' : (document.getElementById('default_visa_cost_roe')?.value || '75.0000');
            const defSaleRoe = isPkr ? '1.0000' : (document.getElementById('default_visa_sale_roe')?.value || '76.0000');
            const defCost = document.getElementById('default_visa_cost_rate')?.value;
            const defSell = document.getElementById('default_visa_sell_rate')?.value;

            document.querySelectorAll('#visasList .visa-card').forEach(card => {
                if (defSup) {
                    const supSel = card.querySelector('select[name*="[supplier_id]"]');
                    if (supSel) supSel.value = defSup;
                }

                const currSel = card.querySelector('.visa-currency') || card.querySelector('select[name*="[currency]"]');
                if (currSel) {
                    currSel.value = defCurr;
                }

                const costRoeInput = card.querySelector('.visa-cost-roe');
                const saleRoeInput = card.querySelector('.visa-sale-roe');
                const legacyRoe = card.querySelector('.visa-roe');

                if (isPkr) {
                    if (costRoeInput) { costRoeInput.value = '1.0000'; costRoeInput.readOnly = true; }
                    if (saleRoeInput) { saleRoeInput.value = '1.0000'; saleRoeInput.readOnly = true; }
                    if (legacyRoe) legacyRoe.value = '1.0000';
                } else {
                    if (costRoeInput) {
                        costRoeInput.readOnly = false;
                        if (defCostRoe !== '' && defCostRoe !== undefined) costRoeInput.value = defCostRoe;
                    }
                    if (saleRoeInput) {
                        saleRoeInput.readOnly = false;
                        if (defSaleRoe !== '' && defSaleRoe !== undefined) saleRoeInput.value = defSaleRoe;
                    }
                    if (legacyRoe && defCostRoe !== '' && defCostRoe !== undefined) legacyRoe.value = defCostRoe;
                }

                if (defCost !== '' && defCost !== undefined) {
                    const cInp = card.querySelector('.visa-cost-rate');
                    if (cInp) cInp.value = defCost;
                }
                if (defSell !== '' && defSell !== undefined) {
                    const sInp = card.querySelector('.visa-sell-rate');
                    if (sInp) sInp.value = defSell;
                }

                card.querySelectorAll('.visa-curr-label').forEach(el => el.textContent = defCurr);
                card.querySelectorAll('.visa-curr-prefix').forEach(el => el.textContent = `1 ${defCurr} =`);
                card.querySelectorAll('.visa-curr-name').forEach(el => el.textContent = `${defCurr}:`);

                if (typeof calculateVisaRow === 'function') {
                    calculateVisaRow(card);
                }
            });
            if (typeof calcTotal === 'function') {
                calcTotal();
            }
            showAutoRefreshToast('Default visa supplier, currency, ROE, and rates applied to all passengers!');
        }

        // Point 4: Live Unique Ticket Check
        function checkUniqueTicketNumbers() {
            const inputs = document.querySelectorAll('.person-flight-ticket');
            const seen = {};
            inputs.forEach(inp => {
                inp.classList.remove('is-invalid');
                const existingWarn = inp.parentElement.querySelector('.ticket-dup-warn');
                if (existingWarn) existingWarn.remove();
            });

            inputs.forEach(inp => {
                const val = inp.value.trim().toLowerCase();
                if (val) {
                    if (seen[val]) {
                        inp.classList.add('is-invalid');
                        seen[val].classList.add('is-invalid');
                        if (!inp.parentElement.querySelector('.ticket-dup-warn')) {
                            const msg = document.createElement('small');
                            msg.className = 'text-danger d-block ticket-dup-warn fs-11 fw-semibold mt-1';
                            msg.innerHTML = '<i class="mdi mdi-alert-circle me-1"></i>Duplicate ticket!';
                            inp.parentElement.appendChild(msg);
                        }
                    } else {
                        seen[val] = inp;
                    }
                }
            });
        }

        // Client Select Auto-population
        document.getElementById('clientSelect')?.addEventListener('change', function() {
            const clientId = parseInt(this.value);
            if (!clientId) return;
            const client = clientsData.find(c => c.id === clientId);
            if (!client) return;

            if (document.getElementById('fill_passport')) document.getElementById('fill_passport').value = client.passport_number || '';
            if (document.getElementById('fill_cnic')) document.getElementById('fill_cnic').value = client.cnic || '';
            if (document.getElementById('fill_phone')) document.getElementById('fill_phone').value = client.phone || '';

            // Auto-fill Primary Passenger (idx 0)
            if (document.getElementById('person_name_0')) document.getElementById('person_name_0').value = client.name || '';
            if (document.getElementById('person_passport_0')) document.getElementById('person_passport_0').value = client.passport_number || '';
            if (document.getElementById('person_cnic_0')) document.getElementById('person_cnic_0').value = client.cnic || '';

            const parts = (client.name || '').trim().split(' ');
            if (parts.length > 1) {
                if (document.getElementById('person_surname_0')) document.getElementById('person_surname_0').value = parts[0];
                if (document.getElementById('person_given_name_0')) document.getElementById('person_given_name_0').value = parts.slice(1).join(' ');
            } else {
                if (document.getElementById('person_given_name_0')) document.getElementById('person_given_name_0').value = client.name || '';
            }

            syncFlightPersons();
        });

        function onPackageSelectChange(sel) {
            const pkgId = parseInt(sel.value);
            if (!pkgId) return;

            const pkg = packagesData.find(p => p.id === pkgId);
            if (!pkg) return;

            const isPkgUmrah = (pkg.package_type === 'umrah' || (!pkg.package_type && document.getElementById('package_type')?.value === 'umrah'));

            const pkgNameContainer = document.getElementById('package_name_container');
            const clientBlock = document.getElementById('clientBlock');
            const companyBlock = document.getElementById('companyBlock');
            const bookingForInput = document.getElementById('booking_for_input');
            const noteBlock = document.getElementById('personsBookingForNote');

            if (isPkgUmrah) {
                if (pkgNameContainer) pkgNameContainer.style.display = 'none';
                if (clientBlock) clientBlock.style.display = '';
                if (companyBlock) companyBlock.style.display = 'none';
                if (bookingForInput) bookingForInput.value = 'client';
                if (noteBlock) noteBlock.classList.add('d-none');
            } else {
                if (pkgNameContainer) pkgNameContainer.style.display = '';
                if (clientBlock) clientBlock.style.display = 'none';
                if (companyBlock) companyBlock.style.display = '';
                if (bookingForInput) bookingForInput.value = 'company';
                if (noteBlock) noteBlock.classList.remove('d-none');
            }

            if (document.getElementById('package_name')) {
                document.getElementById('package_name').value = pkg.name || '';
            }
            if (document.getElementById('package_year') && pkg.year) {
                document.getElementById('package_year').value = pkg.year;
            }

            const pkgCost = parseFloat(pkg.package_amount || pkg.adult_pkr || pkg.adult_sar || 0);
            if (document.getElementById('package_cost')) {
                document.getElementById('package_cost').value = pkgCost ? pkgCost.toFixed(2) : '0.00';
            }

            syncBookingHotelDates();
            calcTotal();
        }

        const tabLinks = Array.from(document.querySelectorAll('#bookingTabs .nav-link'));

        function activateTab(idx) {
            if (idx < 0 || idx >= tabLinks.length) return;
            tabLinks[idx].click();
            const href = tabLinks[idx].getAttribute('href');
            if (href === '#tab-flight') syncFlightPersons();
            if (href === '#tab-visa') {
                const pax = parseInt(document.getElementById('no_of_pax')?.value) || 1;
                rebuildVisasList(pax);
            }
            if (href === '#tab-persons') {
                const pax = parseInt(document.getElementById('no_of_pax')?.value) || 1;
                const container = document.getElementById('personsList');
                if (container && container.children.length === 0) rebuildPersonsList(pax);
            }
            if (href === '#tab-costing') {
                calculateFlight();
                calcTotal();
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function activateTabByHref(href) {
            const link = document.querySelector(`#bookingTabs .nav-link[href="${href}"]`);
            if (link) {
                link.click();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }

        function submitBookingForm(btn) {
            const form = document.getElementById('bookingForm');
            if (!form) return false;

            // 1. Client Validation
            const clientSelect = document.getElementById('clientSelect');
            if (!clientSelect || !clientSelect.value) {
                activateTabByHref('#tab-details');
                alert('Please select a Client before updating the booking.');
                setTimeout(() => {
                    if (typeof $ !== 'undefined' && $(clientSelect).data('select2')) {
                        $(clientSelect).select2('open');
                    } else if (clientSelect) {
                        clientSelect.focus();
                    }
                }, 250);
                return false;
            }

            // 2. Passengers Validation
            const adultsInput = document.getElementById('pax_adults');
            const paxAdults = parseInt(adultsInput?.value) || 0;
            if (paxAdults < 1) {
                activateTabByHref('#tab-details');
                alert('Please specify at least 1 Adult passenger.');
                adultsInput?.focus();
                return false;
            }

            // 3. Make sure totals and calculations are up to date
            if (typeof calcTotal === 'function') {
                calcTotal();
            }

            // 4. Update UI: show spinner & disable buttons
            const submitBtns = document.querySelectorAll('button[type="submit"], .btn-save-booking');
            submitBtns.forEach(b => {
                b.disabled = true;
                b.innerHTML = '<i class="mdi mdi-loading mdi-spin me-1"></i> Updating Booking...';
            });

            // 5. Submit the form
            form.submit();
            return true;
        }

        document.querySelectorAll('#bookingTabs .nav-link').forEach(link => {
            link.addEventListener('click', function() {
                const target = this.getAttribute('href');
                if (target === '#tab-flight') syncFlightPersons();
                if (target === '#tab-visa') {
                    const pax = parseInt(document.getElementById('no_of_pax')?.value) || 1;
                    rebuildVisasList(pax);
                }
                if (target === '#tab-persons') {
                    const pax = parseInt(document.getElementById('no_of_pax')?.value) || 1;
                    const container = document.getElementById('personsList');
                    if (container && container.children.length === 0) rebuildPersonsList(pax);
                }
                if (target === '#tab-costing') {
                    calculateFlight();
                    calcTotal();
                }
            });
        });

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-next, .btn-prev');
            if (!btn) return;
            const cur = tabLinks.findIndex(t => t.classList.contains('active'));
            btn.classList.contains('btn-next') ? activateTab(cur + 1) : activateTab(cur - 1);
        });

        function rebuildPersonsList(newPax) {
            const container = document.getElementById('personsList');
            if (!container) return;
            const currentCount = container.querySelectorAll('.person-card').length;

            if (newPax < currentCount) {
                for (let i = currentCount - 1; i >= newPax; i--) {
                    document.getElementById('person_card_' + i)?.remove();
                }
                updatePaxCountsFromPersons();
                syncFlightPersons();
                syncAllHotelPersons();
                return;
            }

            for (let i = currentCount; i < newPax; i++) {
                container.insertAdjacentHTML('beforeend', `
                    <div class="person-card" id="person_card_${i}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong class="text-primary" style="font-size:13px;">Passenger ${i + 1}</strong>
                            <span class="badge bg-primary person-type-badge" id="person_type_badge_${i}">Adult</span>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold" style="font-size:12px;">Passenger Type <span class="text-danger">*</span></label>
                                <select name="persons[${i}][pax_type]" id="person_pax_type_${i}" class="form-select form-select-sm person-pax-type-select" onchange="onPersonPaxTypeChange(${i}, this.value)">
                                    <option value="adult" selected>Adult (12+ yrs)</option>
                                    <option value="child">Child (2-11 yrs)</option>
                                    <option value="infant">Infant (0-2 yrs)</option>
                                </select>
                            </div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Surname (Passport)</label>
                                <input type="text" name="persons[${i}][surname]" id="person_surname_${i}" class="form-control form-control-sm" oninput="updatePersonFullName(${i})"></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Given Name (Passport)</label>
                                <input type="text" name="persons[${i}][given_name]" id="person_given_name_${i}" class="form-control form-control-sm" oninput="updatePersonFullName(${i})"></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Full Name</label>
                                <input type="text" name="persons[${i}][full_name]" id="person_name_${i}" class="form-control form-control-sm"></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Passport #</label>
                                <input type="text" name="persons[${i}][passport_number]" id="person_passport_${i}" class="form-control form-control-sm"></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">DOB</label>
                                <input type="date" name="persons[${i}][dob]" class="form-control form-control-sm"></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Gender</label>
                                <select name="persons[${i}][gender]" class="form-select form-select-sm">
                                    <option value="">-- Select --</option><option value="male">Male</option><option value="female">Female</option>
                                </select></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">City</label>
                                <input type="text" name="persons[${i}][city]" class="form-control form-control-sm" placeholder="e.g. Lahore / Karachi"></div>
                        </div>

                        <div class="border-top mt-3 pt-3">
                            <strong class="text-secondary d-block mb-2" style="font-size:12px;">
                                <i class="mdi mdi-file-document-multiple me-1"></i> Documents (Optional)
                            </strong>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label" style="font-size:11px;">Passport First Page</label>
                                    <input type="file" name="persons[${i}][passport_photo]" class="form-control form-control-sm" accept="image/*,.pdf">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" style="font-size:11px;">Picture of Passenger</label>
                                    <input type="file" name="persons[${i}][photo]" class="form-control form-control-sm" accept="image/*">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" style="font-size:11px;">Medical Fitness Certificate</label>
                                    <input type="file" name="persons[${i}][medical_certificate]" class="form-control form-control-sm" accept="image/*,.pdf">
                                </div>
                            </div>
                        </div>
                    </div>
                `);
            }
            updatePaxCountsFromPersons();
            syncFlightPersons();
            syncAllHotelPersons();
        }

        function updatePaxCountsFromPersons() {
            let adults = 0;
            let children = 0;
            let infants = 0;

            const paxSelects = document.querySelectorAll('.person-pax-type-select');
            paxSelects.forEach((sel, idx) => {
                const val = sel.value || 'adult';
                if (val === 'child') children++;
                else if (val === 'infant') infants++;
                else adults++;

                const badge = document.getElementById(`person_type_badge_${idx}`);
                if (badge) {
                    if (val === 'child') {
                        badge.className = 'badge bg-info text-dark person-type-badge';
                        badge.textContent = 'Child';
                    } else if (val === 'infant') {
                        badge.className = 'badge bg-warning text-dark person-type-badge';
                        badge.textContent = 'Infant';
                    } else {
                        badge.className = 'badge bg-primary person-type-badge';
                        badge.textContent = 'Adult';
                    }
                }
            });

            if (paxSelects.length === 0) {
                adults = parseInt(document.getElementById('no_of_pax')?.value) || 1;
            }

            if (document.getElementById('pax_adults')) document.getElementById('pax_adults').value = adults;
            if (document.getElementById('pax_children')) document.getElementById('pax_children').value = children;
            if (document.getElementById('pax_infants')) document.getElementById('pax_infants').value = infants;
            if (document.getElementById('hidden_pax_adults')) document.getElementById('hidden_pax_adults').value = adults;
            if (document.getElementById('hidden_pax_children')) document.getElementById('hidden_pax_children').value = children;
            if (document.getElementById('hidden_pax_infants')) document.getElementById('hidden_pax_infants').value = infants;

            if (document.getElementById('bar_pax_adults')) document.getElementById('bar_pax_adults').textContent = adults;
            if (document.getElementById('bar_pax_children')) document.getElementById('bar_pax_children').textContent = children;
            if (document.getElementById('bar_pax_infants')) document.getElementById('bar_pax_infants').textContent = infants;

            if (document.getElementById('flight_adult_pax_label')) document.getElementById('flight_adult_pax_label').textContent = adults;
            if (document.getElementById('flight_child_pax_label')) document.getElementById('flight_child_pax_label').textContent = children;
            if (document.getElementById('flight_infant_pax_label')) document.getElementById('flight_infant_pax_label').textContent = infants;

            calculateFlight();
            calcTotal();
        }

        function onPersonPaxTypeChange(idx, val) {
            // Update Flight row category
            const flRow = document.getElementById(`flight_person_row_${idx}`);
            if (flRow) {
                const flPaxTypeSel = flRow.querySelector('.person-flight-pax-type');
                if (flPaxTypeSel && flPaxTypeSel.value !== val) {
                    flPaxTypeSel.value = val;
                }
            }

            // Update Hotel badges
            document.querySelectorAll(`.hotel-person-row[data-person-idx="${idx}"] .person-hotel-type-badge`).forEach(b => {
                b.textContent = val.toUpperCase();
                b.className = `badge ${val === 'child' ? 'bg-info text-dark' : (val === 'infant' ? 'bg-warning text-dark' : 'bg-primary')} person-hotel-type-badge ms-1`;
            });

            // Update Visa badge
            const vBadge = document.querySelector(`#visa_card_${idx} .person-visa-type-badge`);
            if (vBadge) {
                vBadge.textContent = val.toUpperCase();
                vBadge.className = `badge ${val === 'child' ? 'bg-info text-dark' : (val === 'infant' ? 'bg-warning text-dark' : 'bg-primary')} person-visa-type-badge ms-1`;
            }

            updatePaxCountsFromPersons();
        }

        function onFlightPaxTypeChange(idx, val) {
            const pSel = document.getElementById(`person_pax_type_${idx}`);
            if (pSel && pSel.value !== val) {
                pSel.value = val;
            }
            onPersonPaxTypeChange(idx, val);
        }

        function syncFlightPersons() {
            const pax = parseInt(document.getElementById('no_of_pax')?.value) || 1;
            const list = document.getElementById('flightPersonsList');
            if (!list) return;

            const curr = document.getElementById('flight_currency')?.value || 'SAR';
            const costRoe = (curr === 'PKR') ? 1 : (parseFloat(document.getElementById('flight_cost_roe')?.value) || parseFloat(document.getElementById('flight_roe')?.value) || 75);
            const saleRoe = (curr === 'PKR') ? 1 : (parseFloat(document.getElementById('flight_sale_roe')?.value) || costRoe || 75);
            const mainSupId = document.getElementById('flight_supplier_id')?.value || '';
            const depDate = document.querySelector('input[name="departure_date"]')?.value || '';
            const depTime = document.querySelector('input[name="departure_time"]')?.value || '';
            const depPnr  = document.querySelector('input[name="departure_pnr"]')?.value || '';

            // Preserve any existing DOM input values so user typing is not wiped out
            const existingRowData = {};
            list.querySelectorAll('.flight-person-row').forEach(row => {
                const idx = row.getAttribute('data-idx');
                if (idx !== null) {
                    existingRowData[idx] = {
                        pax_type:           row.querySelector('.person-flight-pax-type')?.value,
                        ticket:             row.querySelector('.person-flight-ticket')?.value,
                        cost:               row.querySelector('.person-flight-cost')?.value,
                        sell:               row.querySelector('.person-flight-sell')?.value,
                        flight_supplier_id: row.querySelector('.person-flight-supplier')?.value,
                        flight_pnr:         row.querySelector('.person-flight-pnr')?.value,
                        flight_date:        row.querySelector('.person-flight-date')?.value,
                        flight_time:        row.querySelector('.person-flight-time')?.value,
                    };
                }
            });

            let rowsHtml = '';
            for (let i = 0; i < pax; i++) {
                const pExisting = (typeof existingPersons !== 'undefined' && existingPersons && existingPersons[i]) ? existingPersons[i] : null;
                const personName = document.getElementById(`person_name_${i}`)?.value || (pExisting ? pExisting.full_name : '') || `Passenger ${i + 1}`;
                const personPass = document.getElementById(`person_passport_${i}`)?.value || (pExisting ? pExisting.passport_number : '') || '';

                const inDom = existingRowData[i];
                const personTypeFromDom = document.getElementById(`person_pax_type_${i}`)?.value;

                let pType   = personTypeFromDom || 'adult';
                let pTicket = '';
                let pCost   = '';
                let pSell   = '';
                let pSup    = '';
                let pPnr    = depPnr;
                let pDate   = depDate;
                let pTime   = depTime;

                if (inDom) {
                    pType   = inDom.pax_type || pType;
                    pTicket = inDom.ticket || '';
                    pCost   = inDom.cost !== undefined ? inDom.cost : '';
                    pSell   = inDom.sell !== undefined ? inDom.sell : '';
                    pSup    = inDom.flight_supplier_id || '';
                    pPnr    = inDom.flight_pnr !== undefined ? inDom.flight_pnr : depPnr;
                    pDate   = inDom.flight_date !== undefined ? inDom.flight_date : depDate;
                    pTime   = inDom.flight_time !== undefined ? inDom.flight_time : depTime;
                } else if (pExisting) {
                    pType   = personTypeFromDom || pExisting.pax_type || 'adult';
                    pTicket = pExisting.flight_ticket || '';
                    pCost   = (pExisting.flight_cost > 0) ? pExisting.flight_cost : '';
                    pSell   = (pExisting.flight_sell > 0) ? pExisting.flight_sell : '';
                    pSup    = pExisting.flight_supplier_id || '';
                    pPnr    = pExisting.flight_pnr || depPnr;
                    pDate   = pExisting.flight_date || depDate;
                    pTime   = pExisting.flight_time || depTime;
                }

                // Smart defaults for legacy bookings if cost/sell is still blank
                if (pCost === '' && typeof existingBooking !== 'undefined' && existingBooking) {
                    if (existingBooking.flight_adult_cost > 0) {
                        pCost = existingBooking.flight_adult_cost;
                    } else if (existingBooking.flight_cost_rate > 0) {
                        pCost = (pax > 1 ? (parseFloat(existingBooking.flight_cost_rate) / pax).toFixed(2) : existingBooking.flight_cost_rate);
                    } else if (existingBooking.flight_cost > 0 && costRoe > 0) {
                        pCost = (parseFloat(existingBooking.flight_cost) / (pax * costRoe)).toFixed(2);
                    }
                }
                if (pSell === '' && typeof existingBooking !== 'undefined' && existingBooking) {
                    if (existingBooking.flight_adult_sell > 0) {
                        pSell = existingBooking.flight_adult_sell;
                    } else if (existingBooking.flight_selling_rate > 0) {
                        pSell = (pax > 1 ? (parseFloat(existingBooking.flight_selling_rate) / pax).toFixed(2) : existingBooking.flight_selling_rate);
                    } else if (existingBooking.flight_charges > 0 && saleRoe > 0) {
                        pSell = (parseFloat(existingBooking.flight_charges) / (pax * saleRoe)).toFixed(2);
                    }
                }

                const costVal = parseFloat(pCost) || 0;
                const sellVal = parseFloat(pSell) || 0;

                // Build supplier dropdown options (Point 3)
                const effectiveSup = pSup || mainSupId;
                let supOptionsHtml = '<option value="">-- Main Supplier --</option>';
                if (typeof suppliersData !== 'undefined' && Array.isArray(suppliersData)) {
                    supOptionsHtml += suppliersData.map(s => `<option value="${s.id}" ${s.id == effectiveSup ? 'selected' : ''}>${s.name}</option>`).join('');
                }

                rowsHtml += `
                <tr class="flight-person-row" data-idx="${i}" id="flight_person_row_${i}">
                    <td class="text-center text-muted fw-bold">${i + 1}</td>
                    <td>
                        <strong class="text-primary person-flight-name-disp">${personName}</strong>
                        <input type="hidden" name="flight_persons[${i}][name]" class="person-flight-name" value="${personName}">
                        <div class="mt-1">
                            <input type="text" name="flight_persons[${i}][passport]" class="form-control form-control-sm person-flight-passport" value="${personPass}" placeholder="Passport #">
                        </div>
                    </td>
                    <td>
                        <select name="flight_persons[${i}][pax_type]" class="form-select form-select-sm person-flight-pax-type" onchange="onFlightPaxTypeChange(${i}, this.value)">
                            <option value="adult" ${pType === 'adult' ? 'selected' : ''}>Adult</option>
                            <option value="child" ${pType === 'child' ? 'selected' : ''}>Child</option>
                            <option value="infant" ${pType === 'infant' ? 'selected' : ''}>Infant</option>
                        </select>
                    </td>
                    "dummy"
                    <td>
                        <select name="flight_persons[${i}][flight_supplier_id]" class="form-select form-select-sm person-flight-supplier">
                            ${supOptionsHtml}
                        </select>
                    </td>
                    "dummy"
                    <td>
                        <div class="mb-1">
                            <input type="text" name="flight_persons[${i}][ticket]" class="form-control form-control-sm person-flight-ticket" value="${pTicket}" placeholder="Ticket / E-Ticket #" oninput="checkUniqueTicketNumbers()">
                        </div>
                        <input type="text" name="flight_persons[${i}][flight_pnr]" class="form-control form-control-sm person-flight-pnr" value="${pPnr}" placeholder="PNR #">
                    </td>
                    "dummy"
                    <td>
                        <div class="mb-1">
                            <input type="date" name="flight_persons[${i}][flight_date]" class="form-control form-control-sm person-flight-date" value="${pDate}">
                        </div>
                        <input type="time" name="flight_persons[${i}][flight_time]" class="form-control form-control-sm person-flight-time" value="${pTime}">
                    </td>
                    <td>
                        <input type="number" step="0.01" name="flight_persons[${i}][flight_cost]" class="form-control form-control-sm text-end person-flight-cost flight-rate-calc" value="${pCost}" placeholder="0.00">
                        <small class="text-danger d-block text-end person-flight-cost-pkr-disp" style="font-size:11px;">${(costVal * costRoe).toFixed(2)} PKR</small>
                    </td>
                    <td>
                        <input type="number" step="0.01" name="flight_persons[${i}][flight_sell]" class="form-control form-control-sm text-end person-flight-sell flight-rate-calc" value="${pSell}" placeholder="0.00">
                        <small class="text-success d-block text-end person-flight-sell-pkr-disp" style="font-size:11px;">${(sellVal * saleRoe).toFixed(2)} PKR</small>
                    </td>
                </tr>`;
            }

            list.innerHTML = `
                <div class="p-2 px-3 mb-2 border rounded bg-light d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <span class="fw-bold text-dark fs-12"><i class="mdi mdi-airplane me-1 text-primary"></i> Passenger-Wise Flight & Ticket Management:</span>
                        <small class="text-muted d-block" style="font-size:11px;">Each passenger can have unique tickets, separate suppliers, custom flight details (Date/Time/PNR), and rates.</small>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <div class="input-group input-group-sm" style="width: auto;">
                            <span class="input-group-text bg-white fs-11">Adult Rate (<span class="flight-curr-label">${curr}</span>):</span>
                            <input type="number" step="0.01" id="quick_adult_cost" class="form-control form-control-sm text-end" placeholder="Cost" style="width: 80px;">
                            <input type="number" step="0.01" id="quick_adult_sell" class="form-control form-control-sm text-end" placeholder="Sell" style="width: 80px;">
                            <button type="button" class="btn btn-primary btn-sm" id="btnQuickApplyAdults"><i class="mdi mdi-check me-1"></i> Apply to All Adults</button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive border rounded bg-white shadow-sm mb-3">
                    <table class="table table-sm table-hover align-middle mb-0" id="flightPersonsTable">
                        <thead class="table-light fs-12">
                            <tr>
                                <th style="width: 30px;" class="text-center">#</th>
                                <th style="width: 17%;">Passenger & Passport</th>
                                <th style="width: 95px;">Category</th>
                                <th style="width: 16%;">Flight Supplier</th>
                                <th style="width: 16%;">Ticket # & PNR</th>
                                <th style="width: 15%;">Flight Date & Time</th>
                                <th style="width: 11%;" class="text-danger text-end">Cost (<span class="flight-curr-label">${curr}</span>)</th>
                                <th style="width: 11%;" class="text-success text-end">Sell (<span class="flight-curr-label">${curr}</span>)</th>
                            </tr>
                        </thead>
                        <tbody id="flightPersonsTableBody">
                            ${rowsHtml}
                        </tbody>
                        <tfoot class="table-light fw-bold fs-12 border-top">
                            <tr>
                                <td colspan="5" class="text-dark">Total Flight Tickets (${pax} Passengers)</td>
                                <td class="text-end text-muted small">Total:</td>
                                <td class="text-end text-danger">
                                    <span id="tbl_flight_cost_curr_total">0.00 ${curr}</span>
                                    <br><small class="text-danger fw-normal" id="tbl_flight_cost_pkr_total">0.00 PKR</small>
                                </td>
                                <td class="text-end text-success">
                                    <span id="tbl_flight_sell_curr_total">0.00 ${curr}</span>
                                    <br><small class="text-success fw-normal" id="tbl_flight_sell_pkr_total">0.00 PKR</small>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            `;

            calculateFlight();
            checkUniqueTicketNumbers();
        }

        document.getElementById('no_of_pax')?.addEventListener('input', function() {
            const val = Math.max(1, parseInt(this.value) || 1);
            if (document.getElementById('bar_pax_badge')) {
                document.getElementById('bar_pax_badge').textContent = val;
            }
            rebuildPersonsList(val);
            rebuildVisasList(val);
            updatePaxCountsFromPersons();
            syncFlightPersons();
            syncAllHotelPersons();
        });

        document.getElementById('no_of_pax')?.addEventListener('change', function() {
            const val = Math.max(1, parseInt(this.value) || 1);
            if (document.getElementById('bar_pax_badge')) {
                document.getElementById('bar_pax_badge').textContent = val;
            }
            rebuildPersonsList(val);
            rebuildVisasList(val);
            updatePaxCountsFromPersons();
            syncFlightPersons();
            syncAllHotelPersons();
        });

        function rebuildVisasList(newPax) {
            const container = document.getElementById('visasList');
            if (!container) return;
            const currentCount = container.querySelectorAll('.visa-card').length;

            if (newPax < currentCount) {
                for (let i = currentCount - 1; i >= newPax; i--) {
                    document.getElementById('visa_card_' + i)?.remove();
                }
                calcTotal();
                return;
            }

            const flightCurr = document.getElementById('flight_currency')?.value || 'SAR';
            const defaultCostRoe = (flightCurr === 'PKR') ? 1 : (document.getElementById('flight_cost_roe')?.value || 75);
            const defaultSaleRoe = (flightCurr === 'PKR') ? 1 : (document.getElementById('flight_sale_roe')?.value || defaultCostRoe);

            const defVisaCurr = document.getElementById('default_visa_currency')?.value;
            const defVisaCostRoe = document.getElementById('default_visa_cost_roe')?.value;
            const defVisaSaleRoe = document.getElementById('default_visa_sale_roe')?.value;
            const defVisaCostRate = document.getElementById('default_visa_cost_rate')?.value;
            const defVisaSellRate = document.getElementById('default_visa_sell_rate')?.value;
            const defVisaSup = document.getElementById('default_visa_supplier_id')?.value;

            const fallbackCurr = defVisaCurr || flightCurr;
            const isDefPkr = (fallbackCurr === 'PKR');
            const fallbackCostRoe = isDefPkr ? 1 : (defVisaCostRoe || defaultCostRoe);
            const fallbackSaleRoe = isDefPkr ? 1 : (defVisaSaleRoe || defaultSaleRoe || fallbackCostRoe);

            for (let i = currentCount; i < newPax; i++) {
                const v = (typeof existingVisas !== 'undefined' && existingVisas && existingVisas[i]) ? existingVisas[i] : {};
                const pName = document.getElementById(`person_name_${i}`)?.value || (v.given_name || '');
                const pPass = document.getElementById(`person_passport_${i}`)?.value || (v.passport_number || '');
                const pDob = document.getElementById(`person_dob_${i}`)?.value || (v.date_of_birth || '');
                const pType = document.getElementById(`person_pax_type_${i}`)?.value || 'adult';
                const vBadgeClass = pType === 'child' ? 'bg-info text-dark' : (pType === 'infant' ? 'bg-warning text-dark' : 'bg-primary');
                const vTypeLabel = pType.toUpperCase();

                const vCurr = v.currency || fallbackCurr;
                const vIsPkr = (vCurr === 'PKR');
                const costRoe = vIsPkr ? 1 : (v.cost_roe || v.roe || fallbackCostRoe);
                const saleRoe = vIsPkr ? 1 : (v.sale_roe || costRoe || fallbackSaleRoe);

                const costRate = parseFloat(v.cost_rate) || parseFloat(defVisaCostRate) || 0;
                const costAmt = parseFloat(v.cost_amount) || (costRate * parseFloat(costRoe));
                const sellRate = parseFloat(v.selling_rate) || parseFloat(defVisaSellRate) || 0;
                const sellAmt = parseFloat(v.selling_amount) || (sellRate * parseFloat(saleRoe));

                const vBookingDate = v.booking_date || '"dummy"';
                const supId = v.supplier_id || defVisaSup || '';

                let supOptions = `<option value="">-- Select Supplier --</option>` +
                    (typeof suppliersData !== 'undefined' ? suppliersData.map(s => `<option value="${s.id}" ${s.id == supId ? 'selected' : ''}>${s.name}</option>`).join('') : '');

                container.insertAdjacentHTML('beforeend', `
                    <div class="visa-card border rounded p-3 mb-2 bg-white" id="visa_card_${i}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <strong class="text-warning" style="font-size:13px;">
                                    <i class="mdi mdi-passport me-1"></i> Visa ${i + 1}: ${pName || 'Passenger ' + (i + 1)}
                                </strong>
                                <span class="badge ${vBadgeClass} person-visa-type-badge ms-1" style="font-size:10px;">${vTypeLabel}</span>
                            </div>
                            <span class="badge bg-warning text-dark">${((v.status || 'pending')).toUpperCase()}</span>
                        </div>
                        <div class="row g-2 mt-1">
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Passport #</label>
                                <input type="text" name="visas[${i}][passport_number]" class="form-control form-control-sm" value="${pPass}"></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Full Name</label>
                                <input type="text" name="visas[${i}][given_name]" class="form-control form-control-sm" value="${pName}"></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">DOB</label>
                                <input type="date" name="visas[${i}][date_of_birth]" class="form-control form-control-sm" value="${pDob}"></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Visa Supplier / Vendor</label>
                                <select name="visas[${i}][supplier_id]" class="form-select form-select-sm">
                                    ${supOptions}
                                </select></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Company</label>
                                <input type="text" name="visas[${i}][company]" class="form-control form-control-sm" value="${v.company || ''}"></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Send To</label>
                                <select name="visas[${i}][send_to]" class="form-select form-select-sm">
                                    <option value="">-- Select --</option>
                                    <option value="shirka" ${v.send_to === 'shirka' ? 'selected' : ''}>Shirka</option>
                                    <option value="consulate" ${v.send_to === 'consulate' ? 'selected' : ''}>Consulate</option>
                                    <option value="both" ${v.send_to === 'both' ? 'selected' : ''}>Both</option>
                                </select></div>
                            <div class="col-md-2">
                                <label class="form-label text-dark fw-bold" style="font-size:12px;">Currency</label>
                                <select name="visas[${i}][currency]" class="form-select form-select-sm visa-currency fw-bold">
                                    <option value="SAR" ${vCurr === 'SAR' ? 'selected' : ''}>SAR</option>
                                    <option value="PKR" ${vCurr === 'PKR' ? 'selected' : ''}>PKR</option>
                                    <option value="USD" ${vCurr === 'USD' ? 'selected' : ''}>USD</option>
                                    <option value="MYR" ${vCurr === 'MYR' ? 'selected' : ''}>MYR</option>
                                    <option value="AED" ${vCurr === 'AED' ? 'selected' : ''}>AED</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-danger fw-bold" style="font-size:12px;">Cost ROE</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light visa-curr-prefix">1 ${vCurr} =</span>
                                    <input type="number" step="0.0001" name="visas[${i}][cost_roe]" class="form-control visa-cost-roe" placeholder="75.00" value="${costRoe}" ${vIsPkr ? 'readonly' : ''}>
                                </div>
                                <input type="hidden" name="visas[${i}][roe]" class="visa-roe" value="${costRoe}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-success fw-bold" style="font-size:12px;">Sale ROE</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light visa-curr-prefix">1 ${vCurr} =</span>
                                    <input type="number" step="0.0001" name="visas[${i}][sale_roe]" class="form-control visa-sale-roe" placeholder="75.00" value="${saleRoe}" ${vIsPkr ? 'readonly' : ''}>
                                </div>
                            </div>
                            <div class="col-md-2"><label class="form-label text-danger fw-semibold" style="font-size:12px;">Cost Rate (<span class="visa-curr-label">${vCurr}</span>)</label>
                                <input type="number" step="0.01" name="visas[${i}][cost_rate]" class="form-control form-control-sm visa-cost-rate" placeholder="0.00" value="${costRate}"></div>
                            <div class="col-md-3"><label class="form-label text-danger fw-semibold" style="font-size:12px;">Total Cost (<span class="visa-curr-label">${vCurr}</span> & PKR)</label>
                                <div class="p-1 px-2 border rounded bg-light">
                                    <div class="d-flex justify-content-between small text-danger"><span class="visa-curr-name">${vCurr}:</span><strong class="visa-cost-sar-disp">${costRate.toFixed(2)} ${vCurr}</strong></div>
                                    <div class="d-flex justify-content-between small text-danger"><span>PKR:</span><strong class="visa-cost-pkr-disp">${costAmt.toFixed(2)} PKR</strong></div>
                                </div>
                                <input type="hidden" name="visas[${i}][cost_amount]" class="visa-cost-amount" value="${costAmt.toFixed(2)}"></div>
                            <div class="col-md-2"><label class="form-label text-success fw-semibold" style="font-size:12px;">Sell Rate (<span class="visa-curr-label">${vCurr}</span>)</label>
                                <input type="number" step="0.01" name="visas[${i}][selling_rate]" class="form-control form-control-sm visa-sell-rate" placeholder="0.00" value="${sellRate}"></div>
                            <div class="col-md-3"><label class="form-label text-success fw-semibold" style="font-size:12px;">Total Sell (<span class="visa-curr-label">${vCurr}</span> & PKR)</label>
                                <div class="p-1 px-2 border rounded bg-light">
                                    <div class="d-flex justify-content-between small text-success"><span class="visa-curr-name">${vCurr}:</span><strong class="visa-sell-sar-disp">${sellRate.toFixed(2)} ${vCurr}</strong></div>
                                    <div class="d-flex justify-content-between small text-success"><span>PKR:</span><strong class="visa-sell-pkr-disp">${sellAmt.toFixed(2)} PKR</strong></div>
                                </div>
                                <input type="hidden" name="visas[${i}][selling_amount]" class="visa-sell-amount" value="${sellAmt.toFixed(2)}"></div>
                            <div class="col-md-3"><label class="form-label" style="font-size:12px;"><i class="mdi mdi-calendar me-1 text-primary"></i> Booking Date</label>
                                <input type="date" name="visas[${i}][booking_date]" class="form-control form-control-sm visa-booking-date" value="${vBookingDate}"></div>

                            <div class="col-md-3"><label class="form-label" style="font-size:12px;">Status</label>
                                <select name="visas[${i}][status]" class="form-select form-select-sm">
                                    <option value="pending" ${(v.status || 'pending') === 'pending' ? 'selected' : ''}>Pending</option>
                                    <option value="applied" ${v.status === 'applied' ? 'selected' : ''}>Applied</option>
                                    <option value="approved" ${v.status === 'approved' ? 'selected' : ''}>Approved</option>
                                    <option value="rejected" ${v.status === 'rejected' ? 'selected' : ''}>Rejected</option>
                                </select></div>
                            <div class="col-md-6"><label class="form-label" style="font-size:12px;">Visa Attachment</label>
                                <input type="file" name="visas[${i}][visa_attachment]" class="form-control form-control-sm" accept="image/*,.pdf"></div>
                        </div>
                    </div>
                `);
            }
            calcTotal();
        }

        // Flight Currency Change Event Listener
        document.getElementById('flight_currency')?.addEventListener('change', function() {
            const val = this.value;
            const costRoeInput = document.getElementById('flight_cost_roe');
            const saleRoeInput = document.getElementById('flight_sale_roe');
            const legacyRoeInput = document.getElementById('flight_roe');

            if (val === 'PKR') {
                if (costRoeInput) { costRoeInput.value = '1.0000'; costRoeInput.readOnly = true; }
                if (saleRoeInput) { saleRoeInput.value = '1.0000'; saleRoeInput.readOnly = true; }
                if (legacyRoeInput) legacyRoeInput.value = '1.0000';
            } else {
                if (costRoeInput) {
                    costRoeInput.readOnly = false;
                    if (val === 'SAR') costRoeInput.value = '75.0000';
                    else if (val === 'MYR') costRoeInput.value = '63.0000';
                    else if (val === 'USD') costRoeInput.value = '278.0000';
                    else if (val === 'AED') costRoeInput.value = '76.0000';
                }
                if (saleRoeInput) {
                    saleRoeInput.readOnly = false;
                    if (val === 'SAR') saleRoeInput.value = '75.0000';
                    else if (val === 'MYR') saleRoeInput.value = '63.0000';
                    else if (val === 'USD') saleRoeInput.value = '278.0000';
                    else if (val === 'AED') saleRoeInput.value = '76.0000';
                }
                if (legacyRoeInput && costRoeInput) legacyRoeInput.value = costRoeInput.value;
            }
            calculateFlight();
        });

        // Flight Calculation (Foreign & PKR for Individual Passengers and Dual ROE)
        function calculateFlight() {
            const adults   = parseInt(document.getElementById('pax_adults')?.value) || 0;
            const children = parseInt(document.getElementById('pax_children')?.value) || 0;
            const infants  = parseInt(document.getElementById('pax_infants')?.value) || 0;
            const pax      = Math.max(1, adults + children + infants);

            const curr = document.getElementById('flight_currency')?.value || 'SAR';
            const isPkr = (curr === 'PKR');

            const costRoeInput = document.getElementById('flight_cost_roe');
            const saleRoeInput = document.getElementById('flight_sale_roe');
            const legacyRoeInput = document.getElementById('flight_roe');

            if (isPkr) {
                if (costRoeInput) { costRoeInput.value = '1.0000'; costRoeInput.readOnly = true; }
                if (saleRoeInput) { saleRoeInput.value = '1.0000'; saleRoeInput.readOnly = true; }
            } else {
                if (costRoeInput) costRoeInput.readOnly = false;
                if (saleRoeInput) saleRoeInput.readOnly = false;
            }

            const costRoe = isPkr ? 1 : (parseFloat(costRoeInput?.value) || parseFloat(legacyRoeInput?.value) || 1);
            const saleRoe = isPkr ? 1 : (parseFloat(saleRoeInput?.value) || costRoe || 1);

            if (legacyRoeInput) legacyRoeInput.value = costRoe;

            // Update UI currency labels
            document.querySelectorAll('.flight-curr-label').forEach(el => el.textContent = curr);
            const prefixEl = document.getElementById('flight_curr_prefix');
            if (prefixEl) prefixEl.textContent = `1 ${curr} =`;
            const prefixSaleEl = document.getElementById('flight_sale_curr_prefix');
            if (prefixSaleEl) prefixSaleEl.textContent = `1 ${curr} =`;

            // Check individual passenger rows in flightPersonsTable
            const personRows = document.querySelectorAll('.flight-person-row');
            let totalCostCurr = 0;
            let totalSellCurr = 0;
            let hasCustomPersonRates = false;

            if (personRows.length > 0) {
                personRows.forEach(row => {
                    const costInput = row.querySelector('.person-flight-cost');
                    const sellInput = row.querySelector('.person-flight-sell');
                    const costVal = parseFloat(costInput?.value) || 0;
                    const sellVal = parseFloat(sellInput?.value) || 0;

                    if (costVal > 0 || sellVal > 0) hasCustomPersonRates = true;

                    totalCostCurr += costVal;
                    totalSellCurr += sellVal;

                    const costPkrDisp = row.querySelector('.person-flight-cost-pkr-disp');
                    const sellPkrDisp = row.querySelector('.person-flight-sell-pkr-disp');
                    if (costPkrDisp) costPkrDisp.textContent = (costVal * costRoe).toFixed(2) + ' PKR';
                    if (sellPkrDisp) sellPkrDisp.textContent = (sellVal * saleRoe).toFixed(2) + ' PKR';
                });
            }

            // Fallback: If no individual person rates were entered, check tier inputs or existing flight_charges / flight_cost
            if (!hasCustomPersonRates) {
                const adultCostCurr = parseFloat(document.getElementById('flight_adult_cost')?.value) || 0;
                const adultSellCurr = parseFloat(document.getElementById('flight_adult_sell')?.value) || 0;
                const childCostCurr = parseFloat(document.getElementById('flight_child_cost')?.value) || 0;
                const childSellCurr = parseFloat(document.getElementById('flight_child_sell')?.value) || 0;
                const infantCostCurr = parseFloat(document.getElementById('flight_infant_cost')?.value) || 0;
                const infantSellCurr = parseFloat(document.getElementById('flight_infant_sell')?.value) || 0;

                const tierCostCurr = (adults * adultCostCurr) + (children * childCostCurr) + (infants * infantCostCurr);
                const tierSellCurr = (adults * adultSellCurr) + (children * childSellCurr) + (infants * infantSellCurr);

                if (tierCostCurr > 0 || tierSellCurr > 0) {
                    totalCostCurr = tierCostCurr;
                    totalSellCurr = tierSellCurr;
                } else {
                    const existingCostPkr = parseFloat(document.getElementById('flight_cost')?.value) || 0;
                    const existingSellPkr = parseFloat(document.getElementById('flight_charges')?.value) || 0;
                    if (existingCostPkr > 0 && costRoe > 0) totalCostCurr = existingCostPkr / costRoe;
                    if (existingSellPkr > 0 && saleRoe > 0) totalSellCurr = existingSellPkr / saleRoe;
                }
            }

            const totalCostPkr = totalCostCurr * costRoe;
            const totalSellPkr = totalSellCurr * saleRoe;

            // Badges / Pax labels
            const adultBadge = document.getElementById('flight_adult_pax_label');
            if (adultBadge) adultBadge.textContent = adults;
            const childBadge = document.getElementById('flight_child_pax_label');
            if (childBadge) childBadge.textContent = children;
            const infantBadge = document.getElementById('flight_infant_pax_label');
            if (infantBadge) infantBadge.textContent = infants;

            // Table footer totals
            const tblCostCurr = document.getElementById('tbl_flight_cost_curr_total');
            if (tblCostCurr) tblCostCurr.textContent = totalCostCurr.toFixed(2) + ' ' + curr;
            const tblCostPkr = document.getElementById('tbl_flight_cost_pkr_total');
            if (tblCostPkr) tblCostPkr.textContent = totalCostPkr.toFixed(2) + ' PKR';
            const tblSellCurr = document.getElementById('tbl_flight_sell_curr_total');
            if (tblSellCurr) tblSellCurr.textContent = totalSellCurr.toFixed(2) + ' ' + curr;
            const tblSellPkr = document.getElementById('tbl_flight_sell_pkr_total');
            if (tblSellPkr) tblSellPkr.textContent = totalSellPkr.toFixed(2) + ' PKR';

            // Row displays for tier table
            const elAdultCostDisp = document.getElementById('flight_adult_cost_sar_disp');
            if (elAdultCostDisp) elAdultCostDisp.textContent = totalCostCurr.toFixed(2);
            const elAdultSellDisp = document.getElementById('flight_adult_sell_sar_disp');
            if (elAdultSellDisp) elAdultSellDisp.textContent = totalSellCurr.toFixed(2);

            // Summary cards
            const elCostSarTot = document.getElementById('flight_cost_sar_total');
            if (elCostSarTot) elCostSarTot.textContent = totalCostCurr.toFixed(2) + ' ' + curr;
            const elCostPkrTot = document.getElementById('flight_cost_pkr_total');
            if (elCostPkrTot) elCostPkrTot.textContent = totalCostPkr.toFixed(2) + ' PKR';
            const elSellSarTot = document.getElementById('flight_sell_sar_total');
            if (elSellSarTot) elSellSarTot.textContent = totalSellCurr.toFixed(2) + ' ' + curr;
            const elSellPkrTot = document.getElementById('flight_sell_pkr_total');
            if (elSellPkrTot) elSellPkrTot.textContent = totalSellPkr.toFixed(2) + ' PKR';

            // Backend compatibility hidden inputs
            const elCost = document.getElementById('flight_cost');
            if (elCost) elCost.value = totalCostPkr.toFixed(2);
            const elCharges = document.getElementById('flight_charges');
            if (elCharges) elCharges.value = totalSellPkr.toFixed(2);
            const elChargesDisp = document.getElementById('flight_charges_disp');
            if (elChargesDisp) elChargesDisp.value = totalSellPkr.toFixed(2);
            const elCostRate = document.getElementById('flight_cost_rate');
            if (elCostRate) elCostRate.value = totalCostCurr.toFixed(2);
            const elSellRate = document.getElementById('flight_selling_rate');
            if (elSellRate) elSellRate.value = totalSellCurr.toFixed(2);

            calcTotal();
        }

        // Hotels Dynamic Management
        let hotelIdx = "dummy";

        function addNewHotel(loc = 'makkah') {
            const noHotelAlert = document.getElementById('noHotelAlert');
            if (noHotelAlert) noHotelAlert.style.display = 'none';

            let locLabel = 'Makkah Hotel';
            let locVal = 'makkah';
            if (loc === 'madinah') { locLabel = 'Madinah Hotel'; locVal = 'madinah'; }
            else if (loc === 'other') { locLabel = 'Other Hotel'; locVal = 'other'; }
            else if (loc === 'jeddah') { locLabel = 'Jeddah Hotel'; locVal = 'jeddah'; }

            const flightCurr = document.getElementById('flight_currency')?.value || 'SAR';
            const defaultCostRoe = (flightCurr === 'PKR') ? 1 : (document.getElementById('flight_cost_roe')?.value || 75);
            const defaultSaleRoe = (flightCurr === 'PKR') ? 1 : (document.getElementById('flight_sale_roe')?.value || defaultCostRoe);
            const isPkr = (flightCurr === 'PKR');

            const html = `
            <div class="hotel-block border rounded p-3 mb-3 bg-white shadow-sm" data-hotel-idx="${hotelIdx}">
                <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                    <h6 class="text-primary mb-0 fw-bold"><i class="mdi mdi-hotel me-1"></i> ${locLabel}</h6>
                    <button type="button" class="btn btn-outline-danger btn-sm remove-hotel"><i class="mdi mdi-delete-outline me-1"></i> Remove</button>
                </div>
                <input type="hidden" name="hotels[${hotelIdx}][location]" value="${locVal}">
                
                "dummy"
                <div class="p-2 mb-3 rounded bg-light border">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label fw-bold text-dark mb-1"><i class="mdi mdi-calendar-range me-1 text-primary"></i> Stay Period (Check In & Check Out)</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white text-primary fw-semibold"><i class="mdi mdi-login me-1"></i> Check In</span>
                                <input type="date" name="hotels[${hotelIdx}][check_in]" class="form-control form-control-sm hotel-checkin" onchange="calculateHotelNights(${hotelIdx})">
                                <span class="input-group-text bg-white text-danger fw-semibold"><i class="mdi mdi-logout me-1"></i> Check Out</span>
                                <input type="date" name="hotels[${hotelIdx}][check_out]" class="form-control form-control-sm hotel-checkout" onchange="calculateHotelNights(${hotelIdx})">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">Nights</label>
                            <input type="number" name="hotels[${hotelIdx}][no_of_nights]" class="form-control form-control-sm hotel-nights" value="1" min="1">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1"><i class="mdi mdi-calendar me-1 text-primary"></i> Booking Date</label>
                            <input type="date" name="hotels[${hotelIdx}][booking_date]" class="form-control form-control-sm hotel-booking-date" value=""dummy"">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-warning fw-bold mb-1"><i class="mdi mdi-calendar-clock me-1 text-warning"></i> Option Date</label>
                            <input type="date" name="hotels[${hotelIdx}][option_date]" class="form-control form-control-sm hotel-option-date border-warning">
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Hotel Name</label>
                        <select name="hotels[${hotelIdx}][hotel_name]" class="form-select hotel-name-select">
                            ${hotelsOptions}
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label d-flex justify-content-between align-items-center mb-1">
                            <span>Hotel Supplier / Vendor</span>
                            <button type="button" class="btn btn-xs btn-link text-primary p-0 text-decoration-none" onclick="refreshSuppliersList(true)" title="Reload Suppliers"><i class="mdi mdi-refresh"></i> Refresh</button>
                        </label>
                        <select name="hotels[${hotelIdx}][supplier_id]" class="form-select">
                            ${suppliersOptions}
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-dark fw-bold">Currency</label>
                        <select name="hotels[${hotelIdx}][currency]" class="form-select hotel-currency fw-bold">
                            <option value="SAR" ${flightCurr === 'SAR' ? 'selected' : ''}>SAR (Riyal)</option>
                            <option value="PKR" ${flightCurr === 'PKR' ? 'selected' : ''}>PKR (Rupee)</option>
                            <option value="USD" ${flightCurr === 'USD' ? 'selected' : ''}>USD (Dollar)</option>
                            <option value="MYR" ${flightCurr === 'MYR' ? 'selected' : ''}>MYR (Ringgit)</option>
                            <option value="AED" ${flightCurr === 'AED' ? 'selected' : ''}>AED (Dirham)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-danger fw-bold">Cost ROE</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light hotel-curr-prefix">1 ${flightCurr} =</span>
                            <input type="number" step="0.0001" name="hotels[${hotelIdx}][cost_roe]" class="form-control hotel-cost-roe" value="${defaultCostRoe}" placeholder="75.00" ${isPkr ? 'readonly' : ''}>
                        </div>
                        <input type="hidden" name="hotels[${hotelIdx}][roe]" class="hotel-roe" value="${defaultCostRoe}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-success fw-bold">Sale ROE</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light hotel-curr-prefix">1 ${flightCurr} =</span>
                            <input type="number" step="0.0001" name="hotels[${hotelIdx}][sale_roe]" class="form-control hotel-sale-roe" value="${defaultSaleRoe}" placeholder="75.00" ${isPkr ? 'readonly' : ''}>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Room Type</label>
                        <select name="hotels[${hotelIdx}][room_type]" class="form-select hotel-room-type">
                            <option value="single">Single</option>
                            <option value="double" selected>Double</option>
                            <option value="triple">Triple</option>
                            <option value="quad">Quad</option>
                            <option value="sharing">Sharing</option>
                            <option value="suite">Suite</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">No. of Rooms</label>
                        <input type="number" name="hotels[${hotelIdx}][no_of_rooms]" class="form-control hotel-rooms" value="1" min="1">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Hotel Room #</label>
                        <input type="text" name="hotels[${hotelIdx}][room_number]" class="form-control hotel-room-number-input" placeholder="e.g. 402 / 505">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-danger fw-semibold">Cost Rate / Night (<span class="hotel-curr-label">${flightCurr}</span>)</label>
                        <input type="number" step="0.01" name="hotels[${hotelIdx}][cost_per_night]" class="form-control hotel-cost-rate" placeholder="0.00" value="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-success fw-semibold">Sell Rate / Night (<span class="hotel-curr-label">${flightCurr}</span>)</label>
                        <input type="number" step="0.01" name="hotels[${hotelIdx}][selling_per_night]" class="form-control hotel-sell-rate" placeholder="0.00" value="0">
                    </div>

                    "dummy"
                    <div class="col-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between p-2 rounded bg-light border gap-2">
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-xs btn-outline-info" onclick="divideHotelMainCost(${hotelIdx})" title="Divide main cost equally among passengers">
                                    <i class="mdi mdi-calculator me-1"></i> Divide / Allocate Main Cost
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-success" onclick="addAdditionalRoomToHotel(${hotelIdx})">
                                    <i class="mdi mdi-plus-circle me-1"></i> + Add Additional Room
                                </button>
                            </div>
                        </div>
                        <div class="additional-rooms-container mt-2" id="additional_rooms_list_${hotelIdx}"></div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-danger fw-semibold">Total Cost (<span class="hotel-curr-label">${flightCurr}</span> & PKR)</label>
                        <div class="p-1 px-2 border rounded bg-light">
                            <div class="d-flex justify-content-between small text-danger"><span class="hotel-curr-name">${flightCurr}:</span><strong class="hotel-cost-sar-disp">0.00 ${flightCurr}</strong></div>
                            <div class="d-flex justify-content-between small text-danger"><span>PKR:</span><strong class="hotel-cost-pkr-disp">0.00 PKR</strong></div>
                        </div>
                        <input type="hidden" name="hotels[${hotelIdx}][cost_amount]" class="hotel-cost-total" value="0">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-success fw-semibold">Total Sell (<span class="hotel-curr-label">${flightCurr}</span> & PKR)</label>
                        <div class="p-1 px-2 border rounded bg-light">
                            <div class="d-flex justify-content-between small text-success"><span class="hotel-curr-name">${flightCurr}:</span><strong class="hotel-sell-sar-disp">0.00 ${flightCurr}</strong></div>
                            <div class="d-flex justify-content-between small text-success"><span>PKR:</span><strong class="hotel-sell-pkr-disp">0.00 PKR</strong></div>
                        </div>
                        <input type="hidden" name="hotels[${hotelIdx}][selling_amount]" class="hotel-sell-total" value="0">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Hotel Voucher / Booking Confirmation</label>
                        <input type="file" name="hotels[${hotelIdx}][hotel_voucher]" class="form-control" accept="image/*,.pdf">
                    </div>

                    "dummy"
                    <div class="col-12 hotel-persons-wrapper mt-3 pt-3 border-top">
                        <div class="p-2 px-3 mb-2 border rounded bg-light d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div>
                                <span class="fw-bold text-dark fs-12"><i class="mdi mdi-account-multiple me-1 text-primary"></i> Individual Passenger Hotel Rates:</span>
                                <small class="text-muted d-block" style="font-size:11px;">Enter custom rates per night for each passenger independently below (e.g. Person 1 and Person 2 can have different rates).</small>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <div class="input-group input-group-sm" style="width: auto;">
                                    <span class="input-group-text bg-white fs-11">Rate / Night (<span class="hotel-curr-label">${flightCurr}</span>):</span>
                                    <input type="number" step="0.01" class="form-control form-control-sm text-end quick-hotel-cost" placeholder="Cost" style="width: 80px;">
                                    <input type="number" step="0.01" class="form-control form-control-sm text-end quick-hotel-sell" placeholder="Sell" style="width: 80px;">
                                    <button type="button" class="btn btn-primary btn-sm btn-quick-hotel-apply"><i class="mdi mdi-check me-1"></i> Apply to All Pax</button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive border rounded bg-white shadow-sm mb-2">
                            <table class="table table-sm table-hover align-middle mb-0 hotel-persons-table">
                                <thead class="table-light fs-12">
                                    <tr>
                                        <th style="width: 35px;" class="text-center">#</th>
                                        <th style="width: 25%;">Passenger Name</th>
                                        <th style="width: 12%;" class="text-center">Nights</th>
                                        <th style="width: 25%;" class="text-danger text-end">Cost / Night (<span class="hotel-curr-label">${flightCurr}</span>)</th>
                                        <th style="width: 25%;" class="text-success text-end">Sell / Night (<span class="hotel-curr-label">${flightCurr}</span>)</th>
                                    </tr>
                                </thead>
                                <tbody class="hotel-persons-tbody">
                                </tbody>
                                <tfoot class="table-light fw-bold fs-12 border-top">
                                    <tr>
                                        <td colspan="3" class="text-dark">Hotel Passenger Totals:</td>
                                        <td class="text-end text-danger">
                                            <span class="hotel-person-tbl-cost-curr">0.00 ${flightCurr}</span>
                                            <br><small class="text-danger fw-normal hotel-person-tbl-cost-pkr">0.00 PKR</small>
                                        </td>
                                        <td class="text-end text-success">
                                            <span class="hotel-person-tbl-sell-curr">0.00 ${flightCurr}</span>
                                            <br><small class="text-success fw-normal hotel-person-tbl-sell-pkr">0.00 PKR</small>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>`;

            document.getElementById('hotelsList').insertAdjacentHTML('beforeend', html);
            const lastRow = document.querySelector('#hotelsList .hotel-block:last-child');
            hotelIdx++;
            syncBookingHotelDates();
            if (lastRow) {
                syncHotelPersons(lastRow);
                autoDivideHotelRates(lastRow);
                calculateHotelRow(lastRow);
            }
            calcTotal();
        }

        document.getElementById('addHotel')?.addEventListener('click', function() {
            addNewHotel('other');
        });

        document.getElementById('hotelsList')?.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-hotel') || e.target.closest('.remove-hotel')) {
                e.target.closest('.hotel-block').remove();
                if (document.querySelectorAll('#hotelsList .hotel-block').length === 0) {
                    const noHotelAlert = document.getElementById('noHotelAlert');
                    if (noHotelAlert) noHotelAlert.style.display = '';
                }
                syncBookingHotelDates();
                calcTotal();
            }
        });

        document.getElementById('hotelsList')?.addEventListener('change', function(e) {
            if (e.target.classList.contains('hotel-currency')) {
                const row = e.target.closest('.hotel-block');
                const val = e.target.value;
                const costRoeInput = row.querySelector('.hotel-cost-roe');
                const saleRoeInput = row.querySelector('.hotel-sale-roe');
                if (val === 'PKR') {
                    if (costRoeInput) { costRoeInput.value = '1.0000'; costRoeInput.readOnly = true; }
                    if (saleRoeInput) { saleRoeInput.value = '1.0000'; saleRoeInput.readOnly = true; }
                } else {
                    if (costRoeInput) {
                        costRoeInput.readOnly = false;
                        if (val === 'SAR') costRoeInput.value = '75.0000';
                        else if (val === 'MYR') costRoeInput.value = '63.0000';
                        else if (val === 'USD') costRoeInput.value = '278.0000';
                        else if (val === 'AED') costRoeInput.value = '76.0000';
                    }
                    if (saleRoeInput) {
                        saleRoeInput.readOnly = false;
                        if (val === 'SAR') saleRoeInput.value = '75.0000';
                        else if (val === 'MYR') saleRoeInput.value = '63.0000';
                        else if (val === 'USD') saleRoeInput.value = '278.0000';
                        else if (val === 'AED') saleRoeInput.value = '76.0000';
                    }
                }
                calculateHotelRow(row);
            }
        });

        function syncHotelPersons(hotelBlock) {
            if (!hotelBlock) return;
            const pax = parseInt(document.getElementById('no_of_pax')?.value) || 1;
            const tbody = hotelBlock.querySelector('.hotel-persons-tbody');
            if (!tbody) return;

            const hotelIdx = hotelBlock.getAttribute('data-hotel-idx') || 0;
            const curr = hotelBlock.querySelector('.hotel-currency')?.value || 'SAR';
            const nights = parseFloat(hotelBlock.querySelector('.hotel-nights')?.value) || 1;
            const isPkr = (curr === 'PKR');
            const costRoe = isPkr ? 1 : (parseFloat(hotelBlock.querySelector('.hotel-cost-roe')?.value) || parseFloat(hotelBlock.querySelector('.hotel-roe')?.value) || 75);
            const saleRoe = isPkr ? 1 : (parseFloat(hotelBlock.querySelector('.hotel-sale-roe')?.value) || costRoe || 75);

            // Preserve any existing DOM input values
            const existingDomData = {};
            tbody.querySelectorAll('.hotel-person-row').forEach(row => {
                const pIdx = row.getAttribute('data-person-idx');
                if (pIdx !== null) {
                    existingDomData[pIdx] = {
                        cost: row.querySelector('.hotel-person-cost')?.value,
                        sell: row.querySelector('.hotel-person-sell')?.value,
                    };
                }
            });

            // Check if saved rates exist in data-person-rates attribute
            let savedPersonRates = [];
            try {
                const rawSaved = hotelBlock.getAttribute('data-person-rates');
                if (rawSaved) {
                    savedPersonRates = typeof rawSaved === 'string' ? JSON.parse(rawSaved) : rawSaved;
                }
            } catch(e) {}

            let rowsHtml = '';
            for (let p = 0; p < pax; p++) {
                const pExisting = (typeof existingPersons !== 'undefined' && existingPersons && existingPersons[p]) ? existingPersons[p] : null;
                const personName = document.getElementById(`person_name_${p}`)?.value || (pExisting ? pExisting.full_name : '') || `Passenger ${p + 1}`;
                const inDom = existingDomData[p];
                const inSaved = (savedPersonRates && savedPersonRates[p]) ? savedPersonRates[p] : null;

                let pCost = '';
                let pSell = '';

                if (inDom) {
                    pCost = inDom.cost !== undefined ? inDom.cost : '';
                    pSell = inDom.sell !== undefined ? inDom.sell : '';
                } else if (inSaved) {
                    pCost = inSaved.cost_per_night !== undefined ? inSaved.cost_per_night : (inSaved.cost || '');
                    pSell = inSaved.selling_per_night !== undefined ? inSaved.selling_per_night : (inSaved.sell || '');
                }

                // If still empty and hotel has cost_per_night / selling_per_night from earlier
                if (pCost === '') {
                    const hCostRate = parseFloat(hotelBlock.querySelector('.hotel-cost-rate')?.value) || 0;
                    if (hCostRate > 0) {
                        pCost = (pax > 1 ? (hCostRate / pax).toFixed(2) : hCostRate.toFixed(2));
                    }
                }
                if (pSell === '') {
                    const hSellRate = parseFloat(hotelBlock.querySelector('.hotel-sell-rate')?.value) || 0;
                    if (hSellRate > 0) {
                        pSell = (pax > 1 ? (hSellRate / pax).toFixed(2) : hSellRate.toFixed(2));
                    }
                }

                const costNum = parseFloat(pCost) || 0;
                const sellNum = parseFloat(pSell) || 0;
                const totCostForeign = costNum * nights;
                const totCostPkr = totCostForeign * costRoe;
                const totSellForeign = sellNum * nights;
                const totSellPkr = totSellForeign * saleRoe;

                const pType = document.getElementById(`person_pax_type_${p}`)?.value || 'adult';
                const hBadgeClass = pType === 'child' ? 'bg-info text-dark' : (pType === 'infant' ? 'bg-warning text-dark' : 'bg-primary');
                const pTypeLabel = pType.toUpperCase();

                rowsHtml += `
                <tr class="hotel-person-row" data-person-idx="${p}">
                    <td class="text-center text-muted fw-bold">${p + 1}</td>
                    <td>
                        <strong class="text-primary person-hotel-name-disp">${personName}</strong>
                        <span class="badge ${hBadgeClass} person-hotel-type-badge ms-1" style="font-size:10px;">${pTypeLabel}</span>
                        <input type="hidden" name="hotels[${hotelIdx}][person_rates][${p}][name]" class="person-hotel-name" value="${personName}">
                    </td>
                    <td class="text-center fw-semibold hotel-person-nights-disp">${nights}</td>
                    <td>
                        <input type="number" step="0.01" name="hotels[${hotelIdx}][person_rates][${p}][cost_per_night]"
                            class="form-control form-control-sm text-end hotel-person-cost" value="${pCost}" placeholder="0.00">
                        <small class="text-danger d-block text-end hotel-person-cost-disp" style="font-size:11px;">
                            ${totCostForeign.toFixed(2)} ${curr} (${totCostPkr.toFixed(2)} PKR)
                        </small>
                        <input type="hidden" name="hotels[${hotelIdx}][person_rates][${p}][total_cost]" class="hotel-person-total-cost" value="${totCostPkr.toFixed(2)}">
                    </td>
                    <td>
                        <input type="number" step="0.01" name="hotels[${hotelIdx}][person_rates][${p}][selling_per_night]"
                            class="form-control form-control-sm text-end hotel-person-sell" value="${pSell}" placeholder="0.00">
                        <small class="text-success d-block text-end hotel-person-sell-disp" style="font-size:11px;">
                            ${totSellForeign.toFixed(2)} ${curr} (${totSellPkr.toFixed(2)} PKR)
                        </small>
                        <input type="hidden" name="hotels[${hotelIdx}][person_rates][${p}][total_selling]" class="hotel-person-total-sell" value="${totSellPkr.toFixed(2)}">
                    </td>
                </tr>`;
            }

            tbody.innerHTML = rowsHtml;
        }

        function syncAllHotelPersons() {
            document.querySelectorAll('.hotel-block').forEach((block, idx) => {
                block.setAttribute('data-hotel-idx', idx);
                syncHotelPersons(block);
                calculateHotelRow(block);
            });
        }

        function calculateHotelRow(row, source) {
            if (!row) return;
            const nights = parseFloat(row.querySelector('.hotel-nights')?.value) || 0;
            const rooms = parseFloat(row.querySelector('.hotel-rooms')?.value) || 1;
            const curr = row.querySelector('.hotel-currency')?.value || 'SAR';
            const isPkr = (curr === 'PKR');

            const costRoeInput = row.querySelector('.hotel-cost-roe');
            const saleRoeInput = row.querySelector('.hotel-sale-roe');

            if (isPkr) {
                if (costRoeInput) { costRoeInput.value = '1.0000'; costRoeInput.readOnly = true; }
                if (saleRoeInput) { saleRoeInput.value = '1.0000'; saleRoeInput.readOnly = true; }
            } else {
                if (costRoeInput) costRoeInput.readOnly = false;
                if (saleRoeInput) saleRoeInput.readOnly = false;
            }

            const costRoe = isPkr ? 1 : (parseFloat(costRoeInput?.value) || parseFloat(row.querySelector('.hotel-roe')?.value) || 1);
            const saleRoe = isPkr ? 1 : (parseFloat(saleRoeInput?.value) || costRoe || 1);

            const legacyRoe = row.querySelector('.hotel-roe');
            if (legacyRoe) legacyRoe.value = costRoe;

            row.querySelectorAll('.hotel-curr-label').forEach(el => el.textContent = curr);
            row.querySelectorAll('.hotel-curr-prefix').forEach(el => el.textContent = `1 ${curr} =`);
            row.querySelectorAll('.hotel-curr-name').forEach(el => el.textContent = `${curr}:`);
            row.querySelectorAll('.hotel-person-nights-disp').forEach(el => el.textContent = nights);

            const personRows = row.querySelectorAll('.hotel-person-row');
            let sumPersonCostForeign = 0;
            let sumPersonSellForeign = 0;
            let hasPersonRates = false;

            if (personRows.length > 0) {
                personRows.forEach(pRow => {
                    const cInput = pRow.querySelector('.hotel-person-cost');
                    const sInput = pRow.querySelector('.hotel-person-sell');
                    const cRate = parseFloat(cInput?.value) || 0;
                    const sRate = parseFloat(sInput?.value) || 0;

                    if (cRate > 0 || sRate > 0) hasPersonRates = true;

                    const pTotCostCurr = cRate * nights;
                    const pTotCostPkr = pTotCostCurr * costRoe;
                    const pTotSellCurr = sRate * nights;
                    const pTotSellPkr = pTotSellCurr * saleRoe;

                    sumPersonCostForeign += pTotCostCurr;
                    sumPersonSellForeign += pTotSellCurr;

                    const cDisp = pRow.querySelector('.hotel-person-cost-disp');
                    const sDisp = pRow.querySelector('.hotel-person-sell-disp');
                    if (cDisp) cDisp.textContent = `${pTotCostCurr.toFixed(2)} ${curr} (${pTotCostPkr.toFixed(2)} PKR)`;
                    if (sDisp) sDisp.textContent = `${pTotSellCurr.toFixed(2)} ${curr} (${pTotSellPkr.toFixed(2)} PKR)`;

                    const hidCost = pRow.querySelector('.hotel-person-total-cost');
                    const hidSell = pRow.querySelector('.hotel-person-total-sell');
                    if (hidCost) hidCost.value = pTotCostPkr.toFixed(2);
                    if (hidSell) hidSell.value = pTotSellPkr.toFixed(2);
                });
            }

            let costCurr = 0;
            let sellCurr = 0;

            if (hasPersonRates) {
                costCurr = sumPersonCostForeign;
                sellCurr = sumPersonSellForeign;
                if (nights > 0) {
                    const crInput = row.querySelector('.hotel-cost-rate');
                    if (crInput && source !== 'costRate') {
                        crInput.value = (costCurr / nights).toFixed(2);
                    }
                    const srInput = row.querySelector('.hotel-sell-rate');
                    if (srInput && source !== 'sellRate') {
                        srInput.value = (sellCurr / nights).toFixed(2);
                    }
                }
            } else {
                const costRate = parseFloat(row.querySelector('.hotel-cost-rate')?.value) || 0;
                const sellRate = parseFloat(row.querySelector('.hotel-sell-rate')?.value) || 0;
                costCurr = nights * rooms * costRate;
                sellCurr = nights * rooms * sellRate;
            }

            const costPkr = costCurr * costRoe;
            const sellPkr = sellCurr * saleRoe;

            const costSarDisp = row.querySelector('.hotel-cost-sar-disp');
            const costPkrDisp = row.querySelector('.hotel-cost-pkr-disp');
            const costTotalInput = row.querySelector('.hotel-cost-total');

            const sellSarDisp = row.querySelector('.hotel-sell-sar-disp');
            const sellPkrDisp = row.querySelector('.hotel-sell-pkr-disp');
            const sellTotalInput = row.querySelector('.hotel-sell-total');

            if (costSarDisp) costSarDisp.textContent = costCurr.toFixed(2) + ' ' + curr;
            if (costPkrDisp) costPkrDisp.textContent = costPkr.toFixed(2) + ' PKR';
            if (costTotalInput) costTotalInput.value = costPkr.toFixed(2);

            if (sellSarDisp) sellSarDisp.textContent = sellCurr.toFixed(2) + ' ' + curr;
            if (sellPkrDisp) sellPkrDisp.textContent = sellPkr.toFixed(2) + ' PKR';
            if (sellTotalInput) sellTotalInput.value = sellPkr.toFixed(2);

            const tblCostCurr = row.querySelector('.hotel-person-tbl-cost-curr');
            const tblCostPkr = row.querySelector('.hotel-person-tbl-cost-pkr');
            const tblSellCurr = row.querySelector('.hotel-person-tbl-sell-curr');
            const tblSellPkr = row.querySelector('.hotel-person-tbl-sell-pkr');

            if (tblCostCurr) tblCostCurr.textContent = costCurr.toFixed(2) + ' ' + curr;
            if (tblCostPkr) tblCostPkr.textContent = costPkr.toFixed(2) + ' PKR';
            if (tblSellCurr) tblSellCurr.textContent = sellCurr.toFixed(2) + ' ' + curr;
            if (tblSellPkr) tblSellPkr.textContent = sellPkr.toFixed(2) + ' PKR';

            let totalHotelSelling = 0;
            document.querySelectorAll('.hotel-sell-total').forEach(el => {
                totalHotelSelling += parseFloat(el.value) || 0;
            });
            if (document.getElementById('hotel_charges')) {
                document.getElementById('hotel_charges').value = totalHotelSelling.toFixed(2);
            }
            calcTotal();
            updateHotelStaySummary();
        }

        function calculateHotelCost(row) {
            calculateHotelRow(row);
        }

        function calculateHotelNights(idx) {
            const block = document.querySelector(`.hotel-block[data-hotel-idx="${idx}"]`);
            if (!block) return;
            const cin = block.querySelector('.hotel-checkin')?.value;
            const cout = block.querySelector('.hotel-checkout')?.value;
            if (cin && cout) {
                const d1 = new Date(cin);
                const d2 = new Date(cout);
                const diffDays = Math.max(1, Math.round((d2 - d1) / (1000 * 60 * 60 * 24)));
                const nightsInput = block.querySelector('.hotel-nights');
                if (nightsInput) {
                    nightsInput.value = diffDays;
                    calculateHotelRow(block);
                }
            }
            updateHotelStaySummary();
        }

        function updateHotelStaySummary() {
            let minIn = null;
            let maxOut = null;
            let totalNights = 0;
            let totalRooms = 0;

            document.querySelectorAll('.hotel-block').forEach(b => {
                const cin = b.querySelector('.hotel-checkin')?.value;
                const cout = b.querySelector('.hotel-checkout')?.value;
                const nights = parseInt(b.querySelector('.hotel-nights')?.value) || 0;
                const rooms = parseInt(b.querySelector('.hotel-rooms')?.value) || 0;
                totalNights += nights;
                totalRooms += rooms;
                if (cin && (!minIn || cin < minIn)) minIn = cin;
                if (cout && (!maxOut || cout > maxOut)) maxOut = cout;
            });

            const sumDisp = document.getElementById('hotel_stay_summary_dates');
            const nightsDisp = document.getElementById('hotel_stay_total_nights');
            const roomsDisp = document.getElementById('hotel_stay_total_rooms');

            if (sumDisp) {
                if (minIn && maxOut) {
                    sumDisp.textContent = `${minIn} to ${maxOut}`;
                    sumDisp.className = 'badge bg-success text-white fs-12 ms-1';
                } else if (minIn) {
                    sumDisp.textContent = `Check-in: ${minIn}`;
                    sumDisp.className = 'badge bg-soft-primary text-primary fs-12 ms-1';
                } else {
                    sumDisp.textContent = 'No check-in dates set';
                    sumDisp.className = 'badge bg-light text-muted fs-12 ms-1';
                }
            }
            if (nightsDisp) nightsDisp.textContent = `${totalNights} Total Nights`;
            if (roomsDisp) roomsDisp.textContent = `${totalRooms} Total Rooms`;
        }

        // Point 8: Automatic & Manual Main Cost Division Across Passengers
        function autoDivideHotelRates(block, whichField) {
            if (!block) return;
            const personRows = block.querySelectorAll('.hotel-person-row');
            const paxCount = personRows.length > 0 ? personRows.length : (parseInt(document.getElementById('no_of_pax')?.value) || 1);
            if (paxCount <= 0) return;

            if (!whichField || whichField === 'cost') {
                const costRate = parseFloat(block.querySelector('.hotel-cost-rate')?.value) || 0;
                const perPaxCost = (costRate > 0) ? (costRate / paxCount).toFixed(2) : '0.00';
                personRows.forEach(el => {
                    const cInput = el.querySelector('.hotel-person-cost');
                    if (cInput) cInput.value = perPaxCost;
                });
            }

            if (!whichField || whichField === 'sell') {
                const sellRate = parseFloat(block.querySelector('.hotel-sell-rate')?.value) || 0;
                const perPaxSell = (sellRate > 0) ? (sellRate / paxCount).toFixed(2) : '0.00';
                personRows.forEach(el => {
                    const sInput = el.querySelector('.hotel-person-sell');
                    if (sInput) sInput.value = perPaxSell;
                });
            }
        }

        function divideHotelMainCost(idx) {
            const block = document.querySelector(`.hotel-block[data-hotel-idx="${idx}"]`);
            if (!block) return;
            autoDivideHotelRates(block);
            calculateHotelRow(block);
        }

        // Point 9: Add Additional Room to existing hotel
        let addRoomCounter = 100;
        function addAdditionalRoomToHotel(hotelIdx) {
            const container = document.getElementById(`additional_rooms_list_${hotelIdx}`);
            if (!container) return;
            const curr = document.querySelector(`.hotel-block[data-hotel-idx="${hotelIdx}"] .hotel-currency`)?.value || 'SAR';
            const rId = addRoomCounter++;

            const html = `
            <div class="border rounded p-2 mb-2 bg-light additional-room-item" id="add_room_${hotelIdx}_${rId}">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <strong class="text-primary fs-12"><i class="mdi mdi-door"></i> Additional Room</strong>
                    <button type="button" class="btn btn-xs btn-outline-danger" onclick="document.getElementById('add_room_${hotelIdx}_${rId}').remove(); calculateHotelRow(document.querySelector('.hotel-block[data-hotel-idx=\\'${hotelIdx}\\']')); updateHotelStaySummary();">×</button>
                </div>
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label fs-11 mb-0">Room Type</label>
                        <select name="hotels[${hotelIdx}][extra_rooms][${rId}][room_type]" class="form-select form-select-sm">
                            <option value="single">Single</option>
                            <option value="double" selected>Double</option>
                            <option value="triple">Triple</option>
                            <option value="quad">Quad</option>
                            <option value="sharing">Sharing</option>
                            <option value="suite">Suite</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-11 mb-0">Room #</label>
                        <input type="text" name="hotels[${hotelIdx}][extra_rooms][${rId}][room_number]" class="form-control form-control-sm" placeholder="e.g. 502">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-11 mb-0">Cost Rate (${curr})</label>
                        <input type="number" step="0.01" name="hotels[${hotelIdx}][extra_rooms][${rId}][cost_rate]" class="form-control form-control-sm add-room-cost" value="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-11 mb-0">Sell Rate (${curr})</label>
                        <input type="number" step="0.01" name="hotels[${hotelIdx}][extra_rooms][${rId}][sell_rate]" class="form-control form-control-sm add-room-sell" value="0">
                    </div>
                </div>
            </div>`;
            container.insertAdjacentHTML('beforeend', html);
            updateHotelStaySummary();
        }

        // Transports Dynamic Management
        let routeIdx = "dummy";

        document.getElementById('addRoute')?.addEventListener('click', function() {
            const flightCurr = document.getElementById('flight_currency')?.value || 'SAR';
            const defaultCostRoe = (flightCurr === 'PKR') ? 1 : (document.getElementById('flight_cost_roe')?.value || 75);
            const defaultSaleRoe = (flightCurr === 'PKR') ? 1 : (document.getElementById('flight_sale_roe')?.value || defaultCostRoe);
            const isPkr = (flightCurr === 'PKR');

            document.getElementById('routesList').insertAdjacentHTML('beforeend', `
                <div class="route-block border rounded p-3 mb-2 bg-white shadow-sm">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small fw-bold">Route #${routeIdx + 1}</span>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-route">×</button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-3"><label class="form-label">Route</label>
                            <select name="transports[${routeIdx}][route]" class="form-select transport-route-select">
                                ${routesOptions}
                            </select></div>
                        <div class="col-md-2"><label class="form-label">Vehicle</label>
                            <select name="transports[${routeIdx}][vehicle]" class="form-select transport-vehicle-select">
                                ${vehiclesOptions}
                            </select></div>
                        <div class="col-md-1"><label class="form-label">Qty</label>
                            <input type="number" name="transports[${routeIdx}][quantity]" class="form-control" value="1" min="1"></div>
                        <div class="col-md-2"><label class="form-label">Type / Status</label>
                            <select name="transports[${routeIdx}][status]" class="form-select">
                                <option value="CONFIRMED" selected>CONFIRMED</option>
                                <option value="PENDING">PENDING</option>
                                <option value="CANCELLED">CANCELLED</option>
                            </select></div>
                        <div class="col-md-1"><label class="form-label">Pax</label>
                            <input type="number" name="transports[${routeIdx}][pax]" class="form-control" value="1" min="1"></div>
                        <div class="col-md-3"><label class="form-label">Transport Supplier / Vendor</label>
                            <select name="transports[${routeIdx}][supplier_id]" class="form-select">
                                ${suppliersOptions}
                            </select></div>
                        <div class="col-md-2">
                            <label class="form-label"><i class="mdi mdi-calendar-range me-1 text-primary"></i> Date</label>
                            <input type="date" name="transports[${routeIdx}][travel_date]" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label"><i class="mdi mdi-clock-outline me-1 text-primary"></i> Pickup Time</label>
                            <input type="text" name="transports[${routeIdx}][travel_time]" class="form-control" placeholder="e.g. 0045 / 1200">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label"><i class="mdi mdi-airplane me-1 text-info"></i> Flight #</label>
                            <input type="text" name="transports[${routeIdx}][flight_no]" class="form-control" placeholder="e.g. PK831">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label"><i class="mdi mdi-map-marker-outline me-1 text-success"></i> Pickup From</label>
                            <input type="text" name="transports[${routeIdx}][pickup_from]" class="form-control transport-pickup-from" placeholder="e.g. JED APT">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label"><i class="mdi mdi-map-marker-check me-1 text-danger"></i> Destination</label>
                            <input type="text" name="transports[${routeIdx}][destination]" class="form-control transport-destination" placeholder="e.g. MAK HTL">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-dark"><i class="mdi mdi-barcode me-1 text-secondary"></i> BRN #</label>
                            <input type="text" name="transports[${routeIdx}][brn]" class="form-control" placeholder="BRN Number">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="mdi mdi-airplane-takeoff me-1 text-info"></i> Airline</label>
                            <select name="transports[${routeIdx}][airline]" class="form-select transport-airline-select">
                                ${airlinesOptions}
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted"><i class="mdi mdi-calendar-check me-1"></i> Booking Date</label>
                            <input type="date" name="transports[${routeIdx}][booking_date]" class="form-control" value=""dummy"">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-dark fw-bold">Currency</label>
                            <select name="transports[${routeIdx}][currency]" class="form-select transport-currency fw-bold">
                                <option value="SAR" ${flightCurr === 'SAR' ? 'selected' : ''}>SAR (Riyal)</option>
                                <option value="PKR" ${flightCurr === 'PKR' ? 'selected' : ''}>PKR (Rupee)</option>
                                <option value="USD" ${flightCurr === 'USD' ? 'selected' : ''}>USD (Dollar)</option>
                                <option value="MYR" ${flightCurr === 'MYR' ? 'selected' : ''}>MYR (Ringgit)</option>
                                <option value="AED" ${flightCurr === 'AED' ? 'selected' : ''}>AED (Dirham)</option>
                            </select></div>
                        <div class="col-md-2">
                            <label class="form-label text-danger fw-bold">Cost ROE</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light transport-curr-prefix">1 ${flightCurr} =</span>
                                <input type="number" step="0.0001" name="transports[${routeIdx}][cost_roe]" class="form-control transport-cost-roe" placeholder="75.00" value="${defaultCostRoe}" ${isPkr ? 'readonly' : ''}>
                            </div>
                            <input type="hidden" name="transports[${routeIdx}][roe]" class="transport-roe" value="${defaultCostRoe}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-success fw-bold">Sale ROE</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light transport-curr-prefix">1 ${flightCurr} =</span>
                                <input type="number" step="0.0001" name="transports[${routeIdx}][sale_roe]" class="form-control transport-sale-roe" placeholder="75.00" value="${defaultSaleRoe}" ${isPkr ? 'readonly' : ''}>
                            </div>
                        </div>
                        <div class="col-md-2"><label class="form-label text-danger fw-semibold">Cost Rate (<span class="transport-curr-label">${flightCurr}</span>)</label>
                            <input type="number" step="0.01" name="transports[${routeIdx}][cost_rate]" class="form-control transport-cost-rate" placeholder="0.00" value="0"></div>
                        <div class="col-md-3"><label class="form-label text-danger fw-semibold">Total Cost (<span class="transport-curr-label">${flightCurr}</span> & PKR)</label>
                            <div class="p-1 px-2 border rounded bg-light">
                                <div class="d-flex justify-content-between small text-danger"><span class="transport-curr-name">${flightCurr}:</span><strong class="transport-cost-sar-disp">0.00 ${flightCurr}</strong></div>
                                <div class="d-flex justify-content-between small text-danger"><span>PKR:</span><strong class="transport-cost-pkr-disp">0.00 PKR</strong></div>
                            </div>
                            <input type="hidden" name="transports[${routeIdx}][cost_amount]" class="transport-cost-amount" value="0"></div>
                        <div class="col-md-2"><label class="form-label text-success fw-semibold">Sell Rate (<span class="transport-curr-label">${flightCurr}</span>)</label>
                            <input type="number" step="0.01" name="transports[${routeIdx}][selling_rate]" class="form-control transport-sell-rate" placeholder="0.00" value="0"></div>
                        <div class="col-md-3"><label class="form-label text-success fw-semibold">Total Sell (<span class="transport-curr-label">${flightCurr}</span> & PKR)</label>
                            <div class="p-1 px-2 border rounded bg-light">
                                <div class="d-flex justify-content-between small text-success"><span class="transport-curr-name">${flightCurr}:</span><strong class="transport-sell-sar-disp">0.00 ${flightCurr}</strong></div>
                                <div class="d-flex justify-content-between small text-success"><span>PKR:</span><strong class="transport-sell-pkr-disp">0.00 PKR</strong></div>
                            </div>
                            <input type="hidden" name="transports[${routeIdx}][selling_amount]" class="transport-sell-amount" value="0"></div>
                        <div class="col-md-6"><label class="form-label">Notes</label>
                            <input type="text" name="transports[${routeIdx}][notes]" class="form-control" placeholder="Notes"></div>
                        <div class="col-md-6"><label class="form-label">Transport Ticket / Receipt</label>
                            <input type="file" name="transports[${routeIdx}][transport_ticket]" class="form-control" accept="image/*,.pdf"></div>
                    </div>
                </div>`);
            routeIdx++;
        });

        document.getElementById('routesList')?.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-route') || e.target.closest('.remove-route')) {
                e.target.closest('.route-block').remove();
                calculateAllTransports();
            }
        });

        document.getElementById('routesList')?.addEventListener('change', function(e) {
            if (e.target.classList.contains('transport-route-select')) {
                const row = e.target.closest('.route-block');
                const selectedText = e.target.options[e.target.selectedIndex]?.text || e.target.value || '';
                let pickup = '';
                let dest = '';
                if (selectedText.includes('→')) {
                    const parts = selectedText.split('→');
                    pickup = parts[0]?.trim();
                    dest = parts[1]?.trim();
                } else if (selectedText.toUpperCase().includes(' TO ')) {
                    const parts = selectedText.split(/ to /i);
                    pickup = parts[0]?.trim();
                    dest = parts[1]?.trim();
                } else if (selectedText.includes('-')) {
                    const parts = selectedText.split('-');
                    pickup = parts[0]?.trim();
                    dest = parts[1]?.trim();
                }
                const pickupInput = row?.querySelector('.transport-pickup-from');
                const destInput = row?.querySelector('.transport-destination');
                if (pickupInput && pickup) pickupInput.value = pickup;
                if (destInput && dest) destInput.value = dest;
            }

            if (e.target.classList.contains('transport-currency')) {
                const row = e.target.closest('.route-block');
                const val = e.target.value;
                const costRoeInput = row.querySelector('.transport-cost-roe');
                const saleRoeInput = row.querySelector('.transport-sale-roe');
                if (val === 'PKR') {
                    if (costRoeInput) { costRoeInput.value = '1.0000'; costRoeInput.readOnly = true; }
                    if (saleRoeInput) { saleRoeInput.value = '1.0000'; saleRoeInput.readOnly = true; }
                } else {
                    if (costRoeInput) {
                        costRoeInput.readOnly = false;
                        if (val === 'SAR') costRoeInput.value = '75.0000';
                        else if (val === 'MYR') costRoeInput.value = '63.0000';
                        else if (val === 'USD') costRoeInput.value = '278.0000';
                        else if (val === 'AED') costRoeInput.value = '76.0000';
                    }
                    if (saleRoeInput) {
                        saleRoeInput.readOnly = false;
                        if (val === 'SAR') saleRoeInput.value = '75.0000';
                        else if (val === 'MYR') saleRoeInput.value = '63.0000';
                        else if (val === 'USD') saleRoeInput.value = '278.0000';
                        else if (val === 'AED') saleRoeInput.value = '76.0000';
                    }
                }
                calculateTransportRow(row);
            }
        });

        function calculateTransportRow(row) {
            if (!row) return;
            const curr = row.querySelector('.transport-currency')?.value || 'SAR';
            const isPkr = (curr === 'PKR');

            const costRoeInput = row.querySelector('.transport-cost-roe');
            const saleRoeInput = row.querySelector('.transport-sale-roe');

            if (isPkr) {
                if (costRoeInput) { costRoeInput.value = '1.0000'; costRoeInput.readOnly = true; }
                if (saleRoeInput) { saleRoeInput.value = '1.0000'; saleRoeInput.readOnly = true; }
            } else {
                if (costRoeInput) costRoeInput.readOnly = false;
                if (saleRoeInput) saleRoeInput.readOnly = false;
            }

            const costRoe = isPkr ? 1 : (parseFloat(costRoeInput?.value) || parseFloat(row.querySelector('.transport-roe')?.value) || 1);
            const saleRoe = isPkr ? 1 : (parseFloat(saleRoeInput?.value) || costRoe || 1);

            const legacyRoe = row.querySelector('.transport-roe');
            if (legacyRoe) legacyRoe.value = costRoe;

            row.querySelectorAll('.transport-curr-label').forEach(el => el.textContent = curr);
            row.querySelectorAll('.transport-curr-prefix').forEach(el => el.textContent = `1 ${curr} =`);
            row.querySelectorAll('.transport-curr-name').forEach(el => el.textContent = `${curr}:`);

            const costRate = parseFloat(row.querySelector('.transport-cost-rate')?.value) || 0;
            const sellRate = parseFloat(row.querySelector('.transport-sell-rate')?.value) || 0;

            const costCurr = costRate;
            const costPkr = costCurr * costRoe;
            const sellCurr = sellRate;
            const sellPkr = sellCurr * saleRoe;

            const costSarDisp = row.querySelector('.transport-cost-sar-disp');
            const costPkrDisp = row.querySelector('.transport-cost-pkr-disp');
            const costAmtInput = row.querySelector('.transport-cost-amount');

            const sellSarDisp = row.querySelector('.transport-sell-sar-disp');
            const sellPkrDisp = row.querySelector('.transport-sell-pkr-disp');
            const sellAmtInput = row.querySelector('.transport-sell-amount');

            if (costSarDisp) costSarDisp.textContent = costCurr.toFixed(2) + ' ' + curr;
            if (costPkrDisp) costPkrDisp.textContent = costPkr.toFixed(2) + ' PKR';
            if (costAmtInput) costAmtInput.value = costPkr.toFixed(2);

            if (sellSarDisp) sellSarDisp.textContent = sellCurr.toFixed(2) + ' ' + curr;
            if (sellPkrDisp) sellPkrDisp.textContent = sellPkr.toFixed(2) + ' PKR';
            if (sellAmtInput) sellAmtInput.value = sellPkr.toFixed(2);

            calculateAllTransports();
        }

        function calculateAllTransports() {
            let totalTransportSelling = 0;
            document.querySelectorAll('.transport-sell-amount').forEach(el => {
                totalTransportSelling += parseFloat(el.value) || 0;
            });
            if (document.getElementById('transport_charges')) {
                document.getElementById('transport_charges').value = totalTransportSelling.toFixed(2);
            }
            calcTotal();
        }

        document.getElementById('visasList')?.addEventListener('change', function(e) {
            if (e.target.classList.contains('visa-currency')) {
                const row = e.target.closest('.visa-card');
                const val = e.target.value;
                const costRoeInput = row.querySelector('.visa-cost-roe');
                const saleRoeInput = row.querySelector('.visa-sale-roe');
                if (val === 'PKR') {
                    if (costRoeInput) { costRoeInput.value = '1.0000'; costRoeInput.readOnly = true; }
                    if (saleRoeInput) { saleRoeInput.value = '1.0000'; saleRoeInput.readOnly = true; }
                } else {
                    if (costRoeInput) {
                        costRoeInput.readOnly = false;
                        if (val === 'SAR') costRoeInput.value = '75.0000';
                        else if (val === 'MYR') costRoeInput.value = '63.0000';
                        else if (val === 'USD') costRoeInput.value = '278.0000';
                        else if (val === 'AED') costRoeInput.value = '76.0000';
                    }
                    if (saleRoeInput) {
                        saleRoeInput.readOnly = false;
                        if (val === 'SAR') saleRoeInput.value = '75.0000';
                        else if (val === 'MYR') saleRoeInput.value = '63.0000';
                        else if (val === 'USD') saleRoeInput.value = '278.0000';
                        else if (val === 'AED') saleRoeInput.value = '76.0000';
                    }
                }
                calculateVisaRow(row);
            }
        });

        function calculateVisaRow(row) {
            if (!row) return;
            const curr = row.querySelector('.visa-currency')?.value || 'SAR';
            const isPkr = (curr === 'PKR');

            const costRoeInput = row.querySelector('.visa-cost-roe');
            const saleRoeInput = row.querySelector('.visa-sale-roe');

            if (isPkr) {
                if (costRoeInput) { costRoeInput.value = '1.0000'; costRoeInput.readOnly = true; }
                if (saleRoeInput) { saleRoeInput.value = '1.0000'; saleRoeInput.readOnly = true; }
            } else {
                if (costRoeInput) costRoeInput.readOnly = false;
                if (saleRoeInput) saleRoeInput.readOnly = false;
            }

            const costRoe = isPkr ? 1 : (parseFloat(costRoeInput?.value) || parseFloat(row.querySelector('.visa-roe')?.value) || 1);
            const saleRoe = isPkr ? 1 : (parseFloat(saleRoeInput?.value) || costRoe || 1);

            const legacyRoe = row.querySelector('.visa-roe');
            if (legacyRoe) legacyRoe.value = costRoe;

            row.querySelectorAll('.visa-curr-label').forEach(el => el.textContent = curr);
            row.querySelectorAll('.visa-curr-prefix').forEach(el => el.textContent = `1 ${curr} =`);
            row.querySelectorAll('.visa-curr-name').forEach(el => el.textContent = `${curr}:`);

            const costRate = parseFloat(row.querySelector('.visa-cost-rate')?.value) || 0;
            const sellRate = parseFloat(row.querySelector('.visa-sell-rate')?.value) || 0;

            const costCurr = costRate;
            const costPkr = costCurr * costRoe;
            const sellCurr = sellRate;
            const sellPkr = sellCurr * saleRoe;

            const costSarDisp = row.querySelector('.visa-cost-sar-disp');
            const costPkrDisp = row.querySelector('.visa-cost-pkr-disp');
            const costAmtInput = row.querySelector('.visa-cost-amount');

            const sellSarDisp = row.querySelector('.visa-sell-sar-disp');
            const sellPkrDisp = row.querySelector('.visa-sell-pkr-disp');
            const sellAmtInput = row.querySelector('.visa-sell-amount');

            if (costSarDisp) costSarDisp.textContent = costCurr.toFixed(2) + ' ' + curr;
            if (costPkrDisp) costPkrDisp.textContent = costPkr.toFixed(2) + ' PKR';
            if (costAmtInput) costAmtInput.value = costPkr.toFixed(2);

            if (sellSarDisp) sellSarDisp.textContent = sellCurr.toFixed(2) + ' ' + curr;
            if (sellPkrDisp) sellPkrDisp.textContent = sellPkr.toFixed(2) + ' PKR';
            if (sellAmtInput) sellAmtInput.value = sellPkr.toFixed(2);

            let totalVisaSelling = 0;
            document.querySelectorAll('.visa-sell-amount').forEach(el => {
                totalVisaSelling += parseFloat(el.value) || 0;
            });
            if (document.getElementById('visa_charges')) {
                document.getElementById('visa_charges').value = totalVisaSelling.toFixed(2);
            }
            calcTotal();
        }

        function syncBookingHotelDates() {
            const hotelBlocks = Array.from(document.querySelectorAll('.hotel-block'));
            let prevCheckoutDate = null;

            hotelBlocks.forEach((block, idx) => {
                const checkInEl  = block.querySelector('input[name*="[check_in]"]');
                const checkOutEl = block.querySelector('input[name*="[check_out]"]');

                if (checkInEl) {
                    if (idx > 0 && prevCheckoutDate) {
                        checkInEl.setAttribute('min', prevCheckoutDate);
                        if (checkInEl.value && checkInEl.value < prevCheckoutDate) {
                            checkInEl.value = prevCheckoutDate;
                        }
                    } else {
                        checkInEl.removeAttribute('min');
                    }
                }

                if (checkOutEl) {
                    const currentIn = checkInEl?.value;
                    if (currentIn) {
                        checkOutEl.setAttribute('min', currentIn);
                        if (checkOutEl.value && checkOutEl.value < currentIn) {
                            checkOutEl.value = currentIn;
                        }
                    } else if (idx > 0 && prevCheckoutDate) {
                        checkOutEl.setAttribute('min', prevCheckoutDate);
                    } else {
                        checkOutEl.removeAttribute('min');
                    }
                }

                if (checkOutEl && checkOutEl.value) {
                    prevCheckoutDate = checkOutEl.value;
                } else {
                    prevCheckoutDate = null;
                }
            });
        }

        function handleHotelDateCalc(target) {
            const row = target.closest('.hotel-block');
            if (!row) return;

            const checkInEl  = row.querySelector('input[name*="[check_in]"]');
            const checkOutEl = row.querySelector('input[name*="[check_out]"]');
            const nightsEl   = row.querySelector('input[name*="[no_of_nights]"]');

            if (!checkInEl || !checkOutEl || !nightsEl) return;

            const isCheckIn  = target === checkInEl;
            const isCheckOut = target === checkOutEl;
            const isNights   = target === nightsEl;

            if (!isCheckIn && !isCheckOut && !isNights) return;

            const inVal  = checkInEl.value;
            const outVal = checkOutEl.value;
            const nVal   = parseInt(nightsEl.value);

            if ((isCheckIn || isCheckOut) && inVal && outVal) {
                const dIn  = new Date(inVal);
                const dOut = new Date(outVal);
                const diff = Math.round((dOut.getTime() - dIn.getTime()) / (1000 * 3600 * 24));
                if (!isNaN(diff) && diff >= 0) {
                    nightsEl.value = diff;
                }
            } else if ((isCheckIn || isNights) && inVal && !isNaN(nVal) && nVal >= 0) {
                const dIn = new Date(inVal);
                dIn.setDate(dIn.getDate() + nVal);
                const year = dIn.getFullYear();
                const month = String(dIn.getMonth() + 1).padStart(2, '0');
                const day = String(dIn.getDate()).padStart(2, '0');
                checkOutEl.value = `${year}-${month}-${day}`;
            } else if (isCheckOut && outVal && !isNaN(nVal) && nVal >= 0 && !inVal) {
                const dOut = new Date(outVal);
                dOut.setDate(dOut.getDate() - nVal);
                const year = dOut.getFullYear();
                const month = String(dOut.getMonth() + 1).padStart(2, '0');
                const day = String(dOut.getDate()).padStart(2, '0');
                checkInEl.value = `${year}-${month}-${day}`;
            }

            syncBookingHotelDates();
            calculateHotelRow(row);
        }

        // Main Total and Dual Currency Costing Calculation
        function calcTotal() {
            const adults   = parseInt(document.getElementById('pax_adults')?.value) || 0;
            const children = parseInt(document.getElementById('pax_children')?.value) || 0;
            const infants  = parseInt(document.getElementById('pax_infants')?.value) || 0;
            const pax      = Math.max(1, adults + children + infants);

            // 1. Flight Foreign & PKR (Cost & Selling)
            const flightCurr = document.getElementById('flight_currency')?.value || 'SAR';
            let flightForeign = 0;
            const flSellDisp = document.getElementById('flight_sell_sar_total')?.textContent || '';
            flightForeign = parseFloat(flSellDisp) || 0;
            const flightSaleRoe = (flightCurr === 'PKR') ? 1 : (parseFloat(document.getElementById('flight_sale_roe')?.value) || parseFloat(document.getElementById('flight_cost_roe')?.value) || 75);
            const flightCostRoe = (flightCurr === 'PKR') ? 1 : (parseFloat(document.getElementById('flight_cost_roe')?.value) || 75);

            let flightCostForeign = 0;
            const flCostDisp = document.getElementById('flight_cost_sar_total')?.textContent || '';
            flightCostForeign = parseFloat(flCostDisp) || 0;
            let flightCostPkr = parseFloat(document.getElementById('flight_cost')?.value) || 0;
            if (flightCostPkr === 0 && flightCostForeign > 0) {
                flightCostPkr = (flightCurr === 'PKR') ? flightCostForeign : (flightCostForeign * flightCostRoe);
            }

            let flightPkr = 0;
            const flSellPkrDisp = document.getElementById('flight_sell_pkr_total')?.textContent || '';
            if (flSellPkrDisp) {
                flightPkr = parseFloat(flSellPkrDisp) || 0;
            }
            if (flightPkr === 0) {
                flightPkr = parseFloat(document.getElementById('flight_charges')?.value) || 0;
            }
            if (flightPkr === 0 && flightForeign > 0) {
                flightPkr = (flightCurr === 'PKR') ? flightForeign : (flightForeign * flightSaleRoe);
            }

            if (flightForeign === 0 && flightPkr > 0 && flightCurr !== 'PKR' && flightSaleRoe > 0) {
                flightForeign = flightPkr / flightSaleRoe;
                const elFlDisp = document.getElementById('flight_sell_sar_total');
                if (elFlDisp) elFlDisp.textContent = flightForeign.toFixed(2) + ' ' + flightCurr;
            }

            // Always synchronize flight_charges input so it never stays 0
            const elCharges = document.getElementById('flight_charges');
            if (elCharges) elCharges.value = flightPkr.toFixed(2);
            const elChargesDisp = document.getElementById('flight_charges_disp');
            if (elChargesDisp) elChargesDisp.value = flightPkr.toFixed(2);

            // 2. Hotel Foreign & PKR
            let hotelCostForeign = 0;
            let hotelCostPkr = 0;
            let hotelSellForeign = 0;
            let hotelSellPkr = 0;
            let hotelCurrs = new Set();
            document.querySelectorAll('.hotel-block').forEach(row => {
                const costTotalInput = row.querySelector('.hotel-cost-total');
                const sellTotalInput = row.querySelector('.hotel-sell-total');
                const costSarDisp = row.querySelector('.hotel-cost-sar-disp');
                const sellSarDisp = row.querySelector('.hotel-sell-sar-disp');
                const curr = row.querySelector('.hotel-currency')?.value || 'SAR';

                const cPkr = parseFloat(costTotalInput?.value) || 0;
                const sPkr = parseFloat(sellTotalInput?.value) || 0;
                const cForeign = parseFloat(costSarDisp?.textContent) || 0;
                const sForeign = parseFloat(sellSarDisp?.textContent) || 0;

                hotelCostForeign += cForeign;
                hotelCostPkr += cPkr;
                hotelSellForeign += sForeign;
                hotelSellPkr += sPkr;
                hotelCurrs.add(curr);
            });
            const hotelCurrLabel = hotelCurrs.size === 1 ? Array.from(hotelCurrs)[0] : (hotelCurrs.size > 1 ? 'Mix' : 'SAR');
            if (document.getElementById('hotel_charges')) document.getElementById('hotel_charges').value = hotelSellPkr.toFixed(2);
            if (document.getElementById('hotel_cost_sar_total')) document.getElementById('hotel_cost_sar_total').textContent = hotelCostForeign.toFixed(2) + ' ' + hotelCurrLabel;
            if (document.getElementById('hotel_cost_pkr_total')) document.getElementById('hotel_cost_pkr_total').textContent = hotelCostPkr.toFixed(2) + ' PKR';
            if (document.getElementById('hotel_sell_sar_total')) document.getElementById('hotel_sell_sar_total').textContent = hotelSellForeign.toFixed(2) + ' ' + hotelCurrLabel;
            if (document.getElementById('hotel_sell_pkr_total')) document.getElementById('hotel_sell_pkr_total').textContent = hotelSellPkr.toFixed(2) + ' PKR';

            // 3. Transport Foreign & PKR
            let transportCostForeign = 0;
            let transportCostPkr = 0;
            let transportSellForeign = 0;
            let transportSellPkr = 0;
            let transportCurrs = new Set();
            document.querySelectorAll('.route-block').forEach(row => {
                const costRate = parseFloat(row.querySelector('.transport-cost-rate')?.value) || 0;
                const sellRate = parseFloat(row.querySelector('.transport-sell-rate')?.value) || 0;
                const curr     = row.querySelector('.transport-currency')?.value || 'SAR';
                const isPkr    = (curr === 'PKR');
                const costRoe  = isPkr ? 1 : (parseFloat(row.querySelector('.transport-cost-roe')?.value) || parseFloat(row.querySelector('.transport-roe')?.value) || 75);
                const saleRoe  = isPkr ? 1 : (parseFloat(row.querySelector('.transport-sale-roe')?.value) || costRoe || 75);

                transportCostForeign += costRate;
                transportCostPkr += costRate * costRoe;

                transportSellForeign += sellRate;
                transportSellPkr += sellRate * saleRoe;
                transportCurrs.add(curr);
            });
            const transportCurrLabel = transportCurrs.size === 1 ? Array.from(transportCurrs)[0] : (transportCurrs.size > 1 ? 'Mix' : 'SAR');
            if (document.getElementById('transport_charges')) document.getElementById('transport_charges').value = transportSellPkr.toFixed(2);
            if (document.getElementById('transport_cost_sar_total')) document.getElementById('transport_cost_sar_total').textContent = transportCostForeign.toFixed(2) + ' ' + transportCurrLabel;
            if (document.getElementById('transport_cost_pkr_total')) document.getElementById('transport_cost_pkr_total').textContent = transportCostPkr.toFixed(2) + ' PKR';
            if (document.getElementById('transport_sell_sar_total')) document.getElementById('transport_sell_sar_total').textContent = transportSellForeign.toFixed(2) + ' ' + transportCurrLabel;
            if (document.getElementById('transport_sell_pkr_total')) document.getElementById('transport_sell_pkr_total').textContent = transportSellPkr.toFixed(2) + ' PKR';

            // 4. Visa Foreign & PKR
            let visaCostForeign = 0;
            let visaCostPkr = 0;
            let visaSellForeign = 0;
            let visaSellPkr = 0;
            let visaCurrs = new Set();
            document.querySelectorAll('.visa-card').forEach(row => {
                const costRate = parseFloat(row.querySelector('.visa-cost-rate')?.value) || 0;
                const sellRate = parseFloat(row.querySelector('.visa-sell-rate')?.value) || 0;
                const curr     = row.querySelector('.visa-currency')?.value || 'SAR';
                const isPkr    = (curr === 'PKR');
                const costRoe  = isPkr ? 1 : (parseFloat(row.querySelector('.visa-cost-roe')?.value) || parseFloat(row.querySelector('.visa-roe')?.value) || 75);
                const saleRoe  = isPkr ? 1 : (parseFloat(row.querySelector('.visa-sale-roe')?.value) || costRoe || 75);

                visaCostForeign += costRate;
                visaCostPkr += costRate * costRoe;

                visaSellForeign += sellRate;
                visaSellPkr += sellRate * saleRoe;
                visaCurrs.add(curr);
            });
            const visaCurrLabel = visaCurrs.size === 1 ? Array.from(visaCurrs)[0] : (visaCurrs.size > 1 ? 'Mix' : 'SAR');
            if (document.getElementById('visa_charges')) document.getElementById('visa_charges').value = visaSellPkr.toFixed(2);
            if (document.getElementById('visa_cost_sar_total')) document.getElementById('visa_cost_sar_total').textContent = visaCostForeign.toFixed(2) + ' ' + visaCurrLabel;
            if (document.getElementById('visa_cost_pkr_total')) document.getElementById('visa_cost_pkr_total').textContent = visaCostPkr.toFixed(2) + ' PKR';
            if (document.getElementById('visa_sell_sar_total')) document.getElementById('visa_sell_sar_total').textContent = visaSellForeign.toFixed(2) + ' ' + visaCurrLabel;
            if (document.getElementById('visa_sell_pkr_total')) document.getElementById('visa_sell_pkr_total').textContent = visaSellPkr.toFixed(2) + ' PKR';

            // Package, Other, Discount
            const pkgCost     = parseFloat(document.getElementById('package_cost')?.value) || 0;
            const pkgTotalPkr = pkgCost * pax;

            const other      = parseFloat(document.getElementById('other_charges')?.value) || 0;
            const discount   = parseFloat(document.getElementById('discount')?.value) || 0;
            const additional = parseFloat(document.getElementById('additional_services_amount')?.value) || 0;
            const received   = parseFloat(document.getElementById('total_received')?.value) || 0;

            const totalPkr   = pkgTotalPkr + hotelSellPkr + visaSellPkr + flightPkr + transportSellPkr + other + additional - discount;
            const balancePkr = totalPkr - received - transactionsPaid;

            let activeCurrs = new Set();
            if (flightForeign > 0) activeCurrs.add(flightCurr);
            if (hotelSellForeign > 0) hotelCurrs.forEach(c => activeCurrs.add(c));
            if (transportSellForeign > 0) transportCurrs.forEach(c => activeCurrs.add(c));
            if (visaSellForeign > 0) visaCurrs.forEach(c => activeCurrs.add(c));
            activeCurrs.delete('PKR');

            let totalForeign = 0;
            let foreignUnit = 'SAR';
            if (activeCurrs.size === 1) {
                foreignUnit = Array.from(activeCurrs)[0];
                totalForeign = (flightCurr === foreignUnit ? flightForeign : 0)
                             + (hotelCurrLabel === foreignUnit ? hotelSellForeign : 0)
                             + (transportCurrLabel === foreignUnit ? transportSellForeign : 0)
                             + (visaCurrLabel === foreignUnit ? visaSellForeign : 0);
            } else if (activeCurrs.size > 1) {
                foreignUnit = 'Mix';
                totalForeign = flightForeign + hotelSellForeign + transportSellForeign + visaSellForeign;
            } else {
                foreignUnit = (flightCurr !== 'PKR') ? flightCurr : 'SAR';
                totalForeign = flightForeign + hotelSellForeign + transportSellForeign + visaSellForeign;
            }

            const avgRoe = totalForeign > 0 ? (totalPkr / totalForeign) : (parseFloat(document.getElementById('flight_sale_roe')?.value) || 75);
            const balanceForeign = avgRoe > 0 ? (balancePkr / avgRoe) : 0;

            if (document.getElementById('total_amount')) document.getElementById('total_amount').value = totalPkr.toFixed(2);
            if (document.getElementById('balance')) document.getElementById('balance').value = balancePkr.toFixed(2);

            // Summary Bar
            if (document.getElementById('bar_pax')) document.getElementById('bar_pax').textContent = `${pax} (A:${adults}, C:${children}, I:${infants})`;
            if (document.getElementById('bar_pax_badge')) document.getElementById('bar_pax_badge').textContent = pax;
            if (document.getElementById('bar_adult')) document.getElementById('bar_adult').textContent = `PKR ${pkgTotalPkr.toFixed(2)}`;
            if (document.getElementById('bar_flight')) document.getElementById('bar_flight').textContent = (flightCurr === 'PKR') ? `PKR ${flightPkr.toFixed(2)}` : `${flightCurr} ${flightForeign.toFixed(2)} / PKR ${flightPkr.toFixed(2)}`;
            if (document.getElementById('bar_hotel')) document.getElementById('bar_hotel').textContent = (hotelCurrLabel === 'PKR') ? `PKR ${hotelSellPkr.toFixed(2)}` : `${hotelCurrLabel} ${hotelSellForeign.toFixed(2)} / PKR ${hotelSellPkr.toFixed(2)}`;
            if (document.getElementById('bar_transport')) document.getElementById('bar_transport').textContent = (transportCurrLabel === 'PKR') ? `PKR ${transportSellPkr.toFixed(2)}` : `${transportCurrLabel} ${transportSellForeign.toFixed(2)} / PKR ${transportSellPkr.toFixed(2)}`;
            if (document.getElementById('bar_visa')) document.getElementById('bar_visa').textContent = (visaCurrLabel === 'PKR') ? `PKR ${visaSellPkr.toFixed(2)}` : `${visaCurrLabel} ${visaSellForeign.toFixed(2)} / PKR ${visaSellPkr.toFixed(2)}`;
            if (document.getElementById('bar_total')) document.getElementById('bar_total').textContent = (activeCurrs.size === 0 && flightCurr === 'PKR') ? `PKR ${totalPkr.toFixed(2)}` : `${foreignUnit} ${totalForeign.toFixed(2)} / PKR ${totalPkr.toFixed(2)}`;
            if (document.getElementById('bar_balance')) document.getElementById('bar_balance').textContent = (activeCurrs.size === 0 && flightCurr === 'PKR') ? `PKR ${balancePkr.toFixed(2)}` : `${foreignUnit} ${balanceForeign.toFixed(2)} / PKR ${balancePkr.toFixed(2)}`;

            // Total Direct Costs PKR & Gross Profit
            const totalDirectCostPkr = hotelCostPkr + flightCostPkr + visaCostPkr + transportCostPkr;
            const grossProfitPkr = totalPkr - totalDirectCostPkr;

            // Total Direct Costs Foreign & Gross Profit
            const totalCostForeign = (flightCurr === foreignUnit ? flightCostForeign : 0)
                                   + (hotelCurrLabel === foreignUnit ? hotelCostForeign : 0)
                                   + (transportCurrLabel === foreignUnit ? transportCostForeign : 0)
                                   + (visaCurrLabel === foreignUnit ? visaCostForeign : 0);
            const grossProfitForeign = totalForeign - totalCostForeign;

            // Costing Dual Currency Cards - Foreign Currency Card
            if (document.getElementById('cost_foreign_curr_title')) document.getElementById('cost_foreign_curr_title').textContent = (activeCurrs.size === 1) ? Array.from(activeCurrs)[0] : (activeCurrs.size > 1 ? 'Multi-Curr' : flightCurr);
            if (document.getElementById('cost_sar_flight_cost')) document.getElementById('cost_sar_flight_cost').textContent = `${flightCostForeign.toFixed(2)} ${flightCurr}`;
            if (document.getElementById('cost_sar_flight')) document.getElementById('cost_sar_flight').textContent = `${flightForeign.toFixed(2)} ${flightCurr}`;
            if (document.getElementById('cost_sar_hotel_cost')) document.getElementById('cost_sar_hotel_cost').textContent = `${hotelCostForeign.toFixed(2)} ${hotelCurrLabel}`;
            if (document.getElementById('cost_sar_hotel')) document.getElementById('cost_sar_hotel').textContent = `${hotelSellForeign.toFixed(2)} ${hotelCurrLabel}`;
            if (document.getElementById('cost_sar_transport_cost')) document.getElementById('cost_sar_transport_cost').textContent = `${transportCostForeign.toFixed(2)} ${transportCurrLabel}`;
            if (document.getElementById('cost_sar_transport')) document.getElementById('cost_sar_transport').textContent = `${transportSellForeign.toFixed(2)} ${transportCurrLabel}`;
            if (document.getElementById('cost_sar_visa_cost')) document.getElementById('cost_sar_visa_cost').textContent = `${visaCostForeign.toFixed(2)} ${visaCurrLabel}`;
            if (document.getElementById('cost_sar_visa')) document.getElementById('cost_sar_visa').textContent = `${visaSellForeign.toFixed(2)} ${visaCurrLabel}`;
            if (document.getElementById('cost_sar_total_cost')) document.getElementById('cost_sar_total_cost').textContent = `${totalCostForeign.toFixed(2)} ${foreignUnit}`;
            if (document.getElementById('cost_sar_total')) document.getElementById('cost_sar_total').textContent = `${totalForeign.toFixed(2)} ${foreignUnit}`;
            if (document.getElementById('cost_sar_balance')) document.getElementById('cost_sar_balance').textContent = `${balanceForeign.toFixed(2)} ${foreignUnit}`;

            // Costing Dual Currency Cards - PKR Card
            if (document.getElementById('cost_pkr_flight_cost')) document.getElementById('cost_pkr_flight_cost').textContent = `${flightCostPkr.toFixed(2)} PKR`;
            if (document.getElementById('cost_pkr_flight')) document.getElementById('cost_pkr_flight').textContent = `${flightPkr.toFixed(2)} PKR`;
            if (document.getElementById('cost_pkr_hotel_cost')) document.getElementById('cost_pkr_hotel_cost').textContent = `${hotelCostPkr.toFixed(2)} PKR`;
            if (document.getElementById('cost_pkr_hotel')) document.getElementById('cost_pkr_hotel').textContent = `${hotelSellPkr.toFixed(2)} PKR`;
            if (document.getElementById('cost_pkr_transport_cost')) document.getElementById('cost_pkr_transport_cost').textContent = `${transportCostPkr.toFixed(2)} PKR`;
            if (document.getElementById('cost_pkr_transport')) document.getElementById('cost_pkr_transport').textContent = `${transportSellPkr.toFixed(2)} PKR`;
            if (document.getElementById('cost_pkr_visa_cost')) document.getElementById('cost_pkr_visa_cost').textContent = `${visaCostPkr.toFixed(2)} PKR`;
            if (document.getElementById('cost_pkr_visa')) document.getElementById('cost_pkr_visa').textContent = `${visaSellPkr.toFixed(2)} PKR`;
            if (document.getElementById('cost_pkr_total_cost')) document.getElementById('cost_pkr_total_cost').textContent = `${totalDirectCostPkr.toFixed(2)} PKR`;
            if (document.getElementById('cost_pkr_total')) document.getElementById('cost_pkr_total').textContent = `${totalPkr.toFixed(2)} PKR`;
            if (document.getElementById('cost_pkr_balance')) document.getElementById('cost_pkr_balance').textContent = `${balancePkr.toFixed(2)} PKR`;

            // Costing Table Breakdown - Cost Row
            if (document.getElementById('cost_sum_hotel')) document.getElementById('cost_sum_hotel').textContent = hotelCostPkr.toFixed(2);
            if (document.getElementById('cost_sum_visa')) document.getElementById('cost_sum_visa').textContent = visaCostPkr.toFixed(2);
            if (document.getElementById('cost_sum_flight')) document.getElementById('cost_sum_flight').textContent = flightCostPkr.toFixed(2);
            if (document.getElementById('cost_sum_transport')) document.getElementById('cost_sum_transport').textContent = transportCostPkr.toFixed(2);
            if (document.getElementById('cost_sum_total')) document.getElementById('cost_sum_total').textContent = totalDirectCostPkr.toFixed(2);

            // Costing Table Breakdown - Selling Row
            if (document.getElementById('sum_pax')) document.getElementById('sum_pax').textContent = pax;
            if (document.getElementById('sum_pkg')) document.getElementById('sum_pkg').textContent = pkgTotalPkr.toFixed(2);
            if (document.getElementById('sum_hotel')) document.getElementById('sum_hotel').textContent = hotelSellPkr.toFixed(2);
            if (document.getElementById('sum_visa')) document.getElementById('sum_visa').textContent = visaSellPkr.toFixed(2);
            if (document.getElementById('sum_flight')) document.getElementById('sum_flight').textContent = flightPkr.toFixed(2);
            if (document.getElementById('sum_transport')) document.getElementById('sum_transport').textContent = transportSellPkr.toFixed(2);
            if (document.getElementById('sum_other')) document.getElementById('sum_other').textContent = (other + additional).toFixed(2);
            if (document.getElementById('sum_discount')) document.getElementById('sum_discount').textContent = discount.toFixed(2);
            if (document.getElementById('sum_total')) document.getElementById('sum_total').textContent = totalPkr.toFixed(2);
        }

        // Global Event Delegation for live calculations
        document.addEventListener('input', function(e) {
            if (e.target.matches('.hotel-cost-rate')) {
                const row = e.target.closest('.hotel-block');
                autoDivideHotelRates(row, 'cost');
                calculateHotelRow(row, 'costRate');
            } else if (e.target.matches('.hotel-sell-rate')) {
                const row = e.target.closest('.hotel-block');
                autoDivideHotelRates(row, 'sell');
                calculateHotelRow(row, 'sellRate');
            } else if (e.target.matches('.hotel-rooms')) {
                const row = e.target.closest('.hotel-block');
                calculateHotelRow(row);
            } else if (e.target.matches('.hotel-nights, .hotel-roe, .hotel-cost-roe, .hotel-sale-roe')) {
                const row = e.target.closest('.hotel-block');
                calculateHotelRow(row);
            } else if (e.target.matches('.hotel-person-cost, .hotel-person-sell')) {
                const row = e.target.closest('.hotel-block');
                calculateHotelRow(row, 'personRate');
            }
            if (e.target.matches('.visa-roe, .visa-cost-roe, .visa-sale-roe, .visa-cost-rate, .visa-sell-rate')) {
                const row = e.target.closest('.visa-card');
                calculateVisaRow(row);
            }
            if (e.target.matches('.transport-roe, .transport-cost-roe, .transport-sale-roe, .transport-cost-rate, .transport-sell-rate')) {
                const row = e.target.closest('.route-block');
                calculateTransportRow(row);
            }
            if (e.target.matches('.flight-rate-calc, .flight-tier-calc, #flight_roe, #flight_cost_roe, #flight_sale_roe, .person-flight-pax-type')) {
                calculateFlight();
            }
            if (e.target.matches('.calc, #package_cost, #other_charges, #discount, #additional_services_amount, #total_received')) {
                calcTotal();
            }
            if (e.target.matches('input[name*="[check_in]"], input[name*="[check_out]"], input[name*="[no_of_nights]"]')) {
                handleHotelDateCalc(e.target);
            }
        });

        document.addEventListener('change', function(e) {
            if (e.target.matches('.hotel-cost-rate')) {
                const row = e.target.closest('.hotel-block');
                autoDivideHotelRates(row, 'cost');
                calculateHotelRow(row, 'costRate');
            } else if (e.target.matches('.hotel-sell-rate')) {
                const row = e.target.closest('.hotel-block');
                autoDivideHotelRates(row, 'sell');
                calculateHotelRow(row, 'sellRate');
            } else if (e.target.matches('.hotel-rooms')) {
                const row = e.target.closest('.hotel-block');
                calculateHotelRow(row);
            } else if (e.target.matches('.hotel-nights, .hotel-roe, .hotel-cost-roe, .hotel-sale-roe')) {
                const row = e.target.closest('.hotel-block');
                calculateHotelRow(row);
            } else if (e.target.matches('.hotel-person-cost, .hotel-person-sell')) {
                const row = e.target.closest('.hotel-block');
                calculateHotelRow(row, 'personRate');
            }
            if (e.target.matches('.visa-roe, .visa-cost-roe, .visa-sale-roe, .visa-cost-rate, .visa-sell-rate')) {
                const row = e.target.closest('.visa-card');
                calculateVisaRow(row);
            }
            if (e.target.matches('.transport-roe, .transport-cost-roe, .transport-sale-roe, .transport-cost-rate, .transport-sell-rate')) {
                const row = e.target.closest('.route-block');
                calculateTransportRow(row);
            }
            if (e.target.matches('.flight-rate-calc, .flight-tier-calc, #flight_roe, #flight_cost_roe, #flight_sale_roe, .person-flight-pax-type')) {
                calculateFlight();
            }
            if (e.target.matches('.calc, #package_cost, #other_charges, #discount, #additional_services_amount, #total_received')) {
                calcTotal();
            }
            if (e.target.matches('input[name*="[check_in]"], input[name*="[check_out]"], input[name*="[no_of_nights]"]')) {
                handleHotelDateCalc(e.target);
            }
        });

        // Quick Apply Adult Rate Handler
        document.addEventListener('click', function(e) {
            if (e.target && (e.target.id === 'btnQuickApplyAdults' || e.target.closest('#btnQuickApplyAdults'))) {
                e.preventDefault();
                const costVal = parseFloat(document.getElementById('quick_adult_cost')?.value) || 0;
                const sellVal = parseFloat(document.getElementById('quick_adult_sell')?.value) || 0;

                document.querySelectorAll('.flight-person-row').forEach(row => {
                    const type = row.querySelector('.person-flight-pax-type')?.value;
                    if (type === 'adult' || !type) {
                        const cInput = row.querySelector('.person-flight-cost');
                        if (cInput && costVal > 0) cInput.value = costVal.toFixed(2);
                        const sInput = row.querySelector('.person-flight-sell');
                        if (sInput && sellVal > 0) sInput.value = sellVal.toFixed(2);
                    }
                });
                calculateFlight();
            }
        });

        // Quick Apply Hotel Pax Handler
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-quick-hotel-apply');
            if (btn) {
                e.preventDefault();
                const hotelBlock = btn.closest('.hotel-block');
                if (!hotelBlock) return;
                const costVal = hotelBlock.querySelector('.quick-hotel-cost')?.value;
                const sellVal = hotelBlock.querySelector('.quick-hotel-sell')?.value;

                hotelBlock.querySelectorAll('.hotel-person-cost').forEach(cInput => {
                    if (costVal !== '' && !isNaN(parseFloat(costVal))) cInput.value = parseFloat(costVal).toFixed(2);
                });
                hotelBlock.querySelectorAll('.hotel-person-sell').forEach(sInput => {
                    if (sellVal !== '' && !isNaN(parseFloat(sellVal))) sInput.value = parseFloat(sellVal).toFixed(2);
                });
                calculateHotelRow(hotelBlock);
            }
        });

        // Initialize on page load
        updatePaxCountsFromPersons();
        syncFlightPersons();
        syncAllHotelPersons();
        document.querySelectorAll('.hotel-block').forEach(r => calculateHotelRow(r));
        document.querySelectorAll('.route-block').forEach(r => calculateTransportRow(r));
        document.querySelectorAll('.visa-card').forEach(r => calculateVisaRow(r));
        syncBookingHotelDates();
        updateHotelStaySummary();
        calculateFlight();
        calcTotal();

        // Two-way Date Synchronization Across Tabs
        const mainBookingDateEl = document.getElementById('booking_date');
        if (mainBookingDateEl) {
            mainBookingDateEl.addEventListener('change', function() {
                const val = this.value;
                if (!val) return;
                const flDateEl = document.getElementById('flight_booking_date');
                if (flDateEl) flDateEl.value = val;
                document.querySelectorAll('.hotel-booking-date').forEach(el => el.value = val);
                document.querySelectorAll('.transport-booking-date').forEach(el => el.value = val);
                document.querySelectorAll('.visa-booking-date').forEach(el => el.value = val);
            });
        }

        document.addEventListener('change', function(e) {
            if (e.target && (e.target.id === 'flight_booking_date' || e.target.name === 'flight_booking_date')) {
                const val = e.target.value;
                if (!val) return;
                if (mainBookingDateEl) mainBookingDateEl.value = val;
            }
            if (e.target && e.target.classList.contains('hotel-booking-date')) {
                const val = e.target.value;
                if (!val) return;
                if (mainBookingDateEl) mainBookingDateEl.value = val;
            }
            if (e.target && e.target.classList.contains('transport-booking-date')) {
                const val = e.target.value;
                if (!val) return;
                if (mainBookingDateEl) mainBookingDateEl.value = val;
            }
            if (e.target && e.target.classList.contains('visa-booking-date')) {
                const val = e.target.value;
                if (!val) return;
                if (mainBookingDateEl) mainBookingDateEl.value = val;
            }
        });

        $(document).ready(function() {
            if (typeof $ !== 'undefined' && $.fn.summernote) {
                $('a[href="#tab-additional-services"]').on('shown.bs.tab', function() {
                    if (!$('#additional_services_detail').next().hasClass('note-editor')) {
                        $('#additional_services_detail').summernote({
                            height: 200,
                            placeholder: 'Enter additional service details here...',
                            toolbar: [
                                ['style', ['style']],
                                ['font', ['bold', 'italic', 'underline', 'clear']],
                                ['fontname', ['fontname']],
                                ['color', ['color']],
                                ['para', ['ul', 'ol', 'paragraph']],
                                ['height', ['height']],
                                ['insert', ['link', 'picture', 'hr']],
                                ['view', ['fullscreen', 'codeview']],
                                ['help', ['help']]
                            ]
                        });
                    }
                });
                
                if ($('#tab-additional-services').hasClass('active')) {
                    $('#additional_services_detail').summernote({
                        height: 200,
                        placeholder: 'Enter additional service details here...',
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'italic', 'underline', 'clear']],
                            ['para', ['ul', 'ol', 'paragraph']],
                        ]
                    });
                }
            }
        });

        // Live Room Capacity Real-Time Checker
        function checkHotelRoomCapacity(input) {
            const row = input.closest('.hotel-row') || input.closest('.row') || input.closest('.hotel-block');
            if (!row) return;

            const roomNumber = input.value.trim();
            let fb = row.querySelector('.room-capacity-feedback');

            if (!roomNumber) {
                input.style.borderColor = '';
                if (fb) fb.innerHTML = '';
                return;
            }

            const hotelSelect = row.querySelector('select[name*="[hotel_name]"]') || row.querySelector('input[name*="[hotel_name]"]');
            const hotelName = hotelSelect ? (hotelSelect.options ? (hotelSelect.options[hotelSelect.selectedIndex]?.text || hotelSelect.value) : hotelSelect.value) : '';
            const locSelect = row.querySelector('select[name*="[location]"]') || row.querySelector('input[name*="[location]"]');
            const location = locSelect ? locSelect.value : '';
            const typeSelect = row.querySelector('select[name*="[room_type]"]');
            const roomType = typeSelect ? typeSelect.value : '';
            const checkInInput = row.querySelector('input[name*="[check_in]"]');
            const checkOutInput = row.querySelector('input[name*="[check_out]"]');
            const checkIn = checkInInput ? checkInInput.value : '';
            const checkOut = checkOutInput ? checkOutInput.value : '';

            if (!fb) {
                fb = document.createElement('div');
                fb.className = 'room-capacity-feedback mt-1';
                input.parentNode.appendChild(fb);
            }

            fb.innerHTML = '<span class="text-muted" style="font-size:10px;"><i class="mdi mdi-loading mdi-spin me-1"></i>Checking room...</span>';

            const params = new URLSearchParams({
                hotel_name: hotelName,
                room_number: roomNumber,
                location: location,
                room_type: roomType,
                check_in: checkIn,
                check_out: checkOut,
                booking_id: '"dummy"'
            });

            fetch(`"dummy"?${params.toString()}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        fb.innerHTML = '';
                        input.style.borderColor = '';
                        return;
                    }
                    if (data.is_overbooked || data.is_full) {
                        input.style.borderColor = '#dc3545';
                        fb.innerHTML = `<span class="badge bg-danger text-white d-block text-start p-1" style="font-size:10px; white-space:normal;"><i class="mdi mdi-alert-circle me-1"></i>${data.message}</span>`;
                    } else if (data.occupied > 0) {
                        input.style.borderColor = '#ffc107';
                        fb.innerHTML = `<span class="badge bg-warning text-dark d-block text-start p-1" style="font-size:10px; white-space:normal;"><i class="mdi mdi-information me-1"></i>${data.message}</span>`;
                    } else {
                        input.style.borderColor = '#198754';
                        fb.innerHTML = `<span class="badge bg-success text-white d-block text-start p-1" style="font-size:10px;"><i class="mdi mdi-check me-1"></i>Room ${data.room_number}: Free (${data.capacity} Beds)</span>`;
                    }
                })
                .catch(err => {
                    console.log('Room check error:', err);
                    if (fb) fb.innerHTML = '';
                });
        }

        document.addEventListener('input', function(e) {
            if (e.target && e.target.classList.contains('hotel-room-number-input')) {
                clearTimeout(e.target._roomCheckTimer);
                e.target._roomCheckTimer = setTimeout(() => checkHotelRoomCapacity(e.target), 400);
            }
        });
        document.addEventListener('change', function(e) {
            if (e.target && e.target.classList.contains('hotel-room-number-input')) {
                checkHotelRoomCapacity(e.target);
            }
        });

        document.getElementById('bookingForm')?.addEventListener('submit', function(e) {
            calculateFlight();
            calcTotal();

            const clientSelect = document.getElementById('clientSelect');
            if (clientSelect && !clientSelect.value) {
                e.preventDefault();
                alert('Please select a Client before updating the booking.');
                const tabBtn = document.querySelector('a[href="#tab-details"]');
                if (tabBtn) {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                        new bootstrap.Tab(tabBtn).show();
                    } else {
                        tabBtn.click();
                    }
                }
                clientSelect.focus();
                return false;
            }

            const paxInput = document.getElementById('no_of_pax');
            if (paxInput && (parseInt(paxInput.value) < 1 || isNaN(parseInt(paxInput.value)))) {
                e.preventDefault();
                alert('Please enter at least 1 passenger.');
                const tabBtn = document.querySelector('a[href="#tab-details"]');
                if (tabBtn) {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                        new bootstrap.Tab(tabBtn).show();
                    } else {
                        tabBtn.click();
                    }
                }
                paxInput.focus();
                return false;
            }
        });
    