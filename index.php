<!DOCTYPE html>
<html lang="en">
   <head>
      <!-- basic -->
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <!-- mobile metas -->
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="viewport" content="initial-scale=1, maximum-scale=1">
      <!-- site metas -->
      <title>yogast</title>
      <meta name="keywords" content="">
      <meta name="description" content="">
      <meta name="author" content="">
      <!-- bootstrap css -->
      <link rel="stylesheet" href="css/bootstrap.min.css">
      <!-- style css -->
      <link rel="stylesheet" href="css/style.css">
      <!-- responsive-->
      <link rel="stylesheet" href="css/responsive.css">
      <!-- awesome fontfamily -->
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

   </head>
   <!-- body -->
   <body class="main-layout">
      <!-- loader  -->
      <div class="loader_bg">
         <div class="loader"><img src="images/loading.gif" alt="" /></div>
      </div>
      <!-- end loader -->
      <div id="mySidepanel" class="sidepanel">
         <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">×</a>
         <a class="active" href="index.html">Home</a>
         <a href="about.html">About</a>
         <a href="games.html">Games</a>
         <a href="contact.html">Contact</a>
      </div>
      <!-- header -->
            <header>
               <!-- header inner -->
               <div class="head-top">
                  <div class="container-fluid">
                     <div class="row d_flex">
                        <div class="col-sm-3">
                           <div class="logo">
                              <a href="index.php"><img src="images/logo.png" /></a>
                           </div>
                        </div>
                        <div class="col-sm-9">
                           <ul class="email text_align_right">
                              <li><a href="joueur_login.php" class="btn btn-light btn-sm" style="border-radius: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: all 0.3s ease; padding: 6px 15px; font-weight: 500;">Login</a></li>
                              <li><a href="joueur_login.php" class="btn btn-outline-light btn-sm" style="background-color: rgb(37, 36, 75); color: white; border-radius: 20px; border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.2); transition: all 0.3s ease; padding: 6px 15px; font-weight: 500;">S'inscrire</a></li>
                              <li> <button class="openbtn" onclick="openNav()"><img src="images/menu_btn.png"></button></li>
                           </ul>
                        </div>
                     </div>
                  </div>
               </div>
            </header>
            <!-- end header -->
      <!-- end header -->
      <!-- start slider section -->
      <div  class=" banner_main">
         <div class="container-fluid">
            <div class="row">
               <div class="col-md-12">
                  <div class="club">
                     <figure><img class="sm_n0" src="images/bbnner.png"> <img class="tes_n0" src="images/banner1.jpg"> </figure>
                     <div class="poa_t">
                        <h1>GAME ON!</h1>
                        <p>Reserve your field in just a few clicks and make the most of your game time!</p>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
 <!-- end slider section -->
<div class="footbol">
   <div class="container-fluid">
      <div class="row">
         <div class="col-md-12">
            <div id="myCarousel" class="carousel slide" data-ride="carousel">
               <ol class="carousel-indicators">
                  <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
                  <li data-target="#myCarousel" data-slide-to="1"></li>
                  <li data-target="#myCarousel" data-slide-to="2"></li>
               </ol>
               <div class="carousel-inner">
                  <div class="carousel-item active">
                     <div class="container-fluid">
                        <div class="carousel-caption relative">
                           <div class="bluid">
                              <div class="foot_img">
                                 <figure><img src="images/foot.png" alt="#"/></figure>
                              </div>
                              <a class="read_more" href="joueur_login.php">Reserve Now </a>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="carousel-item">
                     <div class="container-fluid">
                        <div class="carousel-caption relative">
                           <div class="bluid">
                              <div class="foot_img">
                                 <figure><img src="images/basket.png" alt="#"/></figure>
                              </div>
                              <a class="read_more" href="joueur_login.php">Reserve Now </a>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="carousel-item">
                     <div class="container-fluid">
                        <div class="carousel-caption relative">
                           <div class="bluid">
                              <div class="foot_img">
                                 <figure><img src="images/volley.png" alt="#"/></figure>
                              </div>
                              <a class="read_more" href="joueur_login.php">Reserve Now </a>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <a class="carousel-control-prev" href="#myCarousel" role="button" data-slide="prev">
                  <i class="fa fa-angle-left" aria-hidden="true"></i>
                  <span class="sr-only">Previous</span>
               </a>
               <a class="carousel-control-next" href="#myCarousel" role="button" data-slide="next">
                  <i class="fa fa-angle-right" aria-hidden="true"></i>
                  <span class="sr-only">Next</span>
               </a>
            </div>
         </div>
      </div>
   </div>
</div>
<!-- sports -->
      <div class="sports">
         <div class="container">
            <div class="row">
               <div class="col-md-10 offset-md-1">
                  <div class="titlepage text_align_center">
                     <h2>Explore Our Stadiums</h2>
                  </div>
               </div>
            </div>
            <div class="row d_flex">
               <div class="col-md-3">
                  <div class="sports_main text_align_center">
                     <figure><img  src="images/sport1.png" alt="#"/></figure>
                     <div class="sports_text">
                        <h3>Game 1</h3>
                        <p>Played with six players per team, the goal is to send the ball over the net and score by making it touch the ground.
                        </p>
                     </div>
                  </div>
               </div>
               <div class="col-md-6">
                  <div class="sports_main text_align_center">
                     <figure><img class="dorder" src="images/sport2.png" alt="#"/></figure>
                     <div class="sports_text">
                        <h3 class="dark3">Game 2</h3>
                        <p>A five-player team game where<br> points are scored by shooting the<br>  ball into a hoop placed high<br> above the ground.</p> 
                     </div>
                  </div>
               </div>
               <div class="col-md-3">
                  <div class="sports_main text_align_center">
                     <figure><img src="images/sport3.png" alt="#"/></figure>
                     <div class="sports_text">
                        <h3>Game 3</h3>
                        <p>Two teams of eleven compete to score goals by kicking the ball into the opponent’s net on a large field.
                        </p>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <!-- end sports -->
      <!-- about -->
      <div class="about">
         <div class="container">
            <div class="row">
               <div class="col-md-12">
                  <div class="titlepage text_align_center">
                     <h2>About Our Club</h2>
                     <p>Book your favorite sports field easily and enjoy an unforgettable game with friends! Our platform lets you find and reserve courts in just a few clicks</p>
                  </div>
               </div>
               <div class="col-md-12">
                  <div class="about_img">
                     <figure><img class="img_responsive" src="images/about_img.png" alt="#"/></figure>
                     <div class="abo_btn">
                        <a href="Javascript:void(0)"><img src="images/about_btn.png" alt="#"/></a>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <!-- end about -->
     

      <!-- footer -->
      <footer>
         <div class="footer">
            <div class="container">
               <div class="row">
                  <div class="col-md-8 offset-md-2">
                     <h3>Follow Us</h3>
                     <ul class="social_icon text_align_center">
                        <li> <a href="Javascript:void(0)"><i class="fa fa-facebook-f"></i></a></li>
                        <li> <a href="Javascript:void(0)"><i class="fa fa-twitter"></i></a></li>
                        <li> <a href="Javascript:void(0)"><i class="fa fa-instagram" aria-hidden="true"></i></a></li>
                     </ul>
                     <div class="conta text_align_center">
                        <ul>
                           <li> <a href="Javascript:void(0)"><img src="images/call.png" alt="#"/> Call +01 1234567890
                              </a>
                           </li>
                           <li> <a href="Javascript:void(0)"><img src="images/mall.png" alt="#"/> mohamedoueslati080@gmail.com
                              </a>
                           </li>
                        </ul>
                     </div>
                  </div>
                  <div class="col-md-8 offset-md-2">
                     <div class="menu_bottom text_align_center">
                        <ul>
                           <li><a href="index.html">Home</a></li>
                           <li><a href="about.html">About</a></li>
                           <li><a href="games.html">Games</a></li>
                           <li><a href="contact.html">Contact</a></li>
                        </ul>
                     </div>
                  </div>
               </div>
            </div>
            <div class="copyright text_align_center">
               <div class="container">
                  <div class="row">
                     <div class="col-md-8 offset-md-2">
                        <p>© 2025 All Rights Reserved. </p>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </footer>
      <!-- end footer -->
      <!-- Javascript files-->
      <script src="js/jquery.min.js"></script>
      <script src="js/bootstrap.bundle.min.js"></script>
      <script src="js/jquery-3.0.0.min.js"></script>
      <script src="js/custom.js"></script>
   </body>
</html>