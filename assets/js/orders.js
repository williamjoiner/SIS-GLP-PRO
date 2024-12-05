$(document).ready(function() {
    // Inicializar Select2
    $('.select2').select2({
        language: 'pt-BR',
        theme: 'bootstrap-5'
    });

    // Carregar clientes no select
    $.get('/api/clients/list.php', function(response) {
        if (response.success) {
            let options = '<option value="">Selecione um cliente</option>';
            response.data.forEach(function(client) {
                options += `<option value="${client.id}">${client.name}</option>`;
            });
            $('select[name="customer_id"]').html(options);
            $('#filterCustomer').html(options);
        }
    });

    // Carregar produtos no select
    function loadProducts(select) {
        $.get('/api/products/list.php', function(response) {
            if (response.success) {
                let options = '<option value="">Selecione um produto</option>';
                response.data.forEach(function(product) {
                    options += `<option value="${product.id}" data-price="${product.price}">${product.name}</option>`;
                });
                select.html(options);
            }
        });
    }

    // Inicializar DataTable
    const table = $('#ordersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/api/orders/list.php',
            type: 'POST',
            data: function(d) {
                return {
                    ...d,
                    status: $('#filterStatus').val(),
                    start_date: $('#filterStartDate').val(),
                    end_date: $('#filterEndDate').val(),
                    customer_id: $('#filterCustomer').val()
                };
            }
        },
        columns: [
            { data: 'id' },
            { data: 'client_name' },
            { 
                data: 'created_at',
                render: function(data) {
                    return new Date(data).toLocaleString('pt-BR');
                }
            },
            { 
                data: 'total_amount',
                render: function(data) {
                    return formatMoney(data);
                }
            },
            { data: 'payment_method' },
            { 
                data: 'status',
                render: function(data) {
                    const status = {
                        pending: { label: 'Pendente', class: 'warning' },
                        processing: { label: 'Em Processamento', class: 'info' },
                        ready: { label: 'Pronto', class: 'primary' },
                        delivered: { label: 'Entregue', class: 'success' },
                        cancelled: { label: 'Cancelado', class: 'danger' }
                    };
                    return `<span class="badge bg-${status[data].class}">${status[data].label}</span>`;
                }
            },
            {
                data: null,
                render: function(data) {
                    return `
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-primary view-order" data-id="${data.id}">
                                <i class="bx bx-show"></i>
                            </button>
                            <button type="button" class="btn btn-info edit-order" data-id="${data.id}">
                                <i class="bx bx-edit"></i>
                            </button>
                            <button type="button" class="btn btn-danger delete-order" data-id="${data.id}">
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

    // Atualizar tabela quando filtros mudarem
    $('#filterForm select, #filterForm input').on('change', function() {
        table.ajax.reload();
        updateSummary();
    });

    // Atualizar resumo
    function updateSummary() {
        $.post('/api/orders/summary.php', {
            status: $('#filterStatus').val(),
            start_date: $('#filterStartDate').val(),
            end_date: $('#filterEndDate').val(),
            customer_id: $('#filterCustomer').val()
        }, function(response) {
            if (response.success) {
                $('#totalSales').text(response.data.total_sales);
                $('#completedSales').text(response.data.completed_sales);
                $('#pendingSales').text(response.data.pending_sales);
                $('#averageTicket').text(formatMoney(response.data.average_ticket));
            }
        });
    }

    // Adicionar linha de produto
    $('#addProductRow').click(function() {
        const row = `
            <div class="product-row">
                <div class="row g-3">
                    <div class="col-md-5">
                        <select class="form-select product-select" name="products[]" required>
                            <option value="">Selecione um produto</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" class="form-control quantity" name="quantities[]" min="1" value="1" required>
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control price" readonly>
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control subtotal" readonly>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-sm remove-product">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        $('.products-container').append(row);
        loadProducts($('.products-container .product-select').last());
        updateTotals();
    });

    // Remover linha de produto
    $(document).on('click', '.remove-product', function() {
        $(this).closest('.product-row').remove();
        updateTotals();
    });

    // Atualizar preço quando produto é selecionado
    $(document).on('change', '.product-select', function() {
        const row = $(this).closest('.product-row');
        const price = $(this).find(':selected').data('price') || 0;
        row.find('.price').val(formatMoney(price));
        updateTotals();
    });

    // Atualizar subtotal quando quantidade muda
    $(document).on('change', '.quantity', function() {
        updateTotals();
    });

    // Atualizar totais
    function updateTotals() {
        let subtotal = 0;
        $('.product-row').each(function() {
            const price = parseFloat($(this).find('.price').val().replace('R$ ', '').replace('.', '').replace(',', '.')) || 0;
            const quantity = parseInt($(this).find('.quantity').val()) || 0;
            const rowSubtotal = price * quantity;
            $(this).find('.subtotal').val(formatMoney(rowSubtotal));
            subtotal += rowSubtotal;
        });

        const discount = parseFloat($('input[name="discount"]').val()) || 0;
        const total = subtotal - discount;

        $('#subtotal').text(formatMoney(subtotal));
        $('#total').text(formatMoney(total));
    }

    // Salvar venda
    $('#saveOrder').click(function() {
        const form = $('#orderForm');
        if (!form[0].checkValidity()) {
            form[0].reportValidity();
            return;
        }

        const products = [];
        $('.product-row').each(function() {
            products.push({
                product_id: $(this).find('.product-select').val(),
                quantity: $(this).find('.quantity').val()
            });
        });

        const data = {
            customer_id: form.find('[name="customer_id"]').val(),
            payment_method: form.find('[name="payment_method"]').val(),
            discount: form.find('[name="discount"]').val(),
            notes: form.find('[name="notes"]').val(),
            items: products
        };

        $.ajax({
            url: '/api/orders/create.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: 'Venda criada com sucesso!'
                    }).then(() => {
                        $('#addOrderModal').modal('hide');
                        table.ajax.reload();
                        updateSummary();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: response.message
                    });
                }
            }
        });
    });

    // Visualizar venda
    $(document).on('click', '.view-order', function() {
        const id = $(this).data('id');
        $.get(`/api/orders/read.php?id=${id}`, function(response) {
            if (response.success) {
                const order = response.data;
                $('#view-id').text(order.id);
                $('#view-customer').text(order.client_name);
                $('#view-date').text(new Date(order.created_at).toLocaleString('pt-BR'));
                $('#view-payment').text(order.payment_method);
                $('#view-status').html(`<span class="badge bg-${getStatusColor(order.status)}">${getStatusLabel(order.status)}</span>`);
                $('#view-subtotal').text(formatMoney(order.subtotal));
                $('#view-discount').text(formatMoney(order.discount));
                $('#view-total').text(formatMoney(order.total_amount));
                $('#view-notes').text(order.notes || 'Nenhuma observação');

                let productsHtml = '';
                order.items.forEach(function(item) {
                    productsHtml += `
                        <tr>
                            <td>${item.product_name}</td>
                            <td>${item.quantity}</td>
                            <td>${formatMoney(item.price)}</td>
                            <td>${formatMoney(item.price * item.quantity)}</td>
                        </tr>
                    `;
                });
                $('#view-products tbody').html(productsHtml);

                $('#viewOrderModal').modal('show');
            }
        });
    });

    // Funções auxiliares
    function formatMoney(value) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(value);
    }

    function getStatusColor(status) {
        const colors = {
            pending: 'warning',
            processing: 'info',
            ready: 'primary',
            delivered: 'success',
            cancelled: 'danger'
        };
        return colors[status] || 'secondary';
    }

    function getStatusLabel(status) {
        const labels = {
            pending: 'Pendente',
            processing: 'Em Processamento',
            ready: 'Pronto',
            delivered: 'Entregue',
            cancelled: 'Cancelado'
        };
        return labels[status] || status;
    }

    // Inicialização
    $('#addProductRow').click();
    updateSummary();
});
