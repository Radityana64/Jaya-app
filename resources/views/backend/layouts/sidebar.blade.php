<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{route('admin')}}">
      <div class="sidebar-brand-icon">
        <img src="/backend/img/jayawhite.png" alt="logo" style="width: 70%; height: auto;">  
      </div>
    </a>


    <!-- Divider -->
    <!-- <hr class="sidebar-divider my-0">

   
    <li class="nav-item active">
      <a class="nav-link" href="{{route('admin')}}">
        <i class="fas fa-fw fa-tachometer-alt"></i>
        <span>Dashboard</span></a>
    </li> -->

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Banner
    </div>

    <!-- Nav Item - Pages Collapse Menu -->
    <!-- Nav Item - Charts -->
    <li class="nav-item">
      <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
        <i class="fas fa-image"></i>
        <span>Banner</span>
      </a>
      <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
          <h6 class="collapse-header">Menu Banner:</h6>
          <a class="collapse-item" href="/banner">Banner</a>
          <a class="collapse-item" href="{{route('banner.create')}}">Tambah Banner</a>
        </div>
      </div>
    </li>
    <!-- Divider -->
    <hr class="sidebar-divider">
        <!-- Heading -->
        <div class="sidebar-heading">
            Data Master
        </div>
    
    <!-- Users -->
    <li class="nav-item">
        <a class="nav-link" href="/pelanggan">
            <i class="fas fa-users"></i>
            <span>Pelanggan</span></a>
    </li>

    <!-- Categories -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#categoryCollapse" aria-expanded="true" aria-controls="categoryCollapse">
          <i class="fas fa-sitemap"></i>
          <span>Kategori</span>
        </a>
        <div id="categoryCollapse" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Menu Kategori:</h6>
            <a class="collapse-item" href="{{route ('index.kategori') }}">Kategori</a>
            <a class="collapse-item" href="{{route ('category.create')}}">Tambah Kategori</a>
          </div>
        </div>
    </li>
    <!-- Products -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#productCollapse" aria-expanded="true" aria-controls="productCollapse">
          <i class="fas fa-cubes"></i>
          <span>Produk</span>
        </a>
        <div id="productCollapse" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Menu Produk:</h6>
            <a class="collapse-item" href="{{route ('index.produk') }}">Produk</a>
            <a class="collapse-item" href="{{route ('produk.create') }}">Tambah Produk</a>
          </div>
        </div>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">
     <!-- Heading -->
    <div class="sidebar-heading">
        Penjualan
    </div>

    <li class="nav-item">
      <a class="nav-link" href="/voucher">
          <i class="fas fa-table"></i>
          <span>Voucer</span></a>
    </li>

    <!--Orders -->
    <li class="nav-item">
        <a class="nav-link" href="/pemesanan">
            <i class="fas fa-hammer fa-chart-area"></i>
            <span>Pemesanan</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">
     <!-- Heading -->
    @can('access-pemilik_toko')
    <div class="sidebar-heading">
        Pengaturan 
    </div>

    <li class="nav-item">
      <a class="nav-link" href="/laporan">
          <i class="fas fa-fw fa-chart-area"></i>
          <span>Pelaporan</span></a>
    </li>
    
     <!-- General settings -->
     <li class="nav-item">
        <a class="nav-link" href="/data/admin">
            <i class="fas fa-cog"></i>
            <span>Data Admin</span></a>
    </li>
    @endcan

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
      <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>