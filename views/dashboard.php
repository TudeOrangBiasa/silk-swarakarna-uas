<!-- Dashboard content -- rendered via front controller layout -->

<h1 class="h3 fw-semibold mb-4">Dashboard</h1>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Total Pasien</small>
                        <div class="display-6 fw-bold">3</div>
                    </div>
                    <i class="bi bi-people-fill text-primary fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Total Dokter</small>
                        <div class="display-6 fw-bold">3</div>
                    </div>
                    <i class="bi bi-clipboard-pulse text-primary fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Total Layanan</small>
                        <div class="display-6 fw-bold">4</div>
                    </div>
                    <i class="bi bi-clipboard-data text-primary fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Pemeriksaan Hari Ini</small>
                        <div class="display-6 fw-bold">2</div>
                    </div>
                    <i class="bi bi-calendar-check text-primary fs-1"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h2 class="h5 mb-0">Pemeriksaan Terbaru</h2>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID Periksa</th>
                        <th>Tanggal</th>
                        <th>Pasien</th>
                        <th>Dokter</th>
                        <th>Layanan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>TRX-2026001</code></td>
                        <td>19/06/2026</td>
                        <td>Andi Pratama</td>
                        <td>dr. Sari Wijaya, Sp.THT</td>
                        <td>Audiometri</td>
                        <td><span class="badge rounded-pill bg-warning text-dark">Menunggu</span></td>
                    </tr>
                    <tr>
                        <td><code>TRX-2026002</code></td>
                        <td>19/06/2026</td>
                        <td>Siti Aminah</td>
                        <td>dr. Budi Santoso, Sp.THT-KL</td>
                        <td>BERA</td>
                        <td><span class="badge rounded-pill bg-info text-dark">Sedang Diperiksa</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


