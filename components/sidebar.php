<div class="sidebar">

    <!-- Logo -->
    <div class="logo">

        <div class="logo-icon">
            <i class="fa-solid fa-mug-hot"></i>
        </div>

        <div class="logo-text">
            <h2>IoT Coffee Sorter</h2>
            <p>ADMIN PANEL</p>
        </div>

    </div>

    <!-- Menu -->
    <nav class="menu">

        <a href="profile.php"
        class="<?= ($activePage == 'profile') ? 'active' : '' ?>">


            Profile

        </a>

        <a href="dashboard.php"
        class="<?= ($activePage == 'dashboard') ? 'active' : '' ?>">

            Dashboard

        </a>

        <a href="setting.php"
        class="<?= ($activePage == 'settings') ? 'active' : '' ?>">

            Settings

        </a>

        <!-- Logout -->
        <a href="logout.php" 
        class="<?= ($activePage == 'logout') ? 'active' : '' ?>"
            onclick="confirmLogout()">

            Logout

        </a>

    </nav>

</div>