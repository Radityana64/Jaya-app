@extends('backend.layouts.master')

@section('title', 'E-SHOP || Sales Report')

@section('main-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Grafik Penjualan</h3>
                </div>
                <div class="card-body">
                    <!-- Filter Controls -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Pilih Periode:</label>
                                <select class="form-control" id="periodeSelect">
                                    <option value="bulanan">Bulanan</option>
                                    <option value="tahunan">Tahunan</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Bulanan Controls - Show when periodeSelect is "bulanan" -->
                        <div class="col-md-4" id="bulanControlContainer">
                            <div class="form-group">
                                <label>Bulan:</label>
                                <select class="form-control" id="bulanSelect">
                                    <option value="1">Januari</option>
                                    <option value="2">Februari</option>
                                    <option value="3">Maret</option>
                                    <option value="4">April</option>
                                    <option value="5">Mei</option>
                                    <option value="6">Juni</option>
                                    <option value="7">Juli</option>
                                    <option value="8">Agustus</option>
                                    <option value="9">September</option>
                                    <option value="10">Oktober</option>
                                    <option value="11">November</option>
                                    <option value="12">Desember</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4" id="tahunControlContainer">
                            <div class="form-group">
                                <label>Tahun:</label>
                                <select class="form-control" id="tahunSelect">
                                    <!-- Dynamically generate last 5 years -->
                                    <script>
                                        const currentYear = new Date().getFullYear();
                                        for (let year = currentYear; year >= currentYear - 5; year--) {
                                            document.write(`<option value="${year}">${year}</option>`);
                                        }
                                    </script>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-12 text-right mt-3">
                            <button class="btn btn-primary" id="showReportButton">Tampilkan Laporan</button>
                        </div>
                    </div>
                    
                    <!-- Enhanced Nav tabs for switching between charts -->
                    <div class="chart-tabs-container mb-3">
                        <ul class="nav nav-pills nav-fill chart-tabs" id="chartTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active d-flex flex-column align-items-center justify-content-center py-3" id="pendapatan-tab" data-toggle="tab" href="#pendapatanChart" role="tab">
                                    <i class="fas fa-money-bill-wave mb-2" style="font-size: 1.5rem;"></i>
                                    <span>Total Pendapatan</span>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link d-flex flex-column align-items-center justify-content-center py-3" id="produk-tab" data-toggle="tab" href="#produkChart" role="tab">
                                    <i class="fas fa-shopping-cart mb-2" style="font-size: 1.5rem;"></i>
                                    <span>Jumlah Produk Terjual</span>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link d-flex flex-column align-items-center justify-content-center py-3" id="laba-tab" data-toggle="tab" href="#labaChart" role="tab">
                                    <i class="fas fa-chart-line mb-2" style="font-size: 1.5rem;"></i>
                                    <span>Laba</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Tab content for charts -->
                    <div class="tab-content mt-3" id="chartTabContent">
                        <div class="tab-pane fade show active" id="pendapatanChart" role="tabpanel">
                            <div class="chart-container" style="position: relative; height: 400px; width: 100%; margin: 0 auto;">
                                <canvas id="totalPendapatanChart"></canvas>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="produkChart" role="tabpanel">
                            <div class="chart-container" style="position: relative; height: 400px; width: 100%; margin: 0 auto;">
                                <canvas id="jumlahProdukChart"></canvas>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="labaChart" role="tabpanel">
                            <div class="chart-container" style="position: relative; height: 400px; width: 100%; margin: 0 auto;">
                                <canvas id="LabaChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Chart size controls -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="chartHeight">Tinggi Grafik (px):</label>
                                <input type="range" class="form-control-range" id="chartHeight" min="200" max="800" value="400" step="50">
                                <small class="form-text text-muted" id="heightValue">400px</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="chartWidth">Lebar Grafik (%):</label>
                                <input type="range" class="form-control-range" id="chartWidth" min="50" max="100" value="100" step="5">
                                <small class="form-text text-muted" id="widthValue">100%</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
    /* Enhanced Tab Styling */
    .chart-tabs-container {
        background-color: #f8f9fc;
        border-radius: 0.5rem;
        padding: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .chart-tabs .nav-link {
        color: #5a5c69;
        border-radius: 0.5rem;
        margin: 0 0.25rem;
        transition: all 0.2s;
    }
    
    .chart-tabs .nav-link:hover {
        background-color: #eaecf4;
        transform: translateY(-2px);
    }
    
    .chart-tabs .nav-link.active {
        background-color: #4e73df;
        color: white;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }
    
    .chart-container {
        transition: height 0.3s ease, width 0.3s ease;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .chart-tabs .nav-link {
            padding: 0.5rem 0.25rem;
        }
        
        .chart-tabs .nav-link i {
            font-size: 1rem !important;
        }
    }
</style>
@endpush

@push('scripts')
<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<!-- Include Font Awesome if not already included in master layout -->
<script>
    function getJwtToken() {
        return $('meta[name="api-token"]').attr('content');
    }
    function getApiBaseUrl(){
        return document.querySelector('meta[name="api-base-url"]').getAttribute('content');
    }
    // Chart instances
    let pendapatanChart = null;
    let produkChart = null;
    let labaChart = null;
    
    // Function to handle display toggling based on selected period
    function togglePeriodControls() {
        const selectedPeriode = $("#periodeSelect").val();
        
        if (selectedPeriode === "bulanan") {
            $("#bulanControlContainer").show();
        } else {
            $("#bulanControlContainer").hide();
        }
    }
    
    // Function to format currency
    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', { 
            style: 'currency', 
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(angka);
    }
    
    // Function to create or update chart
    function createOrUpdateChart(chartInstance, ctx, labels, data, label, color, yAxisFormat = 'number') {
        const chartConfig = {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: label,
                    data: data,
                    backgroundColor: color + '33', // With transparency
                    borderColor: color,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: color,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (yAxisFormat === 'currency') {
                                    label += formatRupiah(context.parsed.y);
                                } else {
                                    label += context.parsed.y;
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (yAxisFormat === 'currency') {
                                    // Simplify large numbers in the Y-axis
                                    if (value >= 1000000) {
                                        return 'Rp ' + (value / 1000000).toFixed(1) + ' Juta';
                                    } else if (value >= 1000) {
                                        return 'Rp ' + (value / 1000).toFixed(1) + ' Ribu';
                                    }
                                    return 'Rp ' + value;
                                }
                                return value;
                            }
                        }
                    }
                }
            }
        };

        // If chart instance exists, destroy it before creating a new one
        if (chartInstance) {
            chartInstance.destroy();
        }
        
        // Create new chart
        return new Chart(ctx, chartConfig);
    }
    
    // Function to update chart dimensions
    function updateChartDimensions() {
        const height = $("#chartHeight").val();
        const width = $("#widthWidth").val() || 100;
        
        // Update display values
        $("#heightValue").text(height + 'px');
        $("#widthValue").text(width + '%');
        
        // Apply to all chart containers
        $(".chart-container").css({
            'height': height + 'px',
            'width': width + '%'
        });
        
        // Force charts to resize if they exist
        if (pendapatanChart) pendapatanChart.resize();
        if (produkChart) produkChart.resize();
        if (labaChart) labaChart.resize();
    }
    
    // Function to fetch data and update charts
    function fetchDataAndUpdateCharts() {
        const periode = $("#periodeSelect").val();
        const tahun = $("#tahunSelect").val();
        const bulan = $("#bulanSelect").val();
        
        let apiUrl = '';
        let requestData = {};
        
        if (periode === "bulanan") {
            apiUrl = `${getApiBaseUrl()}/api/laporan/penjualan/bulanan`;
            requestData = {
                tahun: tahun,
                bulan: bulan
            };
        } else {
            apiUrl = `${getApiBaseUrl()}/api/laporan/penjualan/tahunan`;
            requestData = {
                tahun: tahun
            };
        }
        // Show loading indicator
        $(".chart-container").addClass("loading");
        
        $.ajax({
            url: apiUrl,
            type: 'POST',
            data: JSON.stringify(requestData),
            contentType: 'application/json',
            headers: {
                'Authorization': 'Bearer ' + getJwtToken()
            },
            success: function(response) {
                // Hide loading indicator
                $(".chart-container").removeClass("loading");
                
                if (response.status) {
                    const data = response.data;
                    
                    // Prepare data arrays
                    let labels = [];
                    let pendapatanData = [];
                    let produkData = [];
                    let labaData = [];
                    
                    // Process data based on period type
                    if (periode === "bulanan") {
                        // For monthly data
                        data.forEach(item => {
                            // Extract day from date (e.g. "2024-12-01" -> "01")
                            const day = item.tanggal.split('-')[2];
                            labels.push(day);
                            pendapatanData.push(item.total_pendapatan);
                            produkData.push(item.jumlah_produk_terjual);
                            labaData.push(item.laba);
                        });
                    } else {
                        // For yearly data
                        data.forEach(item => {
                            labels.push(item.bulan);
                            pendapatanData.push(item.total_pendapatan);
                            produkData.push(item.jumlah_produk_terjual);
                            labaData.push(item.laba);
                        });
                    }
                    
                    // Create or update charts
                    const pendapatanCtx = document.getElementById('totalPendapatanChart').getContext('2d');
                    pendapatanChart = createOrUpdateChart(
                        pendapatanChart, 
                        pendapatanCtx, 
                        labels, 
                        pendapatanData, 
                        'Total Pendapatan', 
                        '#4e73df',
                        'currency'
                    );
                    
                    const produkCtx = document.getElementById('jumlahProdukChart').getContext('2d');
                    produkChart = createOrUpdateChart(
                        produkChart, 
                        produkCtx, 
                        labels, 
                        produkData, 
                        'Jumlah Produk Terjual', 
                        '#1cc88a'
                    );
                    
                    const labaCtx = document.getElementById('LabaChart').getContext('2d');
                    labaChart = createOrUpdateChart(
                        labaChart, 
                        labaCtx, 
                        labels, 
                        labaData, 
                        'Laba', 
                        '#f6c23e',
                        'currency'
                    );
                } else {
                    Swal.fire({
                        title: "Error!",
                        text: response.message,
                        icon: "error",
                        confirmButtonText: "OK"
                    });
                }
            },
            error: function(xhr, status, error) {
                // Hide loading indicator
                $(".chart-container").removeClass("loading");
                
                console.error('API Error:', error);
                Swal.fire({
                    title: "Gagal mengambil data",
                    text: "Terjadi kesalahan saat mengambil data. Silakan coba lagi.",
                    icon: "error",
                    confirmButtonText: "OK"
                });
            }
        });
    }
    
    // Document ready
    $(document).ready(function() {
        // Initial setup
        togglePeriodControls();
        
        // Event handler for period change
        $("#periodeSelect").change(function() {
            togglePeriodControls();
        });
        
        // Event handler for show report button
        $("#showReportButton").click(function() {
            fetchDataAndUpdateCharts();
        });
        
        // Event handlers for chart dimension changes
        $("#chartHeight").on('input', function() {
            const height = $(this).val();
            $("#heightValue").text(height + 'px');
            $(".chart-container").css('height', height + 'px');
        });
        
        $("#chartWidth").on('input', function() {
            const width = $(this).val();
            $("#widthValue").text(width + '%');
            $(".chart-container").css('width', width + '%');
        });
        
        // Initialize with current month/year data
        fetchDataAndUpdateCharts();
        
        // Add loading spinner style
        $("<style>")
            .text(`
                .chart-container.loading {
                    position: relative;
                    min-height: 200px;
                }
                .chart-container.loading:after {
                    content: '';
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    width: 40px;
                    height: 40px;
                    margin-top: -20px;
                    margin-left: -20px;
                    border: 4px solid #f3f3f3;
                    border-top: 4px solid #4e73df;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                }
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            `)
            .appendTo("head");
    });
</script>
@endpush