<!DOCTYPE html>
<html lang="en">
<head>
    <title>AgriShop: Farm Online Website</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
   
</head>
  <meta charset="UTF-8">
  <title>SRP - Product Prices</title>
  <style>
    * {
      box-sizing: border-box;
    }
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 20px;
      background: #f5f5f5;
    }
    h1 {
      text-align: center;
      margin-bottom: 30px;
    }
    .container {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      justify-content: center;
    }
    .card {
      background: white;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      width: 220px;
      overflow: hidden;
      text-align: center;
      padding-bottom: 15px;
      transition: transform 0.2s;
    }
    .card:hover {
      transform: scale(1.02);
    }
    .card img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-bottom: 1px solid #ddd;
    }
    .card h3 {
      margin: 10px 0 5px;
      font-size: 16px;
    }
    .card p {
      margin: 0;
      font-weight: bold;
      color: #333;
    }
    .price {
      color: #2a8b2a;
    }
  </style>
</head>
<body>
<nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a href="index.php" class="navbar-brand">AgriShop: Farm Online Website</a>
        </div>

        <div class="collapse navbar-collapse" id="navbar-collapse">
            <ul class="nav navbar-nav navbar-right">
                <li><a href="srp.php"><i class="fas fa-home"></i> HOME</a></li>
                <li><a href="buying.php"><i class="fas fa-shopping-cart"></i> BUYING</a></li>
                <li><a href="selling.php"><i class="fas fa-tag"></i> SELLING</a></li>
                <li><a href="mainhome.php"><i class="fas fa-home"></i> TRANSACT</a></li>
                <li><a href="message.php"><i class="fas fa-envelope fa-2x"></i></a></li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        <img src="<?php echo $profile_image; ?>" alt="Profile" class="profile-pic-nav"> <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                        <li role="separator" class="divider"></li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
           
                </li>
                         
                    
            </ul>
        </div>
    </div>
</nav>


<h1>Suggested Retail Price (SRP)</h1>


<div class="container">
  <?php
    $products = [
      [
        'name' => 'Atsuete',
        'image' => 'images/atsuete.jpg',
        'weight' => '1kg',
        'price' => 350.00
      ],
      [
        'name' => 'Watermelon Yellow Oblong',
        'image' => 'images/watermelon-yellow.jpg',
        'weight' => 'kg',
        'price' => 70.00
      ],
      [
        'name' => 'Watermelon Red Round',
        'image' => 'images/watermelon-red.jpg',
        'weight' => 'kg',
        'price' => 60.00
      ],
      [
        'name' => 'Kalubay',
        'image' => 'images/kalubay.jpg',
        'weight' => '1kg',
        'price' => 45.00
      ]
    ];

    foreach ($products as $product) {
      echo '<div class="card">';
      echo '<img src="' . $product['image'] . '" alt="' . $product['name'] . '">';
      echo '<h3>' . $product['name'] . ' <small>' . $product['weight'] . '</small></h3>';
      echo '<p class="price">₱ ' . number_format($product['price'], 2) . ' / kilogram</p>';
      echo '</div>';
    }
  ?>
</div>

</body>
</html>
