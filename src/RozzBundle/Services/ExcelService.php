<?php
/**
 * Created by PhpStorm.
 * User: Jivko
 * Date: 5.5.2019 г.
 * Time: 12:50
 */

namespace RozzBundle\Services;


use Symfony\Component\DependencyInjection\ContainerInterface;

class ExcelService
{

    private $phpExcel;
    private $filesDir;

    public function __construct(ContainerInterface $container)
    {
        $this->phpExcel = $container->get('phpexcel');
        $this->filesDir = $container->getParameter('exl_dir');
    }


    public function getContractsStatistics($contracts, $fileName, $userName)
    {

        $this->unlinkExcelStatFile($userName);

        /**
         * @var \PHPExcel $phpExcelObject
         * @var \PHPExcel_Writer_Excel5 $writer
         */
        $phpExcelObject = $this->phpExcel->createPHPExcelObject($this->filesDir . '\stats.xls');

        $phpExcelObject->getProperties()->setCreator("ROZZ")
            ->setLastModifiedBy("ROZZ");
//            ->setTitle("Office 2005 XLSX Test Document")
//            ->setSubject("Office 2005 XLSX Test Document")
//            ->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.")
//            ->setKeywords("office 2005 openxml php")
//            ->setCategory("Test result file");

        $resultContracts = $contracts['result'];
        if(count($resultContracts) > 0){
            $currentRow = 3;
            foreach ($resultContracts as $index => $contract){
                $phpExcelObject->setActiveSheetIndex(0)
                    ->setCellValueByColumnAndRow(0, $currentRow, $contract->getNum())
                    ->setCellValueByColumnAndRow(1, $currentRow, $contract->getHolder()->getName());

                $usedLands = $contract->getUsedArea();

                $phpExcelObject->setActiveSheetIndex(0)
                    ->mergeCellsByColumnAndRow(0, $currentRow, 0, $currentRow+ count($usedLands) - 1)
                    ->mergeCellsByColumnAndRow(1, $currentRow, 1, $currentRow+ count($usedLands) - 1);

                $totalArea = 0;
                $totalPrice = 0;
                foreach ($usedLands as $i => $usedLand){
                    $land = $usedLand->getLand();

                    $phpExcelObject->setActiveSheetIndex(0)
                        ->setCellValueByColumnAndRow(2, $currentRow, $land->getNum())
                        ->setCellValueByColumnAndRow(3, $currentRow, $land->getMest()->getName())
                        ->setCellValueByColumnAndRow(4, $currentRow, $land->getZem()->getName())
                        ->setCellValueByColumnAndRow(5, $currentRow, $land->getNtp()->getName())
                        ->setCellValueByColumnAndRow(6, $currentRow, $usedLand->getArea())
                        ->setCellValueByColumnAndRow(7, $currentRow, $usedLand->getPrice());

                    $currentRow++;
                    $totalArea += $usedLand->getArea();
                    $totalPrice += $usedLand->getPrice() * $usedLand->getArea();

                }

                $phpExcelObject->setActiveSheetIndex(0)
                    ->mergeCellsByColumnAndRow(8, $currentRow - count($usedLands), 8, $currentRow - 1)
                    ->mergeCellsByColumnAndRow(9, $currentRow - count($usedLands), 9, $currentRow - 1)
                    ->mergeCellsByColumnAndRow(10, $currentRow - count($usedLands), 10, $currentRow - 1);

                $phpExcelObject->setActiveSheetIndex(0)
                    ->setCellValueByColumnAndRow(8, $currentRow - count($usedLands), $totalArea)
                    ->setCellValueByColumnAndRow(9, $currentRow - count($usedLands), $totalPrice)
                    ->setCellValueByColumnAndRow(10, $currentRow - count($usedLands), $contract->getExpire()->format('d/m/Y'));

                $phpExcelObject->getActiveSheet()
                    ->getStyleByColumnAndRow(0, $currentRow - count($usedLands)/*, 10, $currentRow - 1*/)
                    ->getBorders()
                    ->getOutline()
                    ->setBorderStyle( \PHPExcel_Style_Border::BORDER_DOUBLE);
            }




            $phpExcelObject->getActiveSheet()->setTitle('Contracts');
            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $phpExcelObject->setActiveSheetIndex(0);

            // create the writer
            $writer = $this->phpExcel->createWriter($phpExcelObject, 'Excel5');
            $writer->save($fileName);
        }else{

            return false;
        }

    }

    private function unlinkExcelStatFile($userName)
    {
        $files = scandir($this->filesDir);
        $filePrefix = $userName.'_stats_';
        foreach ($files as $file){
            if (substr($file, 0, strlen($filePrefix)) === $filePrefix){
                unlink($this->filesDir.$file);
            }
        }
    }
}