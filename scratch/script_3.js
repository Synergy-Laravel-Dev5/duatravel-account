
            document.addEventListener("DOMContentLoaded", function() {
                if (typeof feather !== "undefined") {
                    feather.replace();
                }

                if (typeof jQuery !== "undefined" && jQuery.fn.select2) {
                    function initAppSelect2() {
                        $('select.select2').each(function() {
                            if (!$(this).hasClass("select2-hidden-accessible")) {
                                $(this).select2({
                                    theme: 'bootstrap-5',
                                    width: '100%',
                                    placeholder: $(this).attr('placeholder') || 'Search & Select...'
                                });
                            }
                        });
                    }

                    initAppSelect2();

                    $(document).on('shown.bs.tab shown.bs.modal shown.bs.collapse', function() {
                        initAppSelect2();
                    });
                }

                $(document).on('input', 'input.person-flight-ticket, input[name*="ticket"], input[id*="ticket"]', function() {
                    if (this.type === 'text' || this.type === 'number') {
                        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 13);
                    }
                });

                $('#sidebar-menu a[href]').each(function() {
                    var href = $(this).attr('href');
                    if (href && href !== '#' && !href.startsWith('javascript') && !$(this).attr('data-bs-toggle')) {
                        $(this).attr('target', '_blank');
                    }
                });

                document.addEventListener('wheel', function(e) {
                    if (document.activeElement && document.activeElement.type === 'number') {
                        document.activeElement.blur();
                    }
                }, { passive: true });
            });
        