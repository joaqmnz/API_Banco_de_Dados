<!-- HTML for static distribution bundle build -->

<?php
$root = $_SERVER["HTTP_HOST"];
?>

<!DOCTYPE html>
<html lang="pt-br">
  <head>
    <meta charset="UTF-8">
    <title>Documentação API</title>
    <link rel="stylesheet" type="text/css" href="../swagger-ui/dist/swagger-ui.css" />
    <link rel="stylesheet" type="text/css" href="index.css" />
    <link rel="icon" type="image/png" href="../swagger-ui/dist/favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="../swagger-ui/dist/favicon-16x16.png" sizes="16x16" />
  </head>

  <body>
    <div id="swagger-ui"></div>
    <script src="../swagger-ui/dist/swagger-ui-bundle.js" charset="UTF-8"> </script>
    <script src="../swagger-ui/dist/swagger-ui-standalone-preset.js" charset="UTF-8"> </script>

    <script>
      window.ui = SwaggerUIBundle({
        url: "http://<?php echo $root?>/documentation/api.php",
        dom_id: '#swagger-ui',
        deepLinking: true,
        presets: [
          SwaggerUIBundle.presets.apis,
          SwaggerUIStandalonePreset
        ],
        plugins: [
          SwaggerUIBundle.plugins.DownloadUrl
        ],
        layout: "StandaloneLayout"
      });
    </script>


  </body>
</html>