<?php require_once __DIR__ . '/../app/config/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title><?php echo isset($page_title) ? $page_title . ' | ' : ''; ?>KingOfPeace Books</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          brandBlue: '#0a4ea1',
          brandGold: '#e0b100',
        }
      }
    }
  }
</script>
<link rel="icon" href="<?php echo BASE_URL; ?>/assets/images/logo.png" type="image/png">
<link rel="apple-touch-icon" href="<?php echo BASE_URL; ?>/assets/images/logo.png">
<link rel="manifest" href="<?php echo BASE_URL; ?>/site.webmanifest">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/popup.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="min-h-screen flex flex-col">
