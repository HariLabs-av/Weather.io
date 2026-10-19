<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
        <!-- Brand Logo aur Naam -->
        <a class="navbar-brand" href="index.php">
            <img src="assests/icon.png" alt="Weather.io Logo" width="30" height="24"
                class="d-inline-block align-text-top">
            Weather.io
        </a>

        <!-- Mobile ke liye Toggler button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Collapse hone wala Content -->
        <div class="collapse navbar-collapse" id="navbarSupportedContent">

            <!-- Search Form -->
            <form class="d-flex me-auto" role="search" id="searchForm">
                <input class="form-control me-2" type="search" placeholder="Search a city" aria-label="Search for a city">
                <button class="btn btn-primary" type="submit">Search</button>
            </form>
            <ul class="navbar-nav ms-auto align-items-lg-center">

                <li class="nav-item my-2 my-lg-0">
                    <?php if (function_exists('isLoggedIn') && isLoggedIn() && (currentUser()['role'] ?? '') === 'admin'): ?>
                        <a class="btn btn-outline-primary ms-2" href="admin/index.php">Dashboard</a>
                        <a class="btn btn-outline-secondary ms-2" href="logout.php">Log out</a>
                    <?php elseif (function_exists('isLoggedIn') && isLoggedIn()): ?>
                        <span class="small text-secondary ms-2">Hi, <?= e((string) currentUser()['name']) ?></span>
                        <a class="btn btn-outline-secondary ms-2" href="logout.php">Log out</a>
                    <?php else: ?>
                        <a class="btn btn-outline-primary ms-2" href="login.php">Log in</a>
                        <a class="btn btn-primary ms-2" href="register.php">Register</a>
                    <?php endif; ?>
                </li>
               
                <!-- Is navbar me Dark Mode Toggle -->
                <li class="nav-item my-2 my-lg-0">
                    <form class="d-flex align-items-center" id="darkModeForm"> <button
                            class="btn btn-outline-secondary ms-2 theme-toggle" type="button" id="darkModeSwitch"
                            aria-pressed="false">🌙 Dark mode</button>
                    </form>
                </li>

            </ul>

        </div>
    </div>
</nav>