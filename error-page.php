<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 50px auto;
        }
        .error-message {
            color: red;
            font-size: 20px;
        }
        .back-link {
            color: blue;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h2>Error</h2>
    <p class="error-message">
        <?php
            // Verifica se a mensagem de erro foi passada por meio da query string
            if(isset($_GET['message'])) {
                // Obtém a mensagem de erro da query string
                $errorMessage = $_GET['message'];

                // Exibe a mensagem de erro adequada com base no código da mensagem
                switch ($errorMessage) {
                    case 'MethodNotAllowed':
                        echo "Método de requisição não permitido.";
                        break;
                    case 'NameNotProvided':
                        echo "O nome do estoque não foi fornecido.";
                        break;
                    default:
                        echo "Ocorreu um erro desconhecido.";
                        break;
                }
            } else {
                // Se nenhum código de erro foi fornecido, exibe uma mensagem genérica
                echo "Ocorreu um erro.";
            }
        ?>
    </p>
    <p>
        <a href="javascript:history.back()" class="back-link">Voltar</a>
    </p>
</body>
</html>
