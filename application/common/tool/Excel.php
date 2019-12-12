<?php
namespace app\common\tool;

/**
 
 * Class Excel
 * @package app\common\tool
 */
class Excel
{
    /**
     * 导出excel
     * @param $data
     * @param $fields
     * key = 层次结构， 值为字段名
     */
    public static function export($data, $fields)
    {
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:filename=".time().".xls");
        $teble_header = implode("\t",array_values($fields));
        $strexport = $teble_header."\r";
        foreach ($data as $row){
            foreach($fields as $key => $val){
                $key_arr = explode('|', $key);

                $variable = '$strexport .= $row';
                foreach($key_arr as $kv) {
                    $variable .= '["' . $kv . '"]';
                }
                $variable .= ';';
                eval($variable);
                $strexport.= "\t";

            }
            $strexport.="\r";
        }

        $strexport=iconv('UTF-8',"GB2312//IGNORE",$strexport);
        exit($strexport);
    }
}