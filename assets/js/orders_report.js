let dailySalesChart;
let topProductsChart;

$(document).ready(function() {
    // Inicializar date range picker
    $('#dateRange').daterangepicker({
        startDate: moment().subtract(30, 'days'),
        endDate: moment(),
        ranges: {
           'Hoje': [moment(), moment()],
           'Ontem': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Últimos 7 dias': [moment().subtract(6, 'days'), moment()],
           'Últimos 30 dias': [moment().subtract(29, 'days'), moment()],
           'Este mês': [moment().startOf('month'), moment().endOf('month')],
           'Mês passado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        locale: {
            format: 'DD/MM/YYYY',
            applyLabel: 'Aplicar',
            cancelLabel: 'Cancelar',
            fromLabel: 'De',
            toLabel: 'Até',
            customRangeLabel: 'Personalizado',
            daysOfWeek: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
            monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro']
        }
    });
    
    // Carregar relatório inicial
    loadReport();
});

function loadReport() {
    const dates = $('#dateRange').data('daterangepicker');
    const status = $('#statusFilter').val();
    const deliverer_id = $('#delivererFilter').val();
    
    $.ajax({
        url: '../../api/orders/report.php',
        method: 'GET',
        data: {
            start_date: dates.startDate.format('YYYY-MM-DD'),
            end_date: dates.endDate.format('YYYY-MM-DD'),
            status: status,
            deliverer_id: deliverer_id
        },
        success: function(response) {
            if (response.success) {
                updateSummary(response.data.summary);
                updateDailyChart(response.data.daily_stats);
                updateTopProductsChart(response.data.top_products);
            } else {
                showAlert('Erro ao carregar relatório: ' + response.message, 'error');
            }
        },
        error: function() {
            showAlert('Erro ao carregar relatório', 'error');
        }
    });
}

function updateSummary(summary) {
    $('#totalOrders').text(summary.total_orders);
    $('#totalAmount').text(`R$ ${parseFloat(summary.total_amount).toFixed(2)}`);
    $('#averageOrderValue').text(`R$ ${parseFloat(summary.average_order_value).toFixed(2)}`);
    
    const deliveryRate = summary.total_orders > 0 
        ? (summary.delivered_orders / summary.total_orders * 100).toFixed(1)
        : 0;
    $('#deliveryRate').text(`${deliveryRate}%`);
}

function updateDailyChart(dailyStats) {
    const dates = dailyStats.map(stat => moment(stat.date).format('DD/MM'));
    const amounts = dailyStats.map(stat => parseFloat(stat.total_amount));
    const orders = dailyStats.map(stat => parseInt(stat.total_orders));
    
    if (dailySalesChart) {
        dailySalesChart.destroy();
    }
    
    const ctx = document.getElementById('dailySalesChart').getContext('2d');
    dailySalesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [
                {
                    label: 'Valor Total (R$)',
                    data: amounts,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1,
                    yAxisID: 'y'
                },
                {
                    label: 'Número de Vendas',
                    data: orders,
                    borderColor: 'rgb(54, 162, 235)',
                    tension: 0.1,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Valor Total (R$)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Número de Vendas'
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });
}

function updateTopProductsChart(products) {
    const names = products.map(product => product.name);
    const quantities = products.map(product => parseInt(product.total_quantity));
    
    if (topProductsChart) {
        topProductsChart.destroy();
    }
    
    const ctx = document.getElementById('topProductsChart').getContext('2d');
    topProductsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: names,
            datasets: [{
                label: 'Quantidade Vendida',
                data: quantities,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.5)',
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(255, 206, 86, 0.5)',
                    'rgba(75, 192, 192, 0.5)',
                    'rgba(153, 102, 255, 0.5)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Quantidade'
                    }
                }
            }
        }
    });
}

function showAlert(message, type) {
    const alertDiv = $('<div>')
        .addClass(`alert alert-${type} alert-dismissible fade show`)
        .attr('role', 'alert')
        .html(`
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `);
    
    $('.container-fluid').prepend(alertDiv);
    
    setTimeout(() => {
        alertDiv.alert('close');
    }, 5000);
}
