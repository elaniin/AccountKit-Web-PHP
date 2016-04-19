<?php
// function to verify session status
// función para verificar el estado de ls sesión
function is_session_started()
{
    if ( php_sapi_name() !== 'cli' ) {
        if ( version_compare(phpversion(), '5.4.0', '>=') ) {
            return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
        } else {
            return session_id() === '' ? FALSE : TRUE;
        }
    }
    return FALSE;
}
// verifying POST data and adding the values to session variables
// verificando los datos POST y añadiendo los valores a variables de sesión
if(isset($_POST["code"])){
  session_start();
  $_SESSION["code"] = $_POST["code"];
  $_SESSION["csrf_nonce"] = $_POST["csrf_nonce"];

  $ch = curl_init();
  // Set url elements
  // Estableciento elementos de la url
  $fb_app_id = '465871913602533';
  $ak_secret = 'eab92d7c75f08c6e95a48341c80b3ffc';
  $token = 'AA|'.$fb_app_id.'|'.$ak_secret;
  // Get access token
  // Obteniendo access token
  $url = 'https://graph.accountkit.com/v1.0/access_token?grant_type=authorization_code&code='.$_POST["code"].'&access_token='.$token;

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_URL,$url);
  $result=curl_exec($ch);
  curl_close($ch);

  $info = json_decode($result);

  // Get account information
  // Obteniendo información de la cuenta
  $url = 'https://graph.accountkit.com/v1.0/me/?access_token='.$info->access_token;

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_URL,$url);
  $result=curl_exec($ch);
  curl_close($ch);

  $final = json_decode($result);
}
?>
<html>
<head>
	<title>Iniciar Sesión con AccountKit</title>
  <link rel="shortcut icon" href="ak-icon.png">
  <link rel="stylesheet" href="css.css">
  <!--Hotlinked Account Kit SDK-->
  <!--Account Kit SDK importado directamente-->
  <script src="https://sdk.accountkit.com/es_ES/sdk.js"></script>
</head>
<body>
<?php
// verifying if the session exists
// verificando si la sesión existe
if(is_session_started() === FALSE && !isset($_SESSION)){
?>
<h1 class="ac">Iniciar Sesión con AccountKit</h1>
<p class="ac">Este ejemplo te muestra como implementar<br>Facebook AccountKit para web usando PHP.</p>
<div class="buttons">
  <button onclick="phone_btn_onclick();">Iniciar Sesión con SMS</button>
  <button onclick="email_btn_onclick();">Iniciar Sesión con Email</button>
</div>
<form action="" method="POST" id="my_form">
  <input type="hidden" name="code" id="code">
  <input type="hidden" name="csrf_nonce" id="csrf_nonce">
</form>
<?php
}else{
?>
<h1 class="ac">Iniciar Sesión con AccountKit</h1>
<p class="ac">La sesión con Facebook AccountKit ya está iniciada.</p>
<h3 class="ac">Tu Información</h3>
<p class="ac">
  <!-- show account information -->
  <!-- mostrar información de la cuenta -->
  <strong>ID:</strong> <?=$final->id?> <br>
  <strong>Código de País:</strong> +<?=$final->phone->country_prefix?> <br>
  <strong>Número de Teléfono:</strong> <?=$final->phone->national_number?> 
</p>
<div class="buttons">
  <button onclick="logout();">Cerrar Sesión</button>
</div>
<?php
}
?>
</body>
<script>
  // initialize Account Kit with CSRF protection
  // inicialización de Account Kit con protección CSRF
  AccountKit_OnInteractive = function(){
    AccountKit.init(
      {
        appId:465871913602533,         
        state:"abcd", 
        version:"v1.0"
      }
      //Si tu configuración en Account Kit lo requiere, debes incluir el parametro app_secret arriba
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
      console.log("Sin Autenticar");
    }
    else if (response.status === "BAD_PARAMS") {
      // handle bad parameters
      console.log("Parámetros Erroneos");
    }
  }

  // phone form submission handler
  function phone_btn_onclick() {
    // you can add countryCode and phoneNumber to set values
    // puedes añadir countryCode y phoneNumber para establecer valores
    AccountKit.login('PHONE', // will use default values if this is not specified * Se utilizarán los valores predeterminados si estos no son especificados
      loginCallback);
  }

  // email form submission handler
  function email_btn_onclick() {  
    // you can add emailAddress   to set value
    // puedes añadir emailAddress para establecer su valor
    AccountKit.login('EMAIL', loginCallback);
  }

  // destroying session
  function logout() {
        document.location = 'logout.php';
  }

</script>
</html>