<?php
$page_title = "Home";
include 'includes/header.php';
include 'includes/navbar.php';
?>

<section class="hero-section">
    <div class="container text-center">
        <h1>Night Market Discovery and Visit Planning System</h1>
        <p class="lead mt-3">
            Discover night markets, explore must-try food, read reviews, and plan your visit easily.
        </p>

        <div class="mt-4">
            <a href="market-list.php" class="btn btn-warning btn-lg me-2">
                Explore Night Markets
            </a>
            <a href="planner.php" class="btn btn-outline-light btn-lg">
                Plan My Visit
            </a>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <h2 class="section-title text-center">Main Features</h2>

        <div class="row g-4 mt-3">

            <div class="col-md-4">
                <div class="card feature-card shadow-sm h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title">Discover Night Markets</h5>
                        <p class="card-text">
                            Search and view night markets by area, state, and operating day.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card feature-card shadow-sm h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title">Must-Try Food</h5>
                        <p class="card-text">
                            Explore popular stalls and recommended food at each night market.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card feature-card shadow-sm h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title">Visit Planner</h5>
                        <p class="card-text">
                            Create your own visit plan and organize night markets into your trip.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<section class="py-5 bg-white">
    <div class="container text-center">
        <h2 class="section-title">Built for Night Market Visitors</h2>
        <p class="lead">
            This system helps clients discover food places, check reviews, and plan a better night market visit.
        </p>
    </div>
</section>

<?php
include 'includes/footer.php';
?>