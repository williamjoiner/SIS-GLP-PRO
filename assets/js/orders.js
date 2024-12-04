let ordersTable;
let products = [];
let currentOrderId = null;

$(document).ready(function() {
    // Inicializar Select2
    $('.select2').select2({
        width: '100%',
        language: 'pt-BR'
    });

    // Inicializar DataTable
    ordersTable = $('#ordersTable').DataTable({
        ajax: {
            url: '../../api/orders/list.php',
            dataSrc: 'data'
        },
        order: [[0, 'desc']],
        columns: [
            { data: 'id' },
            { data: 'client_name' },
            { data: 'deliverer_name' },
            { 
                data: 'total_amount',
                render: function(data) {
                    return `R$ ${parseFloat(data).toFixed(2).replace('.', ',')}`;
                }
            },
            {
                data: 'status',
                render: function(data) {
                    // Se o status for null ou undefined, definir como 'pending'
                    const status = data || 'pending';
                    return getStatusBadge(status);
                }
            },
            { 
                data: 'created_at',
                render: function(data) {
                    return formatDate(data);
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    // Se o status for null ou undefined, definir como 'pending'
                    const status = row.status || 'pending';
                    
                    let buttons = `
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-info" onclick="viewOrder(${row.id})" title="Ver Detalhes">
                                <i class='bx bx-show'></i>
                            </button>`;
                    
                    if (status === 'pending') {
                        buttons += `
                            <button class="btn btn-sm btn-warning" onclick="editOrder(${row.id})" title="Editar">
                                <i class='bx bx-edit'></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="cancelOrder(${row.id})" title="Cancelar">
                                <i class='bx bx-x'></i>
                            </button>`;
                    }

                    if (status === 'pending' || status === 'processing') {
                        buttons += `
                            <button class="btn btn-sm btn-success" onclick="sendToWhatsApp(${row.id})" title="Enviar WhatsApp">
                                <i class='bx bxl-whatsapp'></i>
                            </button>`;
                    }

                    buttons += '</div>';
                    return buttons;
                }
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json'
        }
    });

    // Filtros
    $('#statusFilter, #clientFilter').on('change', function() {
        ordersTable.draw();
    });

    $('#startDate, #endDate').on('change', function() {
        ordersTable.draw();
    });

    // Custom filtering function
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        let status = $('#statusFilter').val();
        let clientId = $('#clientFilter').val();
        let startDate = $('#startDate').val();
        let endDate = $('#endDate').val();
        let row = ordersTable.row(dataIndex).data();

        // Filtro de Status
        if (status && row.status !== status) {
            return false;
        }

        // Filtro de Cliente
        if (clientId && row.client_id !== clientId) {
            return false;
        }

        // Filtro de Data
        if (startDate || endDate) {
            let orderDate = new Date(row.created_at);
            if (startDate && new Date(startDate) > orderDate) return false;
            if (endDate && new Date(endDate) < orderDate) return false;
        }

        return true;
    });

    // Adicionar Produto
    $('#add-product').click(function() {
        const newRow = $('.product-row').first().clone();
        newRow.find('select').val('').trigger('change');
        newRow.find('input[type="number"]').val(1);
        newRow.find('.product-subtotal').val('');
        $('.products-container').append(newRow);
        
        // Reinicializar Select2
        newRow.find('.select2').select2({
            width: '100%',
            language: 'pt-BR'
        });
    });

    // Remover Produto
    $(document).on('click', '.remove-product', function() {
        if ($('.product-row').length > 1) {
            $(this).closest('.product-row').remove();
            calculateTotal();
        }
    });

    // Atualizar subtotal ao mudar produto ou quantidade
    $(document).on('change', '.product-select, .product-quantity', function() {
        const row = $(this).closest('.product-row');
        updateSubtotal(row);
    });

    // Salvar Venda
    $('#saveOrderBtn').click(function() {
        const orderId = $('#order_id').val();
        if (orderId) {
            updateOrder(orderId);
        } else {
            saveOrder();
        }
    });

    // Limpar modal ao fechar
    $('#addOrderModal').on('hidden.bs.modal', function() {
        $('#addOrderForm')[0].reset();
        $('#order_id').val('');
        $('#modalTitle').text('Nova Venda');
        $('.select2').val('').trigger('change');
        $('.product-row:not(:first)').remove();
        $('.product-subtotal').val('');
        $('#total').val('');
    });
});

function updateSubtotal(row) {
    const select = row.find('.product-select');
    const quantity = row.find('.product-quantity').val();
    const option = select.find('option:selected');
    
    if (option.val() && quantity) {
        const price = parseFloat(option.data('price'));
        const subtotal = price * quantity;
        row.find('.product-subtotal').val(
            `R$ ${subtotal.toFixed(2).replace('.', ',')}`
        );
    } else {
        row.find('.product-subtotal').val('');
    }
    
    calculateTotal();
}

function calculateTotal() {
    let total = 0;
    $('.product-row').each(function() {
        const subtotalStr = $(this).find('.product-subtotal').val();
        if (subtotalStr) {
            const subtotal = parseFloat(
                subtotalStr.replace('R$ ', '').replace(',', '.')
            );
            if (!isNaN(subtotal)) {
                total += subtotal;
            }
        }
    });
    
    $('#total').val(`R$ ${total.toFixed(2).replace('.', ',')}`);
}

function saveOrder() {
    const formData = new FormData($('#addOrderForm')[0]);
    const products = [];
    
    // Validar produtos
    $('.product-row').each(function() {
        const productId = $(this).find('.product-select').val();
        const quantity = $(this).find('.product-quantity').val();
        
        if (productId && quantity) {
            products.push({
                product_id: productId,
                quantity: quantity
            });
        }
    });
    
    if (products.length === 0) {
        showAlert('Adicione pelo menos um produto', 'error');
        return;
    }
    
    const data = {
        client_id: formData.get('client_id'),
        deliverer_id: formData.get('deliverer_id'),
        payment_method: formData.get('payment_method'),
        status: formData.get('status'),
        notes: formData.get('notes'),
        products: products
    };
    
    $.ajax({
        url: '../../api/orders/create.php',
        type: 'POST',
        data: JSON.stringify(data),
        contentType: 'application/json',
        success: function(response) {
            if (response.success) {
                $('#addOrderModal').modal('hide');
                ordersTable.ajax.reload();
                showAlert('Venda criada com sucesso!');
            } else {
                showAlert(response.message || 'Erro ao criar venda', 'error');
            }
        },
        error: function() {
            showAlert('Erro ao criar venda', 'error');
        }
    });
}

function viewOrder(id) {
    $.ajax({
        url: `../../api/orders/view.php?id=${id}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const order = response.data;
                
                // Informações Gerais
                $('#view-client').text(order.client_name);
                $('#view-deliverer').text(order.deliverer_name);
                $('#view-status').html(getStatusBadge(order.status));
                
                // Informações de Pagamento
                $('#view-total').text(`R$ ${parseFloat(order.total_amount).toFixed(2).replace('.', ',')}`);
                $('#view-payment').text(getPaymentMethodText(order.payment_method));
                $('#view-date').text(formatDate(order.created_at));
                
                // Produtos
                let productsHtml = '';
                order.products.forEach(function(product) {
                    const subtotal = product.price * product.quantity;
                    productsHtml += `
                        <tr>
                            <td>${product.name}</td>
                            <td>${product.quantity}</td>
                            <td>R$ ${parseFloat(product.price).toFixed(2).replace('.', ',')}</td>
                            <td>R$ ${subtotal.toFixed(2).replace('.', ',')}</td>
                        </tr>
                    `;
                });
                $('#view-products tbody').html(productsHtml);
                
                // Observações
                $('#view-notes').text(order.notes || 'Nenhuma observação');
                
                $('#viewOrderModal').modal('show');
            } else {
                showAlert('Erro ao carregar detalhes da venda', 'error');
            }
        },
        error: function() {
            showAlert('Erro ao carregar detalhes da venda', 'error');
        }
    });
}

function editOrder(id) {
    currentOrderId = id;
    
    $.ajax({
        url: `../../api/orders/view.php?id=${id}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const order = response.data;
                
                // Atualizar título do modal
                $('#modalTitle').text('Editar Venda');
                $('#order_id').val(id);
                
                // Preencher campos
                $('select[name="client_id"]').val(order.client_id).trigger('change');
                $('select[name="deliverer_id"]').val(order.deliverer_id).trigger('change');
                $('select[name="payment_method"]').val(order.payment_method);
                $('select[name="status"]').val(order.status);
                $('textarea[name="notes"]').val(order.notes);
                
                // Limpar produtos existentes
                $('.product-row:not(:first)').remove();
                
                // Adicionar produtos
                order.products.forEach(function(product, index) {
                    if (index > 0) {
                        $('#add-product').click();
                    }
                    
                    const row = $('.product-row').eq(index);
                    row.find('.product-select').val(product.product_id).trigger('change');
                    row.find('.product-quantity').val(product.quantity).trigger('change');
                });
                
                $('#addOrderModal').modal('show');
            } else {
                showAlert('Erro ao carregar dados da venda', 'error');
            }
        },
        error: function() {
            showAlert('Erro ao carregar dados da venda', 'error');
        }
    });
}

function updateOrder(id) {
    const formData = new FormData($('#addOrderForm')[0]);
    const products = [];
    
    // Validar produtos
    $('.product-row').each(function() {
        const productId = $(this).find('.product-select').val();
        const quantity = $(this).find('.product-quantity').val();
        
        if (productId && quantity) {
            products.push({
                product_id: productId,
                quantity: quantity
            });
        }
    });
    
    if (products.length === 0) {
        showAlert('Adicione pelo menos um produto', 'error');
        return;
    }
    
    const data = {
        id: id,
        client_id: formData.get('client_id'),
        deliverer_id: formData.get('deliverer_id'),
        payment_method: formData.get('payment_method'),
        status: formData.get('status'),
        notes: formData.get('notes'),
        products: products
    };
    
    $.ajax({
        url: '../../api/orders/update.php',
        type: 'POST',
        data: JSON.stringify(data),
        contentType: 'application/json',
        success: function(response) {
            if (response.success) {
                $('#addOrderModal').modal('hide');
                ordersTable.ajax.reload();
                showAlert('Venda atualizada com sucesso!');
            } else {
                showAlert(response.message || 'Erro ao atualizar venda', 'error');
            }
        },
        error: function() {
            showAlert('Erro ao atualizar venda', 'error');
        }
    });
}

function cancelOrder(id) {
    if (confirm('Tem certeza que deseja cancelar esta venda?')) {
        $.ajax({
            url: '../../api/orders/update.php',
            type: 'POST',
            data: JSON.stringify({
                id: id,
                status: 'cancelled'
            }),
            contentType: 'application/json',
            success: function(response) {
                if (response.success) {
                    ordersTable.ajax.reload();
                    showAlert('Venda cancelada com sucesso!');
                } else {
                    showAlert('Erro ao cancelar venda', 'error');
                }
            },
            error: function() {
                showAlert('Erro ao cancelar venda', 'error');
            }
        });
    }
}

function sendToWhatsApp(id) {
    $.ajax({
        url: `../../api/orders/view.php?id=${id}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const order = response.data;
                let message = `*PEDIDO #${order.id}*\n\n`;
                message += `*Cliente:* ${order.client_name}\n`;
                if (order.deliverer_name) {
                    message += `*Entregador:* ${order.deliverer_name}\n`;
                }
                message += `\n*PRODUTOS:*\n`;
                
                order.products.forEach(function(product) {
                    const subtotal = product.price * product.quantity;
                    message += `• ${product.quantity}x ${product.name}\n`;
                    message += `  R$ ${subtotal.toFixed(2).replace('.', ',')}\n`;
                });
                
                message += `\n*TOTAL: R$ ${parseFloat(order.total_amount).toFixed(2).replace('.', ',')}*\n`;
                message += `*Forma de Pagamento:* ${getPaymentMethodText(order.payment_method)}\n`;
                
                if (order.notes) {
                    message += `\n*Observações:*\n${order.notes}`;
                }
                
                // Criar URL do WhatsApp
                const encodedMessage = encodeURIComponent(message);
                const whatsappUrl = `https://api.whatsapp.com/send?text=${encodedMessage}`;
                
                // Abrir em nova aba
                window.open(whatsappUrl, '_blank');
            } else {
                showAlert('Erro ao carregar dados do pedido para WhatsApp', 'error');
            }
        },
        error: function() {
            showAlert('Erro ao carregar dados do pedido para WhatsApp', 'error');
        }
    });
}

function getStatusBadge(status) {
    const badges = {
        pending: 'warning',
        processing: 'info',
        completed: 'success',
        cancelled: 'danger'
    };
    
    return `<span class="badge bg-${badges[status]}">${getStatusText(status)}</span>`;
}

function getStatusText(status) {
    const statusText = {
        pending: 'Pendente',
        processing: 'Em Processamento',
        completed: 'Concluído',
        cancelled: 'Cancelado'
    };
    
    return statusText[status] || status;
}

function getPaymentMethodText(method) {
    const methodText = {
        money: 'Dinheiro',
        credit_card: 'Cartão de Crédito',
        debit_card: 'Cartão de Débito',
        pix: 'PIX'
    };
    
    return methodText[method] || method;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function showAlert(message, type = 'success') {
    const alertDiv = $('<div>')
        .addClass(`alert alert-${type} alert-dismissible fade show`)
        .attr('role', 'alert')
        .html(`
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `);
    
    $('.container-fluid').first().prepend(alertDiv);
    
    setTimeout(() => {
        alertDiv.alert('close');
    }, 5000);
}
