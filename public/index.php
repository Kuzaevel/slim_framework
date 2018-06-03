<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Respect\Validation\Validator as V;

require __DIR__ . '/../vendor/autoload.php';

$settings = require __DIR__ . '/../src/settings.php';

require_once '../src/db.php';

$app = new \Slim\App(array("settings" => $settings['settings']));
$container = $app->getContainer();
//$container['view'] = new \Slim\Views\PhpRenderer("../templates/");

$container['validator'] = function () {
    return new Awurth\SlimValidation\Validator();
};

$container['view'] = function ($container) {
    $templates =  '../templates/';
    $cache = realpath('../templates/cache');
    $view = new Slim\Views\Twig($templates, compact('cache'));

    $view->addExtension(
        new Awurth\SlimValidation\ValidatorExtension($container['validator'])
    );

    return $view;
};

$app->get('/', function (Request $request, Response $response) {
    //Get all users from DB
    $sql = "SELECT * FROM users;";
    try{
        $db = new db();
        $db = $db->connect();
        $query = $db->query($sql);
        $users = $query->fetchAll(PDO::FETCH_OBJ);
        $db = null;

        //Debug_info
        if (isset($_GET["debug"])&&$_GET["debug"]==1){
            echo json_encode($users);
        }
    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}}';
    };
    $params = array(
        'users' => $users
    );

    //$app->render('index.html.twig',$vars);
    //    $response = $this->view->render($response, "index.phtml", array("users" => $users, "router" => $this->router));
    //    $response = $this->view->render($response, "index.phtml");

    // Render index-view
    $response = $this->view->render($response, "index.html.twig", $params);
    return $response;
});

$app->get('/add', function (Request $request, Response $response) {
    $params = array(
        'add'=>true,
        'title'=>'Add User'
    );

    // Render add-view
    $response = $this->view->render($response, "user.html.twig", $params);
    return $response;
});

$app->post( '/add', function (Request $request, Response $response) {
//    $data1 = $app->request->isPost();
//    $data2 = $app->request->isFormData();
//    $data = $app->request->getBody();
//    $result = array(
//        'status' => 'ok',
//        'errorCode' => 0,
//        'data' => $data,
//    );
//    $app->response->setStatus(200);
//    $app->response['Content-Type'] = 'text/html';
//    echo json_encode( $result );
//    $request ->getQueryParams();
//    echo json_encode( $request->getParsedBody() );

    $data = $request->getParsedBody();

    if ($request->isPost()) {
        $this->validator->validate($request,
            array(
                'name' => V::notBlank(),
                'surname' => V::notBlank(),
                'patronymic' => V::notBlank(),
                'tel' => V::phone(),
                'email' => V::email()
            )
        );

        if ($this->validator->isValid()) {
            // Add user in database
            $sql = "INSERT INTO users (name, surname, patronymic, tel, email) 
            VALUES (
                '$data[name]', 
                '$data[surname]', 
                '$data[patronymic]', 
                '$data[tel]', 
                '$data[email]')";

            try {
                $db = new db();
                $db = $db->connect();
                $db->exec($sql);
                $db = null;
            } catch (PDOException $e) {
                echo '{"error": {"text": ' . $e->getMessage() . '}}';
            };

            return $response->withRedirect('/');
        }
    }

    $params = array(
        'add'=>true,
        'title'=>'Add User'
    );

    return $this->view->render($response, 'user.html.twig', $params);
});

$app->get('/delete/{id}', function (Request $request, Response $response, $args) {
    $id = (int)$args['id'];
    $sql = "DELETE FROM users WHERE id = '$id'";

    try{
        $db = new db();
        $db = $db->connect();
        $db->exec($sql);
        $db = null;
    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}}';
    };

    return $response->withRedirect('/');
});

$app->get('/view/{id}', function (Request $request, Response $response, $args) {
    $id = (int)$args['id'];
    $sql = "SELECT * FROM users WHERE id = '$id'";
    try{
        $db = new db();
        $db = $db->connect();
        $query = $db->query($sql);
        $users = $query->fetchAll(PDO::FETCH_OBJ);
        $db = null;
    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}}';
    };
    $params = array(
        'user' => $users[0],
        'title'=>'View User',
        'view' => true
    );

    // Render ViewUser-view
    $response = $this->view->render($response, "user.html.twig", $params);
    return $response;
});

$app->get('/edit/{id}', function (Request $request, Response $response, $args) {
    $id = (int)$args['id'];
    $sql = "SELECT * FROM users WHERE id = '$id'";
    try{
        $db = new db();
        $db = $db->connect();
        $query = $db->query($sql);
        $users = $query->fetchAll(PDO::FETCH_OBJ);
        $db = null;
    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}}';
    };
    $params = array(
        'user' => $users[0],
        'title' =>'Edit User',
        'edit' =>true
    );

    // Render Edit-view
    $response = $this->view->render($response, "user.html.twig", $params);
    return $response;
});

$app->post('/edit', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $id = (int)$data['id'];

    $sql = "UPDATE users SET 
              name = '$data[name]',
              surname = '$data[surname]',
              patronymic = '$data[patronymic]',
              tel = '$data[tel]',
              email =  '$data[email]'
            WHERE id = $id; ";

    //todo verification and redirect back to form

    try{
        $db = new db();
        $db = $db->connect();
        $db->exec($sql);
        $db = null;
    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}}';
    };

    return $response->withRedirect('/');
});


$app->run();
