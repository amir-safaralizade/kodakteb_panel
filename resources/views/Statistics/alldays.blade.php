@extends('layouts.dashbord')
@section('header_title', 'گزارش عملکرد')
@section('content')
    <h1 class="page-title">گزارش عملکرد مطب</h1>
    <section class="stats-grid report-stats" aria-label="خلاصه گزارش">
        <article class="stat-card"><div><p>درآمد کل</p><strong>{{ number_format($total_income / 10) }}</strong><small>تومان</small></div></article>
        <article class="stat-card"><div><p>مجموع مراجعات</p><strong>{{ number_format($total_count) }}</strong><small>ویزیت</small></div></article>
        <article class="stat-card"><div><p>روزهای گزارش</p><strong>{{ count($labels) }}</strong><small>روز</small></div></article>
        <article class="stat-card"><div><p>روزهای بدون درآمد</p><strong>{{ collect($data)->filter(fn ($value) => $value == 0)->count() }}</strong><small>روز</small></div></article>
    </section>
    <section class="chart-grid" aria-label="نمودارهای عملکرد">
        <article class="chart-card"><h2>روند درآمد</h2><canvas id="incomeChart"></canvas></article>
        <article class="chart-card"><h2>روند مراجعه بیماران</h2><canvas id="countChart"></canvas></article>
        <article class="chart-card"><h2>بیمه مراجعه‌کنندگان</h2><canvas id="insuranceBar"></canvas></article>
    </section>

<script>
    // Function: calculate linear regression (y = a + b*x)
    function linearRegression(y) {
        if (y.length < 2) return y.slice();
        const x = y.map((_, i) => i + 1);
        const n = y.length;
        const sumX = x.reduce((a, b) => a + b, 0);
        const sumY = y.reduce((a, b) => a + b, 0);
        const sumXY = x.reduce((acc, val, i) => acc + val * y[i], 0);
        const sumX2 = x.reduce((acc, val) => acc + val * val, 0);

        const b = (n * sumXY - sumX * sumY) / (n * sumX2 - sumX * sumX);
        const a = (sumY - b * sumX) / n;

        return x.map(xi => a + b * xi);
    }

    // === Income chart ===
    var labels = @json($labels);  
    var incomeData = @json($data);     
    var incomeTrend = linearRegression(incomeData);

    var ctx = document.getElementById('incomeChart').getContext('2d');
    var incomeChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'درآمد',
                    data: incomeData,
                    fill: false,
                    borderColor: '#12b886',
                    tension: 0.1
                },
                // trend line must be last -> appears on top
                {
                    label: 'خط برآیند',
                    data: incomeTrend,
                    fill: false,
                    borderColor: 'red',
                    borderDash: [5,5],
                    borderWidth: 2,
                    pointRadius: 0,
                    tension: 0
                }
            ]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>

<script>
    // === Count chart ===
    var labels = @json($labels);  
    var countData = @json($count_data);     
    var countTrend = linearRegression(countData);

    var ctx = document.getElementById('countChart').getContext('2d');
    var countChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'تعداد مراجعه کنندگان',
                    data: countData,
                    fill: false,
                    borderColor: '#3bc9db',
                    tension: 0.2
                },
                // trend line last -> appears on top
                {
                    label: 'خط برآیند',
                    data: countTrend,
                    fill: false,
                    borderColor: 'orange',
                    borderDash: [5,5],
                    borderWidth: 2,
                    pointRadius: 0,
                    tension: 0
                }
            ]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>

<script>
    // === Insurance chart ===
    var labels = @json($insurance_list);  
    var data = @json($insurance_list_count);     
    var insurance_mainColorList = @json($insurance_mainColorList);     
    var insurance_borderList = @json($insurance_borderList);     

    var ctx = document.getElementById('insuranceBar').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'بیمه مراجعه کنندگان',
                data: data,
                backgroundColor: insurance_mainColorList,
                borderColor: insurance_borderList,
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>

@endsection
