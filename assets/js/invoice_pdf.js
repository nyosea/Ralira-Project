/**
 * Filename: assets/js/invoice_pdf.js
 * Description: Enhanced PDF download functionality using browser print to PDF
 */

// Get the base path dynamically
function getBasePath() {
    // Try to get the path from the current URL
    const currentPath = window.location.pathname;
    const pathSegments = currentPath.split('/');
    
    // Find the project root (assuming we're in pages/client/)
    const clientIndex = pathSegments.indexOf('pages');
    if (clientIndex > 0) {
        return pathSegments.slice(0, clientIndex).join('/') + '/';
    }
    
    // Fallback to relative path
    return '../../';
}

// Alternative PDF download using window.print()
function downloadInvoiceAsPDF(invoiceId) {
    // Create a new window for the invoice
    const baseUrl = getBasePath();
    const url = baseUrl + 'generate_invoice_pdf.php?id=' + invoiceId;
    
    // Open in new window
    const printWindow = window.open(url, '_blank');
    
    // Wait for the window to load, then trigger print dialog
    printWindow.addEventListener('load', function() {
        setTimeout(() => {
            printWindow.print();
        }, 1000);
    });
}

// Enhanced download with loading indicator
function downloadInvoiceWithLoading(invoiceId) {
    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyiapkan PDF...';
    button.disabled = true;
    
    // Get base URL
    const baseUrl = getBasePath();
    const url = baseUrl + 'generate_invoice_pdf.php?id=' + invoiceId;
    
    // Open invoice in new window
    const newWindow = window.open(url, '_blank');
    
    // Check if window opened successfully
    if (!newWindow || newWindow.closed || typeof newWindow.closed === 'undefined') {
        // Fallback: direct download
        window.location.href = url;
    }
    
    // Restore button after a delay
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    }, 2000);
}

// Auto-download function (saves directly as PDF)
function autoDownloadInvoice(invoiceId) {
    // Create hidden iframe for download
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    const baseUrl = getBasePath();
    iframe.src = baseUrl + 'generate_invoice_pdf.php?id=' + invoiceId + '&download=1';
    document.body.appendChild(iframe);
    
    // Remove iframe after download
    setTimeout(() => {
        document.body.removeChild(iframe);
    }, 5000);
}
