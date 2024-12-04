let ordersTable;

$(document).ready(function() {
    // Inicializar Select2 com busca avançada para clientes
    $('select[name="customer_id"]').select2({
        width: '100%',
        language: 'pt-BR',
        ajax: {
            url: '/api/clients/search.php',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    term: params.term
                };
            },
            processResults: function(data) {
                return {
                    results: data.data.map(function(client) {
                        let text = client.name;
                        if (client.phones) {
                            text += ' - Tel: ' + client.phones.split(',')[0];
                        }
                        if (client.addresses) {
                            text += ' - End: ' + client.addresses.split(',')[0];
                        }
                        return {
                            id: client.id,
                            text: text
                        };
                    })
                };
            },
            cache: true
        },
        minimumInputLength: 2,
        placeholder: 'Digite o nome, telefone ou endereço do cliente'
    });

    // Inicializar Select2 com busca para produtos
    $('.select2-products').select2({
        width: '100%',
        language: 'pt-BR',
        placeholder: 'Digite o nome do produto',
        minimumInputLength: 2,
        ajax: {
            url: '/api/products/list.php',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    search: params.term
                };
            },
            processResults: function(data) {
                return {
                    results: data.map(function(product) {
                        return {
                            id: product.id,
                            text: `${product.name} - R$ ${product.price}`,
                            price: product.price
                        };
                    })
                };
            },
            cache: true
        }
    });

    // Carregar clientes no select
    $.ajax({
        url: '/api/clients/list.php',
        method: 'GET',
        success: function(response) {
            const select = $('select[name="customer_id"]');
            select.empty().append('<option value="">Selecione um cliente</option>');
            response.forEach(function(client) {
                select.append(`<option value="${client.id}">${client.name}</option>`);
            });
        }
    });

    // Carregar produtos no select
    $.ajax({
        url: '/api/products/list.php',
        method: 'GET',
        success: function(response) {
            const select = $('.select2-products');
            select.empty().append('<option value="">Selecione um produto</option>');
            response.forEach(function(product) {
                select.append(`<option value="${product.id}" data-price="${product.price}">${product.name} - R$ ${product.price}</option>`);
            });
        }
    });

    // Inicializar DataTable
    ordersTable = $('#ordersTable').DataTable({
        ajax: {
            url: '/api/orders/list.php',
            dataSrc: 'data'
        },
        columns: [
            { data: 'id' },
            { data: 'client_name' },
            { 
                data: 'created_at',
                render: function(data) {
                    return formatDate(data);
                }
            },
            { 
                data: 'total_amount',
                render: function(data) {
                    return `R$ ${parseFloat(data).toFixed(2)}`;
                }
            },
            {
                data: 'status',
                render: function(data) {
                    return getStatusBadge(data || 'pending');
                }
            },
            {
                data: null,
                render: function(data) {
                    return `
                        <div class="btn-group">
                            <button class="btn btn-sm btn-info" onclick="viewOrder(${data.id})">
                                <i class="bx bx-show"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="editOrder(${data.id})">
                                <i class="bx bx-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteOrder(${data.id})">
                                <i class="bx bx-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[0, 'desc']],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/pt-BR.json'
        }
    });

    // Adicionar Produto
    $('#addProduct').click(function() {
        const productRow = $('.product-row').first().clone();
        productRow.find('select, input').val('');
        productRow.find('.select2-container').remove();
        productRow.find('select').select2();
        $('.products-container').append(productRow);
    });

    // Remover Produto
    $(document).on('click', '.remove-product', function() {
        if ($('.product-row').length > 1) {
            $(this).closest('.product-row').remove();
        }
    });

    // Atualizar preço ao selecionar produto
    $(document).on('change', '.select2-products', function() {
        const option = $(this).find('option:selected');
        const price = option.data('price');
        $(this).closest('.product-row').find('input[name="prices[]"]').val(price);
    });

    // Salvar Venda
    $('#saveOrder').click(function() {
        const formData = new FormData($('#addOrderForm')[0]);
        
        $.ajax({
            url: '/api/orders/create.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#addOrderModal').modal('hide');
                    ordersTable.ajax.reload();
                    showAlert('Venda salva com sucesso!');
                } else {
                    showAlert(response.message, 'error');
                }
            },
            error: function() {
                showAlert('Erro ao salvar venda', 'error');
            }
        });
    });
});

function viewOrder(id) {
    $.ajax({
        url: `/api/orders/view.php?id=${id}`,
        method: 'GET',
        success: function(response) {
            $('#view-id').text(response.id);
            $('#view-customer').text(response.client_name);
            $('#view-date').text(formatDate(response.created_at));
            $('#view-status').html(getStatusBadge(response.status));
            $('#view-total').text(`R$ ${parseFloat(response.total_amount).toFixed(2)}`);
            $('#viewOrderModal').modal('show');
        }
    });
}

function editOrder(id) {
    $.ajax({
        url: `/api/orders/view.php?id=${id}`,
        method: 'GET',
        success: function(response) {
            $('select[name="customer_id"]').val(response.client_id).trigger('change');
            $('#editOrderModal').modal('show');
        }
    });
}

function deleteOrder(id) {
    if (confirm('Tem certeza que deseja excluir esta venda?')) {
        $.ajax({
            url: `/api/orders/delete.php?id=${id}`,
            method: 'DELETE',
            success: function(response) {
                if (response.success) {
                    ordersTable.ajax.reload();
                    showAlert('Venda excluída com sucesso!');
                } else {
                    showAlert(response.message, 'error');
                }
            }
        });
    }
}

function getStatusBadge(status) {
    const badges = {
        'pending': '<span class="badge bg-warning">Pendente</span>',
        'processing': '<span class="badge bg-info">Em Processamento</span>',
        'delivered': '<span class="badge bg-success">Entregue</span>',
        'cancelled': '<span class="badge bg-danger">Cancelado</span>'
    };
    return badges[status] || badges.pending;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function showAlert(message, type = 'success') {
    const alertDiv = $(`<div class="alert alert-${type} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>`);
    
    $('.container-fluid').prepend(alertDiv);
    setTimeout(() => alertDiv.alert('close'), 5000);
}
