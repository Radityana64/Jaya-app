<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
    <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link rounded-circle mr-3">
      <i class="fa fa-bars"></i>
    </button>
    <!-- Topbar Navbar -->
    <ul class="navbar-nav ml-auto"> 
      
      <li class="nav-item dropdown no-arrow mx-1">
        <a class="nav-link dropdown-toggle" href="/" target="_blank" data-toggle="tooltip" data-placement="bottom" title="home" role="button">
          <i class="fas fa-home fa-fw"></i>
        </a>
      </li>
      
      <!-- Divider -->
      <div class="topbar-divider d-none d-sm-block"></div>
      
      <!-- Nav Item - User Information -->
      <li class="nav-item dropdown no-arrow">
        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <span class="mr-2 d-none d-lg-inline text-gray-600 small" id="userRoleDisplay">Admin</span>
        </a>
        <!-- Dropdown - User Information -->
        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown" id="userDropdownMenu">
          <!-- Dynamic content will be added here based on role -->
        </div>
      </li>
    </ul>
</nav>

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

document.addEventListener('DOMContentLoaded', function() {
    // Function to get user data from API
    function getUserData() {
        fetch(`${getApiBaseUrl()}/api/user/profil`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${getJwtToken()}`,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(result => {
            updateNavbar(result.data);
        })
        .catch(error => {
            console.error('Error fetching user data:', error);
        });
    }
    
    // Function to update navbar based on user role
    function updateNavbar(userData) {
        const userRoleDisplay = document.getElementById('userRoleDisplay');
        const userDropdownMenu = document.getElementById('userDropdownMenu');
        
        // Clear previous dropdown content
        userDropdownMenu.innerHTML = '';
        
        if (userData.role === 'pemilik_toko') {
            // Update display name to "Pemilik Toko"
            userRoleDisplay.textContent = 'Pemilik Toko';
            
            // For pemilik_toko, only show logout
            userDropdownMenu.innerHTML = `
                <a class="dropdown-item logout-button" href="#">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i> Logout
                </a>
            `;
        } else if (userData.role === 'admin') {
            // Keep display as "Admin"
            userRoleDisplay.textContent = 'Admin';
            
            // For admin, show name and logout
            userDropdownMenu.innerHTML = `
                <span class="dropdown-item">${userData.nama_lengkap}</span>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item logout-button" href="#">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i> Logout
                </a>
            `;
        }
        const logoutButton = document.querySelector('.logout-button');
        if (logoutButton) {
            logoutButton.addEventListener('click', handleLogout);
        }
    }
    
    // Function to handle logout
    async function handleLogout(e) {
      e.preventDefault();
        try {
        // Logout API
            const apiResponse = await fetch(`${getApiBaseUrl()}/api/logout`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Authorization': `Bearer ${getJwtToken()}`
                }
            });
            const apiData = await apiResponse.json();
            if (!apiResponse.ok) {
                throw new Error(apiData.message || 'API logout failed');
            }

            // Logout Web
            const webResponse = await fetch('http://127.0.0.1:8001/logout/session', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                }
            });
            const webData = await webResponse.json();
            if (!webResponse.ok) {
                throw new Error(webData.message || 'Web logout failed');
            }

            // Reset meta tag jika ada
            const metaApiToken = document.querySelector('meta[name="api-token"]');
            if (metaApiToken) {
                metaApiToken.setAttribute('content', '');
            }

            // Redirect ke login
            window.location.href = '/login';
        } catch (error) {
            console.error('Error during logout:', error);
            alert('Logout failed. Please try again.');
        }
    }
    
    getUserData();
});
</script>