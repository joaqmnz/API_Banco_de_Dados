<?php

$_ENV["TOKEN"] = "Bearer joaquim1234";

$api_message = [
    "api_status" => "OK",
    "response" => []
];

$request_url = $_SERVER["REQUEST_URI"];
$request_method = $_SERVER["REQUEST_METHOD"];
$headers = getallheaders();

if(array_key_exists("Authorization", $headers)) $authorization = $headers["Authorization"];
else $authorization = "";

if($request_method == "GET"){
    header("Content-Type: application/json");

    // Separa a url
    $url = quebrar_url($request_url);

    var_dump($url);

    return;

    if($url[0] == "usuarios") {
        echo json_encode(get_all_usuarios(), JSON_PRETTY_PRINT);
        return;
    }

    if(count($url) <= 1){
        $message = [
            "status" => "ERROR",
            "sent" => $url[0] . "/",
            "expected" => "usuario/cpf/{cpf}, usuario/nome, usuario/nome/{nome}, usuario/data"
        ];
        echo error($message);
        return;
    }

    if(!($url[0] == "usuario")) {
        $message = [
            "status" => "ERROR",
            "response" => [
                "sent" => $url[0] . "/",
                "expected" => "usuario/"
            ]
        ];

        echo error($message);
        return;
    }

    if(strtolower($url[1]) == "cpf"){
        if(count($url) < 3 || count($url) > 3){
            $message = [
                "status" => "ERROR",
                "message" => [
                    "sent" => $request_url,
                    "expected" => "usuario/cpf/{cpf}"
                ]
            ];
            echo error($message);
            return;
        }

        // Buscar cpf
        $cpf = $url[2];
        $response = get_usuario_by_cpf($cpf);
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        return;
    }

    if(strtolower($url[1] == "nome")){
        if(count($url) < 3 || count($url) > 3){
            $message = [
                "status" => "ERROR",
                "message" => [
                    "sent" => $request_url,
                    "expected" => "usuario/nome/{nome}"
                ]
            ];
            echo error($message);
            return;
        }

        // Buscar nome
        $nome = $url[2];
        $response = get_usuario_by_nome($nome);

        echo json_encode($response, JSON_PRETTY_PRINT);
        return;
    }

    if(strpos($url[1], "-")){
        $data = $url[1];
        $response = get_usuario_by_data($data);
        echo json_encode($response);
    } else{
        $nome = $url[1];
        $response = get_nome($nome);
        echo json_encode($response);
    }
    
} else if($request_method == "POST"){
    header("Content-Type: application/json");
    
    if(!(strcmp($_ENV["TOKEN"], $authorization) == 0)){
        $response = [
            "status" => "ERROR",
            "response" => [
                "motive" => "WITHOUT AUTHORIZATION",
                "type" => $request_method,
                "token" => $authorization
            ]
        ];

        echo json_encode($response, JSON_PRETTY_PRINT);
        return;
    }

    if(strcmp($request_url, "/usuario/cadastro") == 0){
        $arquivo = json_decode(file_get_contents(__DIR__ . "/jsons/usuarios.json"));
        $input = json_decode(file_get_contents("php://input"));
        if(empty($arquivo)){
            $file = fopen(__DIR__ . "/jsons/usuarios.json", "w");
            fwrite($file, json_encode($input, JSON_PRETTY_PRINT));
            fclose($file);

            $response = [
                "status" => "SUCCESS",
                "response" => [
                    "added" => $input
                ]
            ];

            echo json_encode($response);
            return;
        }

        $input_keys = [];

        foreach($input as $i => $valor) $input_keys[] = $i;

        if(intval($input_keys[0]) == 0 && $input_keys[0] != "0"){
            $message = [
                "status" => "ERROR",
                "response" => [
                    "motive" => "Unexpected pattern",
                    "sent" => $input_keys[0],
                    "expected" => "Number",
                    "type" => $request_method
                ]
            ];
            
            $response = error($message);

            echo $response;
            return;
        }

        $adicionar = [];
        $removidos = [];

        $a = 0;
        $r = 0;
        foreach($input as $in => $valor_in){
            $igual = 0;
            foreach($arquivo as $ar => $valor_ar){
                if($valor_ar->cpf == $valor_in->cpf || $in == $ar) $igual++;
            }

            if($igual > 0) {
                $removidos[$in] = $valor_in;
                $r++;
            } else {
                $adicionar[$in] = $valor_in;
                $a++;
            }
        }

        $response = [
            "status" => "SUCCESS",
            "response" => [
                "added" => [
                    "data" => $adicionar,
                    "size" => $a
                ],
                "not_added" => [
                    "motive" => "Existing user",
                    "data" => $removidos,
                    "size" => $r
                ]
            ]
        ];
    
        foreach($arquivo as $ar => $valor_ar) $adicionar[$ar] = $valor_ar;

        $file = fopen(__DIR__ . "/jsons/usuarios.json", "w");
        fwrite($file, json_encode($adicionar, JSON_PRETTY_PRINT));
        fclose($file);
    
        echo json_encode($response, JSON_PRETTY_PRINT);
        return;

    } else{
        $message = [
            "status" => "ERROR",
            "response" => [
                "message" => "Invalid endpoint",
                "sent" => $request_url,
                "expected" => "/usuario/cadastro",
                "type" => "Register_User",
            ]
        ];

        $response = error($message);
        echo $response;
        return;
    }
}

function error($message){
    global $api_message;

    $api_message["response"] = $message;

    return json_encode($api_message, JSON_PRETTY_PRINT);
}
function get_nome($nome){
    $arquivo = json_decode(file_get_contents(__DIR__ . "/jsons/usuarios.json"));

    if(empty($arquivo)){
        return [
            "status" => "EMPTY",
            "query" => [
                "type" => "Get_Name",
                "value" => $nome
            ],
            "response" => "No registered users"
        ];
    }

    $aux = [];
    $i = 0;
    foreach($arquivo as $chave => $valor){
        if(in_array(strtolower($nome), explode(" ", strtolower($valor->nome)))){
            $aux[$i] = $valor;
            $i++;
        }
    }

    if(empty($aux)){
        return [
            "status" => "ERROR",
            "response" => "Name '" . $nome . "' not found ",
            "query" => [
                "type" => "Get_Name",
                "value" => $nome
            ]
        ];
    }

    return [
        "status" => "SUCCESS",
        "query" => [
            "type" => "Get_Name",
            "value" => $nome
        ],
        "response" => $aux,
        "size" => $i
    ];

}
function get_usuario_by_data($data){
    $arquivo = json_decode(file_get_contents(__DIR__ . "/jsons/usuarios.json"));

    if(empty($arquivo)){
        return [
            "status" => "EMPTY",
            "query" => [
                "type" => "Get_Date",
                "value" => $data
            ],
            "response" => "No registered users"
        ];
    }

    $aux = [];
    $i = 0;
    foreach($arquivo as $chave => $valor){
        if(strcmp($valor->data_nascimento, $data) == 0){
            $aux[$i] = $valor;
            $i++;
        }
    }

    if(empty($aux)){
        return [
            "status" => "EMPTY",
            "response" => "Date '" . $data . "' not found ",
            "query" => [
                "type" => "Get_Date",
                "value" => $data
            ]
        ];
    }

    return [
        "status" => "SUCCESS",
        "query" => [
            "type" => "Get_Date",
            "value" => $data
        ],
        "response" => $aux,
        "size" => $i
    ];
}
function get_all_usuarios(){
    $arquivo = json_decode(file_get_contents(__DIR__ . "/jsons/usuarios.json"));
    
    if(empty($arquivo)){
        return [
            "status" => "EMPTY",
            "response" => "No registered users",
            "query" => "Get_All_Users"
        ];
    }

    $response = [
        "status" => "FIND"
    ];

    $i = 0;
    foreach($arquivo as $chave => $valor) {
        $response[$i] = $valor;
        $i++;
    }

    $response["size"] = $i;

    return $response;
}
function get_usuario_by_cpf($cpf){
    $arquivo = json_decode(file_get_contents(__DIR__ . "/jsons/usuarios.json"));

    if(empty($arquivo)){
        return [
            "status" => "EMPTY",
            "response" => "No registered users",
            "query" => "Get_By_CPF"
        ];
    }


    $response = [];
    foreach($arquivo as $a => $valor){
        if(strcmp($a, $cpf) == 0){
            $response = [
                "status" => "FIND",
                "user" => $valor
            ];
            break;
        }
    }

    if(empty($response)){
        return [
            "status" => "NOT FIND",
            "response" => "CPF '" . $cpf . "' not found",
            "query" => [
                "type" => "Get_By_CPF",
                "value" => $cpf
            ]
        ];
    }

    return [
        "status" => "SUCCESS",
        "response" => $response,
        "query" => [
            "type" => "Get_By_CPF",
            "value" => $cpf
        ]
    ];
}
function get_usuario_by_nome($nome){
    $arquivo = json_decode(file_get_contents(__DIR__ . "/jsons/usuarios.json"));

    if(empty($arquivo)){
        return [
            "status" => "EMPTY",
            "response" => "No registered users",
            "query" => "Get_By_Name"
        ];
    }

    $response = [];
    $i = 0;
    foreach($arquivo as $a => $valor){
        if(in_array(strtolower($nome), explode(" ", strtolower($valor->nome)))){
            $response[$i] = $valor;
            $i++;
        }
    }

    if(empty($response)){
        return [
            "status" => "NOT FIND",
            "response" => "Name '" . $nome . "' not found",
            "query" => [
                "type" => "Get_By_Name",
                "value" => $nome
            ]
        ];
    }

    return [
        "status" => "SUCCESS",
        "response" => $response,
        "query" => [
            "type" => "Get_By_Name",
            "value" => $nome
        ],
        "find" => $i
    ];
}
function quebrar_url($request){
    $aux = explode("/", $request);

    $url = [];

    $i = 0;
    foreach($aux as $a => $v){
        if($i > 2) $url[] = $v;
        $i += 1;
    }

    return $url;
}
?>