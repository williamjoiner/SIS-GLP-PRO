let productsTable;

$(document).ready(function() {
    // Initialize DataTable
    productsTable = $('#productsTable').DataTable({
        ajax: {
            url: '../../api/products/list.php',
            dataSrc: 'data'
        },
        columns: [
            { data: 'id' },
            { data: 'name' },
            { data: 'description' },
            { 
                data: 'price',
                render: function(data) {
                    return `R$ ${parseFloat(data).toFixed(2)}`;
                }
            },
            { data: 'stock' },
            {
                data: 'stock',
                render: function(data) {
                    return data < 10 ? 
                        '<span class="badge bg-danger">Baixo</span>' : 
                        '<span class="badge bg-success">OK</span>';
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-sm btn-primary" onclick="editProduct(${row.id})">
                            <i class='bx bx-edit'></i>
                        </button>
                        <button class="btn btn-sm btn-info" onclick="showStockModal(${row.id})">
                            <i class='bx bx-package'></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteProduct(${row.id})">
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

    // Format price inputs
    $('.modal').on('shown.bs.modal', function() {
        $(this).find('input[type="number"]').on('input', function() {
            if($(this).attr('step') === '0.01') {
                $(this).val(parseFloat($(this).val()).toFixed(2));
            }
        });
    });
});

function saveProduct() {
    const formData = new FormData(document.getElementById('addProductForm'));
    
    $.ajax({
        url: '../../api/products/create.php',
        type: 'POST',
        data: Object.fromEntries(formData),
        success: function(response) {
            if(response.success) {
                $('#addProductModal').modal('hide');
                $('#addProductForm').trigger('reset');
                productsTable.ajax.reload();
                showAlert('Produto adicionado com sucesso!', 'success');
            } else {
                showAlert('Erro ao adicionar produto: ' + response.message, 'danger');
            }
        },
        error: function() {
            showAlert('Erro ao adicionar produto. Tente novamente.', 'danger');
        }
    });
}

function editProduct(id) {
    $.ajax({
        url: '../../api/products/read.php',
        type: 'GET',
        data: { id: id },
        success: function(response) {
            if(response.success) {
                const product = response.data;
                $('#editId').val(product.id);
                $('#editName').val(product.name);
                $('#editDescription').val(product.description);
                $('#editPrice').val(parseFloat(product.price).toFixed(2));
                $('#editStock').val(product.stock);
                $('#editProductModal').modal('show');
            } else {
                showAlert('Erro ao carregar dados do produto: ' + response.message, 'danger');
            }
        },
        error: function() {
            showAlert('Erro ao carregar dados do produto. Tente novamente.', 'danger');
        }
    });
}

function updateProduct() {
    const formData = new FormData(document.getElementById('editProductForm'));
    
    $.ajax({
        url: '../../api/products/update.php',
        type: 'POST',
        data: Object.fromEntries(formData),
        success: function(response) {
            if(response.success) {
                $('#editProductModal').modal('hide');
                productsTable.ajax.reload();
                showAlert('Produto atualizado com sucesso!', 'success');
            } else {
                showAlert('Erro ao atualizar produto: ' + response.message, 'danger');
            }
        },
        error: function() {
            showAlert('Erro ao atualizar produto. Tente novamente.', 'danger');
        }
    });
}

function showStockModal(id) {
    $('#stockProductId').val(id);
    $('#stockForm').trigger('reset');
    $('#stockModal').modal('show');
}

function adjustStock() {
    const formData = new FormData(document.getElementById('stockForm'));
    
    $.ajax({
        url: '../../api/products/adjust_stock.php',
        type: 'POST',
        data: Object.fromEntries(formData),
        success: function(response) {
            if(response.success) {
                $('#stockModal').modal('hide');
                productsTable.ajax.reload();
                showAlert('Estoque ajustado com sucesso!', 'success');
            } else {
                showAlert('Erro ao ajustar estoque: ' + response.message, 'danger');
            }
        },
        error: function() {
            showAlert('Erro ao ajustar estoque. Tente novamente.', 'danger');
        }
    });
}

function deleteProduct(id) {
    if(confirm('Tem certeza que deseja excluir este produto?')) {
        $.ajax({
            url: '../../api/products/delete.php',
            type: 'POST',
            data: { id: id },
            success: function(response) {
                if(response.success) {
                    productsTable.ajax.reload();
                    showAlert('Produto excluído com sucesso!', 'success');
                } else {
                    showAlert('Erro ao excluir produto: ' + response.message, 'danger');
                }
            },
            error: function() {
                showAlert('Erro ao excluir produto. Tente novamente.', 'danger');
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
