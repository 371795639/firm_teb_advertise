<?php
namespace Admin\Controller;
class ExcelController extends AdminController {
    /**
     * 数据导出为.xls格式
     * @param string $fileName 导出的文件名
     * @param $expCellName     array -> 数据库字段以及字段的注释
     * @param $expTableData    Model -> 连接的数据库
     */
    public function exportExcel($fileName='table',$expCellName,$expTableData){
        $xlsTitle = iconv('utf-8', 'gb2312', $fileName);//文件名称
        $xlsName = $fileName.date("_Y.m.d_H.i.s"); //or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);

        import("Vendor.PHPExcel.PHPExcel");
        import("Vendor.PHPExcel.Writer.Excel5");
        import("Vendor.PHPExcel.IOFactory.php");

        $objPHPExcel = new \PHPExcel();
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');//合并单元格
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $fileName.'  Export time:'.date('Y-m-d H:i:s'));
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]);
        }
        // Miscellaneous glyphs, UTF-8
        for($i=0;$i<$dataNum;$i++){
            for($j=0;$j<$cellNum;$j++){
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+3), $expTableData[$i][$expCellName[$j][0]]);
            }
        }

        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$xlsName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    /**实现导入excel
     **/
    public function impUser(){
        if (!empty($_FILES)) {
            import("@.ORG.UploadFile");
            $config=array(
                'allowExts'=>array('xlsx','xls'),
                'savePath'=>'./Public/upload/',
                'saveRule'=>'time',
            );
            $upload = new UploadFile($config);
            if (!$upload->upload()) {
                $this->error($upload->getErrorMsg());
            } else {
                $info = $upload->getUploadFileInfo();

            }
            vendor("PHPExcel.PHPExcel");
            $file_name=$info[0]['savepath'].$info[0]['savename'];
            $objReader = PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load($file_name,$encode='utf-8');
            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow(); // 取得总行数
            $highestColumn = $sheet->getHighestColumn(); // 取得总列数
            for($i=3;$i<=$highestRow;$i++)
            {
                $data['account']= $data['truename'] = $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
                $sex = $objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
                // $data['res_id']    = $objPHPExcel->getActiveSheet()->getCell("D".$i)->getValue();
                $data['class'] = $objPHPExcel->getActiveSheet()->getCell("E".$i)->getValue();
                $data['year'] = $objPHPExcel->getActiveSheet()->getCell("F".$i)->getValue();
                $data['city']= $objPHPExcel->getActiveSheet()->getCell("G".$i)->getValue();
                $data['company']= $objPHPExcel->getActiveSheet()->getCell("H".$i)->getValue();
                $data['zhicheng']= $objPHPExcel->getActiveSheet()->getCell("I".$i)->getValue();
                $data['zhiwu']= $objPHPExcel->getActiveSheet()->getCell("J".$i)->getValue();
                $data['jibie']= $objPHPExcel->getActiveSheet()->getCell("K".$i)->getValue();
                $data['honor']= $objPHPExcel->getActiveSheet()->getCell("L".$i)->getValue();
                $data['tel']= $objPHPExcel->getActiveSheet()->getCell("M".$i)->getValue();
                $data['qq']= $objPHPExcel->getActiveSheet()->getCell("N".$i)->getValue();
                $data['email']= $objPHPExcel->getActiveSheet()->getCell("O".$i)->getValue();
                $data['remark']= $objPHPExcel->getActiveSheet()->getCell("P".$i)->getValue();
                $data['sex']=$sex=='男'?1:0;
                $data['res_id'] =1;
                $data['last_login_time']=0;
                $data['create_time']=$data['last_login_ip']=$_SERVER['REMOTE_ADDR'];
                $data['login_count']=0;
                $data['join']=0;
                $data['avatar']='';
                $data['password']=md5('123456');
                M('Member')->add($data);

            }
            $this->success('导入成功！');
        }else
        {
            $this->error("请选择上传的文件");
        }
    }

}