<div class="hero-section text-center mb-5">
    <h1>Bienvenue sur OmniMusique</h1>
    <p>Votre plateforme dédiée à l'apprentissage et à la pratique musicale.</p>
</div>

<!-- Catégories (US-14) -->
<div class="categories-grid mb-8">
    <a href="index.php?action=cours" class="card text-center">
        <h3>🎵 Cours</h3>
        <p>Apprenez un instrument</p>
    </a>
    <a href="index.php?action=boutique" class="card text-center">
        <h3>🛒 Boutique</h3>
        <p>Partitions & Instruments</p>
    </a>
    <a href="index.php?action=blog" class="card text-center">
        <h3>📰 Blog</h3>
        <p>Actualités & Conseils</p>
    </a>
    <a href="index.php?action=contact" class="card text-center">
        <h3>✉️ Contact</h3>
        <p>Petites annonces</p>
    </a>
</div>

<!-- Dernières Nouveautés (US-11) -->
<section class="mb-4">
    <h2>Dernières Actualités</h2>
    <div class="grid-3">
        <?php if(isset($derniers_articles)): ?>
            <?php foreach($derniers_articles as $article): ?>
                <div class="card">
                    <?php if(!empty($article['image_url'])): ?>
                        <img src="../../assets/<?php echo htmlspecialchars($article['image_url']); ?>" alt="Image article" class="article-card-img">
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars($article['title']); ?></h3>
                    <small>Par <?php echo htmlspecialchars($article['author'] ?? 'Anonyme'); ?> le <?php echo date('d/m/Y', strtotime($article['created_at'])); ?></small>
                    <p><?php echo substr(strip_tags($article['content']), 0, 100) . '...'; ?></p>
                    <a href="index.php?action=article_details&id=<?php echo $article['id']; ?>" class="btn-small">Lire la suite</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="text-center mt-1">
        <a href="index.php?action=blog" class="btn">Voir tout le blog</a>
    </div>
</section>

<section class="mb-2">
    <h2>Nouveaux Cours</h2>
    <div class="grid-3">
        <?php if(isset($derniers_cours)): ?>
            <?php foreach($derniers_cours as $cours): ?>
                <div class="card">
                    <h3><?php echo htmlspecialchars($cours['title']); ?></h3>
                    <p><strong>Instrument:</strong> <?php echo htmlspecialchars($cours['instrument']); ?></p>
                    <p><strong>Niveau:</strong> <?php echo htmlspecialchars($cours['level']); ?></p>
                    <a href="index.php?action=cours_details&id=<?php echo $cours['id']; ?>" class="btn-small">Voir le cours</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="text-center mt-1">
        <a href="index.php?action=cours" class="btn">Voir tous les cours</a>
    </div>
</section>
