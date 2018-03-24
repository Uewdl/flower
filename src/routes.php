<?php

use Slim\Http\Request;
use Slim\Http\Response;
// Routes
// require 'helper.php';

// unset($_SESSION['user']);
$app->get('/', function (Request $request, Response $response, array $args) {
    // Sample log message
    //$this->logger->info("Slim-Skeleton '/' route")

    //generateBluePrint($this->pdo);
    $stm = $this->pdo->prepare('select * from meta');
    $stm->execute();
    // Render index view
    return $this->renderer->render($response, 'index.phtml', array_merge(['metas'=>json_encode($stm->fetchAll())],$this->flash->getMessages()));
});
$app->post('/',function(Request $request, Response $response, array $args){
    $parameters = $request->getParsedBody();
    $parameters['created_at'] = date('Y-m-d H:i:s');
    $stm = $this->pdo->prepare('insert into transactions(name,amount,remark,concats,created_at)values(?,?,?,?,?)');
    $stm->bindParam(1,$parameters['name'],PDO::PARAM_STR);
    $stm->bindParam(2,$parameters['amount'],PDO::PARAM_INT);
    $stm->bindParam(3,$parameters['remark'],PDO::PARAM_STR);
    $stm->bindParam(4,$parameters['concats'],PDO::PARAM_STR);
    $stm->bindParam(5,$parameters['created_at'],PDO::PARAM_STR);
    $stm->execute();

    $formulas = json_decode($parameters['formulas']);

    foreach($formulas as $formula){
        $stm = $this->pdo->query('insert into transaction_formulas(repository_id,transaction_id,count)values('.$formula->flow.','.$this->pdo->lastInsertId().','.$formula->count.')');
    }

    $this->flash->addMessage('msg','记录添加好了');
    return $response->withRedirect('/', 302);
});
$app->get('/list',function(Request $request, Response $response, array $args){
    //$stm = $this->pdo->prepare('select t.amount,t.remark,t.concats,r.count as repository_count,tf.count,m.name from transactions t left join transaction_formulas tf on tf.transaction_id = t.id left join repository r on r.id = tf.repository_id left join meta m on m.id = r.meta_id');   
    $stm = $this->pdo->prepare('select * from transactions');   
    $stm->execute();
    return $this->renderer->render($response, 'list.phtml', compact('stm'));
});
$app->get('/formula/detail/{id:\d+}',function(Request $request, Response $response, array $args){
    $trans = $this->pdo->prepare('select * from transactions where id = ?');
    $id = +$args['id'];
    $trans->bindParam(1,$id,PDO::PARAM_INT);
    $trans->execute();
    $one = $trans->fetch(PDO::FETCH_ASSOC);

    $detail = $this->pdo->prepare('select * from transaction_formulas tf left join repository r on r.id = tf.repository_id left join meta m on m.id = r.meta_id where tf.transaction_id = ?');
    $detail->bindParam(1,$id,PDO::PARAM_INT);
    $detail->execute();
    return $response->withJson(['meta'=>$one,'formulas'=>$detail->fetchAll(PDO::FETCH_ASSOC)]);
});

$app->get('/repository',function(Request $request, Response $response, array $args){
    $stm = $this->pdo->prepare('select * from meta');
    $stm->execute();

    $stm_repository = $this->pdo->prepare('select * from repository r left join meta m on r.meta_id = m.id');
    $stm_repository->execute();
    return $this->renderer->render($response,'repository.phtml',['stm'=>$stm,'stm_repository'=>$stm_repository]);
});
$app->post('/repository',function(Request $request, Response $response, array $args){
    $parameters = $request->getParsedBody();
    $statement = $this->pdo->prepare('insert into repository(count,amount,meta_id,created_at,remark,remaining)values(:count,:amount,:meta_id,:created_at,:remark,:remaining)');
    $statement->bindParam(':count',$parameters['count'],PDO::PARAM_INT);
    $statement->bindParam(':amount',$parameters['amount'],PDO::PARAM_INT);
    $statement->bindParam(':meta_id',$parameters['meta_id'],PDO::PARAM_INT);
    $statement->bindParam(':created_at',$parameters['created_at'],PDO::PARAM_STR);
    $statement->bindParam(':remark',$parameters['remark'],PDO::PARAM_STR);
    $statement->bindParam(':remaining',$parameters['count'],PDO::PARAM_INT);
    $lastId = $statement->execute();
    $this->flash->addMessage('msg','添加了一条库存');
    return $response->withRedirect('/repository', 302);
});

$app->get('/repository/retrieval/{meta}',function(Request $request, Response $response, array $args){
    $stm_repository = $this->pdo->prepare('select * from repository r left join meta m on r.meta_id = m.id where meta_id = ?');
    $stm_repository->bindParam(1,$args['meta'],PDO::PARAM_INT);
    $r = $stm_repository->execute();

    return json_encode($stm_repository->fetchAll(PDO::FETCH_ASSOC));
});

$app->get('/meta',function(Request $request, Response $response, array $args){
    $sth = $this->pdo->prepare('select * from meta');
    $sth->execute();    
    return $this->renderer->render($response,'meta.phtml',array_merge($args,$this->flash->getMessages(),compact('sth')));
});

$app->post('/meta',function(Request $request, Response $response, array $args){
    $parameters = $request->getParsedBody();
    $statement = $this->pdo->prepare('insert into meta(name,remark)values(?,?)');
    $statement->bindParam(1,$parameters['name'],PDO::PARAM_STR);
    $statement->bindParam(2,$parameters['remark'],PDO::PARAM_STR);
    $lastId = $statement->execute();
    $this->flash->addMessage('msg','花材添加好了');
    return $response->withRedirect('/meta', 302);
});

$app->get('/login',function(Request $request, Response $response, array $args){
    if(checkAuth()){
        $this->flash->addMessage('msg','已经登陆了');
        return $response->withRedirect('/', 302);
    }   

    return $this->renderer->render($response,'login.phtml',$this->flash->getMessages());
});

$app->post('/login',function(Request $request, Response $response, array $args){
    $userinfo = $request->getParsedBody();
    $username = $userinfo['username'];
    $password = $userinfo['password'];
    $remember = isset($userinfo['remember']);

    $find = $this->pdo->prepare('select * from user where username = :user');
    $find->bindParam(':user',$username,PDO::PARAM_STR);
    $find->execute();
    $result = $find->fetch(PDO::FETCH_ASSOC);
    
    if(!$result){
        //$this->flash->addMessage('msg','请检查用户名或密码');
        //return $response->withRedirect('/login',302);
        return $response->withJson(['msg'=>'请检查用户名或密码','status'=>0]);
    }

    if(password_verify($password,$result['password'])){
        $_SESSION['user'] = $result['username'];

        // $this->flash->addMessage('msg','登陆成功');
        // return $response->withRedirect('/',302);

        return $response->withJson(['msg'=>'登陆成功','status'=>1]);
    }

    // $this->flash->addMessage('msg','密码错误');
    // return $response->withRedirect('/login',302);
    return $response->withJson(['msg'=>'密码错误','status'=>0]);
});