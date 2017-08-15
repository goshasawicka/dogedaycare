<?php

require '../config.php';

$db = new PDO("sqlite: /../../db.sqlite");

//INDEX:

$app->get('/', function ($request, $response, $args) use ($app) {
    $title = "Hello";
    $body = "Welcome to Doge Day Care - give us a high paw!";

    $this->view->render($response, 'hello.html', array('title' =>$title,'body' =>$body));
})->setName('home');

//OWNERS:

$app->get('/owners', function($request, $response, $args) use ($app, $db) {
    $sql = "SELECT * FROM dogs_table";
    $dogs = $db->query($sql)->fetchAll();
    $this->view->render($response, 'owners_list.html', array('dogs'=>$dogs));
    $db = null;
})->setName('owners');


$app->get('/owner/{id}', function($request, $response, $args) use ($app, $db){
    $id = $request->getAttribute('route')->getArgument('id');
    $sql = 'SELECT * FROM dogs_table WHERE id = ? LIMIT 1';
    $dogPDO = $db->prepare($sql);
    $dogPDO->execute([$id]);
    $dog = $dogPDO->fetch();
    $sql_inv = 'SELECT * FROM invoices WHERE dog_id = ?;';
    $invoicesPDO = $db->prepare($sql_inv);
    $invoicesPDO->execute([$id]);
    $invoices= $invoicesPDO->fetchAll();
    $this->view->render($response, 'owner_show.html', array('dog'=>$dog, 'invoices'=>$invoices));
    $db = null;
})->setName('owner');


$app->map(['GET', 'POST'], '/new_owner', function ($request, $response, $args) use ($app, $db) {

    if ($request->getMethod() == 'GET') {
        $this->view->render($response, 'owner_new.html');
    }elseif ($request->getMethod() == 'POST') {
        $first_name = $request->getParsedBodyParam('first_name');
        $last_name = $request->getParsedBodyParam('last_name');
        $phone = $request->getParsedBodyParam('phone');
        $dog_name = $request->getParsedBodyParam('dog_name');
        $breed = $request->getParsedBodyParam('breed');

        $sql = "INSERT INTO dogs_table (dog_name, breed, owner_first_name, owner_last_name, owner_phone) VALUES (?, ?, ?, ?, ?);";
        $create = $db->prepare($sql);
        $create->execute([$dog_name, $breed, $first_name, $last_name, $phone]);
        $db=null;

        return $response->withRedirect($this->router->pathFor('owners'), 200);
    }
})->setName('new_owner');


$app->map(['GET', 'POST'], '/edit_owner/{id}', function ($request, $response, $args) use ($app, $db) {
    $id = $request->getAttribute('route')->getArgument('id');

    if ($request->getMethod() == 'GET') {
        $sql = 'SELECT * FROM dogs_table WHERE id = ? LIMIT 1';
        $dogPDO = $db->prepare($sql);
        $dogPDO->execute([$id]);
        $dog = $dogPDO->fetch();
        $this->view->render($response, 'owner_edit.html', array('dog'=>$dog));
        $db = null;

    }elseif ($request->getMethod() == 'POST'){
//    always set: Content-Type: application/x-www-form-urlencoded
        $first_name = $request->getParsedBodyParam('first_name');
        $last_name = $request->getParsedBodyParam('last_name');
        $phone = $request->getParsedBodyParam('phone');
        $dog_name = $request->getParsedBodyParam('dog_name');
        $breed = $request->getParsedBodyParam('breed');

        $sql = "UPDATE dogs_table SET dog_name = ?, breed = ?, owner_first_name = ?, owner_last_name = ?, owner_phone = ? WHERE id = ?;";
        $edit = $db->prepare($sql);
        $edit->execute([$dog_name,$breed,$first_name, $last_name, $phone, $id]);
        $db = null;

        return $response->withRedirect($this->router->pathFor('owners'), 200);
    }
})->setName('edit_owner');


$app->post('/delete_owner/{id}', function($request, $response) use ($db){
    $id = $request->getAttribute('route')->getArgument('id');
    $sql = 'DELETE FROM dogs_table WHERE id = ?;';
    $delete = $db->prepare($sql);
    $delete -> execute([intval($id)]);
    $db=null;
    return $response->withRedirect($this->router->pathFor('owners'), 200);

})->setName('delete');


//DOGS:

$app->get('/dogs', function($request, $response, $args) use ($app, $db) {
    $sql = "SELECT id, dog_name, breed FROM dogs_table;";
    $dogs = $db->query($sql)->fetchAll();
    $this->view->render($response, 'dogs_list.html', array('dogs'=>$dogs));
    $db = null;
})->setName('dogs');


$app->get('/dog/{id}', function($request, $response, $args) use ($app, $db){
    $id = $request->getAttribute('route')->getArgument('id');
    echo $id;
    $sql = "SELECT * FROM dogs INNER JOIN owners on dogs.owner_id = owners.id WHERE dogs.id = ? LIMIT 1;";
    $dogsPDO = $db->prepare($sql);
    $dogsPDO->execute([$id]);
    $dog = $dogsPDO->fetch();
    $this->view->render($response, 'dog_show.html', array('dog'=>$dog));
    $db = null;
})->setName('dog');


// INVOICES:
$app->get('/invoices', function($request, $response, $args) use ($app, $db) {
    $sql = "SELECT * FROM dogs_table INNER JOIN invoices on dogs_table.id = invoices.dog_id;";
    $invoices = $db->query($sql)->fetchAll();
    $this->view->render($response, 'invoices.html', array('invoices'=>$invoices));
    $db = null;
})->setName('invoices');


$app->map(['GET', 'POST'], '/new_invoice', function ($request, $response, $args) use ($app, $db) {

    if ($request->getMethod() == 'GET') {
        $sql = "SELECT id, owner_first_name, owner_last_name, dog_name FROM dogs_table";
        $owners = $db->query($sql)->fetchAll();
        $this->view->render($response, 'invoice_new.html', array('owners'=> $owners));
    }elseif ($request->getMethod() == 'POST') {
        $date = $request->getParsedBodyParam('date');
        $amount = $request->getParsedBodyParam('amount');
        $memo = $request->getParsedBodyParam('memo');
        $owner = $request->getParsedBodyParam('owner');

        $sql = "INSERT INTO invoices (date, amount, memo, dog_id) VALUES (?, ?, ?, ?);";
        $create = $db->prepare($sql);
        $create->execute([$date, $amount, $memo, $owner]);
        $db=null;
        echo $date.$amount.$memo.$owner;

        return $response->withRedirect($this->router->pathFor('invoices'), 200);
    }
})->setName('new_invoice');




