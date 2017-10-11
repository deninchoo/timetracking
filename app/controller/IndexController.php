<?php

class IndexController
{
    public function index()
    {
        $list = $this->listData();
        $view = new View();
        //$view->layout('layout'); //possible to change layout name
        $view->render('index', ['list' => $list]);
    }

    public function listData(){
        $connection = App::connect();

        $sql = 'SELECT * FROM tracking';

        $stmt = $connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

}