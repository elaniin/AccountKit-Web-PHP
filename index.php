<?php
session_start();

// verifying POST data and adding the values to session variables
if(isset($_POST["code"])){
  $_SESSION["code"] = $_POST["code"];
  $_SESSION["csrf_nonce"] = $_POST["csrf_nonce"];
  $ch = curl_init();
  // Set url elements
  $fb_app_id = '465871913602533';
  $ak_secret = 'eab92d7c75f08c6e95a48341c80b3ffc';
  $token = 'AA|'.$fb_app_id.'|'.$ak_secret;
  // Get access token
  $url = 'https://graph.accountkit.com/v1.0/access_token?grant_type=authorization_code&code='.$_POST["code"].'&access_token='.$token;
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_URL,$url);
  $result=curl_exec($ch);
  $info = json_decode($result);
  // Get account information
  $url = 'https://graph.accountkit.com/v1.0/me/?access_token='.$info->access_token;
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_URL,$url);
  $result=curl_exec($ch);
  curl_close($ch);
  $final = json_decode($result);

  $_SESSION['id'] = $final->id;

  if(isset($final->email))
  {
    $_SESSION['email'] = $final->email->address;
  }
  else
  {
    $_SESSION['country_code'] = $final->phone->country_prefix;
    $_SESSION['phone'] = $final->phone->national_number;
  }
}

?>
<html>
<head>
  <title>Login with Account Kit</title>
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="shortcut icon" href="ak-icon.png">
  <link rel="stylesheet" href="css.css">
  <!--Hotlinked Account Kit SDK-->
  <script src="https://sdk.accountkit.com/en_EN/sdk.js"></script>
</head>
<body>
<?php
// verifying if the session exists
if(empty($_SESSION)){
?>
<h1 class="ac">Login with Account Kit</h1>
<p class="ac">This example shows you how to implement<br>Facebook Account Kit for web using PHP.</p>
<div class="buttons">
  <button onclick="phone_btn_onclick();">Login with SMS</button>
  <button onclick="email_btn_onclick();">Login with Email</button>
</div>
<form action="" method="POST" id="my_form">
  <input type="hidden" name="code" id="code">
  <input type="hidden" name="csrf_nonce" id="csrf_nonce">
</form>
<?php
}else{
?>
<h1 class="ac">Login with Account Kit</h1>
<p class="ac">The session with Facebook Account Kit is already started.</p>
<h3 class="ac">Your Information</h3>
<p class="ac">
  <!-- show account information -->
  <strong>ID:</strong> <?=$_SESSION['id']?> <br>
  <?php
  if(isset($_SESSION['email'])){
  ?>  
  <strong>Email:</strong> <?=$_SESSION['email']?>
  <?php
  }else{
  ?>
  <strong>Country Code:</strong> +<?=$_SESSION['country_code']?> <br>
  <strong>Phone Number:</strong> <?=$_SESSION['phone']?> 
  <?php
  }
  ?>  
</p>
<div class="buttons">
  <button onclick="logout();">Logout</button>
</div>
<?php
}
?>
</body>
<script>
  // initialize Account Kit with CSRF protection
  AccountKit_OnInteractive = function(){
    AccountKit.init(
      {
        appId:465871913602533,         
        state:"abcd", 
        version:"v1.0"
      }
      //If your Account Kit configuration requires app_secret, you have to include ir above
    );
  };
  // login callback
  function loginCallback(response) {
    console.log(response);
    if (response.status === "PARTIALLY_AUTHENTICATED") {
      document.getElementById("code").value = response.code;
      document.getElementById("csrf_nonce").value = response.state;
      document.getElementById("my_form").submit();
    }
    else if (response.status === "NOT_AUTHENTICATED") {
      // handle authentication failure
      console.log("Authentication failure");
    }
    else if (response.status === "BAD_PARAMS") {
      // handle bad parameters
      console.log("Bad parameters");
    }
  }
  // phone form submission handler
  function phone_btn_onclick() {
    // you can add countryCode and phoneNumber to set values
    AccountKit.login('PHONE', {}, // will use default values if this is not specified
      loginCallback);
  }
  // email form submission handler
  function email_btn_onclick() {  
    // you can add emailAddress to set value
    AccountKit.login('EMAIL', {}, loginCallback);
  }
  // destroying session
  function logout() {
        document.location = 'logout.php';
  }
</script>
</html>
