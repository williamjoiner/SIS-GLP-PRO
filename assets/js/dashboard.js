$(document).ready(function() {
    // Load dashboard statistics
    loadDashboardStats();
    // Load recent orders
    loadRecentOrders();
    
    // Refresh data every 5 minutes
    setInterval(function() {
        loadDashboardStats();
        loadRecentOrders();
    }, 300000);
});

function loadDashboardStats() {
    $.ajax({
        url: 'api/dashboard_stats.php',
        method: 'GET',
        success: function(response) {
            $('#today-sales').text(response.today_sales);
            $('#pending-orders').text(response.pending_orders);
            $('#total-clients').text(response.total_clients);
            $('#low-stock').text(response.low_stock);
        },
        error: function(xhr, status, error) {
            console.error('Error loading dashboard stats:', error);
        }
    });
}

function loadRecentOrders() {
    $.ajax({
        url: 'api/recent_orders.php',
        method: 'GET',
        success: function(response) {
            const ordersHtml = response.orders.map(order => `
                <tr>
                    <td>#${order.id}</td>
                    <td>${order.client_name}</td>
                    <td>R$ ${order.total_amount}</td>
                    <td><span class="badge bg-${getStatusColor(order.status)}">${order.status}</span></td>
                    <td>${formatDate(order.created_at)}</td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="viewOrder(${order.id})">
                            <i class='bx bx-show'></i>
                        </button>
                        <button class="btn btn-sm btn-primary" onclick="updateOrderStatus(${order.id})">
                            <i class='bx bx-edit'></i>
                        </button>
                    </td>
                </tr>
            `).join('');
            
            $('#recent-orders').html(ordersHtml);
        },
        error: function(xhr, status, error) {
            console.error('Error loading recent orders:', error);
        }
    });
}

function getStatusColor(status) {
    const colors = {
        'pending': 'warning',
        'processing': 'primary',
        'delivered': 'success',
        'cancelled': 'danger'
    };
    return colors[status] || 'secondary';
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

function viewOrder(orderId) {
    window.location.href = `modules/orders/view.php?id=${orderId}`;
}

function updateOrderStatus(orderId) {
    window.location.href = `modules/orders/edit.php?id=${orderId}`;
}
