/* Dashboard — dropdown tanggal/ekspor. URL ekspor dari window.BT_DASHBOARD. */
function toggleDatePickerMenu() {
    const menu = document.getElementById('datePickerMenu');
    if (menu) menu.classList.toggle('hidden');
}

function toggleExportMenu() {
    const menu = document.getElementById('exportMenu');
    if (menu) menu.classList.toggle('hidden');
}

document.addEventListener('click', (e) => {
    const exportContainer = document.getElementById('exportDropdownContainer');
    const exportMenu = document.getElementById('exportMenu');
    if (exportContainer && exportMenu && !exportContainer.contains(e.target)) {
        exportMenu.classList.add('hidden');
    }

    const dateContainer = document.getElementById('datePickerDropdownContainer');
    const dateMenu = document.getElementById('datePickerMenu');
    if (dateContainer && dateMenu && !dateContainer.contains(e.target)) {
        dateMenu.classList.add('hidden');
    }
});

function handleExportReport(type) {
    const menu = document.getElementById('exportMenu');
    if (menu) menu.classList.add('hidden');
    if (type === 'pdf') {
        window.print();
    } else {
        window.location.href = BT_DASHBOARD.exportUrl;
    }
}
