@extends('admin.layouts.app')

@section('title', 'System Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">System Management</li>
                </ol>
            </nav>
            <h3 class="fw-bold text-dark">System Management</h3>
            <p class="text-muted">Perform essential maintenance and optimization tasks for the application.</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- System Information -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 p-4 pb-0">
                    <h5 class="fw-bold mb-0">Server Environment</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex flex-column gap-3">
                        <div class="p-3 bg-light rounded-4 border border-white shadow-sm">
                            <div class="small text-muted mb-1 text-uppercase letter-spacing-1 fw-bold">Laravel Version</div>
                            <div class="fw-bold text-primary fs-5">{{ $laravelVersion }}</div>
                        </div>
                        <div class="p-3 bg-light rounded-4 border border-white shadow-sm">
                            <div class="small text-muted mb-1 text-uppercase letter-spacing-1 fw-bold">PHP Version</div>
                            <div class="fw-bold text-dark fs-5">{{ $phpVersion }}</div>
                        </div>
                        <div class="p-3 bg-light rounded-4 border border-white shadow-sm">
                            <div class="small text-muted mb-1 text-uppercase letter-spacing-1 fw-bold">Database</div>
                            <div class="fw-bold text-dark fs-5 text-truncate">{{ $databaseName }}</div>
                        </div>
                        <div class="p-3 bg-light rounded-4 border border-white shadow-sm">
                            <div class="small text-muted mb-1 text-uppercase letter-spacing-1 fw-bold">Server</div>
                            <div class="fw-bold text-dark fs-5 text-truncate">{{ Str::limit($serverSoftware, 20) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Maintenance Commands -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 p-4">
                    <h5 class="fw-bold mb-0">Maintenance Terminal</h5>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="command-box p-4 rounded-4 border h-100 transition-all">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="p-2 rounded-3 bg-primary bg-opacity-10 me-3 text-primary">
                                        <i class="fa-solid fa-broom fs-4"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0">Application Cache</h6>
                                </div>
                                <p class="small text-muted mb-4">Clears all application cache including data, views, and routes.</p>
                                <form action="{{ route('admin.system.command') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="command" value="cache:clear">
                                    <button type="submit" class="btn btn-primary rounded-pill w-100 shadow-sm">Execute Clear</button>
                                </form>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="command-box p-4 rounded-4 border h-100 transition-all">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="p-2 rounded-3 bg-success bg-opacity-10 me-3 text-success">
                                        <i class="fa-solid fa-gear fs-4"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0">Config & Route Optimize</h6>
                                </div>
                                <p class="small text-muted mb-4">Caches configurations and routes for faster load times.</p>
                                <form action="{{ route('admin.system.command') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="command" value="optimize">
                                    <button type="submit" class="btn btn-success rounded-pill w-100 shadow-sm">Execute Optimize</button>
                                </form>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="command-box p-4 rounded-4 border h-100 transition-all">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="p-2 rounded-3 bg-secondary bg-opacity-10 me-3 text-secondary">
                                        <i class="fa-solid fa-sliders fs-4"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0">Configuration Cache</h6>
                                </div>
                                <p class="small text-muted mb-4">Clears the configuration cache. Useful after updating .env files.</p>
                                <form action="{{ route('admin.system.command') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="command" value="config:clear">
                                    <button type="submit" class="btn btn-secondary rounded-pill w-100 shadow-sm">Clear Config</button>
                                </form>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="command-box p-4 rounded-4 border h-100 transition-all">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="p-2 rounded-3 bg-warning bg-opacity-10 me-3 text-warning">
                                        <i class="fa-solid fa-database fs-4"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0">Database Migrations</h6>
                                </div>
                                <p class="small text-muted mb-4">Runs any pending database migrations to keep schema updated.</p>
                                <form action="{{ route('admin.system.command') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="command" value="migrate">
                                    <button type="submit" class="btn btn-warning rounded-pill w-100 shadow-sm text-white">Run Migrations</button>
                                </form>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="command-box p-4 rounded-4 border h-100 transition-all">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="p-2 rounded-3 bg-info bg-opacity-10 me-3 text-info">
                                        <i class="fa-solid fa-link fs-4"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0">Storage Link</h6>
                                </div>
                                <p class="small text-muted mb-4">Creates a symbolic link for public access to storage files.</p>
                                <form action="{{ route('admin.system.storage-link') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-info rounded-pill w-100 shadow-sm text-white">Create Link</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Log Viewer -->
            <div class="card border-0 shadow-sm rounded-4 mt-4">
                <div class="card-header bg-white border-0 p-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">System Logs (Last 100 Lines)</h5>
                    <form action="{{ route('admin.system.clear-logs') }}" method="POST" onsubmit="return confirm('Are you sure you want to clear all system logs?')">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                            <i class="fa-solid fa-trash-can me-1"></i> Clear Log File
                        </button>
                    </form>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="terminal-view p-3 rounded-4 bg-dark text-light overflow-auto" style="max-height: 400px; font-family: 'Courier New', Courier, monospace; font-size: 13px; line-height: 1.5;">
                        <pre class="mb-0"><code>{{ $logs ?: 'No logs available.' }}</code></pre>
                    </div>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="card border-0 shadow-sm rounded-4 mt-4 bg-danger bg-opacity-10 border border-danger border-opacity-25">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fa-solid fa-triangle-exclamation text-danger me-2 fs-5"></i>
                        <h5 class="fw-bold mb-0 text-danger">Safety Information</h5>
                    </div>
                    <p class="text-danger small mb-0">
                        Running system commands can temporarily affect site availability. Use these tools only when necessary, especially during peak traffic hours. Always ensure you have a fresh database backup before running migrations.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .letter-spacing-1 { letter-spacing: 1px; }
    .command-box {
        background-color: #ffffff;
        border-color: #f1f5f9 !important;
        border-width: 2px !important;
    }
    .command-box:hover {
        border-color: #cbd5e1 !important;
        transform: translateY(-3px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
    }
</style>
@endsection
