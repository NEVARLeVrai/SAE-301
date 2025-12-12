<div class="article-detail-container">
    <a href="index.php?action=blog" class="btn btn-secondary mb-2">&larr; Retour au blog</a>
    
    <article class="card">
        <?php if(!empty($article['image_url'])): ?>
            <img src="../../assets/<?php echo htmlspecialchars($article['image_url']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="article-detail-img">
        <?php endif; ?>
        
        <div class="article-body">
            <h1><?php echo htmlspecialchars($article['title']); ?></h1>
            <p class="card-meta">
                Publié le <?php echo date('d/m/Y', strtotime($article['created_at'])); ?> 
                par <strong><?php echo htmlspecialchars($article['author']); ?></strong> 
                dans <em><?php echo htmlspecialchars($article['category']); ?></em>
            </p>
            
            <div class="article-content article-text mt-2">
                <?php echo nl2br(htmlspecialchars($article['content'])); ?>
            </div>

            <div class="article-actions mt-3">
                <!-- US-16 Favoris -->
                <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                    <?php
                    include_once '../../modeles/Favori.php';
                    $favoriModel = new Favori($db);
                    $isFav = $favoriModel->isFavorite($_SESSION['user_id'], $article['id'], 'article');
                    ?>
                    <?php if ($isFav): ?>
                        <a href="index.php?action=remove_favorite&item_id=<?php echo $article['id']; ?>&item_type=article&from=detail" class="btn btn-danger">
                            ❤️ Retirer des favoris
                        </a>
                    <?php else: ?>
                        <a href="index.php?action=add_favorite&item_id=<?php echo $article['id']; ?>&item_type=article" class="btn btn-secondary">
                            🤍 Ajouter aux favoris
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="index.php?action=login" class="btn btn-secondary">🤍 Ajouter aux favoris</a>
                <?php endif; ?>
            </div>

            <div class="share-buttons mt-2">
                <h3>Partager cet article</h3>
                <?php 
                $currentUrl = urlencode("http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
                $articleTitle = urlencode($article['title']);
                ?>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $currentUrl; ?>" target="_blank" class="btn btn-small btn-facebook">Facebook</a>
                <a href="https://twitter.com/intent/tweet?url=<?php echo $currentUrl; ?>&text=<?php echo $articleTitle; ?>" target="_blank" class="btn btn-small btn-twitter">Twitter</a>
                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo $currentUrl; ?>&title=<?php echo $articleTitle; ?>" target="_blank" class="btn btn-small btn-linkedin">LinkedIn</a>
            </div>
        </div>
    </article>

    <!-- US-35 : Commentaires -->
    <section class="comments-section mt-4">
        <h2>Commentaires</h2>
        
        <?php if(isset($_SESSION['comment_message'])): ?>
            <?php 
            $msg = $_SESSION['comment_message'];
            $alertClass = $msg['type'] === 'success' ? 'alert-success' : 'alert-danger';
            $icon = $msg['type'] === 'success' ? 'check-circle' : 'exclamation-triangle';
            ?>
            <div class="alert <?php echo $alertClass; ?> alert-dismissible fade show" role="alert">
                <i class="bi bi-<?php echo $icon; ?> me-2"></i><?php echo htmlspecialchars($msg['text']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['comment_message']); ?>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['user_logged_in'])): ?>
            <form action="index.php?action=add_comment" method="POST" class="comment-form mb-3">
                <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                <div class="form-group">
                    <label for="comment">Votre commentaire</label>
                    <textarea id="comment" name="content" required class="form-control" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Envoyer</button>
                <small class="text-muted d-block mt-1">Votre commentaire sera visible après modération.</small>
            </form>
            
            <?php if(isset($userComments) && !empty($userComments)): ?>
                <div class="my-comments-status mb-3">
                    <h5>État de vos commentaires :</h5>
                    <?php foreach($userComments as $userComment): ?>
                        <div class="alert alert-<?php 
                            echo $userComment['status'] === 'approved' ? 'success' : 
                                ($userComment['status'] === 'rejected' ? 'danger' : 'warning'); 
                        ?> mb-2" role="alert">
                            <i class="bi bi-<?php 
                                echo $userComment['status'] === 'approved' ? 'check-circle' : 
                                    ($userComment['status'] === 'rejected' ? 'x-circle' : 'clock'); 
                            ?> me-2"></i>
                            <strong>
                                <?php 
                                    echo $userComment['status'] === 'approved' ? 'Approuvé' : 
                                        ($userComment['status'] === 'rejected' ? 'Refusé' : 'En attente'); 
                                ?>
                            </strong>
                            <small class="d-block text-muted mt-1">
                                <?php echo nl2br(htmlspecialchars($userComment['content'])); ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p><a href="index.php?action=login">Connectez-vous</a> pour laisser un commentaire.</p>
        <?php endif; ?>

        <div class="comments-list">
            <?php if(isset($commentaires) && !empty($commentaires)): ?>
                <?php foreach($commentaires as $comment): ?>
                    <div class="comment-card card mb-2">
                        <div class="comment-header">
                            <strong><?php echo htmlspecialchars($comment['username']); ?></strong>
                            <small class="text-muted">le <?php echo date('d/m/Y à H:i', strtotime($comment['created_at'])); ?></small>
                        </div>
                        <div class="comment-body">
                            <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun commentaire pour le moment. Soyez le premier !</p>
            <?php endif; ?>
        </div>
    </section>
</div>