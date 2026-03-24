<?php
require 'init.php';

// 1. Zpracování akcí (přidání, úprava, smazání)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Přidání nového zájmu [cite: 44, 47]
    if (isset($_POST['add'])) {
        $name = trim($_POST['name']);
        if (empty($name)) {
            $_SESSION['msg'] = "Pole nesmí být prázdné."; // [cite: 72]
        } else {
            try {
                $stmt = $db->prepare("INSERT INTO interests (name) VALUES (?)");
                $stmt->execute([$name]);
                $_SESSION['msg'] = "Zájem byl přidán."; // [cite: 68]
            } catch (PDOException $e) {
                $_SESSION['msg'] = "Tento zájem už existuje."; // [cite: 71]
            }
        }
    }

    // Úprava existujícího zájmu [cite: 55, 58]
    if (isset($_POST['update'])) {
        $id = $_POST['id'];
        $name = trim($_POST['name']);
        if (!empty($name)) {
            $stmt = $db->prepare("UPDATE interests SET name = ? WHERE id = ?");
            $stmt->execute([$name, $id]);
            $_SESSION['msg'] = "Zájem byl upraven."; // [cite: 69]
        }
    }

    // Smazání zájmu [cite: 51, 54]
    if (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $stmt = $db->prepare("DELETE FROM interests WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['msg'] = "Zájem byl odstraněn."; // [cite: 70]
    }

    // PRG pattern: Přesměrování po POST požadavku [cite: 61, 62, 64]
    header("Location: index.php");
    exit;
}

// 2. Načtení dat pro zobrazení [cite: 40, 42]
$stmt = $db->prepare("SELECT * FROM interests");
$stmt->execute();
$interests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pomocná logika pro editační formulář
$editInterest = null;
if (isset($_GET['edit'])) {
    foreach ($interests as $i) {
        if ($i['id'] == $_GET['edit']) {
            $editInterest = $i;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>IT Profil 6.0</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Moje zájmy</h1>

    <?php if (isset($_SESSION['msg'])): ?>
        <div class="msg" style="background: #e7f3fe; padding: 10px; margin-bottom: 10px;">
            <?= htmlspecialchars($_SESSION['msg']) ?>
        </div>
        <?php unset($_SESSION['msg']); ?>
    <?php endif; ?>

    <ul>
        <?php foreach ($interests as $interest): ?>
            <li style="margin-bottom: 10px;">
                <strong><?= htmlspecialchars($interest['name']) ?></strong>
                <a href="?edit=<?= $interest['id'] ?>">Upravit</a>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $interest['id'] ?>">
                    <button type="submit" name="delete">Smazat</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>

    <hr>

    <?php if ($editInterest): ?>
        <form method="POST">
            <h3>Upravit zájem</h3>
            <input type="hidden" name="id" value="<?= $editInterest['id'] ?>">
            <input type="text" name="name" value="<?= htmlspecialchars($editInterest['name']) ?>" required>
            <button type="submit" name="update">Uložit změny</button>
            <a href="index.php">Zrušit</a>
        </form>
    <?php else: ?>
        <form method="POST">
            <h3>Přidat nový zájem</h3>
            <input type="text" name="name" placeholder="Vepište zájem...">
            <button type="submit" name="add">Přidat</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>