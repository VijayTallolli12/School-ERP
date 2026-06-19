import './bootstrap';

import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;
import 'admin-lte/dist/js/adminlte.min.js';
import toastr from 'toastr';
const $ = window.jQuery;
window.toastr = toastr;

toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: 'toast-top-right',
    timeOut: 4500,
};

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
        Accept: 'application/json',
    },
});

// ─── Lazy-load helpers ───────────────────────────────────────────────────────
// These dynamically import heavy libraries only when a page actually needs them.
// Each helper caches its promise so multiple callers share one network request.

let _dtPromise = null;
window.lazyDT = function () {
    if (!_dtPromise) {
        _dtPromise = (async () => {
            if (!window.jQuery) {
                throw new Error('jQuery not loaded — lazyDT() called before CDN jQuery script executed');
            }
            const { default: DataTable } = await import('datatables.net-bs5');
            await import('datatables.net-responsive-bs5');
            $.extend(true, DataTable.defaults, {
                responsive: true,
                language: {
                    emptyTable: 'No records available.',
                    zeroRecords: 'No matching records found.',
                    infoEmpty: 'Showing 0 entries',
                    loadingRecords: 'Loading...',
                    processing:
                        '<div class="spinner-border spinner-border-sm text-primary me-1" role="status"></div> Processing...',
                },
            });
            if (!$.fn.DataTable) {
                throw new Error('DataTables plugin not registered on jQuery after import');
            }
            return DataTable;
        })();
    }
    return _dtPromise;
};

let _chartPromise = null;
window.lazyChart = function () {
    if (!_chartPromise) {
        _chartPromise = import('chart.js/auto').then((m) => m.default);
    }
    return _chartPromise;
};

let _swalPromise = null;
window.lazySwal = function () {
    if (!_swalPromise) {
        _swalPromise = import('sweetalert2').then((m) => m.default);
    }
    return _swalPromise;
};

// ─── App namespace ───────────────────────────────────────────────────────────

window.App = {
    toast(type, message) {
        if (typeof type === 'string' && message === undefined) {
            message = type;
            type = 'info';
        }
        toastr[type]?.(message ?? 'Done') ?? toastr.info(message ?? 'Done');
    },

    reloadDataTable(table, resetPaging = false) {
        const dt = typeof table === 'string' ? $(table).DataTable() : table;
        dt?.ajax?.reload(null, resetPaging);
    },

    clearValidation(form) {
        const $form = $(form);
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback.dynamic').remove();
    },

    handleValidation(form, errors) {
        this.clearValidation(form);
        const $form = $(form);

        Object.entries(errors || {}).forEach(([field, messages]) => {
            const bracketField = field.replace(/\.(\w+)/g, '[$1]');
            const input = $form
                .find(`[name="${field}"], [name="${field}[]"], [name="${bracketField}"]`)
                .first();
            input.addClass('is-invalid');
            input.after(`<div class="invalid-feedback dynamic">${messages[0]}</div>`);
        });
    },

    submitAjaxForm(form) {
        const $form = $(form);
        const button = $form.find('[type="submit"]').first();
        const original = button.html();

        if ($form.data('erp-submitting')) return;
        $form.data('erp-submitting', true);

        button
            .prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm me-1"></span> Saving');

        // Safety net — restore button & release lock after 30 s no matter what
        const safety = setTimeout(() => {
            button.prop('disabled', false).html(original);
            $form.removeData('erp-submitting');
        }, 30000);

        $.ajax({
            url: $form.attr('action'),
            method: $form.attr('method') || 'POST',
            data: new FormData(form),
            contentType: false,
            processData: false,
            success(response) {
                clearTimeout(safety);
                App.toast('success', response.message || 'Saved successfully.');

                if (response.redirect) {
                    window.location.href = response.redirect;
                    return;
                }

                if (response.reload) {
                    window.location.reload();
                    return;
                }

                try {
                    $form.trigger('erp:success', response);
                } catch (e) {
                    console.error('[submitAjaxForm] erp:success handler threw:', e);
                }
            },
            error(xhr) {
                clearTimeout(safety);
                if (xhr.status === 422) {
                    App.handleValidation($form, xhr.responseJSON?.errors);
                    App.toast('error', 'Please correct the highlighted errors below.');
                    return;
                }

                App.toast('error', xhr.responseJSON?.message || 'Something went wrong.');
            },
            complete() {
                clearTimeout(safety);
                button.prop('disabled', false).html(original);
                $form.removeData('erp-submitting');
            },
        });
    },

    async confirmDelete(options) {
        const Swal = await window.lazySwal();
        const result = await Swal.fire({
            title: options.title || 'Delete record?',
            text: options.text || 'This action can be restored only if soft deletes are available.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            confirmButtonColor: '#dc3545',
        });

        if (!result.isConfirmed) return;

        $.ajax({
            url: options.url,
            method: 'DELETE',
            success(response) {
                App.toast('success', response.message || 'Deleted successfully.');
                options.onSuccess?.(response);
            },
            error(xhr) {
                App.toast('error', xhr.responseJSON?.message || 'Delete failed.');
            },
        });
    },

    skeleton: {
        show(container, rows = 5) {
            const $el = $(container);
            if (!$el.length) return;
            $el.data('erp-skeleton-original', $el.html());
            let html = '';
            for (let i = 0; i < rows; i++) {
                const width = [90, 75, 82, 68, 95][i % 5];
                html += `<div class="erp-skeleton-row mb-2" style="width:${width}%;height:1rem;border-radius:.375rem;"></div>`;
            }
            $el.html(html);
        },
        hide(container) {
            const $el = $(container);
            if (!$el.length) return;
            const original = $el.data('erp-skeleton-original');
            if (original !== undefined) {
                $el.html(original);
                $el.removeData('erp-skeleton-original');
            }
        },
    },

    initSearchableSelects(container = document) {
        const $container = container instanceof $ ? container : $(container);
        $container.find('select.searchable-select').each(function () {
            const $select = $(this);

            // Skip if already initialized
            if ($select.data('select2')) return;

            const isAjax = $select.data('ajax-url');
            const minInput = $select.data('minimum-input') || 2;
            const placeholder = $select.data('placeholder') || $select.find('option:first').text() || 'Search...';
            const allowClear = $select.data('allow-clear') !== false;

            const config = {
                placeholder,
                allowClear,
                width: '100%',
                language: {
                    inputTooShort: () => `Enter ${minInput}+ characters`,
                    searching: () => 'Searching...',
                    noResults: () => 'No results found',
                },
            };

            // If inside a modal, render dropdown inside the modal to fix z-index
            const $modal = $select.closest('.modal');
            if ($modal.length) {
                config.dropdownParent = $modal;
            }

            if (isAjax) {
                config.minimumInputLength = minInput;
                config.ajax = {
                    url: isAjax,
                    dataType: 'json',
                    delay: 400,
                    data(params) {
                        return { q: params.term || '' };
                    },
                    processResults(data) {
                        return {
                            results: (data.results || data.data || []).map(item => ({
                                id: item.id,
                                text: item.text,
                            })),
                        };
                    },
                    cache: true,
                };
                // Allow the server to handle all matching
                config.matcher = () => true;
            }

            $select.select2(config);
        });
    },

    initTooltips(parent = document) {
        const root = parent || document;
        root.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
            new bootstrap.Tooltip(el);
        });
    },

    formatCurrency(amount, decimals = 2) {
        const num = parseFloat(amount);
        if (isNaN(num)) return '₹0.00';
        return (
            '₹' +
            num.toLocaleString('en-IN', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals,
            })
        );
    },

    refreshSelect2(selector) {
        const $el = $(selector);
        if ($el.data('select2')) {
            $el.select2('destroy');
        }
        $el.find('option').remove();
        this.initSearchableSelects($el.parent());
    },

    refreshSelect2Options(selector, options, keepPlaceholder = true) {
        const $el = $(selector);
        const selVal = $el.val();
        if ($el.data('select2')) {
            $el.select2('destroy');
        }
        $el.find('option').remove();
        if (keepPlaceholder) {
            $el.append('<option value="">' + ($el.data('placeholder') || 'Select...') + '</option>');
        }
        options.forEach(function (opt) {
            $el.append('<option value="' + opt.id + '">' + opt.text + '</option>');
        });
        $el.val(selVal);
        this.initSearchableSelects($el.parent());
    },

    debounce(fn, delay = 300) {
        let timer;
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    },

    showLoader(container, size = 'md') {
        const $el = $(container);
        if (!$el.length) return;
        const sizeMap = { sm: 'spinner-border-sm', md: '', lg: 'spinner-border-lg' };
        $el.html(`
            <div class="d-flex justify-content-center align-items-center py-5">
                <div class="spinner-border ${sizeMap[size] || ''} text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);
    },
};

// ─── Tab persistence (URL hash + localStorage) ──────────────────────────

window.initTabPersistence = function (containerSelector) {
    const $container = $(containerSelector);
    if (!$container.length) return;

    const storageKey = 'activeTab_' + window.location.pathname;

    // Restore: URL hash > localStorage > default (first tab)
    let targetId = window.location.hash.replace('#', '');
    if (!targetId) {
        targetId = localStorage.getItem(storageKey);
    }
    if (targetId) {
        const $tab = $container.find('[data-bs-target="#' + targetId + '"]');
        if ($tab.length) {
            // Defer so Bootstrap has finished initializing the tab markup
            setTimeout(function () {
                bootstrap.Tab.getOrCreateInstance($tab[0]).show();
            }, 50);
        }
    }

    // Persist on every tab change
    $container.on('shown.bs.tab', function (e) {
        const target = $(e.target).attr('data-bs-target');
        if (target) {
            const id = target.replace('#', '');
            window.location.hash = id;
            try {
                localStorage.setItem(storageKey, id);
            } catch (_) {}
        }
    });
};

// Register toast shorthand methods
['success', 'error', 'warning', 'info'].forEach((level) => {
    App.toast[level] = (msg) => toastr[level](msg ?? 'Done');
});

$(document).on('submit', 'form.ajax-form', function (event) {
    event.preventDefault();
    App.submitAjaxForm(this);
});

$(document).on('click', '[data-bs-theme-value]', function () {
    const theme = $(this).data('bs-theme-value');
    document.documentElement.setAttribute('data-bs-theme', theme);
    localStorage.setItem('erp-theme', theme);
});

$(function () {
    App.initTooltips();
    App.initSearchableSelects();
});

document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('erp-theme') || 'light');
