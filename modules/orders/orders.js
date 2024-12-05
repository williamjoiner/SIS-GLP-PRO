$(document).ready(function() {
    // Armazenar o elemento que tinha foco antes do modal abrir
    let lastFocusedElement;

    // Quando o modal for aberto, salvar o elemento que tinha foco
    $('#saleModal').on('show.bs.modal', function() {
        lastFocusedElement = document.activeElement;
    });

    // Quando o modal for fechado, restaurar o foco ao elemento anterior
    $('#saleModal').on('hidden.bs.modal', function() {
        resetForm();
        if (lastFocusedElement) {
            lastFocusedElement.focus();
        }
    });

    // Inicialização do Select2 com suporte a acessibilidade
    $('#client_id').select2({
        theme: 'bootstrap-5',
        placeholder: 'Selecione um cliente',
        allowClear: true,
        ajax: {
            url: '../../api/clients/search.php',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    search: params.term,
                    page: params.page || 1
                };
            },
            processResults: function(data, params) {
                params.page = params.page || 1;
                return {
                    results: data.items,
                    pagination: {
                        more: data.pagination.more
                    }
                };
            },
            cache: true
        },
        templateResult: formatClient,
        templateSelection: formatClient
    }).on('select2:open', function() {
        document.querySelector('.select2-search__field').focus();
    });

    function formatClient(client) {
        if (!client.id) return client.text;
        return $('<span>').text(client.name + ' (' + client.document + ')');
    }

    // Gerenciamento de foco nos modais
    $('#saleModal').on('shown.bs.modal', function() {
        $('#client_id').select2('focus');
    }).on('hidden.bs.modal', function() {
        resetForm();
        $('.btn[data-bs-target="#saleModal"]').focus();
    });

    $('#viewSaleModal').on('shown.bs.modal', function() {
        $(this).find('.btn-close').focus();
    }).on('hidden.bs.modal', function() {
        $('#salesTable').find('button[data-action="view"]:focus').focus();
    });

    // Inicializar Select2 para o método de pagamento
    $('#payment_method').select2({
        theme: 'bootstrap-5',
        minimumResultsForSearch: Infinity
    });

    // Inicializar DataTable
    const table = $('#salesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '../../api/sales/list.php',
            type: 'GET',
            data: function(d) {
                d.status = $('#status_filter').val();
                d.start_date = $('#start_date').val();
                d.end_date = $('#end_date').val();
                d.client_id = $('#customer_filter').val();
            }
        },
        columns: [
            { data: 'id' },
            { data: 'client_name' },
            { data: 'sale_date' },
            { data: 'subtotal', className: 'text-end' },
            { data: 'discount', className: 'text-end' },
            { data: 'total', className: 'text-end' },
            { data: 'payment_method' },
            { data: 'status', className: 'text-center' },
            { data: 'actions', className: 'text-center', orderable: false }
        ],
        order: [[2, 'desc']],
        language: {
            url: '../../assets/js/dataTables.portuguese.json'
        }
    });

    // Atualizar tabela quando os filtros mudarem
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        table.ajax.reload();
    });

    $('#filterForm').on('reset', function(e) {
        setTimeout(function() {
            table.ajax.reload();
        }, 100);
    });

    // Função para adicionar uma nova linha de produto
    function addProductRow() {
        const rowId = Date.now();
        const row = $('<div>', {
            class: 'product-row mb-3',
            'data-row-id': rowId,
            role: 'group',
            'aria-label': 'Produto'
        });

        const productSelect = $('<select>', {
            class: 'form-select product-select',
            required: true,
            'aria-label': 'Selecione um produto'
        }).select2({
            theme: 'bootstrap-5',
            placeholder: 'Selecione um produto',
            allowClear: true,
            ajax: {
                url: '../../api/products/search.php',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.items,
                        pagination: {
                            more: data.pagination.more
                        }
                    };
                },
                cache: true
            },
            templateResult: formatProduct,
            templateSelection: formatProduct
        });

        const quantityInput = $('<input>', {
            type: 'number',
            class: 'form-control quantity',
            min: 1,
            value: 1,
            required: true,
            'aria-label': 'Quantidade'
        });

        const priceInput = $('<input>', {
            type: 'text',
            class: 'form-control currency price',
            readonly: true,
            required: true,
            'aria-label': 'Preço'
        });

        const totalInput = $('<input>', {
            type: 'text',
            class: 'form-control currency total',
            readonly: true,
            required: true,
            'aria-label': 'Total'
        });

        // Botão de remover com foco adequado
        const removeButton = $('<button>', {
            type: 'button',
            class: 'btn btn-danger remove-product',
            'aria-label': 'Remover produto'
        }).html('<i class="bi bi-trash" aria-hidden="true"></i>').on('click', function() {
            const nextRow = row.next('.product-row');
            const prevRow = row.prev('.product-row');
            
            row.remove();
            updateTotals();
            
            // Move o foco para a próxima linha ou anterior se disponível
            if (nextRow.length) {
                nextRow.find('.product-select').select2('focus');
            } else if (prevRow.length) {
                prevRow.find('.product-select').select2('focus');
            } else {
                $('#addProduct').focus();
            }
        });

        row.append(
            $('<div>', { class: 'col-md-5' }).append(productSelect),
            $('<div>', { class: 'col-md-2' }).append(quantityInput),
            $('<div>', { class: 'col-md-2' }).append(priceInput),
            $('<div>', { class: 'col-md-2' }).append(totalInput),
            $('<div>', { class: 'col-md-1' }).append(removeButton)
        );

        // Inicializar máscara para os campos de moeda
        priceInput.mask('#.##0,00', {
            reverse: true,
            placeholder: '0,00'
        });
        totalInput.mask('#.##0,00', {
            reverse: true,
            placeholder: '0,00'
        });

        // Atualizar total quando a quantidade mudar
        quantityInput.on('change', function() {
            updateProductTotal(row);
        });

        // Atualizar total quando o produto for selecionado
        productSelect.on('select2:select', function(e) {
            const price = e.params.data.price;
            row.find('.price').val(formatDecimal(price));
            updateProductTotal(row);
        });

        $('#productsList').append(row);
    }

    function formatProduct(product) {
        if (!product.id) return product.text;
        return $('<span>').text(product.name + ' (' + product.code + ')');
    }

    // Salvar venda
    $('#saleForm').on('submit', function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            return;
        }

        const saleData = {
            client_id: $('#client_id').val(),
            payment_method: $('#payment_method').val(),
            subtotal: parseDecimal($('#subtotal').val()),
            discount: parseDecimal($('#discount').val()),
            total: parseDecimal($('#total').val()),
            products: []
        };

        $('.product-row').each(function() {
            const row = $(this);
            saleData.products.push({
                product_id: row.find('.product-select').val(),
                quantity: parseFloat(row.find('.quantity').val()),
                price: parseDecimal(row.find('.price').val()),
                total: parseDecimal(row.find('.total').val())
            });
        });

        $.ajax({
            url: '../../api/sales/save.php',
            method: 'POST',
            data: JSON.stringify(saleData),
            contentType: 'application/json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Sucesso!',
                        text: 'Venda registrada com sucesso.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        $('#saleModal').modal('hide');
                        table.ajax.reload();
                        updateDashboardCards();
                    });
                } else {
                    Swal.fire({
                        title: 'Erro!',
                        text: response.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    title: 'Erro!',
                    text: 'Ocorreu um erro ao salvar a venda.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    });

    // Ver detalhes da venda
    $(document).on('click', 'button[data-action="view"]', function() {
        const id = $(this).data('id');
        
        $.get('../../api/sales/view.php', { id: id })
            .done(function(response) {
                if (response.success) {
                    const sale = response.sale;
                    
                    $('#viewSaleId').text(sale.id);
                    $('#viewClientName').text(sale.client_name);
                    $('#viewDate').text(sale.sale_date);
                    $('#viewPaymentMethod').text(sale.payment_method);
                    $('#viewStatus').text(sale.status);
                    $('#viewSubtotal').text('R$ ' + sale.subtotal);
                    $('#viewDiscount').text('R$ ' + sale.discount);
                    $('#viewTotal').text('R$ ' + sale.total);

                    let productsHtml = '';
                    response.items.forEach(function(item) {
                        productsHtml += `
                            <tr>
                                <td>${item.product_name} (${item.product_code})</td>
                                <td class="text-end">${item.quantity}</td>
                                <td class="text-end">R$ ${item.price}</td>
                                <td class="text-end">R$ ${item.total}</td>
                            </tr>
                        `;
                    });
                    $('#viewProducts').html(productsHtml);

                    $('#viewSaleModal').modal('show');
                } else {
                    Swal.fire({
                        title: 'Erro!',
                        text: response.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .fail(function() {
                Swal.fire({
                    title: 'Erro!',
                    text: 'Ocorreu um erro ao buscar os detalhes da venda.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
    });

    // Marcar venda como paga
    $(document).on('click', 'button[data-action="pay"]', function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: 'Confirmar Pagamento',
            text: 'Deseja marcar esta venda como paga?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sim',
            cancelButtonText: 'Não'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../../api/sales/update_status.php',
                    method: 'POST',
                    data: JSON.stringify({
                        id: id,
                        status: 'paid'
                    }),
                    contentType: 'application/json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Sucesso!',
                                text: 'Status da venda atualizado com sucesso.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                table.ajax.reload();
                                updateDashboardCards();
                            });
                        } else {
                            Swal.fire({
                                title: 'Erro!',
                                text: response.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Erro!',
                            text: 'Ocorreu um erro ao atualizar o status da venda.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    });

    // Cancelar venda
    $(document).on('click', 'button[data-action="cancel"]', function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: 'Confirmar Cancelamento',
            text: 'Deseja cancelar esta venda? Esta ação irá devolver os produtos ao estoque.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim',
            cancelButtonText: 'Não',
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../../api/sales/update_status.php',
                    method: 'POST',
                    data: JSON.stringify({
                        id: id,
                        status: 'cancelled'
                    }),
                    contentType: 'application/json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Sucesso!',
                                text: 'Venda cancelada com sucesso.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                table.ajax.reload();
                                updateDashboardCards();
                            });
                        } else {
                            Swal.fire({
                                title: 'Erro!',
                                text: response.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Erro!',
                            text: 'Ocorreu um erro ao cancelar a venda.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    });

    // Atualizar cards do dashboard
    function updateDashboardCards() {
        $.get('../../api/sales/dashboard.php')
            .done(function(response) {
                if (response.success) {
                    $('#today_sales').text(response.today_sales);
                    $('#today_revenue').text('R$ ' + response.today_revenue);
                    $('#month_sales').text(response.month_sales);
                    $('#month_revenue').text('R$ ' + response.month_revenue);
                }
            });
    }

    // Inicializar cards
    updateDashboardCards();

    // Funções auxiliares
    function updateProductTotal(row) {
        const quantity = parseInt(row.find('.quantity').val()) || 0;
        const price = parseDecimal(row.find('.price').val());
        const total = quantity * price;
        row.find('.total').val(formatDecimal(total));
        updateTotals();
    }

    function updateTotals() {
        let subtotal = 0;
        $('.product-row').each(function() {
            subtotal += parseDecimal($(this).find('.total').val());
        });
        
        const discount = parseDecimal($('#discount').val());
        const total = subtotal - discount;
        
        $('#subtotal').val(formatDecimal(subtotal));
        $('#total').val(formatDecimal(total));
    }

    function validateForm() {
        let isValid = true;
        
        // Validar cliente
        if (!$('#client_id').val()) {
            showError('Selecione um cliente');
            isValid = false;
        }
        
        // Validar método de pagamento
        if (!$('#payment_method').val()) {
            showError('Selecione um método de pagamento');
            isValid = false;
        }
        
        // Validar produtos
        if ($('.product-row').length === 0) {
            showError('Adicione pelo menos um produto');
            isValid = false;
        }
        
        $('.product-row').each(function() {
            const row = $(this);
            
            // Validar produto selecionado
            if (!row.find('.product-select').val()) {
                showError('Selecione um produto');
                isValid = false;
            }
            
            // Validar quantidade
            const quantity = parseInt(row.find('.quantity').val());
            if (!quantity || quantity < 1) {
                showError('Quantidade inválida');
                isValid = false;
            }
        });
        
        return isValid;
    }

    function resetForm() {
        $('#saleForm')[0].reset();
        $('#client_id').val('').trigger('change');
        $('#payment_method').val('').trigger('change');
        $('#productsList').empty();
    }

    function formatDecimal(value) {
        if (typeof value === 'string') {
            value = parseDecimal(value);
        }
        return value.toFixed(2).replace('.', ',');
    }

    function parseDecimal(value) {
        if (typeof value === 'number') return value;
        return parseFloat(value.replace(/\./g, '').replace(',', '.')) || 0;
    }

    function formatCurrency(value) {
        if (typeof value === 'string') {
            value = parseDecimal(value);
        }
        return value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }

    function showSuccess(message) {
        Swal.fire({
            icon: 'success',
            title: 'Sucesso!',
            text: message,
            showConfirmButton: false,
            timer: 1500
        });
    }

    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: message
        });
    }

    // Atualizar totais quando o desconto mudar
    $('#discount').on('input', updateTotals);

    // Resetar formulário quando o modal for fechado
    $('#saleModal').on('hidden.bs.modal', function() {
        resetForm();
        if (lastFocusedElement) {
            lastFocusedElement.focus();
        }
    });
});
