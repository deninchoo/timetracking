<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class FormController
{
    /**
     * Registration page with form
     */
    public function index()
    {
        $view = new View();
        $view->render('form', []);
    }

    /**
     * Form submit
     */
    public function submit()
    {
        //get $_POST data, validate data
        $data = $this->_validate($_POST);


        if($data === false) {
            header('Location: ' . App::config('url').'form/index');
        }

        // write to database
        $connection = App::connect();

        $sql = 'INSERT INTO tracking
                (iznos, opis, datum, vrsta, zaposlenik)
                VALUES (:iznos, :opis, :datum, :vrsta, :zaposlenik)';

        $stmt = $connection->prepare($sql);
        $stmt->execute($data);

        //@todo: upload submitted file to /private folder, relate file with attendee

        // redirect to thank you page
        header('Location: ' . App::config('url'));
    }

    public function delete(){
        $connection = App::connect();
        $sql = 'DELETE FROM tracking WHERE id = :id';
        $stmt = $connection->prepare($sql);
        $stmt->execute(['id' => $_POST['delete']]);
        header('Location: ' . App::config('url'));
    }

    public function edit(){
        $connection = App::connect();

        $sql = 'SELECT * FROM tracking WHERE id = :id';

        $stmt = $connection->prepare($sql);
        $stmt->execute(['id' => $_POST['edit']]);
        $result = $stmt->fetch();

        $view = new View();
        $view->render('edit', ['data' => $result]);
    }

    public function update(){
        $data = $this->_validate($_POST);
        $id = $_POST['id'];
        $connection = App::connect();
        $sql = 'UPDATE tracking SET zaposlenik = :zaposlenik, iznos = :iznos, opis = :opis, datum = :datum, vrsta = :vrsta WHERE id =:id';
        $stmt = $connection->prepare($sql);
        $stmt->execute($data);
        header('Location: ' . App::config('url'));
    }

    /**
     * @param $data
     * @return array|bool
     */
    private function _validate($data)
    {
        $required = ['iznos', 'opis', 'datum', 'vrsta', 'zaposlenik'];
        $other = ['id'];
        $all = array_merge($required, $other);

        // remove unknown keys from data if any
        $data = array_diff_key($data, $all);

        //validate required keys
        foreach($required as $key) {
            if(!isset($data[$key])) {
                return false;
            }

            // trim (strip whitespaces from values) then check if empty
            $data[$key] = trim((string)$data[$key]);
            if(empty($data[$key])) {
                return false;
            }
        }
        return $data;

    }

    /**
     * Thank you page
     */
    public function thankyou()
    {
        $view = new View();
        $view->render('thankyou');

        // log new entry

        // create a log channel
        $log = new Logger('default');
        $log->pushHandler(new StreamHandler(BP . 'private/default.log', Logger::INFO));

        // add record to the log
        $log->info('Thank you page, entry was created');
    }

}