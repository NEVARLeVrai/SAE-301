<h1 class="text-center">Le Blog Musique</h1>
<p class="text-center mb-3">Toute l'actualité musicale et nos conseils techniques.</p>

<!-- Recherche et Filtres (US-13 & US-02) -->
<div class="p-3">
    <div class="filter-form justify-content-between">
        <form action="index.php" method="GET" class="d-flex gap-2 align-items-center flex-wrap">
            <input type="hidden" name="action" value="blog">
            <input type="text" name="search" class="form-control w-auto" placeholder="Rechercher un article..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit" class="btn btn-small"><i class="bi bi-search"></i> Rechercher</button>
        </form>

        <div class="category-filters d-flex gap-2 flex-wrap align-items-center mt-2 mt-md-0">
            <strong>Catégories : </strong>
            <a href="index.php?action=blog" class="btn btn-small <?php echo !isset($_GET['category']) ? 'btn-admin' : 'btn-outline-secondary'; ?>">Tout</a>
            <?php foreach($categories as $cat): ?>
                <a href="index.php?action=blog&category=<?php echo urlencode($cat); ?>" class="btn btn-small <?php echo (isset($_GET['category']) && $_GET['category'] == $cat) ? 'btn-admin' : 'btn-outline-secondary'; ?>">
                    <?php echo htmlspecialchars($cat); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="blog-container">
    <?php if(isset($liste_articles) && count($liste_articles) > 0): ?>
        <?php foreach($liste_articles as $row): ?>
            <div class="article-card">
                <?php if(!empty($row['image_url'])): ?>
                    <img src="../../assets/<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>" class="article-card-img">
                <?php endif; ?>
                <div class="card-content">
                    <h2><?php echo htmlspecialchars($row['title']); ?></h2>
                    <p class="card-meta"><small><i class="bi bi-calendar"></i> <?php echo date('d/m/Y', strtotime($row['created_at'])); ?> | <i class="bi bi-person"></i> <?php echo htmlspecialchars($row['author']); ?> | <i class="bi bi-tag"></i> <?php echo htmlspecialchars($row['category']); ?></small></p>
                    <p><?php echo substr(strip_tags($row['content']), 0, 150) . '...'; ?></p>
                    <a href="index.php?action=article_details&id=<?php echo $row['id']; ?>" class="btn btn-small mt-2">Lire la suite</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12 text-center">
            <p class="alert alert-info">Aucun article trouvé.</p>
        </div>
    <?php endif; ?>
</div>
