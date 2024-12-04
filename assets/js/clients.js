let clientsTable;

$(document).ready(function() {
    // Initialize DataTable
    clientsTable = $('#clientsTable').DataTable({
        ajax: {
            url: '../../api/clients/list.php',
            dataSrc: 'data'
        },
        columns: [
            { data: 'id' },
            { data: 'name' },
            { data: 'email' },
            { data: 'phone' },
            { data: 'address' },
            {
                data: null,
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-sm btn-primary" onclick="editClient(${row.id})">
                            <i class='bx bx-edit'></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteClient(${row.id})">
                            <i class='bx bx-trash'></i>
                        </button>
                    `;
                }
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json'
        }
    });
});

function saveClient() {
    const formData = new FormData(document.getElementById('addClientForm'));
    
    $.ajax({
        url: '../../api/clients/create.php',
        type: 'POST',
        data: Object.fromEntries(formData),
        success: function(response) {
            if(response.success) {
                $('#addClientModal').modal('hide');
                $('#addClientForm').trigger('reset');
                clientsTable.ajax.reload();
                showAlert('Cliente adicionado com sucesso!', 'success');
            } else {
                showAlert('Erro ao adicionar cliente: ' + response.message, 'danger');
            }
        },
        error: function() {
            showAlert('Erro ao adicionar cliente. Tente novamente.', 'danger');
        }
    });
}

function editClient(id) {
    $.ajax({
        url: '../../api/clients/read.php',
        type: 'GET',
        data: { id: id },
        success: function(response) {
            if(response.success) {
                const client = response.data;
                $('#editId').val(client.id);
                $('#editName').val(client.name);
                $('#editEmail').val(client.email);
                $('#editPhone').val(client.phone);
                $('#editAddress').val(client.address);
                $('#editClientModal').modal('show');
            } else {
                showAlert('Erro ao carregar dados do cliente: ' + response.message, 'danger');
            }
        },
        error: function() {
            showAlert('Erro ao carregar dados do cliente. Tente novamente.', 'danger');
        }
    });
}

function updateClient() {
    const formData = new FormData(document.getElementById('editClientForm'));
    
    $.ajax({
        url: '../../api/clients/update.php',
        type: 'POST',
        data: Object.fromEntries(formData),
        success: function(response) {
            if(response.success) {
                $('#editClientModal').modal('hide');
                clientsTable.ajax.reload();
                showAlert('Cliente atualizado com sucesso!', 'success');
            } else {
                showAlert('Erro ao atualizar cliente: ' + response.message, 'danger');
            }
        },
        error: function() {
            showAlert('Erro ao atualizar cliente. Tente novamente.', 'danger');
        }
    });
}

function deleteClient(id) {
    if(confirm('Tem certeza que deseja excluir este cliente?')) {
        $.ajax({
            url: '../../api/clients/delete.php',
            type: 'POST',
            data: { id: id },
            success: function(response) {
                if(response.success) {
                    clientsTable.ajax.reload();
                    showAlert('Cliente excluído com sucesso!', 'success');
                } else {
                    showAlert('Erro ao excluir cliente: ' + response.message, 'danger');
                }
            },
            error: function() {
                showAlert('Erro ao excluir cliente. Tente novamente.', 'danger');
            }
        });
    }
}

function showAlert(message, type) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('#alertMessage').html(alertHtml);
    setTimeout(() => {
        $('.alert').alert('close');
    }, 5000);
}
