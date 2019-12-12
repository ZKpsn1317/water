<?php
// 本类由系统自动生成，仅供测试用途
namespace app\dlc\controller;
use think\Db;
class UploadController extends BaseController
{

    public function index()
    {
        $this->display();
    }

    public function indeximg()
    {
        //查找带回字段
        $fbid = input('fbid');
        $isall = input('isall'); 
        $issrc = input('issrc')?1:0;
        $this -> assign('issrc',$issrc);
        $this->assign('fbid', $fbid);
        $this->assign('isall', $isall);
        $page = '1,8';
        $m = Db::name('upload');
        $cache = $m->page($page)->order('id desc')->select();

        $this->assign('cache', $cache);
        return($this->fetch('indeximg'));
    }

    public function doupimg()
    {
        $files = request()->file('appfile');
        $info = $files->validate(array('size'=>20000000,'ext'=>'jpg,png,gif,jpeg'))->move(ROOT_PATH .'public'. DS . 'uploads/imgs');

        if ($info) {
            $item['name'] = $info->getInfo('name');
            $item['type'] = $info->getInfo('type');
            $item['savename'] = $info->getSaveName();
            $item['savepath'] = '/uploads/imgs/';
            $count = Db::name('upload')->insert($item)?:1;

            if ($count) {
                $backstr = "'" . $count . "张图片上传成功！'" . ',' . "true";
                echo "<script>parent.doupimgcallback(" . $backstr . ")</script>";
            } else {
                echo "<script>parent.doupimgcallback('图片保存时失败！',false)</script>";
            };

        } else {
            echo "<script>parent.doupimgcallback('" . $files->getError() . "',false)</script>";
        };


    }

    public function delimgs()
    {
        if ($this->request->isAjax()) {
            $imgid = input('ids');

            $imgModel = \app\dlc\model\Upload::get($imgid);
            if ($imgModel && $imgModel->delete()) {
                $data['status'] = 1;
                $data['msg'] = '成功删除图片！';

                $localPath =  ROOT_PATH . DS . 'public' . $imgModel->savepath . $imgModel->savename;
                is_file($localPath) && unlink($localPath);

            } else {
                $data['status'] = 0;
                $data['msg'] = '删除失败，请重试或联系管理员！';
            }
            return($data);
            // return($data, 'JSON');
        } else {
            $this->error('微专家提醒您：禁止外部访问！');
        }
    }


    public function getmoreimg()
    {

        $page = input('p') . ',8';
        $m = Db::name('upload');
        $cache = $m->page($page)->order('id desc')->select();

        if ($cache) {
            $this->assign('cache', $cache);
            return($this->fetch());//封装模板fetch并返回
        } else {
            return("");
        }

    }


    public function kindEditor()
    {
        include ROOT_PATH . 'extend' . DS . 'kindeditor' . DS . 'upload_json.php';
    }

    public function kindEditorFileManager()
    {
        include ROOT_PATH . 'extend' . DS . 'kindeditor' . DS . 'file_manager_json.php';
    }


    public function file()
    {
        $file = request()->file('file');
        if(!$file) {
            echo json_encode(array('code'=>0, 'msg'=>'请上传文件', 'data'=> ''));
        }

        $info = $file->validate(['ext'=>config('uploadFileExt')])->move(ROOT_PATH . 'public' . DS . 'uploads/imgs');

        if ($info) {
            echo json_encode(array('code'=>1, 'msg'=>'ok', 'data'=>  '/public/uploads/imgs/'.date("Ymd") .'/'.$info->getFilename()));
        } else {
            echo json_encode(array('code'=>0, 'msg'=>$file->getError() , 'data'=> ''));
        }

    }

}