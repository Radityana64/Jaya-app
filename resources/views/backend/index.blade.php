@extends('backend.layouts.master')
@section('title','E-SHOP || DASHBOARD')
@section('main-content')
      <div class="container-fluid">
        <!-- Stats Cards -->
        <div class="stats-grid">
            <!-- Top Customers Card -->
            <div class="stat-card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-users"></i> Top Customers
                    </h6>
                    <select id="customerFilter" class="filter-select">
                        <option value="total">Total Spent</option>
                        <option value="quantity">Items Bought</option>
                    </select>
                </div>
                <div id="topCustomersContent"></div>
            </div>

            <!-- Best Products Card -->
            <div class="stat-card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Best Selling Products
                    </h6>
                </div>
                <div id="bestProductsContent"></div>
            </div>
        </div>

        <!-- Order Status Cards -->
        <div class="order-cards">
            <div class="order-card pending-card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-clock"></i> Pending Payment
                    </h6>
                </div>
                <div id="pendingPaymentContent" class="scrollable-content"></div>
            </div>

            <div class="order-card packaging-card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-box"></i> Packaging
                    </h6>
                </div>
                <div id="packagingContent" class="scrollable-content"></div>
            </div>

            <div class="order-card shipped-card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold" style="color: #6A0DAD;">
                        <i class="fas fa-truck"></i> Shipped
                    </h6>
                </div>
                <div id="shippedContent" class="scrollable-content"></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    function getJwtToken() {
        return document.querySelector('meta[name="api-token"]').getAttribute('content');
    }

    function getApiBaseUrl(){
        return document.querySelector('meta[name="api-base-url"]').getAttribute('content');
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR'
        }).format(amount);
    }

    function formatWhatsAppLink(phone) {
        const cleanPhone = phone.replace(/\D/g, '');
        return `https://wa.me/${cleanPhone}`;
    }

    function getTopCustomers(data, filterType = 'total') {
        const completedOrders = data.filter(order => 
            order.status_pemesanan === 'Pesanan_Diterima'
        );

        const customerTotals = completedOrders.reduce((acc, order) => {
            const customer = order.nama_pelanggan;
            if (!acc[customer]) {
                acc[customer] = {
                    total: 0,
                    quantity: 0,
                    phone: order.no_telepon
                };
            }
            acc[customer].total += order.pembayaran.total_pembayaran;
            acc[customer].quantity += order.detail_pemesanan.reduce((sum, detail) => 
                sum + detail.jumlah, 0
            );
            return acc;
        }, {});

        return Object.entries(customerTotals)
            .sort(([, a], [, b]) => 
                filterType === 'total' ? b.total - a.total : b.quantity - a.quantity
            )
            .slice(0, 5) // Limit to top 5 customers
            .map(([name, data]) => ({
                name,
                ...data
            }));
    }

    function getBestSellingProducts(data) {
        const completedOrders = data.filter(order => 
            order.status_pemesanan === 'Pesanan_Diterima'
        );

        // Count by product name only (ignoring variants)
        const productTotals = completedOrders.flatMap(order => 
            order.detail_pemesanan
        ).reduce((acc, detail) => {
            const productName = detail.produk_variasi.nama_produk;
            if (!acc[productName]) {
                acc[productName] = {
                    name: productName,
                    quantity: 0
                };
            }
            acc[productName].quantity += detail.jumlah;
            return acc;
        }, {});

        return Object.values(productTotals)
            .sort((a, b) => b.quantity - a.quantity)
            .slice(0, 5); // Limit to top 5 products
    }

    function renderOrderCard(orders, containerId, cardType) {
        const container = document.getElementById(containerId);
        // Limit to 10 items, scrolling will handle the rest
        container.innerHTML = orders.map(order => `
            <div class="order-item ${cardType}-item">
                <div class="customer-info">
                    <span class="font-weight-bold">${order.nama_pelanggan}</span>
                    <a href="${formatWhatsAppLink(order.no_telepon)}" 
                       class="whatsapp-link" 
                       target="_blank">
                        <i class="fab fa-whatsapp"></i> +${order.no_telepon}
                    </a>
                </div>
                ${order.detail_pemesanan.map(detail => `
                    <div class="product-name">- ${detail.produk_variasi.nama_produk}</div>
                `).join('')}
                <div class="total-payment">
                    ${formatCurrency(order.pembayaran.total_pembayaran)}
                </div>
            </div>
        `).join('');
    }

    async function initDashboard() {
        try {
            const response = await fetch(`${getApiBaseUrl()}/api/pemesanan/data/ringkasan`, {
              headers: {
                  'X-CSRF-TOKEN': getCsrfToken(),
                  'Authorization': `Bearer ${getJwtToken()}`
              },
            });
            const { data } = await response.json();

            // Render top customers (limited to 5)
            const customerFilter = document.getElementById('customerFilter');
            function updateTopCustomers() {
                const topCustomers = getTopCustomers(data, customerFilter.value);
                document.getElementById('topCustomersContent').innerHTML = topCustomers
                    .map(customer => `
                        <div class="stat-item">
                            <div>
                                <div class="font-weight-bold">${customer.name}</div>
                                <a href="${formatWhatsAppLink(customer.phone)}" 
                                   class="whatsapp-link" 
                                   target="_blank">
                                    <i class="fab fa-whatsapp"></i> +${customer.phone}
                                </a>
                            </div>
                            <div class="text-right font-weight-bold">
                                ${customerFilter.value === 'total' 
                                    ? formatCurrency(customer.total)
                                    : `${customer.quantity} items`}
                            </div>
                        </div>
                    `).join('');
            }
            customerFilter.addEventListener('change', updateTopCustomers);
            updateTopCustomers();

            // Render best selling products (limited to 5, without variants)
            const bestProducts = getBestSellingProducts(data);
            document.getElementById('bestProductsContent').innerHTML = bestProducts
                .map(product => `
                    <div class="stat-item">
                        <div>
                            <div class="font-weight-bold">${product.name}</div>
                        </div>
                        <div class="font-weight-bold">
                            ${product.quantity} units
                        </div>
                    </div>
                `).join('');

            // Render order cards (with scrolling)
            renderOrderCard(
                data.filter(order => order.pembayaran.status_pembayaran === 'Pending'),
                'pendingPaymentContent',
                'pending'
            );
            renderOrderCard(
                data.filter(order => order.pengiriman.status_pengiriman === 'Dikemas' && order.status_pemesanan !== 'Pesanan_Dibatalkan'),
                'packagingContent',
                'packaging'
            );
            renderOrderCard(
                data.filter(order => order.pengiriman.status_pengiriman === 'Dikirim' && order.status_pemesanan !== 'Pesanan_Dibatalkan'),
                'shippedContent',
                'shipped'
            );
        } catch (error) {
            console.error('Error fetching data:', error);
        }
    }

    document.addEventListener('DOMContentLoaded', initDashboard);
</script>
@endpush

@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<style>
    .scrollable-content {
        max-height: 600px;
        overflow-y: auto;
    }
    .order-item {
        padding: 10px;
        border-bottom: 1px solid #eee;
    }
    .order-item .customer-info {
        margin-bottom: 5px;
    }
    .order-item .total-payment {
        font-weight: bold;
        margin-top: 2px;
    }
    .order-cards {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 20px;
    }
    .order-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        padding: 15px;
    }
    .pending-card {
        border-left: 4px solid #FBC02D;
    }
    .packaging-card {
        border-left: 4px solid #1976D2;
    }
    .shipped-card {
        border-left: 4px solid #6A0DAD;
    }
    .order-item {
        border: 1px solid #eee;
        border-radius: 6px;
        padding: 10px;
        margin-bottom: 8px;
    }
    .pending-item {
        background-color: #fff9e6;
    }
    .packaging-item {
        background-color: #e8f7fa;
    }
    .shipped-item {
        background-color: #e8ecf9;
    }
    .customer-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 5px;
    }
    .whatsapp-link {
        color: #388E3C;
        text-decoration: none;
        font-size: 0.9em;
    }
    .product-name {
        font-size: 0.9em;
        margin-bottom: 5px;
    }
    .total-payment {
        text-align: right;
        font-weight: bold;
        font-size: 0.9em;
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 20px;
    }
    .stat-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        padding: 15px;
    }
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    .stat-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px;
        border-bottom: 1px solid #eee;
    }
    .stat-item:last-child {
        border-bottom: none;
    }
</style>
@endpush