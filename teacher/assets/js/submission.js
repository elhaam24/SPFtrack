// Submission page JavaScript functionality

// Filter and search functionality
document.addEventListener('DOMContentLoaded', function() {
    const assignmentSelect = document.getElementById('assignmentSelect');
    const statusSelect = document.getElementById('statusSelect');
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('tbody');
    const rows = tableBody.querySelectorAll('tr');

    function filterRows() {
        const assignmentFilter = assignmentSelect.value;
        const statusFilter = statusSelect.value;
        const searchTerm = searchInput.value.toLowerCase();

        rows.forEach(row => {
            if (row.querySelector('td[colspan]')) return; // Skip "no submissions" row

            const assignmentId = row.getAttribute('data-assignment');
            const status = row.getAttribute('data-status');
            const studentInfo = row.getAttribute('data-student');

            let show = true;

            if (assignmentFilter !== 'all' && assignmentId !== assignmentFilter) {
                show = false;
            }

            if (statusFilter !== 'all' && status !== statusFilter) {
                show = false;
            }

            if (searchTerm && !studentInfo.includes(searchTerm)) {
                show = false;
            }

            row.style.display = show ? '' : 'none';
        });
    }

    assignmentSelect.addEventListener('change', filterRows);
    statusSelect.addEventListener('change', filterRows);
    searchInput.addEventListener('input', filterRows);
});

// Toggle all checkboxes
function toggleAll(checkbox) {
    const checkboxes = document.querySelectorAll('.submission-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
}

// Bulk mark as graded
function bulkMarkGraded() {
    const checkboxes = document.querySelectorAll('.submission-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('Please select at least one submission to mark as graded.');
        return;
    }

    const submissionIds = Array.from(checkboxes).map(cb => cb.value);
    
    if (confirm(`Are you sure you want to mark ${submissionIds.length} submission(s) as graded?`)) {
        // Send AJAX request to mark as graded
        fetch('mark_graded.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ submission_ids: submissionIds })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Submissions marked as graded successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating submissions.');
        });
    }
}

// Export CSV
function exportCSV() {
    const table = document.getElementById('submissionTable');
    const rows = table.querySelectorAll('tr');
    let csv = [];

    // Add headers
    const headers = [];
    const headerRow = rows[0].querySelectorAll('th');
    for (let i = 1; i < headerRow.length - 1; i++) { // Skip checkbox and actions
        headers.push(headerRow[i].textContent.trim());
    }
    csv.push(headers.join(','));

    // Add data rows
    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        if (row.style.display === 'none') continue; // Skip hidden rows
        
        const cols = row.querySelectorAll('td');
        if (cols.length <= 1) continue; // Skip empty rows
        
        const rowData = [];
        for (let j = 1; j < cols.length - 1; j++) { // Skip checkbox and actions
            let text = cols[j].textContent.trim();
            text = text.replace(/"/g, '""'); // Escape quotes
            rowData.push('"' + text + '"');
        }
        csv.push(rowData.join(','));
    }

    // Create download link
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', 'submissions_' + new Date().toISOString().split('T')[0] + '.csv');
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
