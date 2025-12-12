<h1>Mes Favoris</h1>

<?php if (!isset($_SESSION['user_logged_in'])): ?>
    <div class="alert alert-warning">
        <p>Vous devez être connecté pour accéder à vos favoris.</p>
        <a href="index.php?action=login" class="btn">Se connecter</a>
    </div>
<?php else: ?>

    <!-- Navigation par onglets -->
    <div class="tabs mb-3">
        <a href="index.php?action=my_favorites&type=course" class="btn <?php echo (!isset($_GET['type']) || $_GET['type'] === 'course') ? 'btn-primary' : 'btn-secondary'; ?>">
            Cours favoris
        </a>
        <a href="index.php?action=my_favorites&type=article" class="btn <?php echo (isset($_GET['type']) && $_GET['type'] === 'article') ? 'btn-primary' : 'btn-secondary'; ?>">
            Articles favoris
        </a>
    </div>

    <?php if (!isset($_GET['type']) || $_GET['type'] === 'course'): ?>
        <!-- Liste des cours favoris -->
        <h2>Mes Cours Favoris</h2>
        <?php if (isset($favorite_courses) && !empty($favorite_courses)): ?>
            <div class="grid-3">
                <?php foreach ($favorite_courses as $cours): ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($cours['title']); ?></h3>
                        <p class="card-meta">
                            <strong>Instrument :</strong> <?php echo htmlspecialchars($cours['instrument']); ?> | 
                            <strong>Niveau :</strong> <?php echo htmlspecialchars($cours['level']); ?>
                        </p>
                        <p><?php echo htmlspecialchars(substr($cours['description'], 0, 100)) . '...'; ?></p>
                        <small>Par <?php echo htmlspecialchars($cours['author'] ?? 'Anonyme'); ?></small>
                        <div class="card-actions mt-2">
                            <a href="index.php?action=cours_details&id=<?php echo $cours['id']; ?>" class="btn-small">Voir</a>
                            <a href="index.php?action=remove_favorite&item_id=<?php echo $cours['id']; ?>&item_type=course" class="btn-small btn-danger" onclick="return confirm('Retirer des favoris ?');">
                                ❌ Retirer
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-muted">Aucun cours en favoris.</p>
            <a href="index.php?action=cours" class="btn">Découvrir les cours</a>
        <?php endif; ?>

    <?php else: ?>
        <!-- Liste des articles favoris -->
        <h2>Mes Articles Favoris</h2>
        <?php if (isset($favorite_articles) && !empty($favorite_articles)): ?>
            <div class="grid-3">
                <?php foreach ($favorite_articles as $article): ?>
                    <div class="card">
                        <?php if (!empty($article['image_url'])): ?>
                            <img src="../../assets/<?php echo htmlspecialchars($article['image_url']); ?>" alt="Image" class="card-img">
                        <?php endif; ?>
                        <h3><?php echo htmlspecialchars($article['title']); ?></h3>
                        <small>Par <?php echo htmlspecialchars($article['author'] ?? 'Anonyme'); ?> le <?php echo date('d/m/Y', strtotime($article['created_at'])); ?></small>
                        <p><?php echo htmlspecialchars(substr(strip_tags($article['content']), 0, 100)) . '...'; ?></p>
                        <div class="card-actions mt-2">
                            <a href="index.php?action=article_details&id=<?php echo $article['id']; ?>" class="btn-small">Lire</a>
                            <a href="index.php?action=remove_favorite&item_id=<?php echo $article['id']; ?>&item_type=article" class="btn-small btn-danger" onclick="return confirm('Retirer des favoris ?');">
                                ❌ Retirer
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-muted">Aucun article en favoris.</p>
            <a href="index.php?action=blog" class="btn">Découvrir le blog</a>
        <?php endif; ?>
    <?php endif; ?>

<?php endif; ?>
