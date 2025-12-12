<div class="cours-detail-container">
    <a href="index.php?action=cours" class="btn btn-secondary mb-2">&larr; Retour aux cours</a>
    
    <h1><?php echo htmlspecialchars($cours_detail['title']); ?></h1>
    
    <div class="card">
        <div class="card-meta">
            <p><strong>Auteur :</strong> <?php echo htmlspecialchars($cours_detail['author']); ?></p>
            <p><strong>Instrument :</strong> <?php echo htmlspecialchars($cours_detail['instrument']); ?></p>
            <p><strong>Niveau :</strong> <?php echo htmlspecialchars($cours_detail['level']); ?></p>
            <p><strong>Catégorie :</strong> <?php echo htmlspecialchars($cours_detail['category']); ?></p>
        </div>

        <div class="card-content mt-2">
            <h3>Description</h3>
            <p><?php echo nl2br(htmlspecialchars($cours_detail['description'])); ?></p>
            
            <?php if(!empty($cours_detail['content'])): ?>
                <h3>Contenu du cours</h3>
                <div class="course-content">
                    <?php echo $cours_detail['content']; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="card-actions mt-2">
            <button class="btn">Télécharger les ressources (PDF)</button>
            
            <!-- US-16 Favoris -->
            <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                <?php
                include_once '../../modeles/Favori.php';
                $favoriModel = new Favori($db);
                $isFav = $favoriModel->isFavorite($_SESSION['user_id'], $cours_detail['id'], 'course');
                ?>
                <?php if ($isFav): ?>
                    <a href="index.php?action=remove_favorite&item_id=<?php echo $cours_detail['id']; ?>&item_type=course&from=detail" class="btn btn-danger">
                        ❤️ Retirer des favoris
                    </a>
                <?php else: ?>
                    <a href="index.php?action=add_favorite&item_id=<?php echo $cours_detail['id']; ?>&item_type=course" class="btn btn-secondary">
                        🤍 Ajouter aux favoris
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <a href="index.php?action=login" class="btn btn-secondary">🤍 Ajouter aux favoris</a>
            <?php endif; ?>
        </div>
    </div>
</div>