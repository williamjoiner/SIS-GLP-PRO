// DataTable instance
let deliverersTable;

$(document).ready(function() {
    initializeSidebar();
    initializeDataTable();
    initializeFormMasks();
    initializeTooltips();
    initializeModalEvents();
});

function initializeSidebar() {
    const toggle = document.querySelector('.toggle');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    
    if (toggle) {
        toggle.onclick = function() {
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('active');
        }
    }
}

function initializeModalEvents() {
    // Clear form when modal is opened
    $('#addDelivererModal').on('show.bs.modal', function (e) {
        clearForm();
    });

    // Handle form submission
    $('#addDelivererForm').on('submit', function(e) {
        e.preventDefault();
        saveDeliverer();
    });
}

function initializeDataTable() {
    deliverersTable = $('#deliverersTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '/api/deliverers/list.php',
            dataSrc: 'data',
            error: function(xhr, error, thrown) {
                showAlert('Erro ao carregar dados: ' + error, 'danger');
            }
        },
        columns: [
            { data: 'id' },
            { data: 'name' },
            { 
                data: 'phone',
                render: function(data) {
                    return formatPhone(data);
                }
            },
            { data: 'vehicle_type' },
            { 
                data: 'license_plate',
                render: function(data) {
                    return data ? data.toUpperCase() : '';
                }
            },
            {
                data: 'status',
                render: function(data) {
                    const badge = data === 'active' ? 
                        '<span class="badge bg-success">Ativo</span>' : 
                        '<span class="badge bg-danger">Inativo</span>';
                    return `<div class="text-center">${badge}</div>`;
                }
            },
            {
                data: 'delivery_count',
                render: function(data) {
                    return `<div class="text-center"><span class="badge bg-info">${data || 0}</span></div>`;
                }
            },
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function(data) {
                    return `
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-primary" onclick="editDeliverer(${data.id})" 
                                    data-bs-toggle="tooltip" title="Editar">
                                <i class='bx bx-edit'></i>
                            </button>
                            <button class="btn btn-info" onclick="viewDeliveries(${data.id})" 
                                    data-bs-toggle="tooltip" title="Histórico">
                                <i class='bx bx-package'></i>
                            </button>
                            <button class="btn btn-danger" onclick="confirmDelete(${data.id})" 
                                    data-bs-toggle="tooltip" title="Excluir">
                                <i class='bx bx-trash'></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[1, 'asc']],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json'
        },
        responsive: true,
        pageLength: 10,
        dom: '<"row"<"col-md-6"l><"col-md-6"f>>rtip'
    });
}

function initializeFormMasks() {
    $('#delivererPhone').mask('(00) 00000-0000');
    $('#delivererLicensePlate').mask('AAA-0000', {
        translation: {
            'A': { pattern: /[A-Za-z]/ }
        }
    });
}

function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

function clearForm() {
    const form = document.getElementById('addDelivererForm');
    if (form) {
        form.reset();
        document.getElementById('delivererId').value = '';
        document.querySelector('#addDelivererModal .modal-title').textContent = 'Novo Entregador';
        
        // Clear validation states
        form.classList.remove('was-validated');
        Array.from(form.elements).forEach(element => {
            element.classList.remove('is-invalid');
            element.classList.remove('is-valid');
        });
    }
}

function editDeliverer(id) {
    fetch('/api/deliverers/read.php?id=' + id)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao carregar dados do entregador');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const deliverer = data.data;
                document.getElementById('delivererId').value = deliverer.id;
                document.getElementById('delivererName').value = deliverer.name;
                document.getElementById('delivererPhone').value = deliverer.phone;
                document.getElementById('delivererVehicleType').value = deliverer.vehicle_type;
                document.getElementById('delivererLicensePlate').value = deliverer.license_plate;
                document.getElementById('delivererStatus').value = deliverer.status;
                
                document.querySelector('#addDelivererModal .modal-title').textContent = 'Editar Entregador';
                new bootstrap.Modal(document.getElementById('addDelivererModal')).show();
            } else {
                throw new Error(data.message || 'Erro ao carregar dados do entregador');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert(error.message, 'danger');
        });
}

function saveDeliverer() {
    const form = document.getElementById('addDelivererForm');
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    const formData = new FormData(form);
    const id = formData.get('id');
    const url = id ? '/api/deliverers/update.php' : '/api/deliverers/create.php';

    // Disable submit button and show loading state
    const submitBtn = document.querySelector('#addDelivererModal .btn-primary');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Salvando...';

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('addDelivererModal')).hide();
            deliverersTable.ajax.reload();
            showAlert('Entregador ' + (id ? 'atualizado' : 'adicionado') + ' com sucesso!', 'success');
        } else {
            throw new Error(data.message || 'Erro ao salvar entregador');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert(error.message, 'danger');
    })
    .finally(() => {
        // Restore button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

function confirmDelete(id) {
    if (confirm('Tem certeza que deseja excluir este entregador?')) {
        deleteDeliverer(id);
    }
}

function deleteDeliverer(id) {
    fetch('/api/deliverers/delete.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + id
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            deliverersTable.ajax.reload();
            showAlert('Entregador excluído com sucesso!', 'success');
        } else {
            throw new Error(data.message || 'Erro ao excluir entregador');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert(error.message, 'danger');
    });
}

function viewDeliveries(id) {
    const modal = new bootstrap.Modal(document.getElementById('deliveriesModal'));
    
    fetch('/api/deliverers/deliveries.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const tbody = document.querySelector('#deliveriesTable tbody');
                tbody.innerHTML = '';
                
                if (data.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center">Nenhuma entrega encontrada</td></tr>';
                } else {
                    data.data.forEach(delivery => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${delivery.order_id}</td>
                                <td>${delivery.client_name}</td>
                                <td>${formatDate(delivery.delivery_date)}</td>
                                <td><span class="badge bg-${getStatusColor(delivery.status)}">${getStatusText(delivery.status)}</span></td>
                                <td>${formatMoney(delivery.total)}</td>
                            </tr>
                        `;
                    });
                }
                modal.show();
            } else {
                throw new Error(data.message || 'Erro ao carregar entregas');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert(error.message, 'danger');
        });
}

// Utility Functions
function formatPhone(phone) {
    if (!phone) return '';
    const cleaned = phone.replace(/\D/g, '');
    if (cleaned.length !== 11) return phone;
    return `(${cleaned.slice(0,2)}) ${cleaned.slice(2,7)}-${cleaned.slice(7)}`;
}

function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatMoney(value) {
    if (value === null || value === undefined) return 'R$ 0,00';
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
}

function getStatusColor(status) {
    const colors = {
        'pending': 'warning',
        'in_progress': 'info',
        'completed': 'success',
        'cancelled': 'danger'
    };
    return colors[status] || 'secondary';
}

function getStatusText(status) {
    const texts = {
        'pending': 'Pendente',
        'in_progress': 'Em Andamento',
        'completed': 'Concluída',
        'cancelled': 'Cancelada'
    };
    return texts[status] || status;
}

function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}
