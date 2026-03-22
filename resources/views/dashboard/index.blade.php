@extends('layouts.app')

@section('content')
<div class="dashboard-cards">
    <!-- Weekly/Monthly Sales Card -->
    <div class="dashboard-card">
        <div class="card-header" id="sales-header" onclick="toggleSalesPeriod()">
            <span id="sales-period">Weekly</span> Sales
        </div>
        <div class="card-content" id="sales-content" onclick="toggleSalesMetric()">
            <div id="sales-value">${{ number_format($salesData['weekly_sales'], 2) }}</div>
            <small id="sales-type" style="display: block; font-size: 0.8rem; color: #666;">Revenue</small>
        </div>
    </div>

    <!-- Total Order Count Card -->
    <div class="dashboard-card">
        <div class="card-header">Total Order Count</div>
        <div class="card-content">
            {{ number_format($salesData['total_orders']) }}
        </div>
    </div>

    <!-- Popular Products Card -->
    <div class="dashboard-card">
        <div class="card-header">Popular Products</div>
        <div class="card-content">
            <div class="popular-products">
                @foreach($salesData['popular_products'] as $product)
                <div class="product-thumb">
                    <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}">
                    <div class="product-name">{{ Str::limit($product['name'], 10) }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Quote of the Day Card -->
    <div class="dashboard-card">
        <div class="card-header">Quote of the Day</div>
        <div class="card-content quote-content">
            @if($dailyQuote)
                <div style="font-style: italic; margin-bottom: 10px;">
                    "{{ $dailyQuote->quote }}"
                </div>
                @if($dailyQuote->author)
                <div style="font-size: 0.8em; color: #666;">
                    - {{ $dailyQuote->author }}
                </div>
                @endif
            @else
                <div style="color: #666;">
                    No quote available today. Check back tomorrow!
                </div>
            @endif
        </div>
    </div>
</div>

<script>
let isWeekly = true;
let isRevenue = true;
const salesData = @json($salesData);

function toggleSalesPeriod() {
    const header = document.getElementById('sales-period');
    const content = document.getElementById('sales-value');
    
    if (isWeekly) {
        header.textContent = 'Monthly';
        content.textContent = isRevenue ? 
            '$' + parseFloat(salesData.monthly_sales).toLocaleString('en-US', {minimumFractionDigits: 2}) :
            salesData.monthly_orders.toLocaleString();
        isWeekly = false;
    } else {
        header.textContent = 'Weekly';
        content.textContent = isRevenue ? 
            '$' + parseFloat(salesData.weekly_sales).toLocaleString('en-US', {minimumFractionDigits: 2}) :
            salesData.weekly_orders.toLocaleString();
        isWeekly = true;
    }
}

function toggleSalesMetric() {
    const content = document.getElementById('sales-value');
    const type = document.getElementById('sales-type');
    
    if (isRevenue) {
        content.textContent = isWeekly ? 
            salesData.weekly_orders.toLocaleString() : 
            salesData.monthly_orders.toLocaleString();
        type.textContent = 'Orders';
        isRevenue = false;
    } else {
        content.textContent = isWeekly ? 
            '$' + parseFloat(salesData.weekly_sales).toLocaleString('en-US', {minimumFractionDigits: 2}) :
            '$' + parseFloat(salesData.monthly_sales).toLocaleString('en-US', {minimumFractionDigits: 2});
        type.textContent = 'Revenue';
        isRevenue = true;
    }
}
</script>
@endsection