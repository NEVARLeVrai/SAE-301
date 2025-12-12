<div class="container mt-4">
    <h2>Vendre un instrument</h2>
    
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php?action=sell_instrument" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="name" class="form-label">Titre de l'annonce</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="5" required></textarea>
        </div>
        
        <div class="mb-3">
            <label for="price" class="form-label">Prix (€)</label>
            <input type="number" step="0.01" class="form-control" id="price" name="price" required>
        </div>
        
        <div class="mb-3">
            <label for="category" class="form-label">Catégorie (Instrument)</label>
            <input type="text" class="form-control" id="category" name="category" placeholder="Ex: Guitare, Piano..." required>
        </div>
        
        <div class="mb-3">
            <label for="image" class="form-label">Photo (Max 5 - Ici 1 pour simplifier)</label>
            <input type="file" class="form-control" id="image" name="image" accept="image/*">
        </div>
        
        <button type="submit" class="btn btn-primary">Soumettre l'annonce</button>
    </form>
</div>
