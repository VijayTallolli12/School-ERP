// jQuery shim (CommonJS) — resolves `require('jquery')` / AMD `define(['jquery'])`
// to the CDN instance. This ensures DataTables registers $.fn.DataTable on the
// SAME jQuery that pages use via window.$ / window.jQuery (loaded from CDN in <head>).
//
// Written as a .cjs module so Vite/Rollup hands the real jQuery function to CJS/AMD
// factories. An ESM shim (`export default window.jQuery`) was compiled into a module
// namespace object ({ default: jQuery }) and then wrapped by rollup's __toESM helper,
// so toastr received a callable shell WITHOUT $.extend / $.fn and threw
// "TypeError: extend is not a function" inside getOptions() on every toast.
module.exports = window.jQuery;
