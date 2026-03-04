@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-4">
        <div class="dashboard-card">
            <div class="card-header">30-Day Revenue</div>
            <div class="card-content">
                ${{ number_format($salesData['total_revenue_30d'], 2) }}
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dashboard-card">
            <div class="card-header">30-Day Orders</div>
            <div class="card-content">
                {{ number_format($salesData['total_orders_30d']) }}
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dashboard-card">
            <div class="card-header">Avg Order Value</div>
            <div class="card-content">
                ${{ number_format($salesData['avg_order_value'], 2) }}
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="dashboard-card" style="height: 400px;">
            <div class="card-header">Sales Overview</div>
            <div class="card-content" style="padding: 20px;">
                <canvas id="salesChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="dashboard-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Recent Sales</span>
                <div class="d-flex gap-2">
                    <input type="text" class="form-control form-control-sm" placeholder="Search..." id="sales-search" style="width: 200px;">
                    <select class="form-control form-control-sm" id="category-filter" style="width: 150px;">
                        <option value="">All Categories</option>
                        <option value="t-shirt">T-Shirts</option>
                        <option value="hoodie">Hoodies</option>
                        <option value="tank">Tank Tops</option>
                    </select>
                </div>
            </div>
            <div class="card-content" style="padding: 0; height: auto;">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="sales-table">
                        <thead>
                            <tr>
                                <th>Order ID <i class="fas fa-sort sort-icon" data-column="id"></i></th>
                                <th>Date <i class="fas fa-sort sort-icon" data-column="date"></i></th>
                                <th>Customer <i class="fas fa-sort sort-icon" data-column="customer"></i></th>
                                <th>Product <i class="fas fa-sort sort-icon" data-column="product"></i></th>
                                <th>Amount <i class="fas fa-sort sort-icon" data-column="amount"></i></th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentSales as $sale)
                            <tr>
                                <td>#{{ $sale['id'] }}</td>
                                <td>{{ $sale['date'] }}</td>
                                <td>{{ $sale['customer'] }}</td>
                                <td>{{ $sale['product'] }}</td>
                                <td>{{ $sale['amount'] }}</td>
                                <td>
                                    <span class="badge badge-success">{{ $sale['status'] }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Sales Chart
const ctx = document.getElementById('salesChart').getContext('2d');
const salesChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json($salesData['labels']),
        datasets: [{
            label: 'Revenue ($)',
            data: @json($salesData['revenue']),
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            tension: 0.4,
            yAxisID: 'y'
        }, {
            label: 'Orders',
            data: @json($salesData['orders']),
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            tension: 0.4,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            x: {
                display: true,
                title: {
                    display: true,
                    text: 'Date'
                }
            },
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Revenue ($)'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Orders'
                },
                grid: {
                    drawOnChartArea: false,
                }
            }
        }
    }
});

// Table sorting and filtering
let sortOrder = {};
let salesData = @json($recentSales);

function sortTable(column, data) {
    const isAsc = sortOrder[column] !== 'asc';
    sortOrder[column] = isAsc ? 'asc' : 'desc';
    
    return data.sort((a, b) => {
        let aVal = a[column];
        let bVal = b[column];
        
        // Handle different data types
        if (column === 'amount') {
            aVal = parseFloat(aVal.replace('$', '').replace(',', ''));
            bVal = parseFloat(bVal.replace('$', '').replace(',', ''));
        } else if (column === 'id') {
            aVal = parseInt(aVal);
            bVal = parseInt(bVal);
        }
        
        if (aVal < bVal) return isAsc ? -1 : 1;
        if (aVal > bVal) return isAsc ? 1 : -1;
        return 0;
    });
}

function renderTable(data) {
    const tbody = document.querySelector('#sales-table tbody');
    tbody.innerHTML = data.map(sale => `
        <tr>
            <td>#${sale.id}</td>
            <td>${sale.date}</td>
            <td>${sale.customer}</td>
            <td>${sale.product}</td>
            <td>${sale.amount}</td>
            <td><span class="badge badge-success">${sale.status}</span></td>
        </tr>
    `).join('');
}

// Sort functionality
document.querySelectorAll('.sort-icon').forEach(icon => {
    icon.addEventListener('click', function() {
        const column = this.getAttribute('data-column');
        const sortedData = sortTable(column, [...salesData]);
        renderTable(sortedData);
        
        // Update sort icons
        document.querySelectorAll('.sort-icon').forEach(i => {
            i.className = 'fas fa-sort sort-icon';
        });
        this.className = `fas fa-sort-${sortOrder[column] === 'asc' ? 'up' : 'down'} sort-icon`;
    });
});

// Search functionality
document.getElementById('sales-search').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const categoryFilter = document.getElementById('category-filter').value.toLowerCase();
    
    let filteredData = salesData.filter(sale => {
        const matchesSearch = Object.values(sale).some(value => 
            value.toString().toLowerCase().includes(searchTerm)
        );
        const matchesCategory = !categoryFilter || 
            sale.product.toLowerCase().includes(categoryFilter);
        
        return matchesSearch && matchesCategory;
    });
    
    renderTable(filteredData);
});

// Category filter
document.getElementById('category-filter').addEventListener('change', function() {
    const searchTerm = document.getElementById('sales-search').value.toLowerCase();
    const categoryFilter = this.value.toLowerCase();
    
    let filteredData = salesData.filter(sale => {
        const matchesSearch = !searchTerm || Object.values(sale).some(value => 
            value.toString().toLowerCase().includes(searchTerm)
        );
        const matchesCategory = !categoryFilter || 
            sale.product.toLowerCase().includes(categoryFilter);
        
        return matchesSearch && matchesCategory;
    });
    
    renderTable(filteredData);
});
</script>
@endsection