@extends('layouts.admin.app')

@section('title', 'Overview')

@section('content')
<div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
  <div>
    <h3 class="fw-bold mb-3">Dashboard</h3>
    <h6 class="op-7 mb-2">Admin Dashboard Overview</h6>
  </div>
  <div class="ms-md-auto py-2 py-md-0">
    <a href="#" class="btn btn-label-info rounded-pill me-2">Manage</a>
    <a href="#" class="btn btn-primary rounded-pill">Add Customer</a>
  </div>
</div>

<!-- Statistics Cards -->
<div class="row">
  <div class="col-sm-6 col-md-3">
    <div class="card card-stats rounded-3 shadow-sm border-0">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-icon">
            <div class="icon-big text-center icon-primary bubble-shadow-small">
              <i class="fas fa-users text-white"></i>
            </div>
          </div>
          <div class="col col-stats ms-3 ms-sm-0">
            <div class="numbers">
              <p class="card-category">Visitors</p>
              <h4 class="card-title">1,294</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-md-3">
    <div class="card card-stats rounded-3 shadow-sm border-0">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-icon">
            <div class="icon-big text-center icon-info bubble-shadow-small">
              <i class="fas fa-user-check text-white"></i>
            </div>
          </div>
          <div class="col col-stats ms-3 ms-sm-0">
            <div class="numbers">
              <p class="card-category">Subscribers</p>
              <h4 class="card-title">1303</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-md-3">
    <div class="card card-stats rounded-3 shadow-sm border-0">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-icon">
            <div class="icon-big text-center icon-success bubble-shadow-small">
              <i class="fas fa-luggage-cart text-white"></i>
            </div>
          </div>
          <div class="col col-stats ms-3 ms-sm-0">
            <div class="numbers">
              <p class="card-category">Sales</p>
              <h4 class="card-title">$ 1,345</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-md-3">
    <div class="card card-stats rounded-3 shadow-sm border-0">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-icon">
            <div class="icon-big text-center icon-secondary bubble-shadow-small">
              <i class="far fa-check-circle text-white"></i>
            </div>
          </div>
          <div class="col col-stats ms-3 ms-sm-0">
            <div class="numbers">
              <p class="card-category">Order</p>
              <h4 class="card-title">576</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Charts Row -->
<div class="row">
  <div class="col-md-8">
    <div class="card rounded-3 shadow-sm border-0">
      <div class="card-header">
        <div class="card-head-row">
          <div class="card-title">User Statistics</div>
          <div class="card-tools">
            <a href="#" class="btn btn-success rounded-pill btn-sm me-2">
              <span class="btn-label">
                <i class="fa fa-pencil"></i>
              </span>
              Export
            </a>
            <a href="#" class="btn btn-info rounded-pill btn-sm">
              <span class="btn-label">
                <i class="fa fa-print"></i>
              </span>
              Print
            </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="chart-container" style="min-height: 375px">
          <canvas id="statisticsChart"></canvas>
        </div>
        <div id="myChartLegend"></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card card-primary rounded-3 shadow-sm border-0">
      <div class="card-header">
        <div class="card-head-row">
          <div class="card-title">Daily Sales</div>
          <div class="card-tools">
            <div class="dropdown">
              <button class="btn btn-success dropdown-toggle btn" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Export
              </button>
              <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <a class="dropdown-item" href="#">Action</a>
                <a class="dropdown-item" href="#">Another action</a>
              </div>
            </div>
          </div>
        </div>
        <div class="card-category">March 25 - April 02</div>
      </div>
      <div class="card-body pb-0">
        <div class="mb-4 mt-2">
          <h1>$4,578.58</h1>
        </div>
        <div class="pull-in">
          <canvas id="dailySalesChart"></canvas>
        </div>
      </div>
    </div>
    <div class="card rounded-3 shadow-sm border-0">
      <div class="card-body pb-0">
        <div class="h1 fw-bold float-end text-primary">+5%</div>
        <h2 class="mb-2">17</h2>
        <p class="text-muted">Users online</p>
        <div class="pull-in sparkline-fix">
          <div id="lineChart"></div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
  <!-- Chart JS -->
  {{-- <script src="{{ asset('admin/js/plugin/chart.js/chart.min.js') }}"></script>
  <!-- jQuery Sparkline -->
  <script src="{{ asset('admin/js/plugin/jquery.sparkline/jquery.sparkline.min.js') }}"></script>
  <!-- Chart Circle -->
  <script src="{{ asset('admin/js/plugin/chart-circle/circles.min.js') }}"></script>
  <!-- Datatables -->
  <script src="{{ asset('admin/js/plugin/datatables/datatables.min.js') }}"></script>
  <!-- Bootstrap Notify -->
  <script src="{{ asset('admin/js/plugin/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
  <!-- jQuery Vector Maps -->
  <script src="{{ asset('admin/js/plugin/jsvectormap/jsvectormap.min.js') }}"></script>
  <script src="{{ asset('admin/js/plugin/jsvectormap/world.js') }}"></script>
  <!-- Sweet Alert -->
  <script src="{{ asset('admin/js/plugin/sweetalert/sweetalert.min.js') }}"></script> --}}
  
  <!-- Dashboard Initialization -->
  {{-- <script src="{{ asset('admin/js/demo.js') }}"></script> --}}
  {{-- <script>
    $("#lineChart").sparkline([102, 109, 120, 99, 110, 105, 115], {
      type: "line",
      height: "70",
      width: "100%",
      lineWidth: "2",
      lineColor: "#177dff",
      fillColor: "rgba(23, 125, 255, 0.14)",
    });

    $("#lineChart2").sparkline([99, 125, 122, 105, 110, 124, 115], {
      type: "line",
      height: "70",
      width: "100%",
      lineWidth: "2",
      lineColor: "#f3545d",
      fillColor: "rgba(243, 84, 93, .14)",
    });

    $("#lineChart3").sparkline([105, 103, 123, 100, 95, 105, 115], {
      type: "line",
      height: "70",
      width: "100%",
      lineWidth: "2",
      lineColor: "#ffa534",
      fillColor: "rgba(255, 165, 52, .14)",
    });
  </script> --}}
@endpush