/*!
* Start Bootstrap - SB Admin v7.0.7 (https://startbootstrap.com/template/sb-admin)
* Copyright 2013-2023 Start Bootstrap
* Licensed under MIT (https://github.com/StartBootstrap/startbootstrap-sb-admin/blob/master/LICENSE)
*/

window.addEventListener('DOMContentLoaded', event => {
    // Toggle the side navigation
    const sidebarToggle = document.body.querySelector('#sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', event => {
            event.preventDefault();
            document.body.classList.toggle('sb-sidenav-toggled');
            localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sb-sidenav-toggled'));
        });
    }

    // Add active class to current nav item
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        if (currentPath.includes(link.getAttribute('href'))) {
            link.classList.add('active');
        }
    });

    // Format currency inputs
    const currencyInputs = document.querySelectorAll('.currency-input');
    currencyInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = this.value.replace(/\D/g, '');
            value = (value / 100).toFixed(2);
            this.value = formatCurrency(value);
        });
    });
});

// Format currency function
function formatCurrency(value) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
}

// Format date function
function formatDate(date) {
    return new Intl.DateTimeFormat('pt-BR', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    }).format(new Date(date));
}

// Format datetime function
function formatDateTime(date) {
    return new Intl.DateTimeFormat('pt-BR', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    }).format(new Date(date));
}

// Show loading spinner
function showLoading() {
    Swal.fire({
        title: 'Carregando...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

// Hide loading spinner
function hideLoading() {
    Swal.close();
}

// Show success message
function showSuccess(message) {
    Swal.fire({
        icon: 'success',
        title: 'Sucesso!',
        text: message,
        timer: 2000,
        showConfirmButton: false
    });
}

// Show error message
function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Erro!',
        text: message
    });
}

// Show confirmation dialog
function showConfirmation(title, text, callback) {
    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim',
        cancelButtonText: 'Não'
    }).then((result) => {
        if (result.isConfirmed && callback) {
            callback();
        }
    });
}

// Handle AJAX errors
function handleAjaxError(xhr, status, error) {
    console.error('Ajax Error:', error);
    let errorMessage = 'Ocorreu um erro ao processar sua solicitação.';
    
    if (xhr.responseJSON && xhr.responseJSON.message) {
        errorMessage = xhr.responseJSON.message;
    }
    
    showError(errorMessage);
}

// Initialize DataTables
$(document).ready(function() {
    // Configure DataTables
    const dataTable = $('#salesTable').DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: 'api/sales/list.php',
            type: 'POST'
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'date', name: 'date' },
            { data: 'customer', name: 'customer' },
            { data: 'total', name: 'total' },
            { data: 'status', name: 'status' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json'
        }
    });

    // Initialize Select2 for dropdowns
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

    // Initialize Datepicker
    $('.datepicker').datepicker({
        format: 'dd/mm/yyyy',
        language: 'pt-BR',
        autoclose: true,
        todayHighlight: true
    });

    // Format currency inputs
    $('.currency').mask('#.##0,00', {
        reverse: true,
        placeholder: '0,00'
    });

    // Handle product selection
    $('#product_id').on('change', function() {
        const productId = $(this).val();
        if (productId) {
            $.ajax({
                url: 'api/products/get.php',
                type: 'POST',
                data: { id: productId },
                success: function(response) {
                    if (response.success) {
                        $('#price').val(response.data.price);
                        $('#stock').val(response.data.stock);
                        calculateTotal();
                    }
                }
            });
        }
    });

    // Calculate total when quantity changes
    $('#quantity').on('input', function() {
        calculateTotal();
    });

    // Add product to sale
    $('#addProduct').on('click', function() {
        const productId = $('#product_id').val();
        const quantity = $('#quantity').val();
        const price = $('#price').val();
        const total = $('#total').val();

        if (!productId || !quantity || quantity <= 0) {
            showError('Por favor, preencha todos os campos corretamente.');
            return;
        }

        $.ajax({
            url: 'api/sales/add_product.php',
            type: 'POST',
            data: {
                product_id: productId,
                quantity: quantity,
                price: price,
                total: total
            },
            success: function(response) {
                if (response.success) {
                    updateProductsList();
                    resetProductForm();
                    showSuccess('Produto adicionado com sucesso!');
                } else {
                    showError(response.message);
                }
            }
        });
    });

    // Save sale
    $('#saveSale').on('click', function() {
        const formData = $('#saleForm').serialize();

        $.ajax({
            url: 'api/sales/save.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#newSaleModal').modal('hide');
                    dataTable.ajax.reload();
                    showSuccess('Venda salva com sucesso!');
                } else {
                    showError(response.message);
                }
            }
        });
    });

    // View sale details
    $(document).on('click', '.view-sale', function() {
        const saleId = $(this).data('id');
        
        $.ajax({
            url: 'api/sales/view.php',
            type: 'POST',
            data: { id: saleId },
            success: function(response) {
                if (response.success) {
                    $('#viewSaleModal .modal-body').html(response.html);
                    $('#viewSaleModal').modal('show');
                } else {
                    showError(response.message);
                }
            }
        });
    });

    // Delete sale
    $(document).on('click', '.delete-sale', function() {
        const saleId = $(this).data('id');
        
        Swal.fire({
            title: 'Tem certeza?',
            text: 'Esta ação não pode ser desfeita!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'api/sales/delete.php',
                    type: 'POST',
                    data: { id: saleId },
                    success: function(response) {
                        if (response.success) {
                            dataTable.ajax.reload();
                            showSuccess('Venda excluída com sucesso!');
                        } else {
                            showError(response.message);
                        }
                    }
                });
            }
        });
    });
});

// Helper Functions
function calculateTotal() {
    const quantity = parseFloat($('#quantity').val()) || 0;
    const price = parseFloat($('#price').val().replace(',', '.')) || 0;
    const total = quantity * price;
    $('#total').val(total.toFixed(2).replace('.', ','));
}

function resetProductForm() {
    $('#product_id').val('').trigger('change');
    $('#quantity').val('');
    $('#price').val('');
    $('#total').val('');
}

function updateProductsList() {
    $.ajax({
        url: 'api/sales/products_list.php',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                $('#productsList').html(response.html);
                updateSaleTotal();
            }
        }
    });
}

function updateSaleTotal() {
    $.ajax({
        url: 'api/sales/total.php',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                $('#saleTotal').html(response.total);
            }
        }
    });
}

function showSuccess(message) {
    Swal.fire({
        icon: 'success',
        title: 'Sucesso!',
        text: message,
        timer: 2000,
        showConfirmButton: false
    });
}

function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Erro!',
        text: message
    });
}

// Format date
function formatDate(date) {
    return moment(date).format('DD/MM/YYYY');
}

// Format currency
function formatCurrency(value) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
}
