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
            <form class="d-flex me-auto" role="search" id='searchForm'>
                <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                <button class="btn btn-outline-success" type="submit">Search</button>
            </form>
            <ul class="navbar-nav ms-auto align-items-lg-center">

                <!-- User Dropdown options -->
               
                <!-- Is navbar me Dark Mode Toggle -->
                <li class="nav-item my-2 my-lg-0">
                    <form class="d-flex align-items-center" id="darkModeForm"> <button
                            class="btn btn-outline-secondary ms-2" type="button" id="darkModeSwitch">Dark Mode</button>
                    </form>
                </li>

            </ul>

        </div>
    </div>
</nav>