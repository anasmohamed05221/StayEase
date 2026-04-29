<?php require_once 'php/search.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Search Results — StayEase</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/main.css">
</head>

<body>

  <!-- NAVBAR (same as index.php) -->
  <nav class="navbar">
    <div class="container nav-inner">
      <a href="index.php" class="logo">StayEase<span>.</span></a>
      <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="search-results.php" class="active">Discover</a>
        <a href="about.html">About</a>
        <a href="reviews.html">Reviews</a>
      </div>
      <div class="nav-auth">
        <a href="dashboard.html" class="profile-icon"><i class="fa-regular fa-circle-user"></i></a>
        <a href="login.html" class="btn-login">Login</a>
      </div>
    </div>
  </nav>
  <main class="results-page">
    <div class="container">

      <!-- Results Header -->
      <div class="results-header">
        <div>
          <h1 id="resultsTitle">Search Results</h1>
          <p id="resultsSubtitle" style="font-size:14px; color:var(--muted); margin-top:4px;"></p>
        </div>
        <div class="sort-bar">
          <label>Sort by:</label>
          <div class="custom-select" id="sortDropdown">
            <div class="custom-select-trigger">
              <?= $sort === 'price_desc' ? 'Price: High to Low' : ($sort === 'rating' ? 'Top Rated' : 'Price: Low to High') ?>
              <i class="fa-solid fa-chevron-down"></i>
            </div>
            <div class="custom-select-options">
              <div class="custom-option <?= $sort === 'price_asc' ? 'selected' : '' ?>" data-value="price_asc">Price: Low to High</div>
              <div class="custom-option <?= $sort === 'price_desc' ? 'selected' : '' ?>" data-value="price_desc">Price: High to Low</div>
              <div class="custom-option <?= $sort === 'rating' ? 'selected' : '' ?>" data-value="rating">Top Rated</div>
            </div>
            <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
          </div>
        </div>
      </div>

      <!-- Two-column layout: sidebar filters + results grid -->
      <div class="results-layout">

        <!-- ── FILTERS SIDEBAR ── -->
        <form id="filtersForm" method="GET" action="search-results.php">
          <!-- Carry over search params -->
          <input type="hidden" name="city" value="<?= htmlspecialchars($city) ?>">
          <input type="hidden" name="check_in" value="<?= htmlspecialchars($checkIn) ?>">
          <input type="hidden" name="check_out" value="<?= htmlspecialchars($checkOut) ?>">
          <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">

          <aside class="filters-panel">
            <div class="filters-top">
              <h3>Filters</h3>
              <a href="search-results.php?city=<?= htmlspecialchars($city) ?>&check_in=<?= htmlspecialchars($checkIn) ?>&check_out=<?= htmlspecialchars($checkOut) ?>" class="clear-btn">Clear all</a>
            </div>

            <div class="filter-group">
              <label class="group-label">Price Range (per night)</label>
              <div class="price-range">
                <input type="range" id="priceRange" name="max_price" min="0" max="1000" value="<?= $maxPrice ?>" step="10">
                <div class="price-labels">
                  <span>$0</span>
                  <span id="priceMax">$<?= $maxPrice ?></span>
                </div>
              </div>
            </div>

            <div class="filter-group">
              <label class="group-label">Star Rating</label>
              <?php foreach ([5, 4, 3] as $s): ?>
                <label class="checkbox-item">
                  <input type="checkbox" name="stars[]" value="<?= $s ?>"
                    <?= (empty($starsFilter) || in_array($s, $starsFilter)) ? 'checked' : '' ?>>
                  <?= $s ?> Stars <span style="color:#F59E0B; margin-left:4px;">★</span>
                </label>
              <?php endforeach; ?>
            </div>

          </aside>
        </form>

        <div id="resultsContainer">

          <?php if (empty($hotels)): ?>
            <div class="empty-state">
              <i class="fa-solid fa-magnifying-glass"></i>
              <h3>No hotels found</h3>
              <p>Try a different city or broaden your search.</p>
            </div>
          <?php else: ?>
            <?php foreach ($hotels as $h):
              $stars      = str_repeat('★', (int)$h['stars']) . str_repeat('☆', 5 - (int)$h['stars']);
              $minPrice   = $h['min_price'] ? '$' . number_format($h['min_price'], 0) : 'N/A';
              $score      = $h['avg_rating'] ? round($h['avg_rating'] * 2, 1) : null;
              $scoreLabel = match (true) {
                $score === null => '',
                $score >= 9.5   => 'EXCEPTIONAL',
                $score >= 9.0   => 'SUPERB',
                $score >= 8.5   => 'EXCELLENT',
                $score >= 8.0   => 'VERY GOOD',
                default         => 'GOOD',
              };
            ?>
              <div class="result-card">
                <div class="result-card-img">
                  <img src="<?= htmlspecialchars($h['image']) ?>" alt="<?= htmlspecialchars($h['name']) ?>">
                  <button class="wishlist-btn"><i class="fa-regular fa-heart"></i></button>
                </div>
                <div class="result-card-body">
                  <div class="result-card-top">
                    <div>
                      <h3><?= htmlspecialchars($h['name']) ?></h3>
                      <p class="result-location"><i class="fa-solid fa-location-dot fa-xs"></i> <?= htmlspecialchars($h['location']) ?></p>
                      <div class="hotel-stars" style="font-size:14px;"><?= $stars ?></div>
                      <p class="result-reviews"><?= number_format($h['review_count']) ?> reviews</p>
                    </div>
                    <?php if ($score): ?>
                      <div class="score-badge"><span><?= $score ?></span><?= $scoreLabel ?></div>
                    <?php endif; ?>
                  </div>
                  <p class="result-desc"><?= htmlspecialchars($h['description']) ?></p>
                  <div class="result-card-bottom">
                    <div class="result-price">
                      <small>Price for <?= $nights ?> night<?= $nights > 1 ? 's' : '' ?></small>
                      <strong><?= $minPrice ?></strong> <span>/ night</span>
                    </div>
                    <a href="hotel-detail.php?id=<?= $h['id'] ?>" class="btn-view">View Deal</a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

        </div>
        <!-- END #resultsContainer -->

      </div>
      <!-- END results-layout -->

      <?php if ($totalPages > 1):
        $winStart = max(1, min($page - 1, $totalPages - 2));
        $winEnd   = min($totalPages, $winStart + 2);
      ?>
        <div class="pagination">
          <?php if ($page > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">‹</a>
          <?php else: ?>
            <span class="pagination-disabled">‹</span>
          <?php endif; ?>

          <?php for ($p = $winStart; $p <= $winEnd; $p++): ?>
            <?php if ($p === $page): ?>
              <span><?= $p ?></span>
            <?php else: ?>
              <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"><?= $p ?></a>
            <?php endif; ?>
          <?php endfor; ?>

          <?php if ($page < $totalPages): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">›</a>
          <?php else: ?>
            <span class="pagination-disabled">›</span>
          <?php endif; ?>
        </div>
      <?php endif; ?>

    </div>
  </main>

  <!-- FOOTER -->
  <footer class="footer">
    <div class="footer-inner">
      <div class="footer-logo">StayEase</div>
      <div class="footer-links">
        <a href="about.html">About Us</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
        <a href="#">Help Center</a>
        <a href="#">Partners</a>
      </div>
      <p class="footer-copy">© 2024 StayEase Inc. All rights reserved.</p>
    </div>
  </footer>

  <script src="js/main.js"></script>
  <script src="js/filters.js"></script>
</body>

</html>