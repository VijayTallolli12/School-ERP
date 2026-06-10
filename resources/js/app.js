import './bootstrap';

import $ from 'jquery';
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;
import DataTable from 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';
import 'admin-lte/dist/js/adminlte.min.js';
import toastr from 'toastr';
import Swal from 'sweetalert2';
import Chart from 'chart.js/auto';
import select2 from 'select2';

window.$ = window.jQuery = $;
window.DataTable = DataTable;
window.toastr = toastr;
window.Swal = Swal;
window.Chart = Chart;
select2($);

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

// Default DataTables language for empty states across all modules
if (typeof $.fn.DataTable !== 'undefined') {
    $.extend(true, $.fn.DataTable.defaults, {
        language: {
            emptyTable: 'No records available.',
            zeroRecords: 'No matching records found.',
            infoEmpty: 'Showing 0 entries',
            loadingRecords: 'Loading...',
            processing: '<div class="spinner-border spinner-border-sm text-primary me-1" role="status"></div> Processing...',
        },
    });
}

window.App = {
    /**
     * Show a toast notification.
     * Supports both:
     *   App.toast('success', 'Saved.')
     *   App.toast.success('Saved.')
     *   App.toast.error('Failed.')
     */
    toast(type, message) {
        // Allow App.toast.error('msg') pattern — type is actually the message
        if (typeof type === 'string' && message === undefined) {
            message = type;
            type = 'info';
        }
        toastr[type]?.(message ?? 'Done') ?? toastr.info(message ?? 'Done');
    },

    /**
     * Reload a DataTable instance, preserving the current page.
     * @param {DataTable.Api|string} table - DataTable API instance or jQuery selector
     * @param {boolean} resetPaging - Whether to reset to page 1 (default: false)
     */
    reloadDataTable(table, resetPaging = false) {
        const dt = typeof table === 'string' ? $(table).DataTable() : table;
        dt?.ajax?.reload(null, resetPaging);
    },

    /**
     * Clear validation errors from a form.
     */
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
            const input = $form.find(`[name="${field}"], [name="${field}[]"], [name="${bracketField}"]`).first();
            input.addClass('is-invalid');
            input.after(`<div class="invalid-feedback dynamic">${messages[0]}</div>`);
        });
    },

    submitAjaxForm(form) {
        const $form = $(form);
        const button = $form.find('[type="submit"]').first();
        const original = button.html();

        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving');

        $.ajax({
            url: $form.attr('action'),
            method: $form.attr('method') || 'POST',
            data: new FormData(form),
            contentType: false,
            processData: false,
            success(response) {
                App.toast('success', response.message || 'Saved successfully.');

                if (response.redirect) {
                    window.location.href = response.redirect;
                    return;
                }

                if (response.reload) {
                    window.location.reload();
                    return;
                }

                $form.trigger('erp:success', response);
            },
            error(xhr) {
                if (xhr.status === 422) {
                    App.handleValidation($form, xhr.responseJSON?.errors);
                    App.toast('error', 'Please correct the highlighted errors below.');
                    return;
                }

                App.toast('error', xhr.responseJSON?.message || 'Something went wrong.');
            },
            complete() {
                button.prop('disabled', false).html(original);
            },
        });
    },

    confirmDelete(options) {
        Swal.fire({
            title: options.title || 'Delete record?',
            text: options.text || 'This action can be restored only if soft deletes are available.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            confirmButtonColor: '#dc3545',
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

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
        });
    },

    /**
     * Skeleton loading helpers.
     * Usage:
     *   App.skeleton.show('#card-body');
     *   // ... fetch data ...
     *   App.skeleton.hide('#card-body');
     */
    skeleton: {
        /**
         * Replace container contents with skeleton placeholders.
         * @param {string|Element} container - CSS selector or DOM element
         * @param {number} rows - Number of skeleton rows to show (default: 5)
         */
        show(container, rows = 5) {
            const $el = $(container);
            if (!$el.length) return;

            // Save original content for restoration
            $el.data('erp-skeleton-original', $el.html());

            let html = '';
            for (let i = 0; i < rows; i++) {
                const width = [90, 75, 82, 68, 95][i % 5];
                html += `<div class="erp-skeleton-row mb-2" style="width:${width}%;height:1rem;border-radius:.375rem;"></div>`;
            }
            $el.html(html);
        },

        /**
         * Restore container content from skeleton state.
         */
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

    /**
     * Initialize Bootstrap tooltips on a parent element.
     */
    initTooltips(parent = document) {
        const root = parent || document;
        root.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
            new bootstrap.Tooltip(el);
        });
    },

    /**
     * Format a number as Indian currency (₹).
     * @param {number} amount
     * @param {number} decimals - Default: 2
     * @returns {string}
     */
    formatCurrency(amount, decimals = 2) {
        const num = parseFloat(amount);
        if (isNaN(num)) return '₹0.00';
        return '₹' + num.toLocaleString('en-IN', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
        });
    },

    /**
     * Debounce a function call.
     * @param {Function} fn
     * @param {number} delay - Milliseconds (default: 300)
     * @returns {Function}
     */
    debounce(fn, delay = 300) {
        let timer;
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    },

    /**
     * Show a loading spinner inside a container element.
     */
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

// Register toast shorthand methods (App.toast.success, App.toast.error, etc.)
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

// Initialize tooltips globally on page load
$(function () {
    App.initTooltips();
});

document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('erp-theme') || 'light');
