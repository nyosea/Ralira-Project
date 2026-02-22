/**
 * Filename: search.js
 * Description: Algoritma pencarian real-time untuk memfilter tabel data.
 * Context: Digunakan di Dashboard Admin, User, dan Psikolog.
 */

document.addEventListener('DOMContentLoaded', () => {
    // Cari semua elemen input yang memiliki class 'search-input'
    // Pastikan di HTML input search diberi class="search-input" dan data-target="#idTabel"
    const searchInputs = document.querySelectorAll('.search-input');

    searchInputs.forEach(input => {
        input.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const targetTableId = this.getAttribute('data-target');
            const table = document.querySelector(targetTableId);

            if (table) {
                filterTable(table, searchTerm);
            }
        });
    });
});

/**
 * Fungsi Filter Baris Tabel
 * @param {HTMLElement} table - Elemen tabel yang akan difilter
 * @param {String} query - Kata kunci pencarian
 */
function filterTable(table, query) {
    // Ambil body tabel agar header tidak ikut tersembunyi
    const tbody = table.querySelector('tbody');
    const rows = tbody ? tbody.querySelectorAll('tr') : table.querySelectorAll('tr:not(thead tr)');

    rows.forEach(row => {
        // Ambil semua teks dalam baris tersebut
        const rowText = row.textContent.toLowerCase();
        
        // Logic: Jika text baris mengandung query, tampilkan. Jika tidak, sembunyikan.
        if (rowText.includes(query)) {
            row.style.display = ''; // Reset display (default table-row)
            row.style.animation = 'fadeIn 0.3s ease'; // Efek animasi halus
        } else {
            row.style.display = 'none';
        }
    });
    
    // Opsional: Tampilkan pesan "Tidak ditemukan" jika semua baris hidden
    // (Logic tambahan bisa dimasukkan di sini jika diperlukan)
}

// Tambahkan CSS Animation Keyframes via JS (agar self-contained)
const styleSheet = document.createElement("style");
styleSheet.innerText = `
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(5px); }
  to { opacity: 1; transform: translateY(0); }
}
`;
document.head.appendChild(styleSheet);