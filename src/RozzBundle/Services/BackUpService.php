<?php

namespace RozzBundle\Services;


use Symfony\Component\DependencyInjection\ContainerInterface;

class BackUpService
{
    private $host;
    private $name;
    private $user;
    private $pass;
    private $mysql;
    private $mysqlDump;

    public function __construct(ContainerInterface $container)
    {
        $this->host = $container->getParameter('database_host');
        $this->name = $container->getParameter('database_name');
        $this->user = $container->getParameter('database_user');
        $this->pass = $container->getParameter('database_password');
        $this->mysql = $container->getParameter('mysql_exe');
        $this->mysqlDump = $container->getParameter('mysql_dump_exe');

    }

    public function dump($filename)
    {
        exec("$this->mysqlDump --user=$this->user --password=$this->pass --host=$this->host $this->name > $filename");
    }

    public function import($filename)
    {
//        var_dump("$this->mysql --user=$this->user --password=$this->pass --host=$this->host $this->name < $filename");
        exec("$this->mysql --user=$this->user --password=$this->pass --host=$this->host $this->name < $filename");
    }

}