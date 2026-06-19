// jQuery shim — resolves `import jQuery from 'jquery'` to the CDN instance.
// This ensures DataTables registers $.fn.DataTable on the SAME jQuery that
// pages use via window.$ / window.jQuery (loaded from CDN in <head>).
export default window.jQuery;
