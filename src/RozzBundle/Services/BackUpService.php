<?php

namespace RozzBundle\Services;


class BackUpService
{
    private $host;
    private $name;
    private $user;
    private $pass;

    public function __construct($host, $name, $user, $pass)
    {
        $this->host = $host;
        $this->name = $name;
        $this->user = $user;
        $this->pass = $pass;
    }

    public function dump($filename){
        exec('C:\xampp\mysql\bin\mysqldump.exe --user='.$this->user.' --password='.$this->pass.' --host='.$this->host.' '.$this->name.' > '.$filename);
    }

}