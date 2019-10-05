<?php
/**
 * Created by PhpStorm.
 * User: Jivko
 * Date: 2.6.2019 г.
 * Time: 12:50
 */

namespace RozzBundle\Services;


use Doctrine\ORM\EntityManager;
use RozzBundle\Entity\Line;
use RozzBundle\Entity\Point;

class CadService
{
    /**
     * @var \Camellia_Converter
     */
    private $convertor;
    private $em;
    private $currentBlock;
    private $currentGraphicObjectType;
    private $currentGraphicObject;
    private $currentGraphicObjectsCount;
    private $cadObject;

    private $lineNumber;
    private $lineCount;
    private $errors = array();

    //batch size - create several objects and then flush connection
    const BATCH_SIZE = 1;

    //Graphic objects
    const POINT = "POINT";
    const LINE = "LINE";
    const CONTOUR = "CONTOUR";
    const TEXT = "TEXT";

    //file blocks
    const SKIP = "SKIP";//used to skip blocks, for now is processed only Layer cadaster
    const HEADER = "HEADER";
    const LAYER = "LAYER";
    const CONTROL = "CONTROL";


    //таблици към слой кадастър
//    private $tablePozemImoti;
//    private $tablePrava;
//    private $tablePersons;
//    private $tableSgradi;
//    private $tableAparts;
//    private $tableUlic;
//    private $tableAddres;
//    private $tableMestnosti;
//    private $tableZapovedi;
//    private $tableIzdateli;
//    private $tableHistory;
//    private $tableServituti;
//    private $tableOgrpimo;
//    private $tableDocs;
//    private $tableGorImoti;
//    //таблици към слой гори
//    private $tablePodotdeli;
//    //таблици към слой регулация
//    private $tableRegconturi;
//    private $tablePrimesta;
//    private $tableOtregdane;
//    //таблици към слой почвена категория
//    private $tableKategoria;
//
//    private $tableImotkateg;

    public function __construct(EntityManager $em)
    {
        $this->convertor = new \Camellia_Converter();
        $this->em = $em;
    }


    public function import($filePath)
    {
        //mb_internal_encoding("UTF-8");
        $handle = fopen($filePath, "r");
        if ($handle) {
            $start = microtime(true);

            //set cad object
            $this->cadObject;
            $this->currentGraphicObjectsCount = 0;
            //read cad line by line
            while (($line = fgets($handle)) !== false) {

//                $line = $this->convertor->convert('bulgarian-mik', 'utf-8', $line);
                $this->setCurrentBlock($line);

                //process line by current block
                $this->processLine($line);

            }
            fclose($handle);
            $end = microtime(true);
            dump($end-$start);
            exit;
        } else {
            // error opening the file.
        }
    }

    private function createPoint($line)
    {

    }

    private function createLine($line)
    {
        $this->currentGraphicObjectType = self::LINE;

        if($this->currentGraphicObjectsCount > self::BATCH_SIZE){
            $this->em->flush();
            $this->em->clear();
            $this->currentGraphicObjectsCount = 0;
        }


        $lineArray = explode(' ', $line);

        $this->currentGraphicObject = new Line();
        $this->currentGraphicObject->setType($lineArray[1]);
        $this->currentGraphicObject->setNumber($lineArray[2]);
        $this->currentGraphicObject->setBorderType($lineArray[3]);

        $this->em->persist($this->currentGraphicObject);

        $this->currentGraphicObjectsCount++;
    }

    private function addPointsToLineObject($line)
    {
        $pointsArray = explode(';', $line);

        foreach ($pointsArray as $pointStr){
            $pointArray = explode(' ', $pointStr);

            if(is_array($pointArray) && count($pointArray) === 6){
                $currentPoint = new Point();
                $currentPoint->setNumber($pointArray[0]);
                $currentPoint->setX($pointArray[1]);
                $currentPoint->setY($pointArray[2]);
                $currentPoint->setZ('0');
                $currentPoint->setType($pointArray[3]);
                $currentPoint->setLine($this->currentGraphicObject);

                $this->em->persist($currentPoint);
            }
        }
    }

    private function createContour($line)
    {

    }


    private function processLayerLine($line)
    {
        //empty row
        if (strlen($line)<2 || $this->startsWith($line, 'END')){
            //empty row or end of block
            return false;
        }

        //point
        elseif($this->startsWith($line, 'P ')) {
            //Todo process points
            //set global graphic object to point and process first line of point
            $this->currentGraphicObject = self::POINT;

            return false;
        }

        //line
        elseif ($this->startsWith($line, 'L ')) {
            //set global graphic object to line and process first line of line
            $this->currentGraphicObjectType = self::LINE;

            $rowArray = explode(' ', $line);

            if(count($rowArray) !== 6){
                //todo throw exception
                dump($rowArray);
                $error = "Грешка в ред $this->lineNumber";
                array_push($this->errors, $error);
                return false;
            }else{

                $this->createLine($line);
            }

        }


        //contours
        elseif ($this->startsWith($line,'C ')) {
            //set global graphic object to contour and process first line of contour
            $this->currentGraphicObjectType = self::CONTOUR;



        }

        //texts
        elseif ($this->startsWith($line,'T ')) {
            //Todo: process texts
            $this->currentGraphicObjectType = self::TEXT;
            return false;

        }

        //inner rows
        else {

            //global graphic object is already set, process next line

            //inner line rows
            if($this->currentGraphicObjectType === self::LINE){
                $this->addPointsToLineObject($line);

            }

            //inner contour rows
            elseif ($this->currentGraphicObjectType === self::CONTOUR){

            }

            //inner text rows
            elseif ($this->currentGraphicObjectType === self::TEXT){
                return false;
            }
        }

    }

    private function processControlLine($line)
    {
       //todo implement control block, to add area to contours
    }

    private function processLine($line){
        if($this->currentBlock === self::SKIP){
            return false;
        }

        if($this->currentBlock === self::LAYER){
            $this->processLayerLine($line);

        }elseif ($this->currentBlock == self::CONTROL)
            $this->processControlLine($line);

    }

    private function setCurrentBlock($line)
    {
        //if line starts with HEADER
        if( $this->startsWith($line,self::HEADER)){
            $this->currentBlock = self::HEADER;
        }elseif ( $this->startsWith($line,self::LAYER)){
            $this->currentBlock = self::LAYER;
        }elseif ( $this->startsWith($line,self::CONTROL)){
            $this->currentBlock = self::CONTROL;
        }elseif(
            //exclude all tables
            $this->startsWith($line, 'TABLE')
        ){
            $this->currentBlock = self::SKIP;
        }
    }

    private function contain($line, $string)
    {
        return strpos($line, $string) !== false;
    }

    private function startsWith($line, $string)
    {
        $length = strlen($string);
        return substr($line, 0, $length) === $string;
    }

}