<?php 
function checkAuth(){
    return isset($_SESSION['user']);
}
function generateBluePrint(PDO $pdo){
    $user = 'CREATE TABLE user (id integer PRIMARY KEY AUTOINCREMENT,username STRING,password STRING,created_at TIMESTAMP)';
    $repository = 'CREATE TABLE repository (id integer  PRIMARY KEY AUTOINCREMENT,meta_id integer,count integer,amount decimal(11,2) ,remaining integer ,remark STRING,created_at TIMESTAMP NULL)';
    $meta = 'CREATE TABLE meta (id integer  PRIMARY KEY AUTOINCREMENT ,name STRING ,remark TEXT)';
    $transactions = 'CREATE TABLE transactions (id integer  PRIMARY KEY AUTOINCREMENT ,amount numeric ,remark TEXT,concats string,created_at TIMESTAMP NULL)';
    $transaction_formulas = 'CREATE TABLE transaction_formulas (id integer  PRIMARY KEY AUTOINCREMENT ,count numeric ,repository_id integer,transaction_id integer)';    
    $pdo->exec($user);
    $pdo->exec($repository);
    $pdo->exec($meta);
    $pdo->exec($transaction_formulas);
    $pdo->exec($transactions);
};